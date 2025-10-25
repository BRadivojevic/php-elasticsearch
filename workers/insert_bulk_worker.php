
<?php
require_once __DIR__.'/../vendor/autoload.php';

use App\Config\Config;
use App\Logger\LoggerFactory;
use App\Db\MsSql;
use App\Es\ClientFactory7;
use App\Es\Bulk7;
use App\Utils\Normalizer;

function parseArgs(array $argv): array {
    $out = [];
    foreach ($argv as $arg) {
        if (strpos($arg, '=') !== false) {
            [$k,$v] = explode('=', $arg, 2);
            $out[$k] = $v;
        }
    }
    return $out;
}

$args = parseArgs(array_slice($argv, 1));
$index = $args['index'] ?? null;
$sqlFile = $args['sql'] ?? null;
$start = (int)($args['start'] ?? 1);
$end   = (int)($args['end'] ?? 10000);

$root = dirname(__DIR__);
$cfg = new Config($root);
$logger = LoggerFactory::make('worker.insert', $cfg->logPath());

if (!$index || !$sqlFile || !is_file($sqlFile)) {
    fwrite(STDERR, "Usage: workers/insert_bulk_worker.php index=<index> sql=<sql-file> start=<1> end=<10000>\n");
    exit(1);
}

$db = new MsSql($cfg->mssql());
$client = ClientFactory7::make($cfg->esHosts(), $cfg->esUsername(), $cfg->esPassword(), $cfg->esVerify());
$bulk = new Bulk7($client, $logger, $cfg->bulkChunk());

$sql = file_get_contents($sqlFile);
$params = [$start, $end];
$stmt = $db->query($sql, $params);
$iter = $db->iterateAssoc($stmt);

$dead = $cfg->deadLetterFile();
$dlh = fopen($dead, 'a');

$bulk->indexMany($index, (function() use ($iter, $logger, $dlh) {
    foreach ($iter as $row) {
        if (!isset($row['acckey']) || $row['acckey'] === null || $row['acckey'] === '') {
            fwrite($dlh, json_encode(['error'=>'missing acckey','row'=>$row])."\n");
            continue;
        }
        // Transform + normalization + idempotent id
        $row['_id'] = $row['acckey'];
        foreach ($row as $k => $v) {
            if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}/', $v)) {
                $row[$k] = Normalizer::dateOrNull($v);
            } else {
                $row[$k] = Normalizer::nullIfEmpty($v);
            }
        }
        yield $row;
    }
})(), null, function($doc, $error) use ($dlh) {
    fwrite($dlh, json_encode(['error'=>$error,'doc'=>$doc])."\n");
});

fclose($dlh);
$logger->info('insert.done', ['index'=>$index, 'start'=>$start, 'end'=>$end, 'deadletter'=>$dead]);
