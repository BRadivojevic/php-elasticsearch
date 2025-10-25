
<?php
namespace App\Db;

final class MsSql {
    private $conn;

    public function __construct(array $cfg) {
        $connectionInfo = [
            'Database' => $cfg['db'],
            'UID' => $cfg['user'],
            'PWD' => $cfg['password'],
            'TrustServerCertificate' => $cfg['encrypt'] ? '0' : '1',
            'Encrypt' => $cfg['encrypt'] ? '1' : '0',
            'CharacterSet' => 'UTF-8',
        ];
        $this->conn = sqlsrv_connect($cfg['host'], $connectionInfo);
        if (!$this->conn) {
            throw new \RuntimeException('MSSQL connect failed: '.print_r(sqlsrv_errors(), true));
        }
    }

    public function query(string $sql, array $params = []) {
        $stmt = sqlsrv_query($this->conn, $sql, $params);
        if (!$stmt) {
            throw new \RuntimeException('MSSQL query failed: '.print_r(sqlsrv_errors(), true));
        }
        return $stmt;
    }

    public function iterateAssoc($stmt): \Generator {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            yield $row;
        }
    }

    public function close(): void {
        if ($this->conn) sqlsrv_close($this->conn);
    }
}
