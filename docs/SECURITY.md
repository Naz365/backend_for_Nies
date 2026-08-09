# N.I. Engineering Digital Platform — Security Specification & Policy

**Document Version:** 1.0 (Phase 2 — Security Audit Completed)  
**Governing Standard:** Sections 9, 10, 11, 47, 54 of the *N.I. Engineering Services AI Agent Master Migration & Execution Specification*  
**Active Working Branch:** `migration`

---

## 1. Security Architecture & Threat Model

```
       [ Public Client (Browser) ]
                   │
                   ▼  HTTPS (Strict TLS 1.3)
      [ Cloudflare Edge Security ]
       ├── DDoS Protection & WAF
       ├── Rate Limiting (5 req/min on Contact/Orders)
       └── DNS Level Subdomain Isolation
                   │
                   ├──► Astro Public Storefront (Static SSG / Pre-rendered HTML)
                   └──► Laravel REST API (Stateless JSON) & Filament Admin (Session Guard)
```

---

## 2. Core Security Invariants & Policy Enforcements

### A. Zero Secrets in Version Control
- `.env` is permanently untracked in Git (`.gitignore` verified on both repositories).
- `.env.example` contains only non-sensitive key names with empty default values.
- `APP_KEY` is dynamically generated and injected at platform runtime (`generateValue: true` on Render).

### B. Production Debug Mode Strictly Disabled
- `APP_DEBUG=false` is enforced in `render.yaml` and `docker-entrypoint.sh`.
- Stack traces, database query logs, and environment variable dumps are suppressed from HTTP exception responses.

### C. Single Authoritative Administration Gateway
- Filament 3.x Admin panel (`manage.niengineeringbd.com`) is the exclusive administration portal.
- All legacy duplicate login routes (`/admin/login`, `/admin/login-action`, `/admin/dashboard`) and plaintext credential disclosure endpoints (`/api/v1/cms-status`) have been permanently removed.

### D. Server-Authoritative Financial Integrity
- **Price Authority:** Item prices, discounts, delivery fees, and order totals are calculated and asserted strictly on the server (`CheckoutService.php`).
- **Inventory Authority:** Real-time stock counts are locked (`Product::lockForUpdate()`) and decremented during checkout to prevent race conditions and overselling.
- **Frozen Snapshots:** Order historical items store frozen snapshots of title, SKU, and unit price at time of purchase.

### E. Order Tracking Protection (Section 19 / 22)
- Public order tracking (`GET /api/v1/orders/{order_number}`) requires customer telephone number verification or dedicated tracking token to prevent enumeration attacks.

### F. File Upload & Media Hardening
- Filament file uploads enforce strict MIME-type allowlists (`image/jpeg`, `image/png`, `image/webp`).
- Upload paths are randomized and served via persistent object storage (R2/S3) or secure storage links.

### G. Strict Database Migration Failure Policy
- Additive migrations are executed strictly via `php artisan migrate --force`.
- Migration failures immediately halt container initialization (`|| true` removed).
- Destructive commands (`migrate:fresh`, `db:wipe`) are strictly prohibited in production.

---

## 3. Security Audit Checklist (Phase 2)

- [x] **Secret Scanning:** Zero `.env` files, private keys, or API tokens tracked in Git.
- [x] **Debug Suppression:** `APP_DEBUG=false` confirmed in Docker and Render manifests.
- [x] **Endpoint Sanitization:** `/api/v1/cms-status` and duplicate `/admin/login` eliminated.
- [x] **Order Protection:** Phone verification enabled on public order lookups.
- [x] **Migration Hardening:** Strict `migrate --force` execution in entrypoint.
- [x] **MIME Validation:** Image uploads restricted to verified image formats.
