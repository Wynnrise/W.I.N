<?php
// ============================================================================
//  ddf_sync.php  —  REALTOR.ca DDF® API Sync
//  Syncs by property type × city to bypass 100-listing API limit
//  Cron: every 2 hours
//  0 */2 * * * /usr/local/bin/php /home/u990588858/domains/wynnstonconcierge.com/public_html/ddf_sync.php >> /tmp/ddf_sync.log 2>&1
// ============================================================================

// Allow CLI or a secret key via browser (for manual trigger)
$allowed = php_sapi_name() === 'cli'
    || ($_GET['key'] ?? '') === 'ddf_run_2024_secure';
if (!$allowed) { http_response_code(403); exit('Forbidden'); }

define('DDF_CLIENT_ID',     'tLPMhuBQrsNLoNzH6otsoaqe');
define('DDF_CLIENT_SECRET', 'aNQyHcVEGqCmPDVP4anZyptE');
define('DDF_TOKEN_URL',     'https://identity.crea.ca/connect/token');
define('DDF_API_BASE',      'https://ddfapi.realtor.ca/odata/v1/');
define('DDF_PAGE_SIZE',     100);

// ── Target cities ─────────────────────────────────────────────────────────────
$target_cities = [
    'Vancouver',
    'Burnaby',
    'West Vancouver',
    'North Vancouver',
    'Richmond',
    'Coquitlam',
    'Port Coquitlam',
];

// ── Target property types (DDF PropertySubType values, no commercial) ─────────
$target_types = [
    'Apartment',
    'Single Family',
    'Townhouse',
    'Half Duplex',
    'Duplex',
];

require_once __DIR__ . '/Base/db.php';

function log_msg(string $msg): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
}

// ── Table ─────────────────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS ddf_listings (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    listing_key      VARCHAR(30)   NOT NULL UNIQUE,
    mls_number       VARCHAR(30),
    address          VARCHAR(255),
    street_number    VARCHAR(20),
    street_name      VARCHAR(100),
    unit_number      VARCHAR(20),
    city             VARCHAR(100),
    neighborhood     VARCHAR(100),
    province         VARCHAR(50),
    postal_code      VARCHAR(15),
    country          VARCHAR(50)   DEFAULT 'Canada',
    property_type    VARCHAR(100),
    bedrooms         INT,
    bedrooms_above   INT,
    bathrooms        INT,
    bathrooms_partial INT,
    sqft             INT,
    price            DECIMAL(15,2),
    price_formatted  VARCHAR(30),
    lease_amount     DECIMAL(10,2),
    description      TEXT,
    status           VARCHAR(50)   DEFAULT 'Active',
    latitude         DECIMAL(10,7),
    longitude        DECIMAL(10,7),
    img1             VARCHAR(500),
    img2             VARCHAR(500),
    img3             VARCHAR(500),
    img4             VARCHAR(500),
    img5             VARCHAR(500),
    img6             VARCHAR(500),
    photos_count     INT           DEFAULT 0,
    photos_json      MEDIUMTEXT,
    parking          INT,
    year_built       INT,
    tax_amount       DECIMAL(10,2),
    tax_year         INT,
    zoning           VARCHAR(100),
    heating          VARCHAR(255),
    cooling          VARCHAR(255),
    building_type    VARCHAR(100),
    listed_date      DATE,
    video_url        VARCHAR(500),
    listing_url      VARCHAR(500),
    modified_at      DATETIME,
    raw_json         MEDIUMTEXT,
    synced_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_city  (city),
    INDEX idx_ptype (property_type),
    INDEX idx_price (price),
    INDEX idx_status(status),
    INDEX idx_mls   (mls_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
log_msg("✅ Table ready");

// ── Sync log table ────────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS ddf_sync_log (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    synced_at   DATETIME NOT NULL,
    duration_s  INT,
    inserted    INT DEFAULT 0,
    deleted     INT DEFAULT 0,
    status      VARCHAR(20) DEFAULT 'success',
    notes       TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$sync_start = microtime(true);

// ── Token ─────────────────────────────────────────────────────────────────────
function get_access_token(): string {
    $ch = curl_init(DDF_TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type'    => 'client_credentials',
            'client_id'     => DDF_CLIENT_ID,
            'client_secret' => DDF_CLIENT_SECRET,
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT    => 15,
    ]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code !== 200) { log_msg("❌ Token failed HTTP $http_code"); exit(1); }
    $data = json_decode($response, true);
    if (empty($data['access_token'])) { log_msg("❌ No token in response"); exit(1); }
    log_msg("✅ Got access token (expires in {$data['expires_in']}s)");
    return $data['access_token'];
}

