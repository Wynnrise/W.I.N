<?php
/**
 * Wynston cron config — shared by all cron scripts
 * Uses credentials from Base/db.php (existing site DB connection)
 */

// ─── Database ────────────────────────────────────────────────
function db_connect(): PDO {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=u990588858_Property;charset=utf8mb4',
        'u990588858_Multiplex',
        'Concac1979$',
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    return $pdo;
}

// ─── Unit conversion (run at ingestion only — store metres/sqm always) ───────
function to_metres(float $value, string $unit): float {
    return ($unit === 'ft') ? round($value * 0.3048, 2) : round($value, 2);
}

function to_sqm(float $value, string $unit): float {
    return ($unit === 'sqft') ? round($value * 0.0929, 2) : round($value, 2);
}

define('COV_FRONTAGE_UNIT', 'ft');
define('COV_AREA_UNIT', 'sqft');

// ─── Eligibility thresholds (always metres — never change these) ──────────────
define('THRESHOLD_3UNIT_WIDTH',  7.5);
define('THRESHOLD_3UNIT_AREA',   200);
define('THRESHOLD_4UNIT_WIDTH',  10.0);
define('THRESHOLD_4UNIT_AREA',   306);
define('THRESHOLD_6UNIT_WIDTH',  15.1);
define('THRESHOLD_6UNIT_AREA',   557);
define('THRESHOLD_MIN_AREA',     280);

// ─── API settings ────────────────────────────────────────────
define('COV_API_BASE',     'https://opendata.vancouver.ca/api/explore/v2.1/catalog/datasets');
define('COV_DATASET_TAX',  'property-tax-report');
define('COV_API_LIMIT',    100);
define('COV_API_DELAY_MS', 200);

define('FTN_RADIUS_M', 400);

// ─── Logger ──────────────────────────────────────────────────
class CronLogger {
    private string $name;
    private string $logfile;

    public function __construct(string $name) {
        $this->name    = $name;
        $this->logfile = '/home/u990588858/domains/wynston.ca/public_html/logs/'
                       . $name . '_' . date('Y-m-d') . '.log';
        $dir = dirname($this->logfile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public function info(string $msg): void  { $this->write('INFO',  $msg); }
    public function error(string $msg): void { $this->write('ERROR', $msg); }
    public function warn(string $msg): void  { $this->write('WARN',  $msg); }

    private function write(string $level, string $msg): void {
        $line = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ' . $msg . PHP_EOL;
        file_put_contents($this->logfile, $line, FILE_APPEND);
        echo $line;
    }
}
