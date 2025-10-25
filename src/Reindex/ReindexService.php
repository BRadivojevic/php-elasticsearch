
<?php
namespace App\Reindex;

use App\Es\Bulk7;
use Elasticsearch\Client;
use Psr\Log\LoggerInterface;

final class ReindexService {
    private Client $client;
    private LoggerInterface $logger;
    private int $scrollSize;
    private string $scrollTtl;

    public function __construct(Client $client, LoggerInterface $logger, int $scrollSize = 1000, string $scrollTtl = '2m') {
        $this->client = $client;
        $this->logger = $logger;
        $this->scrollSize = $scrollSize;
        $this->scrollTtl = $scrollTtl;
    }

    public function reindex(string $sourceIndex, string $destIndex, ?callable $transform = null): void {
        $resp = $this->client->search([
            'index' => $sourceIndex,
            'scroll' => $this->scrollTtl,
            'size' => $this->scrollSize,
            'body' => ['query' => ['match_all' => (object)[]]],
        ]);
        $scrollId = $resp['_scroll_id'] ?? null;
        $hits = $resp['hits']['hits'] ?? [];

        $bulk = new Bulk7($this->client, $this->logger);
        $count = 0;

        while (!empty($hits)) {
            $docs = array_map(function($h) {
                $src = $h['_source'] ?? [];
                if (isset($h['_id'])) $src['_id'] = $h['_id'];
                return $src;
            }, $hits);
            $bulk->indexMany($destIndex, $docs, $transform);
            $count += count($docs);
            $this->logger->info('reindex.batch', ['count_total' => $count]);

            $resp = $this->client->scroll(['scroll' => $this->scrollTtl, 'scroll_id' => $scrollId]);
            $scrollId = $resp['_scroll_id'] ?? null;
            $hits = $resp['hits']['hits'] ?? [];
        }

        if ($scrollId) {
            try { $this->client->clearScroll(['scroll_id' => $scrollId]); } catch (\Throwable $e) {}
        }
        $this->logger->info('reindex.done', ['source' => $sourceIndex, 'dest' => $destIndex, 'count' => $count]);
    }
}
