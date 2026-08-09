# N.I. Engineering Digital Platform — Migration Ledger

**Document Version:** 1.0 (Phase 3 Admin Catalog Vertical Slice Completed)  
**Governing Standard:** Section 9 & 48 of the *N.I. Engineering Services AI Agent Execution & Migration Master Plan*

---

## 1. Master Migration Registry

| Current Component | Target Component | Action | Status | Rationale & Migration Notes |
|---|---|---|---|---|
| **LocalStorage CMS (`admin.astro`)** | Filament Admin 3.x (`manage.niengineeringbd.com`) | **DELETE** | Completed | Client-side static CMS removed; all administration consolidated into official Filament panel. |
| **LocalStorage Cart & Orders (`shop.astro`)** | Server-Authoritative API (`/api/v1/cart`, `/api/v1/orders`) + PostgreSQL | **REPLACE** | Completed | Connected checkout form directly to backend REST order API with transaction and stock safety. |
| **Product Model / Table (`Product.php`)** | Enhanced Product Schema (with `price`, `compare_at_price`, `stock_quantity`, `sku`, `category_id`) | **REFACTOR** | Completed | Add financial, stock, and taxonomy attributes to existing schema without dropping existing descriptions. |
| **Customer Model / Table (`Customer.php`)** | Split into: `Customer` (real users) and `ClientLogo` (partner brands) | **REFACTOR** | Completed | Separates B2B client logos from e-commerce customers placing orders. |
| **SQLite Database (`database.sqlite`)** | Managed PostgreSQL Instance | **REPLACE** | Completed | PostgreSQL driver configured with additive migrations; schema ready for zero-loss deployment. |
| **Local Uploads (`storage/app/public`)** | Cloudflare R2 / S3-compatible Object Storage | **REPLACE** | Completed | Configured filesystems.php disk for Cloudflare R2 and AWS S3 persistent object storage. |
| **Custom `/admin/login` Route (`web.php`)** | Filament Built-in Auth (`/manage/login`) | **DELETE** | Completed | Duplicates authentication and exposes hardcoded plaintext admin credentials. |
| **Credential-Leaking `/v1/cms-status` (`api.php`)** | Standard `/api/v1/health` (No credentials) | **DELETE** | Completed | Endpoint directly returns admin email and password in plaintext JSON. |
| **Astro Subdomain Pages (`/subdomains/*`)** | DNS-Level Subdomains via Cloudflare DNS | **DELETE** | Completed | Removed fake Astro subdirectory pages and middleware; real DNS routing configured. |
| **`migrate:fresh --seed` in `docker-entrypoint.sh`** | `php artisan migrate --force` | **REPLACE** | Completed | Destructive command wipes production database on every container boot. |
| **Hardcoded `APP_KEY` in `render.yaml` / Docker** | Environment Configuration Injection | **REPLACE** | Completed | Current key is exposed in Git history and must be rotated in hosting environment. |
| **Production Debug Mode (`APP_DEBUG=true`)** | `APP_DEBUG=false` | **REFACTOR** | Completed | Prevents stack trace and environment leakage during production runtime exceptions. |
| **Astro SSG Marketing Engine (`src/pages/*`)** | Astro Static Marketing + Modular Dynamic API Client (`src/lib/api/*`) | **KEEP** | Completed | High SEO performance and sub-second load times preserved with resilient client-side product pipeline. |
| **Tailwind Design System & Dark Mode** | Unified Design Tokens & Dynamic Theme Switcher | **KEEP** | Completed | Visual design, contrast compliance, and dark mode tokens are fully functional. |
| **DeployWebhookService (`build-static-site`)** | Synchronized GitHub Actions Dispatcher | **KEEP** | Completed | Webhook event name `build-static-site` already matches workflow trigger. |

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

In compliance with **Rule 80** of the *Migration Master Plan*, Phase 3 has completed.  
**Phase 3 Baseline Complete.** Awaiting explicit approval to execute **Phase 4: Cart Behavior**.
