# Inventra Dashboard System - Enhancement Guide

## Overview

This document outlines the comprehensive enhancements made to your Inventra inventory management dashboard system. The enhancements integrate three dashboard modules: **Admin Dashboard**, **Branch Dashboard**, and **Settings Dashboard**.

---

## 📁 Project Structure

```
dev.board.tmlhub.com/
├── config/
│   ├── config.php
│   └── database.php
├── core/
│   ├── Router.php
│   ├── Security.php
│   ├── SecurityManager.php
│   └── SessionManager.php
├── modules/
│   └── Inventra/
│       ├── config/
│       │   └── module.php
│       ├── controllers/
│       │   ├── AdminController.php          (Enhanced with AJAX handlers)
│       │   ├── AuthController.php
│       │   ├── BaseController.php
│       │   ├── BranchController.php
│       │   ├── DashboardController.php
│       │   └── SettingsController.php       (NEW - Settings management)
│       ├── models/
│       │   ├── AdminDashboardModel.php
│       │   ├── BranchDashboardModel.php
│       │   ├── SettingsDashboardModel.php   (NEW - Settings data access)
│       │   └── User.php
│       └── views/
│           ├── admin_dashboard.php
│           ├── settings_dashboard.php       (NEW - Settings UI)
│           └── branch/
│               ├── dashboard.php
│               └── components/
│                   ├── cards.php
│                   ├── modals.php
│                   └── tables.php
├── public/
│   ├── css/
│   │   └── branch.css
│   └── js/
│       └── branch.js
├── views/
│   ├── layout/
│   │   └── main.php
│   └── pages/
│       └── login.php
└── index.php
```

---

## 🎯 Key Enhancements

### 1. **Settings Dashboard** (NEW)

#### Controller: `SettingsController.php`
**Location:** `modules/Inventra/controllers/SettingsController.php`

**Responsibilities:**
- Manage inventory items (CRUD operations)
- Manage item categories (CRUD operations)
- Manage inventory batches (CRUD operations)
- Handle AJAX requests for real-time updates

**Key Methods:**
```php
public function index()                      // Display settings dashboard
public function handleAjax()                 // Process AJAX requests
private function handleAddItem()             // Add inventory item
private function handleEditItem()            // Edit inventory item
private function handleDeleteItem()          // Delete inventory item
private function handleAddCategory()         // Add category
private function handleEditCategory()        // Edit category
private function handleDeleteCategory()      // Delete category
private function handleAddBatch()            // Add batch
private function handleEditBatch()           // Edit batch
private function handleDeleteBatch()         // Delete batch
```

**Security Features:**
- CSRF token validation on all AJAX requests
- Admin-only access verification
- Input sanitization for all form fields
- PDO prepared statements for database operations

---

#### Model: `SettingsDashboardModel.php`
**Location:** `modules/Inventra/models/SettingsDashboardModel.php`

**Inventory Items Methods:**
```php
getAllItems()                    // Fetch all items
getItemById($id)                // Fetch single item
addItem($data)                  // Create new item
updateItem($id, $data)          // Update existing item
deleteItem($id)                 // Remove item
searchItems($query)             // Search items by name/code
```

**Categories Methods:**
```php
getAllCategories()              // Get all categories
getCategoryByKey($key)          // Get single category
addCategory($data)              // Add new category
updateCategory($key, $data)     // Update category
deleteCategory($key)            // Remove category
```

**Batches Methods:**
```php
getAllBatches()                 // Get all batches
getBatchById($id)              // Get single batch
getBatchesByStatus($status)    // Filter by status
addBatch($data)                // Create new batch
updateBatch($id, $data)        // Update batch
deleteBatch($id)               // Remove batch
```

**Utility Methods:**
```php
countItemsInBatch($batchId)    // Count items in batch
getItemCountByCategory($cat)   // Count items per category
getTotalItemCount()            // Total items count
getTotalBatchCount()           // Total batches count
searchBatches($query)          // Search batches
```

---

#### View: `settings_dashboard.php`
**Location:** `modules/Inventra/views/settings_dashboard.php`

