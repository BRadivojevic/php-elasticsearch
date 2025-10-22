# PHP Elasticsearch Reindex Workers
Tiny, env-driven CLI example using `_reindex`.

## Run
```bash
cp .env.example .env
composer install
php bin/reindex_full.php

## C) Commit & push
```bash
git add .
git commit -m "Initial: env-driven ES reindex CLI"
git push -u origin main
