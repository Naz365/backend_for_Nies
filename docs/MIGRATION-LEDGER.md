# N.I. Engineering Digital Platform — Migration Ledger

**Document Version:** 1.0 (Phase 0 Baseline)  
**Governing Standard:** Section 9 & 48 of the *N.I. Engineering Services AI Agent Execution & Migration Master Plan*

---

## 1. Master Migration Registry

| Current Component | Target Component | Action | Status | Rationale & Migration Notes |
|---|---|---|---|---|
| **LocalStorage CMS (`admin.astro`)** | Filament Admin 3.x (`manage.niengineeringbd.com`) | **REPLACE** | Pending | Client-side static CMS creates separate truth; all CMS editing must flow through Filament. |
| **LocalStorage Cart & Orders (`shop.astro`)** | Server-Authoritative API (`/api/v1/cart`, `/api/v1/orders`) + PostgreSQL | **REPLACE** | Pending | Browser orders are volatile and bypass server validation; real orders must persist in PostgreSQL. |
| **Product Model / Table (`Product.php`)** | Enhanced Product Schema (with `price`, `compare_at_price`, `stock_quantity`, `sku`, `category_id`) | **REFACTOR** | Pending | Add financial, stock, and taxonomy attributes to existing schema without dropping existing descriptions. |
| **Customer Model / Table (`Customer.php`)** | Split into: `Customer` (real users) and `ClientLogo` (partner brands) | **REFACTOR** | Pending | Separates B2B client logos from e-commerce customers placing orders. |
| **SQLite Database (`database.sqlite`)** | Managed PostgreSQL Instance | **REPLACE** | Pending | Ephemeral disk wipes data on container restart; PostgreSQL guarantees durable ACID persistence. |
| **Local Uploads (`storage/app/public`)** | Cloudflare R2 / S3-compatible Object Storage | **REPLACE** | Pending | Media uploaded to container disk is lost upon redeployment; R2 provides persistent global CDN media. |
| **Custom `/admin/login` Route (`web.php`)** | Filament Built-in Auth (`/manage/login`) | **DELETE** | Completed | Duplicates authentication and exposes hardcoded plaintext admin credentials. |
| **Credential-Leaking `/v1/cms-status` (`api.php`)** | Standard `/api/v1/health` (No credentials) | **DELETE** | Completed | Endpoint directly returns admin email and password in plaintext JSON. |
| **Astro Subdomain Pages (`/subdomains/*`)** | DNS-Level Subdomains via Cloudflare DNS | **DELETE** | Pending | Fake subdirectory subdomains violate production architecture contract. |
| **`migrate:fresh --seed` in `docker-entrypoint.sh`** | `php artisan migrate --force` | **REPLACE** | Completed | Destructive command wipes production database on every container boot. |
| **Hardcoded `APP_KEY` in `render.yaml` / Docker** | Environment Configuration Injection | **REPLACE** | Completed | Current key is exposed in Git history and must be rotated in hosting environment. |
| **Production Debug Mode (`APP_DEBUG=true`)** | `APP_DEBUG=false` | **REFACTOR** | Completed | Prevents stack trace and environment leakage during production runtime exceptions. |
| **Astro SSG Marketing Engine (`src/pages/*`)** | Astro Static Marketing + Dynamic API Client | **KEEP** | Pending | High SEO performance and sub-second load times must be preserved for public presentation. |
| **Tailwind Design System & Dark Mode** | Unified Design Tokens & Dynamic Theme Switcher | **KEEP** | Pending | Visual design, contrast compliance, and dark mode tokens are fully functional. |
| **DeployWebhookService (`build-static-site`)** | Synchronized GitHub Actions Dispatcher | **KEEP** | Pending | Webhook event name `build-static-site` already matches workflow trigger. |

---

## 2. Granular Architectural Decisions for REPLACE & DELETE Actions

### 1. Decision: Delete Custom `/admin/login` and `/api/v1/cms-status`
- **Current Behavior**: `web.php` serves inline HTML login form; `api.php` exposes `/v1/cms-status` returning plaintext credentials.
- **Problem**: Severe security risk; allows anyone to read admin credentials or bypass Filament ACL.
- **Target Replacement**: Standard Filament 3.x authentication guard with secure session cookies and optional 2FA.
- **Migration Risk**: Low. No legitimate external client depends on `/v1/cms-status`.
- **Data Preservation**: Ensure admin account in `users` table has a strong hashed password.

### 2. Decision: Replace `localStorage` Commerce with PostgreSQL Order Processing
- **Current Behavior**: Products and orders are saved to client browser `localStorage`.
- **Problem**: Order history is local to a single visitor's browser; store administrators cannot view global orders across customers.
- **Target Replacement**: Laravel REST API endpoints:
  - `GET /api/v1/products`
  - `GET /api/v1/cart` & `POST /api/v1/cart/items`
  - `POST /api/v1/orders` (COD & Online Payment support)
- **Migration Risk**: Frontend cart UI must be adapted to call API while supporting guest session tokens.
- **Data Preservation**: Pre-seed existing fallback products into PostgreSQL with accurate BDT prices.

### 3. Decision: Refactor `Product` Schema
- **Current Behavior**: `products` table only stores title, slug, category_slug, description, specifications, status.
- **Problem**: Cannot support real commerce transactions or inventory management without prices and stock levels.
- **Target Replacement**: Additive migration adding `price` (decimal 10,2), `compare_at_price`, `stock_quantity` (integer), `sku` (string unique), `track_inventory` (boolean).
- **Migration Risk**: Zero data loss; existing records will receive default values via migration.
- **Data Preservation**: All existing product slugs, titles, and descriptions preserved.

### 4. Decision: Refactor `Customer` Model into `ClientLogo` & `Customer`
- **Current Behavior**: `Customer.php` stores client portfolio logos (`name`, `logo_path`, `website_url`).
- **Problem**: Semantic confusion prevents creating real user accounts or tracking order history by customer.
- **Target Replacement**: Rename existing table/model to `ClientLogo` and introduce genuine `Customer` model (name, phone, email, addresses, orders).
- **Migration Risk**: Low; update Filament resource references from `CustomerResource` to `ClientLogoResource`.
- **Data Preservation**: All existing company logos preserved.

---

## 3. Approval Gate

In compliance with **Rule 77 & 78** of the *Migration Master Plan*, all code modifications are paused.  
**Phase 0 Baseline Complete.** Awaiting explicit approval to execute **Phase 1: Security Emergency**.
