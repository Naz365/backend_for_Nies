# N.I. Engineering Digital Platform — Production Deployment & DNS Cutover Guide

**Document Version:** 1.0 (Master Migration Complete)  
**Governing Standard:** Sections 50–70 of the *N.I. Engineering Services AI Agent Execution & Migration Master Plan*

---

## 1. Production Architecture Topology

```
                                  [ Cloudflare DNS ]
                                         │
                 ┌───────────────────────┼───────────────────────┐
                 │                       │                       │
                 ▼                       ▼                       ▼
       niengineeringbd.com      manage.niengineeringbd.com    api.niengineeringbd.com
        (Astro SSG on            (Filament 3.x Admin on       (Laravel 11 REST API on
        GitHub Pages / CDN)        Render Docker Service)       Render Docker Service)
                 │                       │                       │
                 │                       └───────────┬───────────┘
                 │                                   │
                 ▼                                   ▼
        [ Browser Client ]                  [ Managed PostgreSQL ]
                 │                                   │
                 └─────────── REST API ──────────────┘
```

---

## 2. DNS Record Configuration Table

Configure the following DNS records inside Cloudflare DNS (or your domain registrar):

| Type | Name / Subdomain | Target / Value | TTL | Proxy Status | Purpose |
|---|---|---|---|---|---|
| **CNAME** | `@` (root) | `naz365.github.io` | Auto | Proxied (Orange Cloud) | Public Marketing & Shop Storefront |
| **CNAME** | `www` | `naz365.github.io` | Auto | Proxied (Orange Cloud) | Canonical redirect to root domain |
| **CNAME** | `manage` | `ni-engineering-backend.onrender.com` | Auto | Proxied (Orange Cloud) | Authoritative Admin CMS Dashboard |
| **CNAME** | `api` | `ni-engineering-backend.onrender.com` | Auto | Proxied (Orange Cloud) | Stateless REST API Gateway |

---

## 3. Production Environment Variables Checklist

### Backend Service (Render / Docker Environment)
Configure these secure environment variables in the Render Dashboard (**Dashboard > Environment > Environment Variables**):

```ini
# Application Configuration
APP_NAME="N.I. Engineering Digital Platform"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GENERATE_VIA_RENDER_SECRET
APP_URL=https://api.niengineeringbd.com

# Database Connection (Managed PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=dpg-xxxxxxxxxxxx-a.singapore-postgres.render.com
DB_PORT=5432
DB_DATABASE=nies_production
DB_USERNAME=nies_admin
DB_PASSWORD=YOUR_STRONG_PG_PASSWORD
DB_SSLMODE=require

# Logging
LOG_CHANNEL=stderr
LOG_LEVEL=info

# Admin Initial Credentials (Seeder)
ADMIN_EMAIL=admin@niengineeringbd.com
ADMIN_DEFAULT_PASSWORD=YOUR_SECURE_ADMIN_PASSWORD

# GitHub Actions Deployment Trigger
GITHUB_REPOSITORY="Naz365/frontend_for_Nies"
GITHUB_TOKEN=ghp_YOUR_GITHUB_PERSONAL_ACCESS_TOKEN

# Cloudflare R2 / S3 Object Storage (Optional for Media CDN)
FILESYSTEM_DISK=public
# R2_ACCESS_KEY_ID=
# R2_SECRET_ACCESS_KEY=
# R2_BUCKET=nies-media
# R2_URL=https://media.niengineeringbd.com
```

### Frontend Service (GitHub Pages / Actions)
Configure in GitHub Repository Secrets (**Settings > Secrets and variables > Actions**):

```ini
PUBLIC_API_URL=https://api.niengineeringbd.com/api/v1
```

---

## 4. Step-by-Step Production Cutover Procedure

### Step 1: Database Migration Execution (Additive Only)
Run non-destructive additive migrations on the production PostgreSQL database:
```bash
php artisan migrate --force
```
> [!IMPORTANT]
> Never run `migrate:fresh` or `db:wipe` in production. The codebase uses strictly additive migrations that preserve all existing records.

### Step 2: Seed Initial Catalog & Admin
```bash
php artisan db:seed --force
```

### Step 3: Verify API Health Check
Execute a GET request to verify system operational readiness:
```bash
curl -I https://api.niengineeringbd.com/api/v1/health
# Expected Response: HTTP/2 200 OK {"status":"healthy","service":"N.I. Engineering API"}
```

### Step 4: Verify Admin Login
1. Navigate to `https://manage.niengineeringbd.com`
2. Log in with `ADMIN_EMAIL` and `ADMIN_DEFAULT_PASSWORD`.
3. Verify that **Products**, **Categories**, **Customer Orders**, and **Project Quotations** appear in the left navigation sidebar.

### Step 5: Verify Storefront Checkout
1. Navigate to `https://niengineeringbd.com/shop/`
2. Add an equipment product to cart.
3. Complete test checkout with Cash on Delivery.
4. Verify order appears immediately in Filament Admin under **Customer Orders**.

---

## 5. Rollback Strategy

If any unforeseen deployment issue occurs:
1. **Frontend**: Revert the GitHub Pages commit to the previous stable SHA.
2. **Backend**: Render enables instant rollback to previous container image with 1-click in the dashboard.
3. **Database**: PostgreSQL automated continuous WAL archiving allows point-in-time recovery.
