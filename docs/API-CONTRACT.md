# N.I. Engineering Digital Platform — REST API Contract Specification (v1)

**Document Version:** 1.0 (Phase 5 — API Contract Standardized)  
**Governing Standard:** Sections 22, 23, 28, 57 of the *N.I. Engineering Services AI Agent Master Migration & Execution Specification*  
**Base URL (Production):** `https://api.niengineeringbd.com/api/v1`  
**Base URL (Staging):** `https://api-staging.niengineeringbd.com/api/v1`  
**Data Format:** JSON (`application/json`)  
**Character Encoding:** UTF-8  
**Active Working Branch:** `migration`

---

## 1. Global API Response Standards & Schema Envelope

All v1 endpoints adhere to the standardized response envelope structure:

### Standard Success Response Schema
```json
{
  "data": { ... },
  "meta": {
    "total": 50,
    "per_page": 20,
    "current_page": 1,
    "last_page": 3
  },
  "message": "Operation completed successfully",
  "errors": null
}
```

### Standard Error Response Schema
```json
{
  "data": null,
  "meta": null,
  "message": "Validation error or resource not found",
  "errors": {
    "field_name": ["Specific validation error message."]
  }
}
```

### Standard HTTP Status Codes
| Status Code | Description | Usage Scenario |
|---|---|---|
| **`200 OK`** | Request succeeded | Standard GET, PUT, DELETE responses |
| **`201 Created`** | Resource created | Order created, Quote request submitted |
| **`400 Bad Request`** | Malformed request | Corrupt JSON or invalid syntax |
| **`401 Unauthenticated`** | Authentication required | Protected admin actions |
| **`403 Forbidden`** | Authorization failed | Access denied / Phone mismatch on order tracking |
| **`404 Not Found`** | Resource missing | Unknown product slug or order number |
| **`409 Conflict`** | Resource state conflict | Concurrent stock contention |
| **`422 Unprocessable Entity`** | Validation failure | Out of stock or missing required fields |
| **`429 Too Many Requests`** | Rate limit exceeded | Form spam throttling (5 req/min) |
| **`500 Server Error`** | Server runtime error | Unhandled system exception |

---

## 2. Verified REST API Endpoints Matrix

### A. Health & Diagnostics
#### `GET /api/v1/health`
- **Auth:** Public
- **Response (`200 OK`):**
```json
{
  "data": {
    "status": "healthy",
    "service": "N.I. Engineering API",
    "timestamp": "2026-08-09T20:38:00+06:00"
  },
  "meta": null,
  "message": null,
  "errors": null
}
```

---

