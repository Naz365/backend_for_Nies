# N.I. Engineering Digital Platform — Current State Audit (Phase 0 Baseline)

**Generated:** August 2026  
**Auditor:** AI Migration Specialist & Senior Architect  
**Repositories Under Audit:**  
- **Frontend:** [https://github.com/Naz365/frontend_for_Nies](https://github.com/Naz365/frontend_for_Nies) (Local: `c:\Users\pc\Desktop\project nies`)  
- **Backend:** [https://github.com/Naz365/backend_for_Nies](https://github.com/Naz365/backend_for_Nies) (Local: `c:\Users\pc\Desktop\project nies\backend`)  
**Governing Strategy:** *N.I. Engineering Services AI Agent Execution & Migration Master Plan*

---

## 1. Executive Summary

This baseline audit evaluates the architecture, data structures, security stance, commerce flows, and deployment mechanisms across both the Astro frontend and Laravel/Filament backend.

The platform currently operates with a high degree of client/server dissonance:
1. **Frontend (`frontend_for_Nies`)**: A high-performance static Astro application with Tailwind CSS and dynamic dark mode, but containing parallel client-side CMS, shopping cart, and order persistence mechanisms using browser `localStorage`.
2. **Backend (`backend_for_Nies`)**: A containerized Laravel 11 application with Filament 3 admin panel, but currently configured with ephemeral SQLite storage, destructive database deployments (`migrate:fresh`), exposed credentials, and an incomplete e-commerce data model.

---

## 2. Detailed Technical Audit by Domain

### A. Frontend Architecture (`frontend_for_Nies`)
- **Framework & Tooling**: Astro v4.x, Vite, Tailwind CSS v3.x, TypeScript.
- **Output Mode**: Static SSG (`output: 'static'`, `outDir: './docs'` targeting GitHub Pages).
- **Routing**:
  - Marketing & Information: `/`, `/about-us/`, `/products/`, `/blog/`, `/blog/[slug]`, `/contact/`, `/company-profile/`, `/projects/[slug]`
  - Commerce: `/shop/` (Client-side catalog, interactive cart drawer, cash-on-delivery checkout)
  - Admin: `/admin/` (Client-side static CMS portal protected by client passphrase)
  - Subdomain Simulation: `/subdomains/api`, `/subdomains/erp`, `/subdomains/manage`, `/subdomains/portal`, `/subdomains/shop`
- **Data Layer & Fallbacks**:
  - `src/lib/api.ts` provides fallback JSON records for Projects, Blogs, Site Settings, and Products.
  - Client-side code queries `localStorage` for dynamic articles (`nies_custom_blog_posts`), products (`nies_custom_products`), and orders (`nies_customer_orders`).

### B. Backend Architecture (`backend_for_Nies`)
- **Framework & Core**: Laravel 11.x, PHP 8.2+, Filament Admin 3.x.
- **Models & Eloquent Entities**:
  - `User.php`: Filament admin accounts.
  - `Project.php`: Portfolio engineering case studies.
  - `Product.php`: Catalog items (currently missing pricing, SKU, stock quantity, and inventory flags).
  - `BlogPost.php`: Technical articles & safety guides.
  - `Customer.php`: **Misnamed model** representing client company logos (name, logo_path, website_url) rather than real commerce customers.
  - `SiteSetting.php`: Key-value configuration for corporate contact details and PDF profile.
  - `ContactSubmission.php`: Inquiries submitted via contact forms.
- **Filament Resources**:
  - `BlogPostResource.php`, `ContactSubmissionResource.php`, `CustomerResource.php`, `ProductResource.php`, `ProjectResource.php`.

### C. Database Architecture
- **Current Database**: SQLite (`database/database.sqlite`) stored on ephemeral container disk.
- **Migrations (`database/migrations/`)**:
  - `2026_07_28_000000_create_users_table.php`
  - `2026_07_28_000001_create_projects_table.php`
  - `2026_07_28_000002_create_products_table.php` (Lacks `price`, `compare_at_price`, `stock_quantity`, `sku`)
  - `2026_07_28_000003_create_blog_posts_table.php`
  - `2026_07_28_000004_create_customers_table.php` (Acts as `client_logos`)
  - `2026_07_28_000005_create_site_settings_table.php`
  - `2026_07_28_000006_create_contact_submissions_table.php`
  - `2026_07_28_000007_create_cache_table.php`
- **Critical Deficiency**: No tables for `categories`, `orders`, `order_items`, `payments`, `carts`, `cart_items`, `quote_requests`, or true `customers`.

### D. Authentication & Security
- **Exposed Secret Key**: `APP_KEY` hardcoded into `docker-entrypoint.sh` and `render.yaml`.
- **Exposed Credentials Endpoint**: `GET /api/v1/cms-status` returns plaintext admin credentials (`admin@niengineeringbd.com` / `password123`).
- **Duplicate Admin Auth**: `routes/web.php` exposes a custom `/admin/login` form with hardcoded plaintext credentials alongside the official Filament auth portal.
- **Debug Mode**: `APP_DEBUG=true` configured in production deployment manifests (`render.yaml` and `docker-entrypoint.sh`).

### E. Commerce & Order Management
- **Current Cart & Checkout**: Runs exclusively inside client browser (`src/pages/shop.astro`) via `localStorage.getItem('nies_shopping_cart')`.
- **Order Generation**: Orders are stored in client `localStorage` (`nies_customer_orders`) and formatted into WhatsApp URL links.
- **Admin Order View**: `/admin/` in Astro frontend renders orders from the visitor's local browser storage.
- **Defect**: Backend Laravel has no checkout endpoint, no order persistence, and no validation of prices or stock availability.

### F. Media & Storage
- **Current Media Storage**: Local `public/wp-content/uploads/` directory on frontend and `storage/app/public` in backend.
- **Target Storage**: Cloudflare R2 / S3-compatible persistent object storage.

### G. Deployment & Pipeline
- **Frontend Hosting**: GitHub Pages static hosting from `./docs` branch `master`.
- **Backend Hosting**: Render Free Web Service with Docker runtime.
- **Deployment Script (`docker-entrypoint.sh`)**: Executes destructive `php artisan migrate:fresh --seed --force` on container startup, wiping database tables upon container restart.
- **Webhook Dispatch**: `DeployWebhookService.php` triggers `build-static-site` repository dispatch to GitHub Actions.

---

## 3. Key Deficiencies & Migration Targets

| # | Domain | Current State (Problematic) | Target State (Master Plan) |
|---|---|---|---|
| 1 | **Database** | SQLite on ephemeral storage | Managed PostgreSQL on persistent tier |
| 2 | **Migration Rule** | `migrate:fresh --seed` (Destructive) | `php artisan migrate --force` (Additive only) |
| 3 | **Secrets** | Hardcoded `APP_KEY` in git & manifests | Managed environment variables; `.env` untracked |
| 4 | **Auth Security** | Duplicate `/admin/login` & `/v1/cms-status` leaking passwords | Filament exclusive authentication; 2FA enabled |
| 5 | **Debug Mode** | `APP_DEBUG=true` in production | `APP_DEBUG=false` strictly enforced |
| 6 | **Product Schema** | Title/slug/description only | Price, compare_at_price, stock_quantity, SKU, category_id |
| 7 | **Customer Model** | `Customer` used for partner logos | Split: `Customer` (real users) + `ClientLogo` (brand logos) |
| 8 | **Commerce Logic** | Browser `localStorage` + WhatsApp | Server-authoritative API (`/api/v1/cart`, `/api/v1/orders`) |
| 9 | **Subdomains** | Static path simulation (`/subdomains/*`) | DNS-level routing (`api.`, `manage.`, `niengineeringbd.com`) |
| 10 | **Media Storage** | Local ephemeral storage | Cloudflare R2 / S3 persistent storage |

---

## 4. Phase 0 Acceptance Sign-off

- [x] Both Frontend and Backend repositories fully inspected.
- [x] All routes, models, migrations, controllers, services, and configs cataloged.
- [x] Security vulnerabilities and credential leaks isolated.
- [x] `docs/MIGRATION-LEDGER.md` created with complete item classifications.
- [x] No production code altered during audit.