// ── Fetch one page ────────────────────────────────────────────────────────────
function fetch_batch(string $token, string $city, string $ptype, int $skip): array {
    $city_safe  = str_replace("'", "''", $city);
    $ptype_safe = str_replace("'", "''", $ptype);
    $filter     = "City eq '{$city_safe}' and PropertySubType eq '{$ptype_safe}' and StandardStatus eq 'Active'";
    $url        = DDF_API_BASE . 'Property?' . http_build_query([
        '$filter' => $filter,
        '$top'    => DDF_PAGE_SIZE,
        '$skip'   => $skip,
    ]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token, 'Accept: application/json'],
        CURLOPT_TIMEOUT        => 45,
    ]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code !== 200) {
        log_msg("  ⚠️  HTTP $http_code for $city/$ptype skip=$skip — skipping");
        return [];
    }
    return json_decode($response, true)['value'] ?? [];
}

// ── Prepared statement ────────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    INSERT INTO ddf_listings
        (listing_key, mls_number, address, street_number, street_name, unit_number,
         city, neighborhood, province, postal_code, country, property_type,
         bedrooms, bedrooms_above, bathrooms, bathrooms_partial, sqft,
         price, price_formatted, lease_amount, description, status,
         latitude, longitude, img1, img2, img3, img4, img5, img6,
         photos_count, photos_json, parking, year_built, tax_amount, tax_year,
         zoning, heating, cooling, building_type, listed_date, video_url, listing_url, modified_at, raw_json)
    VALUES
        (:listing_key, :mls_number, :address, :street_number, :street_name, :unit_number,
         :city, :neighborhood, :province, :postal_code, :country, :property_type,
         :bedrooms, :bedrooms_above, :bathrooms, :bathrooms_partial, :sqft,
         :price, :price_formatted, :lease_amount, :description, :status,
         :latitude, :longitude, :img1, :img2, :img3, :img4, :img5, :img6,
         :photos_count, :photos_json, :parking, :year_built, :tax_amount, :tax_year,
         :zoning, :heating, :cooling, :building_type, :listed_date, :video_url, :listing_url, :modified_at, :raw_json)
    ON DUPLICATE KEY UPDATE
        mls_number=VALUES(mls_number), address=VALUES(address), city=VALUES(city),
        neighborhood=VALUES(neighborhood), province=VALUES(province),
        postal_code=VALUES(postal_code), property_type=VALUES(property_type),
        bedrooms=VALUES(bedrooms), bedrooms_above=VALUES(bedrooms_above),
        bathrooms=VALUES(bathrooms), bathrooms_partial=VALUES(bathrooms_partial),
        sqft=VALUES(sqft), price=VALUES(price), price_formatted=VALUES(price_formatted),
        lease_amount=VALUES(lease_amount), description=VALUES(description),
        status=VALUES(status), latitude=VALUES(latitude), longitude=VALUES(longitude),
        img1=VALUES(img1), img2=VALUES(img2), img3=VALUES(img3),
        img4=VALUES(img4), img5=VALUES(img5), img6=VALUES(img6),
        photos_count=VALUES(photos_count), photos_json=VALUES(photos_json),
        parking=VALUES(parking), year_built=VALUES(year_built),
        tax_amount=VALUES(tax_amount), tax_year=VALUES(tax_year),
        zoning=VALUES(zoning), heating=VALUES(heating), cooling=VALUES(cooling),
        video_url=VALUES(video_url), listing_url=VALUES(listing_url),
        building_type=VALUES(building_type), listed_date=VALUES(listed_date),
        modified_at=VALUES(modified_at), raw_json=VALUES(raw_json), synced_at=NOW()
