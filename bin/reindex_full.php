<?php
require __DIR__.'/../vendor/autoload.php';
$host = getenv('ELASTICSEARCH_HOST') ?: 'http://localhost:9200';
$user = getenv('ELASTICSEARCH_USER') ?: null;
$pass = getenv('ELASTICSEARCH_PASS') ?: null;
$source = getenv('SOURCE_INDEX') ?: 'source';
$target = getenv('TARGET_INDEX') ?: 'target';

$client = new App\Es\Client($host, $user, $pass);
[$code, $res, $err] = $client->reindex($source, $target);
echo "HTTP $code\n$err\n$res\n";
