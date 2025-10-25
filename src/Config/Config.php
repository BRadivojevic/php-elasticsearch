
<?php
namespace App\Config;

use Dotenv\Dotenv;

final class Config {
    private array $env;

    public function __construct(string $rootDir) {
        if (is_file($rootDir.'/.env')) {
            $dotenv = Dotenv::createImmutable($rootDir);
            $dotenv->load();
        }
        $this->env = $_ENV + $_SERVER;
    }

    public function esHosts(): array {
        return array_map('trim', explode(',', $this->env['ES_HOSTS'] ?? 'http://localhost:9200'));
    }
    public function esUsername(): ?string { return $this->env['ES_USERNAME'] ?? null; }
    public function esPassword(): ?string { return $this->env['ES_PASSWORD'] ?? null; }
    public function esVerify(): bool { return filter_var($this->env['ES_SSL_VERIFY'] ?? 'false', FILTER_VALIDATE_BOOLEAN); }

    public function mssql(): array {
        return [
            'host' => $this->env['MSSQL_HOST'] ?? 'localhost',
            'db' => $this->env['MSSQL_DB'] ?? '',
            'user' => $this->env['MSSQL_USER'] ?? '',
            'password' => $this->env['MSSQL_PASSWORD'] ?? '',
            'encrypt' => filter_var($this->env['MSSQL_ENCRYPT'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
        ];
    }

    public function appEnv(): string { return $this->env['APP_ENV'] ?? 'dev'; }
    public function logPath(): string { return $this->env['APP_LOG'] ?? __DIR__.'/../../var/app.log'; }
    public function bulkChunk(): int { return (int)($this->env['BULK_CHUNK_SIZE'] ?? 1000); }
    public function scrollSize(): int { return (int)($this->env['SCROLL_SIZE'] ?? 1000); }
    public function scrollTtl(): string { return $this->env['SCROLL_TTL'] ?? '2m'; }
    public function batchSizeRows(): int { return (int)($this->env['BATCH_SIZE_ROWS'] ?? 10000); }
    public function deadLetterFile(): string { return $this->env['DEADLETTER_FILE'] ?? __DIR__.'/../../var/deadletter.jsonl'; }

    public function jobStore(): string { return $this->env['JOB_STORE'] ?? 'file'; }
    public function jobsFile(): string { return $this->env['JOBS_FILE'] ?? __DIR__.'/../../var/jobs.jsonl'; }
}