");

// ── Main loop ─────────────────────────────────────────────────────────────────
log_msg("=== DDF Sync Started ===");
$token        = get_access_token();
$grand_total  = 0;
$combo        = 0;
$total_combos = count($target_cities) * count($target_types);

foreach ($target_cities as $city) {
    foreach ($target_types as $ptype) {
        $combo++;
        $skip        = 0;
        $batch_count = 0;

        log_msg("[$combo/$total_combos] $city — $ptype");

        while (true) {
            $listings = fetch_batch($token, $city, $ptype, $skip);
            $n        = count($listings);
            if ($n === 0) break;

            foreach ($listings as $l) {
                // ── Parse media ───────────────────────────────────────────────
                $photos = []; $video_url = null; $building_type = null;
                if (!empty($l['Media']) && is_array($l['Media'])) {
                    usort($l['Media'], fn($a,$b) => ($a['Order']??99) <=> ($b['Order']??99));
                    foreach ($l['Media'] as $m) {
                        $cat = $m['MediaCategory'] ?? '';
                        $url = $m['MediaURL']      ?? '';
                        if (!$url) continue;
                        if (in_array($cat, ['Video Tour Website','Video','Virtual Tour','Unbranded Virtual Tour'])) {
                            if (!$video_url) $video_url = $url;
                        } else {
                            $photos[] = $url;
                        }
                    }
                }

                // Extract building type from StructureType array (e.g. ["Apartment"], ["House"], ["Townhouse"])
                if (!empty($l['StructureType']) && is_array($l['StructureType'])) {
                    $st = array_filter($l['StructureType']);
                    if ($st) $building_type = implode(', ', $st);
                }
                // Also check CommonInterest for condo/strata identification
                if (empty($building_type) && !empty($l['CommonInterest'])) {
                    $building_type = $l['CommonInterest'];
                }

                // Extract listed_date from OriginalEntryTimestamp (store as DATE, not full timestamp)
                $listed_date = null;
                if (!empty($l['OriginalEntryTimestamp'])) {
                    $listed_date = date('Y-m-d', strtotime($l['OriginalEntryTimestamp']));
                }
                $photos_json = $photos ? json_encode($photos) : null;

                // ── Price ─────────────────────────────────────────────────────
                $price           = (float)($l['ListPrice'] ?? 0);
                $price_formatted = $price > 0 ? '$' . number_format($price) : 'T.B.A.';

                // ── Address ───────────────────────────────────────────────────
                $unit    = !empty($l['UnitNumber']) ? trim($l['UnitNumber']) . '-' : '';
                $address = trim($unit . ($l['StreetNumber']??'') . ' ' . ($l['StreetName']??'') . ' ' . ($l['StreetSuffix']??''));
                if (!trim($address)) $address = $l['UnparsedAddress'] ?? '';

                $heating     = implode(', ', array_filter((array)($l['Heating'] ?? [])));
                $cooling     = implode(', ', array_filter((array)($l['Cooling'] ?? [])));
                $modified_at = !empty($l['ModificationTimestamp'])
                    ? date('Y-m-d H:i:s', strtotime($l['ModificationTimestamp'])) : null;

                $stmt->execute([
                    ':listing_key'       => $l['ListingKey']                  ?? '',
                    ':mls_number'        => $l['ListingId']                   ?? '',
                    ':address'           => $address,
                    ':street_number'     => $l['StreetNumber']                ?? '',
                    ':street_name'       => $l['StreetName']                  ?? '',
                    ':unit_number'       => $l['UnitNumber']                  ?? '',
                    ':city'              => $l['City']                        ?? '',
                    ':neighborhood'      => $l['SubdivisionName']             ?? '',
                    ':province'          => $l['StateOrProvince']             ?? 'BC',
                    ':postal_code'       => $l['PostalCode']                  ?? '',
                    ':country'           => $l['Country']                     ?? 'Canada',
                    ':property_type'     => $l['PropertySubType']             ?? '',
                    ':bedrooms'          => (int)($l['BedroomsTotal']         ?? 0) ?: null,
                    ':bedrooms_above'    => (int)($l['BedroomsAboveGrade']    ?? 0) ?: null,
                    ':bathrooms'         => (int)($l['BathroomsTotalInteger'] ?? 0) ?: null,
                    ':bathrooms_partial' => (int)($l['BathroomsPartial']      ?? 0) ?: null,
                    ':sqft'              => (int)($l['LivingArea']            ?? 0) ?: null,
                    ':price'             => $price ?: null,
                    ':price_formatted'   => $price_formatted,
                    ':lease_amount'      => (float)($l['LeaseAmount']         ?? 0) ?: null,
                    ':description'       => $l['PublicRemarks']               ?? '',
                    ':status'            => $l['StandardStatus']              ?? 'Active',
                    ':latitude'          => (float)($l['Latitude']            ?? 0) ?: null,
                    ':longitude'         => (float)($l['Longitude']           ?? 0) ?: null,
                    ':img1'              => $photos[0] ?? null,
                    ':img2'              => $photos[1] ?? null,
                    ':img3'              => $photos[2] ?? null,
                    ':img4'              => $photos[3] ?? null,
                    ':img5'              => $photos[4] ?? null,
                    ':img6'              => $photos[5] ?? null,
                    ':photos_count'      => (int)($l['PhotosCount']           ?? count($photos)),
                    ':photos_json'       => $photos_json,
                    ':parking'           => (int)($l['ParkingTotal']          ?? 0) ?: null,
                    ':year_built'        => (int)($l['YearBuilt']             ?? 0) ?: null,
                    ':tax_amount'        => (float)($l['TaxAnnualAmount']     ?? 0) ?: null,
                    ':tax_year'          => (int)($l['TaxYear']               ?? 0) ?: null,
                    ':zoning'            => $l['ZoningDescription']           ?? '',
                    ':heating'           => $heating,
                    ':cooling'           => $cooling,
                    ':video_url'         => $video_url,
                    ':building_type'     => $building_type,
                    ':listed_date'       => $listed_date,
                    ':listing_url'       => $l['ListingURL']                  ?? '',
                    ':modified_at'       => $modified_at,
                    ':raw_json'          => json_encode($l),
                ]);
            }

            $batch_count += $n;
            $grand_total += $n;
            log_msg("  ✅ +$n listings (batch total: $batch_count)");

            // No more pages if less than a full page returned
            if ($n < DDF_PAGE_SIZE) break;

            // Safety cap: 500 per city/type combo to protect shared hosting
            if ($batch_count >= 500) {
                log_msg("  ⚠️  Safety cap reached for $city/$ptype");
                break;
            }

            $skip += DDF_PAGE_SIZE;
            sleep(1); // be kind to DDF API between pages
        }

        if ($batch_count === 0) log_msg("  — No listings found");
    }
}

// ── Remove stale listings not updated in 6 hours ──────────────────────────────
$deleted = $pdo->exec("DELETE FROM ddf_listings WHERE synced_at < DATE_SUB(NOW(), INTERVAL 6 HOUR)");
if ($deleted > 0) log_msg("🗑️  Removed $deleted stale listings");

// ── Write sync log ────────────────────────────────────────────────────────────
$duration = (int)(microtime(true) - $sync_start);
$pdo->prepare("INSERT INTO ddf_sync_log (synced_at, duration_s, inserted, deleted, status, notes) VALUES (NOW(), :dur, :ins, :del, 'success', :notes)")
    ->execute([':dur' => $duration, ':ins' => $grand_total, ':del' => $deleted, ':notes' => implode(', ', $target_cities)]);

log_msg("=== Sync Complete — $grand_total total listings processed in {$duration}s ===");