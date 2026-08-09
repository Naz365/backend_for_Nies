# N.I. Engineering Digital Platform — Database Architecture & Schema Specification

**Document Version:** 1.0 (Phase 2 Production Data Foundation)  
**Governing Standard:** Sections 15–25, 50, 80 of the *N.I. Engineering Services AI Agent Execution & Migration Master Plan*  
**Authoritative Database Engine:** Managed PostgreSQL 15+ / 16  
**Migration Strategy:** Strictly Additive (`php artisan migrate --force`)

---

## 1. Schema Overview & Entity Relationship Architecture

```
                    ┌─────────────────────────┐
                    │       categories        │
                    └────────────┬────────────┘
                                 │ 1:N
                    ┌────────────▼────────────┐
                    │        products         │
                    └───────┬─────────┬───────┘
                            │ 1:N     │ 1:N
               ┌────────────┘         └────────────┐
               │                                   │
      ┌────────▼────────┐                 ┌────────▼────────┐
      │   cart_items    │                 │   order_items   │
      └────────▲────────┘                 └────────▲────────┘
               │ N:1                               │ N:1
      ┌────────┴────────┐                 ┌────────┴────────┐
      │      carts      │                 │     orders      │
      └────────┬────────┘                 └────────┬────────┘
               │                                   │
               │ N:1                               │ N:1
      ┌────────▼────────┐                 ┌────────▼────────┐
      │    customers    │◄────────────────┤    payments     │
      └────────┬────────┘                 └─────────────────┘
               │ 1:N
      ┌────────▼────────┐
      │    addresses    │
      └─────────────────┘
```

---

## 2. Table Specifications

### A. Taxonomy & Catalog Domain

#### 1. `categories`
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `name` | `VARCHAR(255)` | No | — | Category title (e.g. Fire Extinguishers) |
| `slug` | `VARCHAR(255)` | No | — | Unique URL-safe slug |
| `description` | `TEXT` | Yes | NULL | Category overview description |
| `image` | `VARCHAR(255)` | Yes | NULL | Category thumbnail image path |
| `is_active` | `BOOLEAN` | No | `true` | Visibility toggle |
| `sort_order` | `INTEGER` | No | `0` | UI display ordering |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

#### 2. `products`
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `category_id` | `BIGINT` (FK) | Yes | NULL | References `categories(id)` (ON DELETE SET NULL) |
| `sku` | `VARCHAR(100)` | Yes | NULL | Unique Stock Keeping Unit |
| `title` | `VARCHAR(255)` | No | — | Product name |
| `slug` | `VARCHAR(255)` | No | — | Unique URL slug |
| `category_slug` | `VARCHAR(100)` | No | — | Legacy fallback category identifier |
| `category_name` | `VARCHAR(100)` | Yes | NULL | Category display string |
| `image` | `VARCHAR(255)` | Yes | NULL | Primary product image path |
| `description` | `TEXT` | Yes | NULL | Product marketing description |
| `specifications` | `TEXT` | Yes | NULL | Technical specifications (HTML) |
| `price` | `NUMERIC(12,2)` | No | `0.00` | Authoritative selling price in ৳ BDT |
| `compare_at_price` | `NUMERIC(12,2)` | Yes | NULL | Strikethrough regular price in ৳ BDT |
| `stock_quantity` | `INTEGER` | No | `100` | Current available inventory count |
| `track_inventory` | `BOOLEAN` | No | `true` | Inventory enforcement flag |
| `is_featured` | `BOOLEAN` | No | `false` | Featured on storefront |
| `status` | `VARCHAR(50)` | No | `'published'` | `'draft'`, `'published'`, `'archived'` |
| `meta_title` | `VARCHAR(255)` | Yes | NULL | SEO Meta Title |
| `meta_description` | `TEXT` | Yes | NULL | SEO Meta Description |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

---

### B. Customer & Address Domain

#### 3. `customers`
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `user_id` | `BIGINT` (FK) | Yes | NULL | Optional account reference to `users(id)` |
| `name` | `VARCHAR(255)` | No | — | Customer full name |
| `email` | `VARCHAR(255)` | Yes | NULL | Email address (indexed) |
| `phone` | `VARCHAR(50)` | No | — | Contact phone number (indexed) |
| `is_active` | `BOOLEAN` | No | `true` | Account active flag |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

#### 4. `addresses`
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `customer_id` | `BIGINT` (FK) | No | — | References `customers(id)` (CASCADE) |
| `type` | `VARCHAR(50)` | No | `'shipping'` | `'shipping'` or `'billing'` |
| `address_line_1` | `VARCHAR(255)` | No | — | Street address / Building / Road |
| `address_line_2` | `VARCHAR(255)` | Yes | NULL | Area (e.g. Gulshan, Badda, Uttara) |
| `city` | `VARCHAR(100)` | No | `'Dhaka'` | City |
| `postal_code` | `VARCHAR(20)` | Yes | NULL | Postal / Zip Code |
| `is_default` | `BOOLEAN` | No | `true` | Default address selection |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

---

### C. Shopping Cart Domain

#### 5. `carts`
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `customer_id` | `BIGINT` (FK) | Yes | NULL | References `customers(id)` |
| `session_token` | `VARCHAR(255)` | No | — | Anonymous guest token (indexed) |
| `status` | `VARCHAR(50)` | No | `'active'` | `'active'`, `'converted'`, `'abandoned'` |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

