# PHP Elasticsearch Reindex Workers

Tiny, env-driven examples for reindexing data between Elasticsearch indices using plain PHP + cURL.

## Features
- Minimal _reindex client
- CLI script for full reindex
- Env-based configuration (host, auth, indices)

## Quick Start
cp .env.example .env
composer install
php bin/reindex_full.php
