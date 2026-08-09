# N.I. Engineering Digital Platform — Database Architecture & Schema Specification

**Document Version:** 1.0 (Phase 3 — Database Foundation Completed)  
**Governing Standard:** Sections 12–19, 46, 57 of the *N.I. Engineering Services AI Agent Master Migration & Execution Specification*  
**Authoritative Database Engine:** Managed PostgreSQL 15+ / 16  
**Migration Strategy:** Strictly Additive (`php artisan migrate --force`)  
**Active Working Branch:** `migration`

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

## 2. Table Specifications & Constraints

### A. Taxonomy & Catalog Domain

#### 1. `categories`
| Column | Type | Nullable | Default | Constraints / Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `name` | `VARCHAR(255)` | No | — | Category title (e.g. Fire Extinguishers) |
| `slug` | `VARCHAR(255)` | No | — | `UNIQUE(slug)` index |
| `description` | `TEXT` | Yes | NULL | Overview description |
| `image` | `VARCHAR(255)` | Yes | NULL | Thumbnail image path |
| `is_active` | `BOOLEAN` | No | `true` | Visibility status |
| `sort_order` | `INTEGER` | No | `0` | Order of display |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

#### 2. `products`
| Column | Type | Nullable | Default | Constraints / Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `category_id` | `BIGINT` (FK) | Yes | NULL | References `categories(id)` (`ON DELETE SET NULL`) |
| `sku` | `VARCHAR(100)` | Yes | NULL | `UNIQUE(sku)` index |
| `title` | `VARCHAR(255)` | No | — | Product name |
| `slug` | `VARCHAR(255)` | No | — | `UNIQUE(slug)` index |
| `category_slug` | `VARCHAR(100)` | No | — | Fallback identifier |
| `category_name` | `VARCHAR(100)` | Yes | NULL | Category display label |
| `image` | `VARCHAR(255)` | Yes | NULL | Product image asset path |
| `description` | `TEXT` | Yes | NULL | Overview description |
| `specifications` | `TEXT` | Yes | NULL | Rich HTML specifications |
| `price` | `NUMERIC(12,2)` | No | `0.00` | Authoritative selling price (৳ BDT) |
| `compare_at_price` | `NUMERIC(12,2)` | Yes | NULL | Strikethrough regular price (৳ BDT) |
| `stock_quantity` | `INTEGER` | No | `100` | Inventory balance count |
| `track_inventory` | `BOOLEAN` | No | `true` | Inventory tracking flag |
| `is_featured` | `BOOLEAN` | No | `false` | Featured flag |
| `status` | `VARCHAR(50)` | No | `'published'` | `'draft'`, `'published'`, `'archived'` |
| `meta_title` | `VARCHAR(255)` | Yes | NULL | SEO Meta Title |
| `meta_description` | `TEXT` | Yes | NULL | SEO Meta Description |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

---

### B. Customer & Address Domain

#### 3. `customers`
| Column | Type | Nullable | Default | Constraints / Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `user_id` | `BIGINT` (FK) | Yes | NULL | References `users(id)` (`ON DELETE SET NULL`) |
| `name` | `VARCHAR(255)` | No | — | Full customer name |
| `email` | `VARCHAR(255)` | Yes | NULL | `INDEX(email)` |
| `phone` | `VARCHAR(50)` | No | — | `INDEX(phone)` |
| `is_active` | `BOOLEAN` | No | `true` | Account active flag |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

#### 4. `addresses`
| Column | Type | Nullable | Default | Constraints / Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `customer_id` | `BIGINT` (FK) | No | — | References `customers(id)` (`ON DELETE CASCADE`) |
| `type` | `VARCHAR(50)` | No | `'shipping'` | `'shipping'` or `'billing'` |
| `address_line_1` | `VARCHAR(255)` | No | — | Street address / Building |
| `address_line_2` | `VARCHAR(255)` | Yes | NULL | Area (e.g. Gulshan, Badda) |
| `city` | `VARCHAR(100)` | No | `'Dhaka'` | City |
| `postal_code` | `VARCHAR(20)` | Yes | NULL | Zip Code |
| `is_default` | `BOOLEAN` | No | `true` | Default selection |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

---

### C. Shopping Cart Domain

#### 5. `carts`
| Column | Type | Nullable | Default | Constraints / Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `customer_id` | `BIGINT` (FK) | Yes | NULL | References `customers(id)` |
| `session_token` | `VARCHAR(255)` | No | — | `INDEX(session_token)` |
| `status` | `VARCHAR(50)` | No | `'active'` | `'active'`, `'converted'`, `'abandoned'` |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

#### 6. `cart_items`
| Column | Type | Nullable | Default | Constraints / Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `cart_id` | `BIGINT` (FK) | No | — | References `carts(id)` (`CASCADE`) |
| `product_id` | `BIGINT` (FK) | No | — | References `products(id)` (`CASCADE`) |
| `quantity` | `INTEGER` | No | `1` | Quantity |
| `unit_price` | `NUMERIC(12,2)` | No | — | Cached snapshot unit price in ৳ BDT |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

