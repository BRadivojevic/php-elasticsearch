
<?php
require_once __DIR__.'/../vendor/autoload.php';

use App\Config\Config;
use App\Logger\LoggerFactory;
use App\Db\MsSql;
use App\Queue\FileJobStore;
use App\Queue\MsSqlJobStore;
use App\Es\ClientFactory7;
use App\Reindex\ReindexService;
use App\Utils\Normalizer;

$root = dirname(__DIR__);
$cfg = new Config($root);
$logger = LoggerFactory::make('worker.reindex', $cfg->logPath());

if ($cfg->jobStore() === 'mssql') {
    $db = new MsSql($cfg->mssql());
    $store = new MsSqlJobStore($db);
} else {
    $store = new FileJobStore($cfg->jobsFile());
}

$job = $store->nextQueued();
if (!$job) {
    $logger->info('no.jobs');
    exit(0);
}

$store->markRunning($job['id']);
$payload = $job['payload'] ?? $job;
$source = $payload['source_index'] ?? null;
$dest   = $payload['dest_index'] ?? null;
$tenant = $payload['tenant'] ?? 'default';

try {
    $client = ClientFactory7::make($cfg->esHosts(), $cfg->esUsername(), $cfg->esPassword(), $cfg->esVerify());
    $svc = new ReindexService($client, $logger, $cfg->scrollSize(), $cfg->scrollTtl());

    $svc->reindex($source, $dest, function(array $doc) {
        foreach ($doc as $k => $v) {
            if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}/', $v)) {
                $doc[$k] = Normalizer::dateOrNull($v);
            } else {
                $doc[$k] = Normalizer::nullIfEmpty($v);
            }
        }
        return $doc;
    });

    $store->markFinished($job['id'], ['source'=>$source,'dest'=>$dest,'tenant'=>$tenant]);
    $logger->info('job.done', ['job_id' => $job['id']]);
} catch (Throwable $e) {
    $store->markFailed($job['id'], $e->getMessage());
    $logger->error('job.fail', ['job_id'=>$job['id'], 'error'=>$e->getMessage()]);
    exit(1);
}
