
<?php
require_once __DIR__.'/../vendor/autoload.php';

use App\Config\Config;
use App\Db\MsSql;
use App\Queue\FileJobStore;
use App\Queue\MsSqlJobStore;

header('Content-Type: application/json');

$root = dirname(__DIR__);
$cfg = new Config($root);

$id = $_GET['id'] ?? '';
if ($id === '') { http_response_code(400); echo json_encode(['error'=>'missing id']); exit; }

try {
    if ($cfg->jobStore() === 'mssql') {
        $db = new MsSql($cfg->mssql());
        $store = new MsSqlJobStore($db);
    } else {
        $store = new FileJobStore($cfg->jobsFile());
    }

    $row = $store->get($id);
    if (!$row) { http_response_code(404); echo json_encode(['error'=>'not found']); exit; }
    echo json_encode($row);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
