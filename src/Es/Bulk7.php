
<?php
namespace App\Es;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;

final class Bulk7 {
    private Client $client;
    private LoggerInterface $logger;
    private int $chunk;

    public function __construct(Client $client, LoggerInterface $logger, int $chunk = 1000) {
        $this->client = $client;
        $this->logger = $logger;
        $this->chunk = $chunk;
    }

    public function indexMany(string $index, iterable $docs, ?callable $transform = null, ?callable $onDead = null): void {
        $ops = [];
        $count = 0;
        foreach ($docs as $doc) {
            $count++;
            $body = $transform ? $transform($doc) : $doc;
            $op = ['index' => ['_index' => $index]];
            if (isset($body['_id'])) {
                $op['index']['_id'] = $body['_id'];
                unset($body['_id']);
            }
            $ops[] = $op;
            $ops[] = $body;

            if (count($ops) >= $this->chunk * 2) {
                $this->flush($ops, $onDead);
                $ops = [];
            }
        }
        if ($ops) $this->flush($ops, $onDead);
        $this->logger->info('bulk.index.done', ['count' => $count, 'index' => $index]);
    }

    private function flush(array $ops, ?callable $onDead = null): void {
        $attempts = 0;
        $maxAttempts = 4;
        while (true) {
            try {
                $resp = $this->client->bulk(['body' => $ops]);
                if (!empty($resp['errors'])) {
                    if ($onDead && isset($resp['items'])) {
                        foreach ($resp['items'] as $i => $item) {
                            $action = $item['index'] ?? null;
                            if ($action && isset($action['error'])) {
                                $doc = $ops[$i*2 + 1] ?? null;
                                $onDead($doc, $action['error']);
                            }
                        }
                    } else {
                        $this->logger->warning('bulk.errors', ['items' => $resp['items'] ?? []]);
                    }
                }
                return;
            } catch (\Throwable $e) {
                $attempts++;
                $this->logger->warning('bulk.retry', ['attempt'=>$attempts, 'error'=>$e->getMessage()]);
                if ($attempts >= $maxAttempts) {
                    $this->logger->error('bulk.giveup', ['error'=>$e->getMessage()]);
                    throw $e;
                }
                usleep(200000 * $attempts);
            }
        }
    }
}