**Features:**
- **Tabbed Interface:** Main tabs for Inventory and Batches
- **Sub-tabs:** Items and Categories under Inventory
- **Item Management:**
  - Add items with name, code, category, unit value, image, description
  - Edit existing items
  - Delete items with confirmation
  - Display items in sortable table

- **Category Management:**
  - Add categories with custom icons (emoji)
  - Edit category details
  - Delete categories
  - Grid view for visual category display

- **Batch Management:**
  - Create batches with MM/YYYY format
  - Set batch status (planning, distributing, completed, cancelled)
  - Add supplier and notes
  - View all batches in table format
  - Edit/delete batches

**Styling:**
- Responsive design (desktop, tablet, mobile)
- Color-coded status badges
- Smooth animations and transitions
- Professional WordPress-style UI

---

### 2. **Enhanced Admin Controller**

#### Controller: `AdminController.php`
**Location:** `modules/Inventra/controllers/AdminController.php`

**AJAX Handlers for Admin Actions:**
```php
handleApproveAllocation()        // Approve branch allocations
handleDeclineAllocation()        // Decline with reason
handleApproveAdjustment()        // Approve inventory adjustments
handleDeclineAdjustment()        // Decline with reason
handleApproveDispute()           // Resolve disputes as approved
handleDeclineDispute()           // Resolve disputes as declined
handleApproveTransfer()          // Approve branch transfers
handleDeclineTransfer()          // Decline transfers
```

**Security Checks:**
- Authentication verification
- Role-based access control (admin only)
- CSRF token validation
- Request validation with proper HTTP status codes

**Response Format:**
```json
{
  "success": true/false,
  "message": "Operation result message"
}
```

---

### 3. **Architecture & Design Patterns**

#### MVC Pattern
- **Models:** PDO-based database access layer
- **Views:** Template files with PHP variables
- **Controllers:** Business logic and request handling

#### Security Layer
- **CSRF Protection:** Token generation and validation
- **Session Management:** User authentication state
- **Input Validation:** Sanitization of all user inputs
- **SQL Injection Prevention:** PDO prepared statements

#### Data Flow
```
User Request
    ↓
Router (index.php?action=...)
    ↓
Controller (handleAjax / render view)
    ↓
Model (Database queries via PDO)
    ↓
Database
    ↓
Response (JSON for AJAX, HTML for views)
```

---

## 🔄 Usage Examples

### Access Settings Dashboard
```
GET /index.php?action=settings
```

### Add Item via AJAX
```javascript
const formData = new FormData();
formData.append('ajax_action', 'add_item');
formData.append('item_name', 'Widget');
formData.append('item_code', 'WID-001');
formData.append('item_category', 'tools');
formData.append('unit_value', '25.50');
formData.append('_wpnonce', csrfToken);

fetch('/index.php?action=settings_ajax', {
    method: 'POST',
    body: formData
}).then(r => r.json()).then(data => {
    if (data.success) {
        alert('Item added successfully');
        location.reload();
    }
});
```

### Approve Allocation via Admin AJAX
```javascript
const formData = new FormData();
formData.append('action', 'approve_allocation');
formData.append('allocation_id', 123);
formData.append('_wpnonce', csrfToken);

fetch('/index.php?action=admin_ajax', {
    method: 'POST',
    body: formData
}).then(r => r.json()).then(data => {
    // Handle response
});
```

---

## 🔧 Configuration

### Database Tables Required
```sql
-- Items table
CREATE TABLE IF NOT EXISTS inventra_inventory_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(100) UNIQUE NOT NULL,
    category VARCHAR(100),
    unit_value DECIMAL(10, 2),
    image_url TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Batches table
CREATE TABLE IF NOT EXISTS inventra_batches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    batch_mm_yyyy VARCHAR(7),
    status ENUM('planning', 'distributing', 'completed', 'cancelled'),
    supplier VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories (stored as PHP option or custom table)
-- Current implementation stores in PHP array
```

---

## 📝 Environment Setup

