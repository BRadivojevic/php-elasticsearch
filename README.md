# PHP Elasticsearch Reindex Workers

Lightweight, framework-free implementation for reindexing and migrating data between Elasticsearch indices using **plain PHP + cURL**.  
Built for automation pipelines and data synchronization tasks across production environments.

---

## 🚀 Overview
This module provides a safe and modular way to reindex large Elasticsearch datasets without downtime.  
It’s used in production to manage live schema migrations, index updates, and data transformations.

**Core Capabilities**
- ⚙️ Minimal `_reindex` client wrapper for Elasticsearch API  
- 🧰 CLI scripts for full and selective reindex operations  
- 🔐 Env-based configuration for host, auth, and index definitions  
- 📊 Safe batching and job-level logging  
- 🧩 Easily embeddable in larger PHP systems or cron jobs  

---

## 🧠 Tech Stack
| Layer | Technology |
|:--|:--|
| Language | PHP 8+ |
| HTTP Client | cURL |
| Data Store | Elasticsearch 7.x / 8.x |
| Config | `.env` (dotenv) |
| Runtime | CLI / Cron-ready scripts |

---

## ⚙️ Installation & Setup

```bash
git clone https://github.com/BRadivojevic/php-elasticsearch-reindex-workers.git
cd php-elasticsearch-reindex-workers
composer install
cp .env.example .env
php bin/reindex_full.php
