
<?php
namespace App\Queue;

use App\Db\MsSql;

final class MsSqlJobStore implements JobStoreInterface {
    private MsSql $db;

    public function __construct(MsSql $db) { $this->db = $db; }

    public function enqueue(array $job): string {
        $id = bin2hex(random_bytes(8));
        $sql = "INSERT INTO _jobs (id, type, payload, status, created_at) VALUES (?, ?, ?, 'queued', SYSDATETIME())";
        $this->db->query($sql, [$id, $job['type'] ?? 'reindex', json_encode($job)]);
        return $id;
    }

    public function nextQueued(): ?array {
        $sql = "SELECT TOP 1 id, type, payload, status, created_at, started_at, finished_at, last_error
                FROM _jobs WITH (ROWLOCK, READPAST)
                WHERE status = 'queued'
                ORDER BY created_at ASC";
        $stmt = $this->db->query($sql);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if (!$row) return null;
        $row['payload'] = json_decode($row['payload'] ?? "{}", true);
        return $row;
    }

    public function markRunning(string $id): void {
        $this->db->query("UPDATE _jobs SET status='running', started_at=SYSDATETIME() WHERE id=?", [$id]);
    }

    public function markFinished(string $id, array $meta = []): void {
        $this->db->query("UPDATE _jobs SET status='finished', finished_at=SYSDATETIME(), meta=? WHERE id=?", [json_encode($meta), $id]);
    }

    public function markFailed(string $id, string $error): void {
        $this->db->query("UPDATE _jobs SET status='failed', finished_at=SYSDATETIME(), last_error=? WHERE id=?", [$error, $id]);
    }

    public function get(string $id): ?array {
        $stmt = $this->db->query("SELECT id, type, payload, status, created_at, started_at, finished_at, last_error, meta FROM _jobs WHERE id=?", [$id]);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if (!$row) return null;
        $row['payload'] = json_decode($row['payload'] ?? "{}", true);
        $row['meta'] = json_decode($row['meta'] ?? "{}", true);
        return $row;
    }
}
