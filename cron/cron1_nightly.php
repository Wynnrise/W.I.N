<?php
require_once __DIR__ . '/../config/cron_config.php';
require_once __DIR__ . '/CovLotPuller.php';
require_once __DIR__ . '/TransitPuller.php';

$log = new CronLogger('cron1_nightly');
$log->info('=== Cron 1 start ===');

try {
    $pdo = db_connect();

    $log->info('Task 1: COV R1-1 lot pull');
    $puller = new CovLotPuller($pdo, $log);
    $puller->run();

    $log->info('Task 2: TransLink FTN stop pull');
    $transit = new TransitPuller($pdo, $log);
    $transit->run();

    $log->info('Task 3: Transit proximity update');
    $proximity = new TransitProximityUpdater($pdo, $log);
    $proximity->run();

    $log->info('=== Cron 1 complete ===');

} catch (Exception $e) {
    $log->error('Fatal: ' . $e->getMessage());
}
