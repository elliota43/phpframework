# Advanced CRUD API Example

This is a comprehensive RESTful API example built with the PHP framework. It demonstrates advanced CRUD operations, relationships, filtering, validation, and JSON responses.

## Features

- **Full CRUD Operations** - Create, Read, Update, Delete for Tasks and Categories
- **RESTful API Design** - Standard HTTP methods and status codes
- **JSON Request/Response** - Complete JSON API with proper content types
- **Model Relationships** - Tasks belong to Categories with eager loading support
- **Filtering & Search** - Query parameters for filtering tasks by status, priority, category, and search
- **Validation** - Input validation with detailed error messages
- **Accessors & Mutators** - Model-level data transformation
- **Error Handling** - Proper HTTP status codes and error responses

## Quick Start

### 1. Setup Database

```bash
# Create database and seed sample data
php examples/api/bin/setup.php
```

### 2. Start Development Server

```bash
# Run the API server on port 9090
php -S 127.0.0.1:9090 -t examples/api/public examples/api/public/index.php
```

### 3. Test the API

Open your browser or use a tool like curl/Postman:

```bash
# Get API info
curl http://127.0.0.1:9090/

# List all tasks
curl http://127.0.0.1:9090/tasks

# Get a single task
curl http://127.0.0.1:9090/tasks/1
```

## API Endpoints

### Tasks

#### List All Tasks
```
GET /tasks
```

**Query Parameters:**
- `status` - Filter by status (`pending`, `in_progress`, `completed`, `cancelled`)
- `priority` - Filter by priority (`low`, `medium`, `high`, `urgent`)
- `category_id` - Filter by category ID
- `search` - Search in title and description
- `include` - Include relationships (e.g., `category`)

**Example:**
```bash
curl "http://127.0.0.1:9090/tasks?status=pending&priority=high&include=category"
```

#### Get Single Task
```
GET /tasks/{id}
```

**Query Parameters:**
- `include` - Include relationships (e.g., `category`)

**Example:**
```bash
curl "http://127.0.0.1:9090/tasks/1?include=category"
```

#### Create Task
```
POST /tasks
Content-Type: application/json
```

**Request Body:**
```json
{
    "title": "New Task",
    "description": "Task description",
    "status": "pending",
    "priority": "medium",
    "category_id": 1,
    "due_date": "2024-12-31"
}
```

**Valid Status Values:** `pending`, `in_progress`, `completed`, `cancelled`
**Valid Priority Values:** `low`, `medium`, `high`, `urgent`

**Example:**
```bash
curl -X POST http://127.0.0.1:9090/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Complete API documentation",
    "description": "Write comprehensive docs",
    "status": "in_progress",
    "priority": "high",
    "category_id": 1
  }'
```

#### Update Task
```
PUT /tasks/{id}
Content-Type: application/json
```

**Request Body:** (all fields optional)
```json
{
    "title": "Updated Task",
    "status": "completed",
    "priority": "low"
}
```

**Example:**
```bash
curl -X PUT http://127.0.0.1:9090/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed",
    "priority": "high"
  }'
```

#### Delete Task
```
DELETE /tasks/{id}
```

**Example:**
```bash
curl -X DELETE http://127.0.0.1:9090/tasks/1
```

### Categories

#### List All Categories
```
GET /categories
```

**Query Parameters:**
- `include` - Include relationships (e.g., `tasks`)

**Example:**
```bash
curl "http://127.0.0.1:9090/categories?include=tasks"
```

#### Get Single Category
```
GET /categories/{id}
```

**Query Parameters:**
- `include` - Include relationships (e.g., `tasks`)

#### Create Category
```
POST /categories
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "Work",
    "description": "Work-related tasks"
}
```

#### Update Category
```
PUT /categories/{id}
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "Updated Category",
    "description": "New description"
}
```

#### Delete Category
```
DELETE /categories/{id}
```

## Response Format

### Success Response

```json
{
    "data": {
        "id": 1,
        "title": "Complete API documentation",
        "description": "Write comprehensive documentation",
        "status": "in_progress",
        "priority": "high",
        "category_id": 1,
        "due_date": "2024-12-31",
        "created_at": "2024-12-04 12:00:00",
        "updated_at": "2024-12-04 12:00:00"
    }
}
```

### Error Response

```json
{
    "error": "Validation failed",
    "errors": {
        "title": "Title is required",
        "status": "Status must be one of: pending, in_progress, completed, cancelled"
    }
}
```

### List Response

```json
{
    "data": [
        {
            "id": 1,
            "title": "Task 1",
            ...
        },
        {
            "id": 2,
            "title": "Task 2",
            ...
        }
    ],
    "count": 2
}
```

## HTTP Status Codes

- `200` - Success (GET, PUT)
- `201` - Created (POST)
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Example Use Cases

### Filter Tasks by Status
```bash
curl "http://127.0.0.1:9090/tasks?status=completed"
```

### Search Tasks
```bash
curl "http://127.0.0.1:9090/tasks?search=documentation"
```

### Get Task with Category
```bash
curl "http://127.0.0.1:9090/tasks/1?include=category"
```

### Create Task with All Fields
```bash
curl -X POST http://127.0.0.1:9090/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Fix critical bug",
    "description": "Fix authentication issue",
    "status": "in_progress",
    "priority": "urgent",
    "category_id": 1,
    "due_date": "2024-12-05"
  }'
```

### Update Task Status
```bash
curl -X PUT http://127.0.0.1:9090/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "completed"}'
```

## Database Schema

### Categories Table
- `id` (INTEGER, PRIMARY KEY)
- `name` (TEXT, NOT NULL)
- `description` (TEXT)
- `created_at` (TEXT)
- `updated_at` (TEXT)

### Tasks Table
- `id` (INTEGER, PRIMARY KEY)
- `title` (TEXT, NOT NULL)
- `description` (TEXT)
- `status` (TEXT, DEFAULT 'pending')
- `priority` (TEXT, DEFAULT 'medium')
- `category_id` (INTEGER, FOREIGN KEY)
- `due_date` (TEXT)
- `created_at` (TEXT)
- `updated_at` (TEXT)

## Architecture

This example demonstrates:

1. **Model-View-Controller (MVC)** pattern
2. **ActiveRecord** ORM with relationships
3. **RESTful** API design principles
4. **Dependency Injection** via service container
5. **Query Builder** with filtering capabilities
6. **Collection** utilities for data manipulation
7. **JSON** request/response handling
8. **Validation** with error reporting

## Code Structure

```
examples/api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── TaskController.php
│   │   │   └── CategoryController.php
│   │   └── Kernel.php
│   └── Models/
│       ├── Task.php
│       └── Category.php
├── bin/
│   └── setup.php
├── public/
│   └── index.php
├── routes/
│   └── api.php
├── storage/
│   └── views/
├── bootstrap.php
├── database.sqlite
└── README.md
```

## Notes

- The API uses SQLite for simplicity - replace with MySQL/PostgreSQL for production
- Validation is basic - extend with a validation library for production
- Authentication/Authorization not included - add middleware for production
- Error handling is simplified - add proper logging for production
- CORS headers not set - add if needed for frontend integration

## Extending the API

### Add Pagination
Modify `TaskController::index()` to add `limit()` and `offset()` based on query params.

### Add Authentication
Create middleware to validate API keys or JWT tokens.

### Add Rate Limiting
Implement rate limiting middleware to prevent abuse.

### Add Caching
Cache frequently accessed endpoints using a caching layer.

### Add Soft Deletes
Modify models to implement soft delete functionality.