#### 6. `cart_items`
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `cart_id` | `BIGINT` (FK) | No | — | References `carts(id)` (CASCADE) |
| `product_id` | `BIGINT` (FK) | No | — | References `products(id)` (CASCADE) |
| `quantity` | `INTEGER` | No | `1` | Item quantity |
| `unit_price` | `NUMERIC(12,2)` | No | — | Snapshot unit price in ৳ BDT |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

---

### D. Orders & Payments Domain (Snapshot Integrity)

#### 7. `orders`
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `order_number` | `VARCHAR(100)` | No | — | Unique order code (e.g. `NIES-2026-00001`) |
| `customer_id` | `BIGINT` (FK) | Yes | NULL | References `customers(id)` |
| `customer_name` | `VARCHAR(255)` | No | — | Customer name snapshot |
| `customer_email` | `VARCHAR(255)` | Yes | NULL | Customer email snapshot |
| `customer_phone` | `VARCHAR(50)` | No | — | Customer phone snapshot |
| `shipping_address` | `TEXT` | No | — | Complete delivery address snapshot |
| `subtotal` | `NUMERIC(12,2)` | No | — | Products subtotal in ৳ BDT |
| `shipping_fee` | `NUMERIC(12,2)` | No | `0.00` | Delivery fee in ৳ BDT |
| `discount_amount` | `NUMERIC(12,2)` | No | `0.00` | Promo / discount deduction |
| `total_amount` | `NUMERIC(12,2)` | No | — | Net payable amount in ৳ BDT |
| `payment_method` | `VARCHAR(50)` | No | `'cod'` | `'cod'`, `'sslcommerz'`, `'bkash'`, `'nagad'` |
| `payment_status` | `VARCHAR(50)` | No | `'unpaid'` | `'unpaid'`, `'paid'`, `'failed'`, `'refunded'` |
| `status` | `VARCHAR(50)` | No | `'pending'` | `'pending'`, `'confirmed'`, `'processing'`, `'shipped'`, `'delivered'`, `'cancelled'` |
| `notes` | `TEXT` | Yes | NULL | Internal admin / fulfillment notes |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

#### 8. `order_items` (Historical Snapshot Rule)
> [!IMPORTANT]
> `order_items` stores frozen product title, SKU, and unit price snapshots at time of purchase. Future price edits to the `products` table will NEVER corrupt past order totals.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `order_id` | `BIGINT` (FK) | No | — | References `orders(id)` (CASCADE) |
| `product_id` | `BIGINT` (FK) | Yes | NULL | References `products(id)` (SET NULL on delete) |
| `product_title_snapshot` | `VARCHAR(255)` | No | — | Frozen title snapshot |
| `sku_snapshot` | `VARCHAR(100)` | Yes | NULL | Frozen SKU snapshot |
| `unit_price_snapshot` | `NUMERIC(12,2)` | No | — | Frozen unit price in ৳ BDT |
| `quantity` | `INTEGER` | No | `1` | Ordered quantity |
| `line_total` | `NUMERIC(12,2)` | No | — | Frozen line total (`unit_price * quantity`) |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

#### 9. `payments`
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `order_id` | `BIGINT` (FK) | No | — | References `orders(id)` (CASCADE) |
| `transaction_id` | `VARCHAR(255)` | Yes | NULL | Gateway transaction identifier |
| `payment_method` | `VARCHAR(50)` | No | `'cod'` | Payment channel |
| `gateway` | `VARCHAR(50)` | No | `'manual_cod'` | `'manual_cod'`, `'sslcommerz'`, etc. |
| `amount` | `NUMERIC(12,2)` | No | — | Paid amount in ৳ BDT |
| `currency` | `VARCHAR(10)` | No | `'BDT'` | Currency ISO code |
| `status` | `VARCHAR(50)` | No | `'pending'` | `'pending'`, `'success'`, `'failed'`, `'cancelled'` |
| `gateway_response` | `JSONB` | Yes | NULL | Raw webhook payload for auditability |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

---

### E. Corporate, Quotes & Brand Domain

#### 10. `quote_requests`
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `request_number` | `VARCHAR(100)` | No | — | Unique quotation code (e.g. `QR-2026-00001`) |
| `customer_name` | `VARCHAR(255)` | No | — | Client contact name |
| `company_name` | `VARCHAR(255)` | Yes | NULL | Company / Factory name |
| `email` | `VARCHAR(255)` | Yes | NULL | Client email |
| `phone` | `VARCHAR(50)` | No | — | Contact telephone |
| `service_type` | `VARCHAR(100)` | No | — | System / Service required |
| `project_description` | `TEXT` | No | — | Detailed engineering scope |
| `status` | `VARCHAR(50)` | No | `'new'` | `'new'`, `'contacted'`, `'quoted'`, `'closed'` |
| `notes` | `TEXT` | Yes | NULL | Internal engineering estimator notes |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

#### 11. `client_logos`
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `name` | `VARCHAR(255)` | No | — | Partner / Client brand name |
| `logo_path` | `VARCHAR(255)` | No | — | Image asset path |
| `website_url` | `VARCHAR(255)` | Yes | NULL | Partner URL |
| `sort_order` | `INTEGER` | No | `0` | Order |
| `is_active` | `BOOLEAN` | No | `true` | Visibility |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |
