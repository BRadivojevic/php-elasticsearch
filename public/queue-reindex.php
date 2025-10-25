
<?php
require_once __DIR__.'/../vendor/autoload.php';

use App\Config\Config;
use App\Logger\LoggerFactory;
use App\Db\MsSql;
use App\Queue\FileJobStore;
use App\Queue\MsSqlJobStore;

header('Content-Type: application/json');

$root = dirname(__DIR__);
$cfg = new Config($root);
$logger = LoggerFactory::make('http', $cfg->logPath());

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true) ?: [];

$source = $payload['source_index'] ?? null;
$dest   = $payload['dest_index'] ?? null;
$tenant = $payload['tenant'] ?? 'default';

if (!$source || !$dest) {
    http_response_code(400);
    echo json_encode(['error' => 'source_index and dest_index required']);
    exit;
}

try {
    if ($cfg->jobStore() === 'mssql') {
        $db = new MsSql($cfg->mssql());
        $store = new MsSqlJobStore($db);
    } else {
        $store = new FileJobStore($cfg->jobsFile());
    }

    $jobId = $store->enqueue([
        'type' => 'reindex',
        'source_index' => $source,
        'dest_index' => $dest,
        'tenant' => $tenant,
    ]);

    $logger->info('enqueue.ok', ['job_id' => $jobId, 'source' => $source, 'dest' => $dest, 'tenant' => $tenant]);
    echo json_encode(['job_id' => $jobId]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
