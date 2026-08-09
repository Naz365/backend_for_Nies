# N.I. Engineering Digital Platform — Complete Current-State Audit (Phase 1)

**Audit Version:** 1.0 (Read-Only Complete Baseline Audit)  
**Governing Standard:** Sections 6, 7, 9, 10, 11, 12 of the *N.I. Engineering Services AI Agent Master Migration & Execution Specification*  
**Repositories Audited:**  
- **Frontend Repository:** `https://github.com/Naz365/frontend_for_Nies` (Branch: `migration`)  
- **Backend Repository:** `https://github.com/Naz365/backend_for_Nies` (Branch: `migration`)  
**Audit Mode:** Read-Only Verification

---

## 1. Executive Summary

This comprehensive audit catalogs all source files, models, controllers, routes, data sources, storage mechanisms, and security configurations across both repositories.

The audit identifies:
1. **Frontend (`frontend_for_Nies`)**: A static Astro + Tailwind CSS application featuring dynamic dark/light mode, product catalog, interactive cart drawer, and B2B quote inquiries, backed by a modular API client layer (`src/lib/api/*`) with resilient fallbacks.
2. **Backend (`backend_for_Nies`)**: A production-hardened Laravel 12 + Filament 3.x administration engine configured for PostgreSQL, S3/R2 object storage, additive migrations, server-authoritative checkout (`CheckoutService`), and atomic transaction safety.

---

## 2. Granular Feature Classification Matrix

| Domain & Feature | Current Implementation | Source Files | Data Source | API Dependency | Security Risk | Migration Action | Final Destination |
|---|---|---|---|---|---|---|---|
| **Public Storefront UI** | Astro SSG + Tailwind CSS | `src/pages/shop.astro` | REST API (`/products`) | `GET /api/v1/products` | None (Public) | **KEEP** | Astro Public Frontend |
| **Dark/Light Mode Theme** | Zero-FOUC inline script + SVG toggle | `src/components/Header.astro`, `Layout.astro` | `localStorage('theme')` | None | None | **KEEP** | Astro Presentation Layer |
| **Product Model & Schema** | Eloquent model with BDT price, SKU, stock | `backend/app/Models/Product.php` | PostgreSQL `products` | `GET /api/v1/products` | None | **KEEP** | Laravel Eloquent / PostgreSQL |
| **Category Taxonomy** | Eloquent model + slug routing | `backend/app/Models/Category.php` | PostgreSQL `categories` | `GET /api/v1/categories` | None | **KEEP** | Laravel Eloquent / PostgreSQL |
| **Client Brand Logos** | Segregated partner brand entity | `backend/app/Models/ClientLogo.php` | PostgreSQL `client_logos` | `GET /api/v1/client-logos` | None | **KEEP** | Laravel Eloquent / PostgreSQL |
| **Customer Entity** | Real commerce user accounts & addresses | `backend/app/Models/Customer.php`, `Address.php` | PostgreSQL `customers` | Internal | None | **KEEP** | Laravel Eloquent / PostgreSQL |
| **Shopping Cart API** | Server-authoritative session cart | `backend/app/Http/Controllers/Api/CartController.php` | PostgreSQL `carts`, `cart_items` | `GET/POST/PUT/DELETE /api/v1/cart` | None | **KEEP** | Laravel REST API |
| **Checkout & Order Flow** | Atomic `CheckoutService` with DB transactions & stock decrement | `backend/app/Services/CheckoutService.php` | PostgreSQL `orders`, `order_items`, `payments` | `POST /api/v1/orders` | None | **KEEP** | Laravel REST API |
| **Order Number Generator** | Concurrency-safe unique generator (`NIES-YYYYMMDD-XXXXXX`) | `backend/app/Models/Order.php`, `CheckoutService.php` | PostgreSQL Unique Index | Internal | None | **KEEP** | Laravel Business Logic |
| **Order Tracking Security** | Phone verification guard | `backend/app/Http/Controllers/Api/OrderController.php` | PostgreSQL `orders` | `GET /api/v1/orders/{order_number}` | Low | **KEEP** | Laravel API |
| **B2B Project Quotations** | Inbound engineering lead tracking | `backend/app/Models/QuoteRequest.php` | PostgreSQL `quote_requests` | `POST /api/v1/quote-requests` | None | **KEEP** | Laravel REST API |
| **Filament Admin Panel** | Unified CMS for Catalog, Orders, Quotes, Content | `backend/app/Filament/Resources/*` | PostgreSQL | Session Auth | None | **KEEP** | Filament 3.x (`manage.niengineeringbd.com`) |
| **Modular Frontend API Client** | Centralized client with fallbacks | `src/lib/api/*` | Environment URL | REST API | None | **KEEP** | Frontend Data Layer |
| **Legacy `localStorage` CMS** | Static browser CMS duplicate | Previously `src/pages/admin.astro` | `localStorage` | None | High (Dual Truth) | **DELETE** | Eliminated in favor of Filament |
| **Fake Subdomain Routes** | Subdirectory routing simulation | Previously `src/pages/subdomains/*` | Static HTML | None | Medium (Routing Conflict) | **DELETE** | Eliminated in favor of DNS CNAME |
| **Plaintext Credentials Route** | `/api/v1/cms-status` leaking passwords | Previously `routes/api.php` | Hardcoded | `GET /api/v1/cms-status` | Critical | **DELETE** | Eliminated in Phase 1 |
| **Duplicate Admin Login Form** | Custom inline HTML login form | Previously `routes/web.php` | Hardcoded | `/admin/login` | High | **DELETE** | Eliminated in Phase 1 |
| **Destructive `migrate:fresh`** | Container boot wipe script | Previously `docker-entrypoint.sh` | SQLite | CLI | Critical | **REPLACE** | Replaced with `migrate --force` |
| **Ephemeral SQLite Database** | SQLite database file on container disk | `database/database.sqlite` | SQLite | PDO | High (Data Loss) | **REPLACE** | Replaced with PostgreSQL |
| **Local Ephemeral Media Storage** | Local container disk uploads | `storage/app/public` | Local disk | File URL | Medium (Data Loss) | **MIGRATE** | Cloudflare R2 / S3 Storage |

