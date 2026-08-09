# N.I. Engineering Digital Platform — Security Specification & Policy

**Document Version:** 1.0 (Phase 1 Security Emergency Completed)  
**Governing Standard:** Sections 5, 13, 14, 61, 79 of the *N.I. Engineering Services AI Agent Execution & Migration Master Plan*

---

## 1. Core Security Principles

1. **Never Trust the Client**: All financial calculations (item prices, discount calculations, order subtotals, VAT, and shipping rates) and inventory validations MUST be performed and asserted exclusively on the server (Laravel).
2. **Zero Secrets in Version Control**: `.env`, `APP_KEY`, API tokens, payment secrets, database passwords, and SMTP credentials must NEVER be committed to Git.
3. **Debug Mode Disabled in Production**: `APP_DEBUG=false` must be strictly enforced on all public/production deployments to prevent stack trace disclosures.
4. **Unified Admin Authentication**: Filament 3.x is the sole, authoritative admin authentication gateway. No parallel or custom login bypass routes are permitted.
5. **No Credential Exposure via Endpoints**: No public API route may return credentials, database connection strings, or server secrets.

---

## 2. Hardening Measures Implemented (Phase 1)

### A. Credential Leaks Eliminated
- **Deleted Endpoint**: `GET /api/v1/cms-status` previously returned plaintext admin credentials (`admin@niengineeringbd.com` / `password123`). This endpoint has been completely eliminated and replaced with a clean `/v1/health` status check.
- **Deleted Duplicate Auth**: Removed custom `/admin/login`, `/admin/login-action`, and inline `/admin/dashboard` routes from `routes/web.php`. All admin access redirects directly to Filament (`/admin`).

### B. Secret Rotation & Manifest Sanitization
- **`APP_KEY` in Manifests**: Hardcoded base64 application keys removed from `render.yaml` and `docker-entrypoint.sh`. Configured `generateValue: true` in Render to ensure keys are securely managed by the hosting platform.
- **`.env.example` Sanitized**: All sensitive default credentials in `.env.example` replaced with blank placeholders.
- **`.gitignore` Enforced**: `.env` and all environment variant files are verified untracked and ignored.

### C. Production Configuration Guard
- **`APP_DEBUG=false`**: Explicitly set in `render.yaml` and `docker-entrypoint.sh`.
- **Additive Migrations Only**: Removed `php artisan migrate:fresh --seed` from `docker-entrypoint.sh`. Replaced with non-destructive `php artisan migrate --force` to prevent data loss.

---

## 3. Production Admin Access Protocol

| Resource | Target Production Domain | Authentication Guard |
|---|---|---|
| **CMS & Store Admin** | `manage.niengineeringbd.com` (or `/admin`) | Filament 3.x Authenticated Session Guard |
| **REST API** | `api.niengineeringbd.com` (or `/api/v1`) | Stateless JSON responses; rate-limited endpoints |
| **Public Storefront** | `niengineeringbd.com` | Astro SSG Static Presentation |

---

## 4. Acceptance Criteria Checklist (Phase 1)

- [x] Hardcoded `APP_KEY` removed from repository files and manifests.
- [x] `.env` is untracked by Git (`git ls-files .env` returns empty).
- [x] `.env.example` sanitized with safe placeholders.
- [x] Production debug mode set to `APP_DEBUG=false`.
- [x] Duplicate `/admin/login` removed.
- [x] Credential-leaking `/api/v1/cms-status` removed.
- [x] Filament configured as the sole admin authentication gateway.
- [x] Destructive `migrate:fresh` removed from container startup.
