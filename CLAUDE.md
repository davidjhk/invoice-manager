# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**IMPORTANT: This is a Yii2 Basic Application Template with an integrated invoice management system.**

This is NOT a standalone HTML/CSS/JavaScript application. It's a full Yii2 web application with:
- Server-side PHP controllers and models
- Database integration (MySQL)
- Yii2's built-in authentication system
- An embedded invoice management single-page application

## Architecture

### Framework: Yii2 Basic Application Template

**Main Components:**
- **Yii2 Framework**: PHP MVC framework handling routing, authentication, database operations
- **Controllers**: Handle HTTP requests and business logic
- **Models**: Database entities and business logic (User, Invoice, Customer, etc.)
- **Views**: Render HTML responses and templates
- **Database**: MySQL database with proper table structure

### Authentication System
- **User Model**: Uses Yii2's built-in authentication with hardcoded users (admin/admin, demo/demo)
- **Session Management**: Yii2's user component with auto-login capability
- **Access Control**: All sensitive operations require authentication
- **Login**: Standard Yii2 login form at `/site/login`
- **Logout**: POST request to `/site/logout`

### Invoice Management App
- **Location**: Embedded SPA accessible at `/site/invoice-app`
- **Authentication**: Requires Yii2 login before access
- **Frontend**: Single-page JavaScript application using vanilla JS
- **Data Storage**: Browser localStorage for client-side data persistence
- **Integration**: JavaScript checks Yii2 authentication status via `/site/check-auth`

## File Structure

```
invoice/                              # Yii2 Application Root
├── web/                              # Web accessible directory
│   ├── index.php                    # Application entry point
│   ├── invoice/                     # Invoice app static files
│   │   ├── styles.css               # Invoice app CSS
│   │   └── script.js                # Invoice app JavaScript
│   └── assets/                      # Yii2 generated assets
├── controllers/                      # Yii2 Controllers
│   ├── SiteController.php           # Main controller with login/invoice-app
│   ├── InvoiceController.php        # Invoice CRUD operations
│   ├── CustomerController.php       # Customer management
│   └── CompanyController.php        # Company settings
├── models/                          # Yii2 Models
│   ├── User.php                     # User authentication model
│   ├── LoginForm.php                # Login form validation
│   ├── Invoice.php                  # Invoice database model
│   ├── Customer.php                 # Customer database model
│   └── Company.php                  # Company settings model
├── views/                           # Yii2 Views
│   ├── layouts/main.php             # Main layout with navigation
│   ├── site/                        # Site controller views
│   │   ├── login.php                # Login page
│   │   ├── index.php                # Dashboard
│   │   └── invoice-app.php          # Invoice SPA container
│   ├── invoice/                     # Invoice CRUD views
│   ├── customer/                    # Customer management views
│   └── company/                     # Company settings views
├── config/                          # Yii2 Configuration
│   ├── web.php                      # Main app configuration
│   ├── db.php                       # Database configuration
│   └── params.php                   # Application parameters
├── migrations/                      # Database migrations
├── vendor/                          # Composer dependencies
├── composer.json                    # PHP dependencies
└── yii                              # Console application entry
```

## Development Notes

### Running the Application
- **Entry Point**: `/web/index.php` (Yii2 application)
- **Web Server**: Requires PHP web server (Apache/Nginx) or PHP built-in server
- **Database**: MySQL database required for full functionality
- **Dependencies**: Managed via Composer

### Authentication Flow
1. User accesses any protected route (e.g., `/site/invoice-app`)
2. Yii2 checks authentication status
3. If not logged in, redirects to `/site/login`
4. After successful login, user can access invoice app
5. Invoice app JavaScript confirms auth status via AJAX

### Invoice App Integration
- **Access URL**: `/site/invoice-app` or `/invoice-app` (pretty URL)
- **Layout**: Uses no Yii2 layout (standalone HTML page)
- **Authentication Check**: JavaScript calls `/site/check-auth` endpoint
- **Logout**: Redirects to Yii2 logout (`/site/logout`)
- **Data Persistence**: Client-side localStorage (independent of Yii2 database)

### Database Configuration
- **Host**: localhost
- **Database**: bitnami_wordpress
- **Username**: bn_wordpress
- **Password**: d6ab501583
- **Tables**: Prefixed with `jdosa_` (companies, customers, invoices, etc.)

### Key URLs and Routes
- `/` - Dashboard (requires login)
- `/site/login` - Login page
- `/site/logout` - Logout (POST only)
- `/site/invoice-app` - Invoice management SPA
- `/site/check-auth` - Authentication status API (JSON)
- `/invoice` - Traditional invoice CRUD
- `/customer` - Customer management
- `/company/settings` - Company settings

### Email Configuration
- Uses SwiftMailer through Yii2
- File transport enabled by default (development)
- Invoice app also supports SMTP2GO for direct email sending

### Important Reminders for Claude

1. **NEVER assume this is a standalone HTML app** - Always work within Yii2 framework
2. **Authentication is handled by Yii2** - Don't create custom login systems
3. **Use Yii2 conventions** - Controllers in `/controllers/`, models in `/models/`, views in `/views/`
4. **Database operations** - Use Yii2 Active Record, not direct SQL
5. **Routing** - Use Yii2 URL rules in `config/web.php`
6. **Static files** - Place in `/web/` directory for web access
7. **Security** - Use Yii2's built-in security features (CSRF, access control)
8. **Entry point** - Always use `/web/index.php`, never direct file access
9. **ALWAYS provide a list of changed files** - At the end of each task completion, list all modified/created files

### Code Patterns
- **Controllers**: Use Yii2 Controller base class and behaviors
- **Models**: Extend ActiveRecord for database entities
- **Views**: Use Yii2 view rendering with layouts
- **Authentication**: Use `Yii::$app->user` component
- **Database**: Use Yii2 migrations for schema changes
- **Configuration**: Store settings in `config/` directory

### Invoice App Specific
- **Frontend Framework**: Vanilla JavaScript (InvoiceManager class)
- **UI Pattern**: Single-page application with view switching
- **Data Flow**: localStorage for persistence, AJAX for auth checks
- **PDF Generation**: Client-side using jsPDF and html2canvas
- **Email**: Dual support (Yii2 SwiftMailer + SMTP2GO API)