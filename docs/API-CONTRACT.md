# N.I. Engineering Digital Platform — REST API Contract Specification (v1)

**Document Version:** 1.0 (Phase 3 Admin Catalog Vertical Slice)  
**Governing Standard:** Sections 28, 29, 30, 80 of the *N.I. Engineering Services AI Agent Execution & Migration Master Plan*  
**Base URL (Production):** `https://api.niengineeringbd.com/api/v1`  
**Data Format:** JSON (`application/json`)  
**Character Encoding:** UTF-8

---

## 1. Global Response Schema & Standards

### Standard Success Envelope
```json
{
  "success": true,
  "data": { ... },
  "pagination": {
    "total": 50,
    "per_page": 20,
    "current_page": 1,
    "last_page": 3
  }
}
```

### Standard Error Envelope
```json
{
  "success": false,
  "message": "Resource not found or validation error",
  "errors": {
    "field_name": ["Specific error detail"]
  }
}
```

---

## 2. Catalog & Taxonomy Endpoints

### `GET /api/v1/categories`
Retrieves all active product categories with live published product counts.

- **Authentication:** Public
- **HTTP Method:** `GET`
- **Query Parameters:** None
- **Success Response (`200 OK`):**
```json
{
  "success": true,
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
  ]
}
```

---

### `GET /api/v1/categories/{slug}`
Retrieves a single category by slug along with its published products.

- **Authentication:** Public
- **HTTP Method:** `GET`
- **Path Parameters:**
  - `slug` (string, required): Category slug (e.g. `fire-extinguishers`)
- **Success Response (`200 OK`):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Fire Extinguishers",
    "slug": "fire-extinguishers",
    "products": [ ... ]
  }
}
```

---

### `GET /api/v1/products`
Retrieves paginated equipment products with live prices in ৳ BDT and stock availability.

- **Authentication:** Public
- **HTTP Method:** `GET`
- **Query Parameters:**
  - `category` (string, optional): Filter by category slug (e.g. `fire-extinguishers`)
  - `q` / `search` (string, optional): Keyword search matching product title, SKU, description, specifications
  - `featured` (boolean, optional): `1` or `true` to filter featured items
  - `in_stock` (boolean, optional): `1` or `true` to filter available stock
  - `sort` (string, optional): `featured` (default), `price-low`, `price-high`, `newest`
  - `per_page` (integer, optional): Items per page (default: `50`, max: `100`)
- **Success Response (`200 OK`):**
```json
{
  "success": true,
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
  "pagination": {
    "total": 8,
    "per_page": 50,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

### `GET /api/v1/products/{slug}`
Retrieves complete product details for individual product view.

- **Authentication:** Public
- **HTTP Method:** `GET`
- **Path Parameters:**
  - `slug` (string, required): Product slug or ID
- **Success Response (`200 OK`):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "sku": "EXT-ABC-6KG",
    "title": "ABC Dry Chemical Powder Extinguisher (6kg)",
    "slug": "abc-dry-powder-extinguisher-6kg",
    "price": "1450.00",
    "compare_at_price": "1800.00",
    "stock_quantity": 100,
    "track_inventory": true,
    "description": "Multipurpose Class A, B, C fire extinguisher filled with 90% MAP powder.",
    "specifications": "<p>Certified under BFSCD standard. Nitrogen pressurized to 14-15 bar.</p>",
    "category": {
      "id": 1,
      "name": "Fire Extinguishers",
      "slug": "fire-extinguishers"
    }
  }
}
```

---

## 3. Shopping Cart Endpoints (Server-Authoritative)

### Session Header Requirement
All Cart API requests accept an optional `X-Cart-Session` header containing a UUID token. If omitted, the server automatically generates one and returns it in the response payload.

### `GET /api/v1/cart`
Retrieves current shopping cart, items, live product prices, and calculated subtotal.

- **Authentication:** Public (Guest Session / Token)
- **HTTP Method:** `GET`
- **Headers:** `X-Cart-Session: <uuid>`
- **Success Response (`200 OK`):**
```json
{
  "success": true,
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
  }
}
```

---

### `POST /api/v1/cart/items`
Adds an item to the shopping cart. Server enforces inventory limits and validates price.

- **Authentication:** Public
- **HTTP Method:** `POST`
- **Headers:** `X-Cart-Session: <uuid>`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "product_id": 1,
  "quantity": 1
}
```
- **Success Response (`200 OK`):** Returns refreshed cart object.
- **Stock Error (`422 Unprocessable Entity`):**
```json
{
  "success": false,
  "message": "Insufficient stock. Only 5 units available."
}
```

---

### `PUT /api/v1/cart/items/{id}`
Updates the quantity of an item in the cart. If quantity is `0`, item is removed.

- **Authentication:** Public
- **HTTP Method:** `PUT`
- **Request Body:** `{"quantity": 3}`
- **Success Response (`200 OK`):** Returns refreshed cart object.

---

### `DELETE /api/v1/cart/items/{id}`
Removes a specific line item from the cart.

- **Authentication:** Public
- **HTTP Method:** `DELETE`
- **Success Response (`200 OK`):** Returns refreshed cart object.

---

### `DELETE /api/v1/cart`
Empties all items from the current cart session.

- **Authentication:** Public
- **HTTP Method:** `DELETE`
- **Success Response (`200 OK`):** Returns empty cart object.

---

## 4. Brand & Partner Endpoints

### `GET /api/v1/client-logos`
Retrieves corporate partner logos for the homepage trust carousel.

- **Authentication:** Public
- **HTTP Method:** `GET`
- **Success Response (`200 OK`):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "BRAC University",
      "logo_path": "/wp-content/uploads/2017/11/bracuni.png",
      "website_url": "https://www.bracu.ac.bd",
      "sort_order": 1,
      "is_active": true
    }
  ]
}
```

---

## 4. Public Content & Contact Endpoints

| Endpoint | Method | Description |
|---|---|---|
| `/api/v1/health` | `GET` | Service status heartbeat |
| `/api/v1/projects` | `GET` | Portfolio engineering projects |
| `/api/v1/blog` | `GET` | Safety guides & technical articles |
| `/api/v1/settings` | `GET` | Site contact details, phones, address, PDF profile URL |
| `/api/v1/contact` | `POST` | Contact form inquiries (Rate-limited: 5 requests/min) |
