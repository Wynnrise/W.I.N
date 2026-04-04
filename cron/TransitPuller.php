<?php
/**
 * TransitPuller — pulls TransLink FTN stops from GTFS feed → transit_stops
 * TransitProximityUpdater — updates transit_proximate + nearest_ftn_stop_m
 * on every lot in plex_properties using Haversine distance
 *
 * TransLink GTFS: https://gtfs.translink.ca/v2/gtfs
 * The GTFS feed is a ZIP containing stops.txt among other files.
 * FTN routes are identified by route type (3 = bus, 1 = subway/rail, 0 = tram)
 * and route short name patterns known to be FTN.
 *
 * Simplified approach for launch: pull all stops from GTFS,
 * flag as FTN = 1 (filter to known FTN routes in Session 04 logic layer).
 * Proximity check uses Haversine in PHP — no PostGIS needed.
 */

require_once __DIR__ . '/../config/cron_config.php';

class TransitPuller {
    private PDO $pdo;
    private CronLogger $log;

    // Known TransLink FTN route short names (bus rapid + rail)
    // Source: TransLink FTN map — update as network changes
    private array $ftn_routes = [
        // SkyTrain lines
        'EXPO', 'MILL', 'CANA',
        // RapidBus routes (R-series)
        'R1', 'R2', 'R3', 'R4', 'R5', 'R6',
        // B-Line routes
        '41', '43', '49', '99', '98',
        // SeaBus
        'SEAB',
    ];

    public function __construct(PDO $pdo, CronLogger $log) {
        $this->pdo = $pdo;
        $this->log = $log;
    }

    public function run(): void {
        $this->log->info('TransitPuller: downloading GTFS feed');

        $zip_path = sys_get_temp_dir() . '/translink_gtfs.zip';
        $gtfs_url = 'https://gtfs.translink.ca/v2/gtfs';

        // Download the GTFS zip
        $zip_data = @file_get_contents($gtfs_url);
        if ($zip_data === false) {
            $this->log->error('TransitPuller: failed to download GTFS feed');
            return;
        }

        file_put_contents($zip_path, $zip_data);
        $this->log->info('TransitPuller: GTFS downloaded (' . round(strlen($zip_data) / 1024) . ' KB)');

        // Extract stops.txt from zip
        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== true) {
            $this->log->error('TransitPuller: failed to open GTFS zip');
            return;
        }

        $stops_csv = $zip->getFromName('stops.txt');
        $zip->close();
        @unlink($zip_path);

        if ($stops_csv === false) {
            $this->log->error('TransitPuller: stops.txt not found in GTFS zip');
            return;
        }

        $this->processStops($stops_csv);
    }

    private function processStops(string $csv): void {
        $lines  = explode("\n", trim($csv));
        $header = str_getcsv(array_shift($lines));
        $header = array_map('trim', $header);

        // Map column names
        $col = array_flip($header);
        $required = ['stop_id', 'stop_name', 'stop_lat', 'stop_lon'];
        foreach ($required as $field) {
            if (!isset($col[$field])) {
                $this->log->error("TransitPuller: missing column $field in stops.txt");
                return;
            }
        }

        $sql = "
            INSERT INTO transit_stops (stop_id, stop_name, lat, lng, is_ftn, city, updated_at)
            VALUES (:stop_id, :stop_name, :lat, :lng, 1, 'Vancouver', NOW())
            ON DUPLICATE KEY UPDATE
                stop_name  = VALUES(stop_name),
                lat        = VALUES(lat),
                lng        = VALUES(lng),
                updated_at = NOW()
        ";
        $stmt = $this->pdo->prepare($sql);

        $upserted = 0;
        $skipped  = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $fields = str_getcsv($line);
            if (count($fields) < count($header)) continue;

            $stop_id   = trim($fields[$col['stop_id']]   ?? '');
            $stop_name = trim($fields[$col['stop_name']] ?? '');
            $lat       = (float)($fields[$col['stop_lat']] ?? 0);
            $lng       = (float)($fields[$col['stop_lon']] ?? 0);

            if (empty($stop_id) || $lat == 0 || $lng == 0) {
                $skipped++;
                continue;
            }

            // Filter to Vancouver metro bounding box
            // Vancouver lat: 49.18–49.35, lng: -123.28 to -122.95
            if ($lat < 49.18 || $lat > 49.35 || $lng < -123.28 || $lng > -122.95) {
                $skipped++;
                continue;
            }

            try {
                $stmt->execute([
                    ':stop_id'   => $stop_id,
                    ':stop_name' => $stop_name,
                    ':lat'       => $lat,
                    ':lng'       => $lng,
                ]);
                $upserted++;
            } catch (Exception $e) {
                $this->log->warn("TransitPuller: failed on stop $stop_id");
                $skipped++;
            }
        }

        $this->log->info("TransitPuller: $upserted stops upserted, $skipped skipped");
    }
}


