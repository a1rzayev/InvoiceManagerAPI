# Invoice Manager API 🧾

Hey there! 👋 Welcome to the Invoice Manager API - a powerful Laravel-based solution for managing invoices, products, and users. Think of it as your digital assistant for all things invoicing!

## What's Inside? 🎁

This API is packed with everything you need to run a smooth invoicing operation:

- **👥 User Management**: Handle different user types (Admins, Sellers, Clients) with ease
- **📦 Product Management**: Keep track of your products with smart search and filtering
- **🧾 Invoice Management**: Create, track, and manage invoices with full status tracking
- **📚 Interactive Documentation**: Test everything right in your browser with Swagger UI

## Quick Start 🚀

Want to get this running on your machine? Here's how:

1. **Clone and install**:
   ```bash
   composer install
   ```

2. **Set up your environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure your database** in the `.env` file

4. **Run the migrations**:
   ```bash
   php artisan migrate
   ```

5. **Generate the API docs**:
   ```bash
   php artisan l5-swagger:generate
   ```

6. **Start the server**:
   ```bash
   php artisan serve
   ```

That's it! 🎉 Your API will be running at `http://localhost:8000`

## Check Out the Documentation 📖

The best part? You can explore and test the entire API right in your browser! Just head over to:

```
http://localhost:8000/api/documentation
```

Or simply visit `http://localhost:8000` - it'll redirect you straight to the docs!

## What Can You Do? 🤔

### Users 👥
- Get all users: `GET /api/users`
- Create a new user: `POST /api/users`
- Get a specific user: `GET /api/users/{id}`
- Update a user: `PUT /api/users/{id}`
- Delete a user: `DELETE /api/users/{id}`
- Get users by role: `/api/users/admins/list`, `/api/users/sellers/list`, `/api/users/clients/list`

### Products 📦
- Get all products: `GET /api/products`
- Create a new product: `POST /api/products`
- Get a specific product: `GET /api/products/{id}`
- Update a product: `PUT /api/products/{id}`
- Delete a product: `DELETE /api/products/{id}`
- Search products: `GET /api/products/search/query`
- Filter by price: `GET /api/products/price-range/filter`

### Invoices 🧾
- Get all invoices: `GET /api/invoices`
- Create a new invoice: `POST /api/invoices`
- Get a specific invoice: `GET /api/invoices/{id}`
- Update an invoice: `PUT /api/invoices/{id}`
- Delete an invoice: `DELETE /api/invoices/{id}`
- Filter by status: `GET /api/invoices/status/{status}`
- Get by seller: `GET /api/invoices/seller/{sellerId}`
- Get by client: `GET /api/invoices/client/{clientId}`
- Update status: `PATCH /api/invoices/{id}/status`

## Data Structure 📊

### User Model
```json
{
  "id": "uuid",
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "address": "123 Main St",
  "role": "seller", // admin, seller, or client
  "is_active": true
}
```

### Product Model
```json
{
  "id": "uuid",
  "name": "Premium Widget",
  "description": "The best widget money can buy",
  "unit_price": 29.99
}
```

### Invoice Model
```json
{
  "id": "uuid",
  "seller_id": "uuid",
  "client_id": "uuid",
  "status": "draft", // draft, sent, paid, overdue
  "items": [
    {
      "product_id": "uuid",
      "quantity": 2,
      "total_price": 59.98
    }
  ]
}
```

## Validation Rules 📝

### Creating/Updating Users
- **name**: Required, max 255 characters
- **email**: Required, valid email, must be unique
- **password**: Required, minimum 8 characters
- **phone**: Optional, max 20 characters
- **address**: Optional, max 500 characters
- **role**: Required, must be admin/seller/client
- **is_active**: Boolean (defaults to true)

### Creating/Updating Products
- **name**: Required, max 255 characters, must be unique
- **description**: Optional, max 1000 characters
- **unit_price**: Required, must be 0 or greater

### Creating/Updating Invoices
- **seller_id**: Required, must be a valid seller user
- **client_id**: Required, must be a valid client user
- **status**: Optional, defaults to "draft"
- **items**: Required array with at least one item:
  - **product_id**: Required, must exist
  - **quantity**: Required, minimum 1
  - **total_price**: Required, minimum 0

## Error Handling ⚠️

The API uses standard HTTP status codes to let you know what's happening:

- **200** ✅ - Everything worked perfectly
- **201** ✅ - Something new was created successfully
- **400** ❌ - Something's wrong with your request
- **404** ❌ - Couldn't find what you're looking for
- **409** ❌ - Conflict (like trying to delete a product that's used in invoices)
- **422** ❌ - Validation failed (check the error details)

All error responses include a helpful message explaining what went wrong.

## Need Help? 🤝

If you run into any issues or have questions:

1. **Check the Swagger docs** - They're interactive and you can test endpoints right there
2. **Look at the error messages** - They're designed to be helpful
3. **Check the logs** - Laravel keeps detailed logs in `storage/logs/`

## Tech Stack 💻

- **Laravel 12** - The PHP framework
- **L5-Swagger** - For beautiful API documentation
- **SQLite** - Simple database (you can switch to MySQL/PostgreSQL)
- **OpenAPI 3.0** - Modern API specification

---

Happy coding! 🎉 If you find this useful, feel free to star the repo or contribute!
