<div align="center">

# 🚒 N.I. Engineering Services — Enterprise Backend Platform
### High-Performance REST API, Filament 3.x Admin CMS & Fire Safety Commerce Engine

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-FFA500?style=for-the-badge&logo=filament&logoColor=white)](https://filamentphp.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)
[![Tests](https://img.shields.io/badge/Automated_Tests-52%2F52_PASS-success?style=for-the-badge&logo=checkmarx&logoColor=white)](tests/verify_business_logic.php)

<p align="center">
  <b>The single authoritative core for N.I. Engineering Services & Fire Safety Platform</b><br>
  Powering corporate catalog, real e-commerce checkout, B2B quotation workflows, field service dispatch, and fire safety asset tracking across Bangladesh.
</p>

[🌐 Live Storefront](https://niengineeringbd.com/) • [🎛️ Admin Dashboard](https://manage.niengineeringbd.com/) • [🔌 API Gateway](https://api.niengineeringbd.com/) • [🧪 Verification Test Suite](tests/verify_business_logic.php)

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

## 🏆 Current Backend Status (Phase 0 Audit & Phase 1 Hardening)

Following the *Phase 0 Production Reality Check & Integration Execution Specification*, the backend platform has achieved 100% test passing status and hardened enterprise security:

| Feature / System | Status | Verification & Implementation |
|---|---|---|
| **Product Category Auto-Sync** | **Active** | `Product::booted()` saving hook auto-resolves `category_slug`, `category_name`, and `slug` from `category_id` |
| **IDOR Protected Tracking** | **Hardened** | `GET /api/v1/orders/{order_number}` requires matching customer phone verification; unverified queries mask names (`K*** A***`) and hide shipping addresses |
| **Standardized JSON Envelope** | **Unified** | All public API controllers return standardized `{ "success": true, "data": ... }` envelopes |
| **CORS Policy** | **Configured** | Dedicated `config/cors.php` allowing `X-Cart-Session` header and production domain origins |
| **Route Rate Limiting** | **Enforced** | Public catalog/cart routes throttled to `60 req/min`, order/contact mutations throttled to `15 req/min` |
| **Git Working Tree Hygiene** | **Pristine** | Local SQLite runtime databases and bootstrap cache files untracked from Git index |
| **Automated Verification Suite** | **52 / 52 Passed** | 100% pass rate across 7 business logic and security domains |

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

## 🔌 Registered REST API Endpoints

All 21 endpoints are mapped under `/api/v1`:

| Method | Endpoint | Description | Rate Limit | Auth |
|---|---|---|---|---|
| `GET` | `/api/ping` | Health ping probe | — | Public |
| `GET` | `/api/v1/health` | Service health heartbeat & DB status | — | Public |
| `GET` | `/api/v1/categories` | Categories taxonomy with product counts | 60/min | Public |
| `GET` | `/api/v1/categories/{slug}` | Single category detail | 60/min | Public |
| `GET` | `/api/v1/products` | Equipment catalog with search & filters | 60/min | Public |
| `GET` | `/api/v1/products/{slug}` | Product specifications & stock balance | 60/min | Public |
| `GET` | `/api/v1/client-logos` | Brand partner logos for trust carousel | 60/min | Public |
| `GET` | `/api/v1/cart` | Session-aware shopping cart (`X-Cart-Session`) | 60/min | Public |
| `POST`| `/api/v1/cart/items` | Add item with stock limit validation | 60/min | Public |
| `PUT` | `/api/v1/cart/items/{id}` | Update item quantity (Passing `0` deletes) | 60/min | Public |
| `DELETE` | `/api/v1/cart/items/{id}` | Remove line item | 60/min | Public |
| `DELETE` | `/api/v1/cart` | Flush active cart items | 60/min | Public |
| `POST`| `/api/v1/orders` | Atomic transaction checkout (COD / Online) | 15/min | Public |
| `GET` | `/api/v1/orders/{order_number}` | Order tracking with phone verification | 60/min | Public |
| `POST`| `/api/v1/quote-requests` | Submit B2B project quotation request | 15/min | Public |
| `POST`| `/api/v1/service-requests` | Submit field refilling / maintenance request | 15/min | Public |
| `GET` | `/api/v1/service-requests/{num}`| Public service request status check | 60/min | Public |
| `POST`| `/api/v1/contact` | Submit general contact message | 15/min | Public |
| `GET` | `/api/v1/projects` | Engineering portfolio case studies | 60/min | Public |
| `GET` | `/api/v1/blog` | Technical safety knowledge base articles | 60/min | Public |
| `GET` | `/api/v1/settings` | Corporate contact info & PDF profile URL | 60/min | Public |

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

## 🧪 Automated Testing & Verification Suite

Run the comprehensive 52-assertion business logic and security verification test:

```bash
php tests/verify_business_logic.php
```

### Test Suite Output:
```text
==================================================
1. PRODUCT CATALOG TESTS: 5 Passed
2. CART SYSTEM TESTS: 6 Passed
3. CHECKOUT & AUTHORITATIVE PRICING SECURITY: 14 Passed
4. INSUFFICIENT STOCK & BOUNDARY: 2 Passed
5. ORDER STATE MACHINE & AUDIT LOGGING: 8 Passed
6. B2B QUOTE & SERVICE REQUEST: 4 Passed
7. SECURITY & SCHEMA INTEGRATION: 13 Passed
==================================================
TEST SUMMARY: 52 Passed, 0 Failed (100% Success)
==================================================
```

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

## 📄 License & Governance

Proprietary software developed for **N.I. Engineering Services & Fire Safety Platform (Dhaka, Bangladesh)**. All rights reserved.