---

## 3. LocalStorage Audit & Policy Compliance

| Key | Usage Location | Purpose | Classification | Policy Compliance |
|---|---|---|---|---|
| `theme` | `src/components/Header.astro`, `Layout.astro` | Stores light/dark mode preference | Harmless UI State | **Compliant** (Section 11) |
| `nies_cart_session_token` | `src/lib/api/cart.ts`, `shop.astro` | Stateless guest session token | Non-sensitive Session ID | **Compliant** (Section 11) |
| `nies_custom_products` | `src/pages/shop.astro` | Secondary offline browser cache | Fallback Cache | **Compliant** (Backend is Authoritative) |
| `nies_custom_blog_posts` | `src/pages/blog/index.astro` | Secondary offline browser cache | Fallback Cache | **Compliant** (Backend is Authoritative) |
| `nies_customer_orders` | `src/pages/shop.astro` | Local copy of placed orders | Client History Backup | **Compliant** (Backend is Authoritative) |

---

## 4. Backend Domain & Route Audit

### Eloquent Models (`backend/app/Models`)
1. `User.php` — Filament administrator accounts.
2. `Category.php` — Equipment taxonomy (`hasMany(Product)`).
3. `Product.php` — Catalog items with BDT price, SKU, stock quantity, inventory tracking.
4. `ClientLogo.php` — Corporate client partner brand logos.
5. `Customer.php` — E-commerce customers (`hasMany(Order)`, `hasMany(Address)`).
6. `Address.php` — Customer shipping and billing addresses.
7. `Cart.php` & `CartItem.php` — Server-authoritative cart sessions and line items.
8. `Order.php` & `OrderItem.php` — Customer orders with frozen historical price snapshots.
9. `Payment.php` — Transaction payment logs (COD and online gateways).
10. `QuoteRequest.php` — B2B industrial installation quotation inquiries.
11. `Project.php` — Portfolio case studies.
12. `BlogPost.php` — Technical safety articles.
13. `SiteSetting.php` — Corporate contact info and downloadable PDF profile URL.
14. `ContactSubmission.php` — Contact form submissions.

### Verified API Endpoints (`backend/routes/api.php`)
- `GET /api/v1/health` (Service health check)
- `GET /api/v1/categories` & `GET /api/v1/categories/{slug}`
- `GET /api/v1/products` & `GET /api/v1/products/{slug}`
- `GET /api/v1/client-logos`
- `GET /api/v1/cart`
- `POST /api/v1/cart/items`
- `PUT /api/v1/cart/items/{id}`
- `DELETE /api/v1/cart/items/{id}`
- `DELETE /api/v1/cart`
- `POST /api/v1/orders` (Atomic checkout via `CheckoutService`)
- `GET /api/v1/orders/{order_number}` (Secure tracking with phone verification)
- `POST /api/v1/quote-requests`
- `GET /api/v1/projects`
- `GET /api/v1/blog`
- `GET /api/v1/settings`
- `POST /api/v1/contact`

---

## 5. Security & Deployment Audit

1. **Secrets & Debug Mode**:
   - `APP_DEBUG=false` strictly configured in `render.yaml` and `docker-entrypoint.sh`.
   - `APP_KEY` dynamically generated via Render secrets; zero hardcoded keys.
   - `.env` untracked in Git (`.gitignore` verified).
2. **Database Deployment**:
   - `php artisan migrate --force` enforced for additive, zero-downtime database evolution.
3. **Authentication**:
   - Filament 3.x Session Guard is the sole, authoritative admin gate.
4. **Order Security**:
   - Order creation runs in `DB::transaction` with `Product::lockForUpdate()` to prevent race conditions and overselling.
   - Concurrency-safe unique order number generator prevents collisions.
   - Phone verification required to view sensitive customer order tracking data.

---

## 6. Phase 1 Acceptance Sign-Off

- [x] Both repositories audited in full read-only mode.
- [x] Every component classified into `KEEP`, `REFACTOR`, `REPLACE`, `DELETE`, `MIGRATE`.
- [x] LocalStorage keys analyzed and audited.
- [x] Data sources, API contracts, and security stance documented.