---

### D. Orders & Payments Domain (Frozen Snapshots)

#### 7. `orders`
| Column | Type | Nullable | Default | Constraints / Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `order_number` | `VARCHAR(100)` | No | — | `UNIQUE(order_number)` index |
| `customer_id` | `BIGINT` (FK) | Yes | NULL | References `customers(id)` |
| `customer_name` | `VARCHAR(255)` | No | — | Customer name snapshot |
| `customer_email` | `VARCHAR(255)` | Yes | NULL | Customer email snapshot |
| `customer_phone` | `VARCHAR(50)` | No | — | Customer phone snapshot |
| `shipping_address` | `TEXT` | No | — | Delivery address snapshot |
| `subtotal` | `NUMERIC(12,2)` | No | — | Products subtotal (৳ BDT) |
| `shipping_fee` | `NUMERIC(12,2)` | No | `0.00` | Shipping charge (৳ BDT) |
| `discount_amount` | `NUMERIC(12,2)` | No | `0.00` | Discount deduction (৳ BDT) |
| `total_amount` | `NUMERIC(12,2)` | No | — | Net payable total (৳ BDT) |
| `payment_method` | `VARCHAR(50)` | No | `'cod'` | `'cod'`, `'sslcommerz'`, etc. |
| `payment_status` | `VARCHAR(50)` | No | `'unpaid'` | `'unpaid'`, `'paid'`, `'failed'`, `'refunded'` |
| `status` | `VARCHAR(50)` | No | `'pending'` | `'pending'`, `'confirmed'`, `'processing'`, `'shipped'`, `'delivered'`, `'cancelled'` |
| `notes` | `TEXT` | Yes | NULL | Internal admin notes |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

#### 8. `order_items` (Historical Snapshot Rule)
| Column | Type | Nullable | Default | Constraints / Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `order_id` | `BIGINT` (FK) | No | — | References `orders(id)` (`CASCADE`) |
| `product_id` | `BIGINT` (FK) | Yes | NULL | References `products(id)` (`SET NULL`) |
| `product_title_snapshot` | `VARCHAR(255)` | No | — | Frozen title snapshot |
| `sku_snapshot` | `VARCHAR(100)` | Yes | NULL | Frozen SKU snapshot |
| `unit_price_snapshot` | `NUMERIC(12,2)` | No | — | Frozen unit price in ৳ BDT |
| `quantity` | `INTEGER` | No | `1` | Ordered quantity |
| `line_total` | `NUMERIC(12,2)` | No | — | Frozen line total (`unit_price * quantity`) |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

#### 9. `payments`
| Column | Type | Nullable | Default | Constraints / Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `order_id` | `BIGINT` (FK) | No | — | References `orders(id)` (`CASCADE`) |
| `transaction_id` | `VARCHAR(255)` | Yes | NULL | `INDEX(transaction_id)` |
| `payment_method` | `VARCHAR(50)` | No | `'cod'` | Payment channel |
| `gateway` | `VARCHAR(50)` | No | `'manual_cod'` | Gateway handler |
| `amount` | `NUMERIC(12,2)` | No | — | Amount paid in ৳ BDT |
| `currency` | `VARCHAR(10)` | No | `'BDT'` | Currency code |
| `status` | `VARCHAR(50)` | No | `'pending'` | `'pending'`, `'success'`, `'failed'`, `'cancelled'` |
| `gateway_response` | `JSONB` | Yes | NULL | Raw response log |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

---

### E. Corporate, Quotes & Brand Domain

#### 10. `quote_requests`
| Column | Type | Nullable | Default | Constraints / Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `request_number` | `VARCHAR(100)` | No | — | `UNIQUE(request_number)` index |
| `customer_name` | `VARCHAR(255)` | No | — | Contact person |
| `company_name` | `VARCHAR(255)` | Yes | NULL | Company / Factory name |
| `email` | `VARCHAR(255)` | Yes | NULL | Email |
| `phone` | `VARCHAR(50)` | No | — | Phone |
| `service_type` | `VARCHAR(100)` | No | — | Requested service category |
| `project_description` | `TEXT` | No | — | Scope of work |
| `status` | `VARCHAR(50)` | No | `'new'` | `'new'`, `'contacted'`, `'quoted'`, `'closed'` |
| `notes` | `TEXT` | Yes | NULL | Internal estimator notes |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |

#### 11. `client_logos`
| Column | Type | Nullable | Default | Constraints / Description |
|---|---|---|---|---|
| `id` | `BIGSERIAL` (PK) | No | Auto | Primary key |
| `name` | `VARCHAR(255)` | No | — | Partner / Client brand name |
| `logo_path` | `VARCHAR(255)` | No | — | Image asset path |
| `website_url` | `VARCHAR(255)` | Yes | NULL | Website URL |
| `sort_order` | `INTEGER` | No | `0` | Display order |
| `is_active` | `BOOLEAN` | No | `true` | Active toggle |
| `created_at` / `updated_at` | `TIMESTAMP` | No | `now()` | Timestamps |
