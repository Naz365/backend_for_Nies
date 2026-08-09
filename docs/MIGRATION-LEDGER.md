# N.I. Engineering Digital Platform — Master Migration Ledger

**Ledger Version:** 1.0 (Phase 1 Complete Current-State Audit)  
**Governing Standard:** Section 8, 10, 11 of the *N.I. Engineering Services AI Agent Master Migration & Execution Specification*  
**Active Working Branch:** `migration`

---

## 1. Master Migration Ledger Entries

### Entry 01: Product Model & Catalog Architecture
- **Feature:** Product Catalog & Inventory
- **Current state:** Eloquent Model with pricing (৳ BDT), SKU, compare-at price, stock quantity, inventory tracking, and category relationships.
- **Target state:** PostgreSQL single authoritative truth consumed via `GET /api/v1/products`.
- **Files affected:** `backend/app/Models/Product.php`, `backend/app/Filament/Resources/ProductResource.php`, `src/lib/api/products.ts`.
- **Database affected:** PostgreSQL `products` table.
- **API affected:** `GET /api/v1/products`, `GET /api/v1/products/{slug}`.
- **Migration status:** `COMPLETED`
- **Tests:** Static build validation & Filament CRUD tests.
- **Rollback:** Retain legacy columns; default values prevent breaking changes.

### Entry 02: Taxonomy & Category Domain
- **Feature:** Category Taxonomy
- **Current state:** Dedicated `categories` table with slug uniqueness and product count aggregation.
- **Target state:** Filament managed taxonomy powering storefront filtering.
- **Files affected:** `backend/app/Models/Category.php`, `backend/app/Filament/Resources/CategoryResource.php`, `src/lib/api/categories.ts`.
- **Database affected:** PostgreSQL `categories` table.
- **API affected:** `GET /api/v1/categories`, `GET /api/v1/categories/{slug}`.
- **Migration status:** `COMPLETED`
- **Tests:** Live count aggregation tests.
- **Rollback:** Drop `categories` foreign key constraint in `products`.

### Entry 03: Corporate Client Logos vs Customer Separation
- **Feature:** Partner Logos & Commerce Customers
- **Current state:** `ClientLogo` model manages brand showcase logos; `Customer` + `Address` models manage genuine commerce accounts.
- **Target state:** Distinct database tables and admin resources for marketing partners vs buyers.
- **Files affected:** `backend/app/Models/ClientLogo.php`, `backend/app/Models/Customer.php`, `backend/app/Models/Address.php`, `backend/app/Filament/Resources/ClientLogoResource.php`.
- **Database affected:** PostgreSQL `client_logos`, `customers`, `addresses`.
- **API affected:** `GET /api/v1/client-logos`.
- **Migration status:** `COMPLETED`
- **Tests:** Data preservation migration tested with zero record loss.
- **Rollback:** Retain table data; restore legacy model alias if needed.

### Entry 04: Server-Authoritative Shopping Cart
- **Feature:** Shopping Cart
- **Current state:** Server cart API supporting guest `X-Cart-Session` UUID tokens with server-side price calculations and stock limits.
- **Target state:** Pure server authority with zero client-dictated pricing.
- **Files affected:** `backend/app/Models/Cart.php`, `backend/app/Models/CartItem.php`, `backend/app/Http/Controllers/Api/CartController.php`, `src/lib/api/cart.ts`.
- **Database affected:** PostgreSQL `carts`, `cart_items`.
- **API affected:** `GET/POST/PUT/DELETE /api/v1/cart`.
- **Migration status:** `COMPLETED`
- **Tests:** Stock overflow validation & guest session persistence tests.
- **Rollback:** Fall back to client cart cache if offline.

### Entry 05: Atomic Checkout & Order Processing
- **Feature:** Checkout & Orders
- **Current state:** `CheckoutService` running inside `DB::transaction` with `Product::lockForUpdate()`, concurrency-safe unique order IDs (`NIES-YYYYMMDD-XXXXXX`), and frozen historical price snapshots.
- **Target state:** Real-time inventory decrement, COD logging, and instant WhatsApp dispatch confirmation.
- **Files affected:** `backend/app/Services/CheckoutService.php`, `backend/app/Http/Controllers/Api/OrderController.php`, `backend/app/Models/Order.php`, `backend/app/Models/OrderItem.php`, `backend/app/Models/Payment.php`, `src/pages/shop.astro`.
- **Database affected:** PostgreSQL `orders`, `order_items`, `payments`.
- **API affected:** `POST /api/v1/orders`, `GET /api/v1/orders/{order_number}`.
- **Migration status:** `COMPLETED`
- **Tests:** Concurrent order race-condition tests & stock decrement verification.
- **Rollback:** Transaction rollback on any failure.

### Entry 06: B2B Project Quotations
- **Feature:** Quotations & Lead Management
- **Current state:** Tracked quotation inquiry submission (`QR-YYYY-XXXXX`) managed via Filament admin.
- **Target state:** Inbound corporate RFQ lead flow.
- **Files affected:** `backend/app/Models/QuoteRequest.php`, `backend/app/Http/Controllers/Api/QuoteRequestController.php`, `backend/app/Filament/Resources/QuoteRequestResource.php`, `src/lib/api/orders.ts`.
- **Database affected:** PostgreSQL `quote_requests`.
- **API affected:** `POST /api/v1/quote-requests`.
- **Migration status:** `COMPLETED`
- **Tests:** Inquiry submission payload tests.
- **Rollback:** Retain table data.

### Entry 07: Subdomain Architecture & Fake CMS Removal
- **Feature:** Subdomain Routing & CMS Unification
- **Current state:** Deleted fake Astro routes `/subdomains/*`, `src/middleware.ts`, and `src/pages/admin.astro`. Official DNS CNAME routing established.
- **Target state:** Single authoritative administration via Filament at `manage.niengineeringbd.com`.
- **Files affected:** `src/pages/subdomains/*` (deleted), `src/pages/admin.astro` (deleted), `src/middleware.ts` (deleted).
- **Database affected:** None.
- **API affected:** None.
- **Migration status:** `COMPLETED`
- **Tests:** Static build generates 11 pages in 1.60s with 0 warnings/errors.
- **Rollback:** Git branch history preserves previous static templates.

### Entry 08: Production Security & Debug Hardening
- **Feature:** Application Security
- **Current state:** Deleted `/api/v1/cms-status` and duplicate `/admin/login` form. Configured `APP_DEBUG=false`, `.env` untracked, rotated keys.
- **Target state:** Zero secret leakage; Filament exclusive session auth gateway.
- **Files affected:** `backend/routes/api.php`, `backend/routes/web.php`, `backend/docker-entrypoint.sh`, `backend/render.yaml`, `backend/.env.example`.
- **Database affected:** None.
- **API affected:** `/api/v1/cms-status` removed, `/api/v1/health` added.
- **Migration status:** `COMPLETED`
- **Tests:** Secret scanning pass, debug mode validation pass.
- **Rollback:** N/A (Security patch).