### B. Taxonomy & Categories
#### `GET /api/v1/categories`
- **Auth:** Public
- **Description:** Returns active categories with live published product counts.
- **Response (`200 OK`):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Fire Extinguishers",
      "slug": "fire-extinguishers",
      "description": "ABC Dry Chemical Powder, CO2, and Foam Extinguishers",
      "image": "/wp-content/uploads/2017/05/fire-extinguishers1.jpg",
      "is_active": true,
      "sort_order": 1,
      "products_count": 6
    }
  ],
  "meta": null,
  "message": null,
  "errors": null
}
```

#### `GET /api/v1/categories/{slug}`
- **Auth:** Public
- **Response (`200 OK`):** Returns category detail with nested published products.

---

### C. Product Catalog & Inventory
#### `GET /api/v1/products`
- **Auth:** Public
- **Query Parameters:**
  - `category` (string, optional): Filter by category slug
  - `q` / `search` (string, optional): Keyword search matching title, description, SKU, specifications
  - `featured` (boolean, optional): Filter featured items (`1` or `true`)
  - `in_stock` (boolean, optional): Filter in-stock items (`1` or `true`)
  - `sort` (string, optional): `featured`, `price-low`, `price-high`, `newest`
  - `per_page` (integer, optional): Items per page (default: 50)
- **Response (`200 OK`):**
```json
{
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "sku": "EXT-ABC-6KG",
      "title": "ABC Dry Chemical Powder Extinguisher (6kg)",
      "slug": "abc-dry-powder-extinguisher-6kg",
      "category_slug": "fire-extinguishers",
      "category_name": "Fire Extinguishers",
      "price": "1450.00",
      "compare_at_price": "1800.00",
      "stock_quantity": 100,
      "track_inventory": true,
      "is_featured": true,
      "status": "published",
      "image": "/wp-content/uploads/2017/05/fire-extinguishers1.jpg",
      "description": "Multipurpose Class A, B, C fire extinguisher filled with 90% MAP powder.",
      "category": {
        "id": 1,
        "name": "Fire Extinguishers",
        "slug": "fire-extinguishers"
      }
    }
  ],
  "meta": {
    "total": 8,
    "per_page": 50,
    "current_page": 1,
    "last_page": 1
  },
  "message": null,
  "errors": null
}
```

#### `GET /api/v1/products/{slug}`
- **Auth:** Public
- **Response (`200 OK`):** Returns single product with HTML specifications and inventory balance.

---

### D. Server-Authoritative Shopping Cart
#### `GET /api/v1/cart`
- **Auth:** Public (Session Token)
- **Headers:** `X-Cart-Session: <uuid>`
- **Response (`200 OK`):**
```json
{
  "data": {
    "cart_id": 12,
    "session_token": "nies_guest_9f8d1e2a",
    "items": [
      {
        "id": 4,
        "product_id": 1,
        "title": "ABC Dry Chemical Powder Extinguisher (6kg)",
        "slug": "abc-dry-powder-extinguisher-6kg",
        "image": "/wp-content/uploads/2017/05/fire-extinguishers1.jpg",
        "unit_price": 1450.00,
        "quantity": 2,
        "line_total": 2900.00,
        "stock_quantity": 100,
        "in_stock": true
      }
    ],
    "item_count": 2,
    "subtotal": 2900.00,
    "currency": "BDT"
  },
  "meta": null,
  "message": null,
  "errors": null
}
```

#### `POST /api/v1/cart/items`
- **Request Body:** `{"product_id": 1, "quantity": 1}`
- **Response (`200 OK`):** Returns updated cart payload.
- **Stock Error (`422 Unprocessable Entity`):** `{"message": "Insufficient stock."}`

#### `PUT /api/v1/cart/items/{id}`
- **Request Body:** `{"quantity": 3}` (Passing `0` removes item)
- **Response (`200 OK`):** Returns updated cart payload.

#### `DELETE /api/v1/cart/items/{id}` & `DELETE /api/v1/cart`
- **Response (`200 OK`):** Returns updated / emptied cart payload.

---

### E. Checkout & Orders (Transaction-Safe)
#### `POST /api/v1/orders`
- **Auth:** Public (Session Token)
- **Headers:** `X-Cart-Session: <uuid>`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "customer_name": "Rafiqul Islam",
  "customer_phone": "+880 1711-000000",
  "customer_email": "rafiq@example.com",
  "shipping_address": "House 12, Road 4, Sector 3, Uttara, Dhaka",
  "payment_method": "cod",
  "notes": "Please call before arrival."
}
```
- **Response (`201 Created`):**
```json
{
  "data": {
    "order_number": "NIES-20260809-X8K9M2",
    "total_amount": 2900.00,
    "currency": "BDT",
    "payment_method": "cod",
    "payment_status": "unpaid",
    "status": "pending",
    "items_count": 1,
    "created_at": "2026-08-09T20:38:00+06:00"
  },
  "meta": null,
  "message": "Order placed successfully",
  "errors": null
}
```

#### `GET /api/v1/orders/{order_number}`
- **Auth:** Public with Phone Verification Guard (Section 19 / 22)
- **Query Parameters:** `phone` (optional phone match check)
- **Response (`200 OK`):** Returns order fulfillment status and frozen snapshot items.

---

### F. B2B Project Quotations
#### `POST /api/v1/quote-requests`
- **Request Body:**
```json
{
  "customer_name": "Md. Hasan Ali",
  "company_name": "Apex Footwear Ltd.",
  "phone": "+880 1819-000000",
  "email": "hasan@apex.com",
  "service_type": "suppression_system",
  "project_description": "Requirement for FM-200 gas flooding system in 450 sqft server room.",
  "notes": "Urgent timeline."
}
```
- **Response (`201 Created`):**
```json
{
  "data": {
    "request_number": "QR-2026-00001",
    "customer_name": "Md. Hasan Ali",
    "service_type": "suppression_system",
    "status": "new",
    "created_at": "2026-08-09T20:38:00+06:00"
  },
  "meta": null,
  "message": "Quotation request submitted successfully.",
  "errors": null
}
```

---

### G. Content & Inquiries
- `GET /api/v1/client-logos`: Partner brand showcase logos for trust carousel.
- `GET /api/v1/projects`: Portfolio case studies.
- `GET /api/v1/blog`: Safety articles & guides.
- `GET /api/v1/settings`: Corporate contact details and downloadable PDF profile URL.
- `POST /api/v1/contact`: General contact form inquiries (Rate-limited).
