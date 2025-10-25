
# Elasticsearch 7 Reindex & Insert Workers (MSSQL + Background Jobs) — acckey idempotency

Production-grade PHP project for indexing **very large** SQL Server datasets into **Elasticsearch 7.17.13**, with:
- **Server-side batching** via `ROW_NUMBER()` / `OFFSET ... FETCH`
- **Background workers** to avoid Cloudflare 524/534 timeouts
- **Idempotent upserts using `acckey` as `_id`**
- **Dead-letter** logging for malformed/missing IDs
- **JSON logging** and clean normalization (dates/diacritics)

Author: **Boško Radivojević** — [BRadivojevic](https://github.com/BRadivojevic)

## Setup (no terminal needed)
1. Open in PhpStorm/VS Code → use Composer GUI to install.
2. Copy `.env.example` → `.env` and set ES + MSSQL.
3. Serve `public/` with your IDE.
4. POST JSON to `queue-reindex.php` to enqueue; run `workers/reindex_worker.php` from IDE to process.

## Insert from MSSQL → ES (with `acckey` idempotency)
- Put SQL in `examples/select_batch_row_number.sql` (uses `?` for `start,end`).
- Run `workers/insert_bulk_worker.php` from IDE with Program arguments:
  `index=your_index sql=examples/select_batch_row_number.sql start=1 end=10000`

© 2025 Boško Radivojević. MIT License.