### Required Configuration (config.php)
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventra_db');
define('DB_USER', 'db_user');
define('DB_PASS', 'db_password');
define('BASE_URL', 'http://dev.board.tmlhub.com');
define('CSRF_TOKEN_LENGTH', 32);
```

### Route Mapping
Update your Router to recognize new routes:
```php
'settings' => 'SettingsController@index'
'settings_ajax' => 'SettingsController@handleAjax'
```

---

## 🚀 Features & Capabilities

### Settings Dashboard Features
✅ Tabbed interface for organized navigation  
✅ Real-time form validation  
✅ AJAX-based CRUD operations  
✅ Responsive design  
✅ Item search functionality  
✅ Batch status tracking  
✅ Category emoji support  
✅ Batch MM/YYYY format  
✅ Supplier tracking  
✅ Notes field for additional info  

### Admin Dashboard Enhancements
✅ Approval workflows for allocations  
✅ Adjustment approval/decline  
✅ Dispute resolution  
✅ Transfer approval system  
✅ Reason tracking for declines  
✅ Real-time notifications (ready for implementation)  

---

## 🔐 Security Considerations

1. **CSRF Protection**
   - Every form includes CSRF token
   - Token verified on AJAX handler entry
   - Tokens regenerated per session

2. **Authentication**
   - Session validation on every request
   - Role-based access control (admin vs branch)
   - Redirect to login if not authenticated

3. **Input Validation**
   - Sanitization of text fields
   - Type casting for numeric values
   - URL validation for image URLs
   - Enum validation for status fields

4. **SQL Injection Prevention**
   - PDO prepared statements exclusively
   - Parameterized queries throughout
   - No raw SQL string concatenation

---

## 📊 Database Integration

All models use PDO (PHP Data Objects) for:
- Connection pooling via singleton pattern
- Prepared statements with parameter binding
- Exception handling for errors
- Consistent fetch modes (associative arrays)

Example Query Pattern:
```php
$stmt = $this->pdo->prepare("
    SELECT id, name FROM table WHERE status = ?
");
$stmt->execute(['active']);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

---

## 🎨 UI/UX Features

### Responsive Design
- Mobile-first approach
- Breakpoints at 768px and 480px
- Touch-friendly button sizing
- Flexible grid layouts

### Visual Feedback
- Success/error alerts
- Status badge color coding
- Hover states on interactive elements
- Loading states for async operations

### Accessibility
- ARIA labels for screen readers
- Semantic HTML structure
- Keyboard navigation support
- Color contrast compliance

---

## 📈 Future Enhancements

1. **Batch Management**
   - Bulk operations (import/export)
   - Batch history tracking
   - Cost analysis per batch

2. **Item Management**
   - Image upload functionality
   - Barcode generation
   - Item variants support

3. **Reporting**
   - Usage statistics
   - Inventory valuation
   - Category performance reports

4. **Automation**
   - Scheduled batch status updates
   - Automatic low-stock alerts
   - Batch expiry notifications

---

## 🐛 Troubleshooting

### Common Issues

**"Insufficient permissions" error**
- Verify user role is 'admin'
- Check session is active
- Confirm CSRF token is passed

**"Database connection failed"**
- Check DATABASE configuration
- Verify MySQL user permissions
- Ensure required tables exist

**Form not submitting**
- Verify CSRF token is included
- Check form data format
- Review browser console for JS errors

---

## 📞 Support & Maintenance

### Code Quality
- PHPDoc comments on all methods
- Consistent naming conventions
- DRY principles applied
- Exception handling implemented

### Version Control
- Git-ready structure
- .gitignore configured
- Clear commit messages

### Documentation
- Inline code comments
- This comprehensive guide
- Method signatures documented

---

## Summary of New Files

| File | Type | Purpose |
|------|------|---------|
| `SettingsController.php` | Controller | Settings CRUD operations |
| `SettingsDashboardModel.php` | Model | Database access for settings |
| `settings_dashboard.php` | View | Settings UI with forms |

## Modified Files

| File | Changes |
|------|---------|
| `AdminController.php` | Added 8 AJAX handler methods |

---

**Last Updated:** January 12, 2026  
**Version:** 1.0.0  
**Compatibility:** PHP 7.4+, MySQL 8.0+
