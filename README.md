<div align="center">

# 🚒 N.I. Engineering Services — Enterprise Backend Platform
### High-Performance REST API, Filament 3.x Admin CMS & Fire Safety Commerce Engine

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-FFA500?style=for-the-badge&logo=filament&logoColor=white)](https://filamentphp.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)
[![Render](https://img.shields.io/badge/Render-Deployed-46E3B7?style=for-the-badge&logo=render&logoColor=white)](https://render.com/)

<p align="center">
  <b>The single authoritative core for N.I. Engineering Services & Fire Safety Platform</b><br>
  Powering corporate catalog, real e-commerce checkout, B2B quotation workflows, field service dispatch, and fire safety asset tracking across Bangladesh.
</p>

[🌐 Live API Documentation](docs/API-CONTRACT.md) • [🗄️ Database Architecture](docs/DATABASE-MIGRATION.md) • [🚀 Production Deployment Guide](docs/PRODUCTION-DEPLOYMENT-GUIDE.md) • [🛡️ Security Policy](docs/SECURITY.md)

</div>

---

## 🏛️ Platform Architecture Topology

```
                                  [ Cloudflare DNS ]
                                         │
                 ┌───────────────────────┼───────────────────────┐
                 │                       │                       │
                 ▼                       ▼                       ▼
        niengineeringbd.com     manage.niengineeringbd.com   api.niengineeringbd.com
        (Astro SSG on CDN)       (Filament 3.x Admin Panel)   (Laravel 12 REST API)
                 │                       │                       │
                 │                       └───────────┬───────────┘
                 │                                   │
                 ▼                                   ▼
        [ Browser Client ]                  [ Managed PostgreSQL ]
                 │                                   │
                 └─────────── REST API ──────────────┘
```

---

## ✨ Core Platform Capabilities

### 🛒 1. Server-Authoritative Commerce & Checkout
- **Strict Price Authority:** Browser prices are strictly ignored; prices and order subtotals are calculated directly from PostgreSQL.
- **Atomic Concurrency Protection:** `Product::lockForUpdate()` prevents overselling and race conditions during simultaneous orders.
- **Frozen Price Snapshots:** `order_items` stores frozen snapshots of product title, SKU, and unit price in ৳ BDT at the time of purchase.
- **Collision-Resistant Order IDs:** Concurrency-safe unique order number generator (`NIES-YYYYMMDD-XXXXXX`).
- **Instant WhatsApp Dispatch:** Generates formatted WhatsApp messages for instant customer order verification.

### 🎛️ 2. Filament 3.x Unified Administrative Dashboard
- **Product & Taxonomy Management:** Full BDT pricing, compare-at pricing, SKU, stock quantity, and inventory tracking flags.
- **Order Lifecycle Fulfillment:** `pending` ➔ `confirmed` ➔ `processing` ➔ `shipped` ➔ `delivered` ➔ `cancelled`.
- **B2B Quotation Management:** Sales lead tracking (`new` ➔ `contacted` ➔ `quoted` ➔ `approved` ➔ `closed`).
- **Partner Client Logos:** Dedicated showcase manager for partner enterprise logos.
- **Technical Content CMS:** Full Markdown/Rich-Text blog, project case studies, and company profile management.

### 🧯 3. Field Service & Fire Safety Asset Management
- **Field Service Requests:** Extinguisher chemical refilling, hydro pressure testing, fire alarm servicing, hydrant/pump maintenance.
- **Asset Lifecycle Tracking:** Unique asset tagging (`AST-YYYY-XXXXXX`), cylinder serial numbers, building zone locations, and automated annual refill due-date reminders.
- **Inventory Audit History:** Immutable `inventory_transactions` logging for supplier restocks, order sales, write-offs, and count adjustments.

### 🛡️ 4. Security & Idempotent Payment Architecture
- **Payment Webhook Idempotency:** Safely handles duplicate gateway webhooks with zero redundant payment records.
- **Phone-Guarded Order Tracking:** Public order tracking requires customer telephone verification to prevent enumeration attacks.
- **Zero Exposed Secrets:** Production debug mode permanently disabled (`APP_DEBUG=false`), with dynamic platform secret injection.

---

## 🔌 REST API Endpoints Overview

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `GET` | `/api/v1/health` | Service health heartbeat | Public |
| `GET` | `/api/v1/categories` | Categories list with active product counts | Public |
| `GET` | `/api/v1/products` | Equipment catalog with search, category & stock filters | Public |
| `GET` | `/api/v1/products/{slug}` | Product specifications & stock balance | Public |
| `GET` | `/api/v1/client-logos` | Brand partner logos for trust carousel | Public |
| `GET` | `/api/v1/cart` | Session-aware shopping cart (`X-Cart-Session`) | Public |
| `POST`| `/api/v1/cart/items` | Add item with stock limit validation | Public |
| `PUT` | `/api/v1/cart/items/{id}` | Update item quantity (Passing `0` deletes) | Public |
| `DELETE` | `/api/v1/cart/items/{id}` | Remove line item | Public |
| `DELETE` | `/api/v1/cart` | Flush active cart items | Public |
| `POST`| `/api/v1/orders` | Atomic transaction checkout (COD / Online) | Public |
| `GET` | `/api/v1/orders/{order_number}` | Order tracking with phone verification | Public |
| `POST`| `/api/v1/quote-requests` | Submit B2B project quotation request | Public |
| `POST`| `/api/v1/service-requests` | Submit field refilling / maintenance request | Public |
| `GET` | `/api/v1/projects` | Portfolio case studies | Public |
| `GET` | `/api/v1/blog` | Technical safety knowledge base articles | Public |
| `GET` | `/api/v1/settings` | Corporate contact info & PDF profile URL | Public |

*For complete request/response envelopes, see [docs/API-CONTRACT.md](docs/API-CONTRACT.md).*

---

## 🚀 Quick Start & Local Setup

### Prerequisites
- **PHP:** `^8.2` or `^8.3` (with `pdo`, `pdo_pgsql` or `pdo_sqlite`, `intl`, `bcmath`, `zip`)
- **Composer:** `^2.x`
- **PostgreSQL / SQLite**

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/Naz365/backend_for_Nies.git
cd backend_for_Nies
composer install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Configure your database connection in `.env`:
```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nies_production
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Run Additive Migrations & Database Seeder
```bash
# Execute strictly additive database migrations
php artisan migrate

# Seed initial catalog, categories, client logos, and admin account
php artisan db:seed
```

> **Default Admin Login:**
> - **URL:** `http://127.0.0.1:8000/manage`
> - **Email:** `admin@niengineeringbd.com`
> - **Password:** `AdminSecure#NIES2026!`

### 4. Start Development Server
```bash
php artisan serve
```
API is now running at `http://127.0.0.1:8000/api/v1`.

---

## 🐳 Docker Deployment

The repository includes a production-hardened `Dockerfile` equipped with Apache, PHP 8.3, `pdo_pgsql`, and an automated entrypoint:

```bash
# Build Docker container image
docker build -t nies-backend .

# Run container
docker run -p 8080:80 \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=your-postgres-host \
  -e DB_DATABASE=nies_production \
  -e DB_USERNAME=nies_admin \
  -e DB_PASSWORD=your_password \
  nies-backend
```

---

## 🧪 Automated Testing Suite

The repository includes a comprehensive 9-domain integration test suite verifying end-to-end commerce and database integrity:

```bash
php tests/full_integration_test.php
```

```
================================================================
   N.I. ENGINEERING SERVICES — FULL PLATFORM INTEGRATION TEST   
================================================================
  [PASS] Category Taxonomy & Product Count
  [PASS] Product Catalog with BDT Pricing & SKU
  [PASS] Server-Authoritative Cart Lifecycle
  [PASS] Atomic Checkout & Frozen Snapshot Creation
  [PASS] Payment Webhook Idempotency
  [PASS] Quote Request Submission
  [PASS] Field Service Request Lifecycle
  [PASS] Fire Safety Asset Tracking & Due Date
  [PASS] Inventory Audit Transaction Logging
================================================================
  RESULTS: 9 / 9 TESTS PASSED (100%)
================================================================
```

---

## 📚 Architectural Documentation Suite

1. 📋 [CURRENT-STATE-AUDIT.md](docs/CURRENT-STATE-AUDIT.md) — Comprehensive system inventory and component classification.
2. 📊 [MIGRATION-LEDGER.md](docs/MIGRATION-LEDGER.md) — Step-by-step migration tracking ledger.
3. 🛡️ [SECURITY.md](docs/SECURITY.md) — Security standards, threat model, and phone verification guards.
4. 🗄️ [DATABASE-MIGRATION.md](docs/DATABASE-MIGRATION.md) — Full PostgreSQL schema, table structures, and snapshot rules.
5. 🔌 [API-CONTRACT.md](docs/API-CONTRACT.md) — v1 REST API contract with request/response schemas.
6. 🚀 [PRODUCTION-DEPLOYMENT-GUIDE.md](docs/PRODUCTION-DEPLOYMENT-GUIDE.md) — Cloudflare DNS cutover, environment variables, and rollback steps.

---

## 📄 License & Governance

Proprietary software developed for **N.I. Engineering Services & Fire Safety Platform (Dhaka, Bangladesh)**. All rights reserved.
