# N.I. Engineering Digital Platform — Production & Staging DNS Deployment Topology

**Document Version:** 2.0 (Phase 17 — DNS Architecture Specification)  
**Governing Standard:** Section 44 of the *N.I. Engineering Services AI Agent Master Migration & Execution Specification*  
**Active Working Branch:** `migration`

---

## 1. Multi-Tier Subdomain Architecture

```
                                  [ Cloudflare DNS (Proxy: Full Strict SSL) ]
                                                       │
                 ┌─────────────────────────────────────┼─────────────────────────────────────┐
                 │                                     │                                     │
                 ▼                                     ▼                                     ▼
        [ Public Website Tier ]                [ API Gateway Tier ]                [ Administration Tier ]
        niengineeringbd.com                   api.niengineeringbd.com             manage.niengineeringbd.com
        (Astro SSG on CDN)                    (Laravel 12 REST API)               (Filament 3.x CMS)
                 │                                     │                                     │
                 │                                     └──────────────────┬──────────────────┘
                 │                                                        │
                 ▼                                                        ▼
        [ Browser Client ]                                     [ Managed PostgreSQL ]
                 │                                                        │
                 └────────────────── Stateless JSON REST ─────────────────┘
```

---

## 2. Complete DNS Record Configuration Table

Configure the following canonical DNS records inside Cloudflare DNS (or authoritative domain registrar):

### A. Production Environment (Live Platform)
| Type | Hostname / Subdomain | Target / Destination | TTL | Cloudflare Proxy | Target Service |
|---|---|---|---|---|---|
| **CNAME** | `@` (`niengineeringbd.com`) | `naz365.github.io` | Auto | Proxied (Orange Cloud) | Astro SSG Public Storefront |
| **CNAME** | `www` (`www.niengineeringbd.com`) | `naz365.github.io` | Auto | Proxied (Orange Cloud) | Canonical Redirect to Apex Domain |
| **CNAME** | `manage` (`manage.niengineeringbd.com`) | `ni-engineering-backend.onrender.com` | Auto | Proxied (Orange Cloud) | Filament 3.x Admin CMS Dashboard |
| **CNAME** | `api` (`api.niengineeringbd.com`) | `ni-engineering-backend.onrender.com` | Auto | Proxied (Orange Cloud) | Stateless REST API Gateway (v1) |

### B. Staging Environment (Pre-Production Validation)
| Type | Hostname / Subdomain | Target / Destination | TTL | Cloudflare Proxy | Target Service |
|---|---|---|---|---|---|
| **CNAME** | `staging` (`staging.niengineeringbd.com`) | `naz365.github.io` | Auto | Proxied (Orange Cloud) | Staging Astro Frontend Preview |
| **CNAME** | `manage-staging` | `ni-engineering-backend-staging.onrender.com` | Auto | Proxied (Orange Cloud) | Staging Filament CMS Dashboard |
| **CNAME** | `api-staging` | `ni-engineering-backend-staging.onrender.com` | Auto | Proxied (Orange Cloud) | Staging REST API Gateway (v1) |

---

## 3. Cloudflare Edge Rules & Security Standards

1. **SSL/TLS Mode:** `Full (Strict)` — Enforces end-to-end encryption between Cloudflare Edge and Render origin servers with valid origin certificates.
2. **Always Use HTTPS:** `Enabled` (Automatic 301 redirect for all HTTP requests).
3. **HTTP/2 & HTTP/3 (QUIC):** `Enabled` for minimal latency in Bangladesh and global clients.
4. **WAF & Rate Limiting:**
   - Public Storefront: Standard DDoS mitigation.
   - API Order Submission (`/api/v1/orders`): 5 requests / minute per IP.
   - Contact & Quote Forms (`/api/v1/quote-requests`, `/api/v1/contact`): 5 requests / minute per IP.
   - Admin Login (`/manage/login`): 5 login attempts / minute rate limit.

---

## 4. Environment Variables Checklist

### Backend Service (Render Environment)
```ini
APP_NAME="N.I. Engineering Digital Platform"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GENERATE_VIA_RENDER_SECRET
APP_URL=https://api.niengineeringbd.com
FILAMENT_URL=https://manage.niengineeringbd.com

DB_CONNECTION=pgsql
DB_HOST=dpg-xxxxxxxxxxxx-a.singapore-postgres.render.com
DB_PORT=5432
DB_DATABASE=nies_production
DB_USERNAME=nies_admin
DB_PASSWORD=YOUR_STRONG_PG_PASSWORD
DB_SSLMODE=require

LOG_CHANNEL=stderr
LOG_LEVEL=info

FILESYSTEM_DISK=public
# R2_ACCESS_KEY_ID=
# R2_SECRET_ACCESS_KEY=
# R2_BUCKET=nies-media
# R2_URL=https://media.niengineeringbd.com
```

### Frontend Service (GitHub Pages / Actions)
```ini
PUBLIC_API_URL=https://api.niengineeringbd.com/api/v1
```
