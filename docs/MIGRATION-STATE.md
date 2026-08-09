# N.I. Engineering Digital Platform — Migration State & Baseline Freeze

**Document Version:** 1.0 (Phase 0 — Repository Freeze)  
**Governing Standard:** Section 5 of the *N.I. Engineering Services AI Agent Master Migration & Execution Specification*  
**Execution Timestamp:** 2026-08-09T20:25:00+06:00  
**Active Working Branch:** `migration`

---

## 1. Baseline Environment & Artifact Record

| Parameter | Recorded Value | Notes |
|---|---|---|
| **Frontend Repository** | `https://github.com/Naz365/frontend_for_Nies.git` | Local: `c:\Users\pc\Desktop\project nies` |
| **Frontend Commit SHA** | `6ffecd3c30e099358f6548ca9155e90ad391fb75` | Starting baseline commit |
| **Backend Repository** | `https://github.com/Naz365/backend_for_Nies.git` | Local: `c:\Users\pc\Desktop\project nies\backend` |
| **Backend Commit SHA** | `a7e21c0ce288186ae3bc613bb763de9b7f4cab5f` | Starting baseline commit |
| **Node.js Runtime** | `v22.19.0` | Node.js x64 LTS environment |
| **PHP Runtime** | `8.3.31 (cli)` | PHP 8.3 CLI with PostgreSQL PDO drivers |
| **Laravel Framework** | `12.64.0` | Backend application engine |
| **Astro Framework** | `^7.1.6` (Core static generator) | Static SSG engine with Tailwind CSS |
| **Database Engine** | PostgreSQL 15+ (`pgsql` connection) | Strictly additive migrations |
| **Object Storage** | Cloudflare R2 / AWS S3 compatible | Configured in `filesystems.php` |
| **Production Host (Frontend)** | GitHub Pages (`docs/` branch `master` or Cloudflare Pages) | Custom domain: `niengineeringbd.com` |
| **Production Host (Backend)** | Render Web Service (Docker container) | Custom domain: `api.niengineeringbd.com` & `manage.niengineeringbd.com` |

---

## 2. Git Branch Topology

Both repositories are configured with the three-tier branching structure:
1. `main` (or `master`): Production release branch.
2. `staging`: Pre-production validation and integration branch.
3. `migration` (Active): Working branch where all phased refactoring and migration activities execute.

---

## 3. Phase 0 Acceptance Sign-Off

- [x] Repositories frozen and starting SHAs recorded.
- [x] Branch topology (`main`, `staging`, `migration`) created.
- [x] Active working branch set to `migration`.
- [x] Node, PHP, Laravel, and Astro versions documented.
- [x] `docs/MIGRATION-STATE.md` initialized.