class TransitProximityUpdater {
    private PDO $pdo;
    private CronLogger $log;

    public function __construct(PDO $pdo, CronLogger $log) {
        $this->pdo = $pdo;
        $this->log = $log;
    }

    public function run(): void {
        $this->log->info('TransitProximityUpdater: loading FTN stops');

        // Load all FTN stops into memory (small dataset, fast lookup)
        $stops = $this->pdo
            ->query("SELECT stop_id, stop_name, lat, lng FROM transit_stops WHERE is_ftn = 1")
            ->fetchAll();

        if (empty($stops)) {
            $this->log->warn('TransitProximityUpdater: no FTN stops found — skipping');
            return;
        }

        $this->log->info('TransitProximityUpdater: ' . count($stops) . ' FTN stops loaded');

        // Load all lots that have lat/lng
        $lots = $this->pdo
            ->query("SELECT id, pid, lat, lng FROM plex_properties WHERE lat IS NOT NULL AND lng IS NOT NULL")
            ->fetchAll();

        $this->log->info('TransitProximityUpdater: ' . count($lots) . ' lots to check');

        $sql = "
            UPDATE plex_properties
            SET transit_proximate    = :proximate,
                nearest_ftn_stop_m   = :distance_m,
                updated_at           = NOW()
            WHERE id = :id
        ";
        $stmt = $this->pdo->prepare($sql);

        $updated = 0;
        foreach ($lots as $lot) {
            [$nearest_dist, $nearest_stop] = $this->findNearest(
                (float)$lot['lat'],
                (float)$lot['lng'],
                $stops
            );

            $proximate  = ($nearest_dist !== null && $nearest_dist <= FTN_RADIUS_M) ? 1 : 0;
            $distance_m = $nearest_dist !== null ? (int)round($nearest_dist) : null;

            $stmt->execute([
                ':id'         => $lot['id'],
                ':proximate'  => $proximate,
                ':distance_m' => $distance_m,
            ]);
            $updated++;
        }

        $this->log->info("TransitProximityUpdater: $updated lots updated");
    }

    /**
     * Haversine distance — returns nearest stop distance in metres
     * No PostGIS required.
     */
    private function findNearest(float $lot_lat, float $lot_lng, array $stops): array {
        $min_dist = PHP_INT_MAX;
        $min_stop = null;

        foreach ($stops as $stop) {
            $dist = $this->haversine(
                $lot_lat, $lot_lng,
                (float)$stop['lat'], (float)$stop['lng']
            );
            if ($dist < $min_dist) {
                $min_dist = $dist;
                $min_stop = $stop;
            }
        }

        return [$min_dist === PHP_INT_MAX ? null : $min_dist, $min_stop];
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float {
        $R  = 6371000; // Earth radius in metres
        $dL = deg2rad($lat2 - $lat1);
        $dl = deg2rad($lng2 - $lng1);
        $a  = sin($dL/2) * sin($dL/2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dl/2) * sin($dl/2);
        $c  = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }
}
