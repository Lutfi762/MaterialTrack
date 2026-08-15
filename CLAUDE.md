CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Project Overview

MaterialTrack is a Laravel 13 + Inertia.js + Vue 3 inventory management system for tracking material stock movements across departments. The application manages items, stock ins/outs, stock opnames (physical counts), locations, categories, and departments with role-based permissions via Spatie Laravel Permission.

Tech Stack
Backend: Laravel 13.21 (PHP 8.3+)
Frontend: Vue 3.5 + Inertia.js 3.6
Styling: Tailwind CSS 4.0
Charts: ApexCharts via vue3-apexcharts
Auth: Laravel Fortify with two-factor authentication and passkey support
PDF: barryvdh/laravel-dompdf
Excel: maatwebsite/excel
Build: Vite 8.0
Development Commands
Setup
bash
composer setup              # Full setup: install deps, copy .env, generate key, migrate, build assets
composer dev                # Start all services: Laravel server, queue worker, Pail logs, Vite dev server
Daily Development
bash
php artisan serve           # Start Laravel dev server (http://127.0.0.1:8000)
npm run dev                 # Start Vite dev server for hot module replacement
php artisan queue:work      # Process background jobs (stock movement observers)
php artisan pail            # Real-time log viewer
Database
bash
php artisan migrate         # Run migrations
php artisan migrate:fresh --seed  # Reset database and seed
php artisan db:seed         # Run seeders only
Testing & Code Quality
bash
composer test               # Clear config cache and run PHPUnit tests
php artisan test            # Run PHPUnit tests
php artisan pint            # Format code with Laravel Pint
Cache Management
bash
php artisan config:cache    # Cache configuration
php artisan config:clear    # Clear configuration cache
php artisan cache:clear     # Clear application cache
php artisan route:cache     # Cache routes
php artisan route:clear     # Clear route cache
Architecture
Core Domain Models

The system tracks inventory through these primary entities:

Item: Core inventory items with current_stock, min_stock, computed stock_status attribute (aman, menipis, habis)
Category: Item categories, linked to departments via many-to-many pivot (category_department)
Department: Organizational units that own categories
Location: Physical storage locations for items
Pic: Person In Charge (penanggung jawab) for transactions
StockIn: Incoming stock transactions
StockOut: Outgoing stock transactions with PIC tracking
StockOpname: Physical stock count/audit records
StockMovement: Immutable ledger of all stock changes (updated_at is disabled)
Automatic Stock Movement Tracking

Stock movements are automatically recorded via Eloquent Observers (app/Observers/):

StockInObserver: Logs stock increases when StockIn records are created
StockOutObserver: Logs stock decreases when StockOut records are created

These observers create StockMovement records with:

stock_before and balance_after for audit trail
reference_type and reference_id for polymorphic relation to source transaction
user_id from auth()->id()
type enum: in, out, opname, adjustment

Important: When modifying stock transaction logic, ensure observers are triggered correctly to maintain movement history integrity.

Permission System

Uses Spatie Laravel Permission for RBAC:

Permissions follow pattern: resource.action (e.g., items.create, roles.edit)
Frontend permission checks via global mixin: hasAnyPermission(['items.create', 'items.edit'])
Middleware protection on routes: ->middleware('permission:roles.index|roles.create')
Permissions are seeded via PermissionsTableSeeder, roles via RolesTableSeeder
Frontend Structure

Inertia.js Setup (resources/js/app.js):

Vue 3 with Inertia.js adapter
Global mixins for hasAnyPermission() and formatPrice()
ApexCharts plugin for dashboard visualizations
Pages resolved via ./Pages/**/*.vue glob pattern

Layouts:

Layouts/App.vue: Main authenticated layout with Sidebar.vue and Header.vue
Layouts/Auth.vue: Guest layout for login/register

Key Component Patterns:

Components/Pagination.vue: Reusable Laravel pagination component
Components/Breadcrumb.vue: Dynamic breadcrumb from route meta
Utils/confirmDialog.js: SweetAlert2 wrapper for confirmations
Utils/permissions.js: Permission check utilities
UI/UX Design System

This project is in an ongoing visual redesign toward a premium enterprise SaaS look (reference: Linear, Vercel dashboard, Stripe dashboard, SAP Fiori modern). Follow this section for ANY frontend/UI task, whether explicitly asked or not, unless the user says otherwise.

Hard rule

UI redesign tasks touch frontend only: resources/js/**, Tailwind config, and static assets. Never modify migrations, models, controllers, routes, permissions, seeders, or any app/** PHP logic as a side effect of a UI task. If a UI change seems to require a backend change, stop and ask first.

Design tokens
Sidebar: 
#0F172A (dark navy)
Primary/accent: 
#7C3AED (purple)
Background: 
#F8FAFC
Card: white + soft shadow, rounded-xl
Status colors: success 
#16A34A, warning 
#F59E0B, danger 
#DC2626
Font: Inter or Plus Jakarta Sans

Keep these centralized in the Tailwind config / a shared tokens file — don't hardcode hex values inline across components. If a tokens file doesn't exist yet at resources/css or tailwind.config.js, create one and reuse it everywhere.

Component reuse

Before building a new UI piece, check if an equivalent already exists (Components/Pagination.vue, Components/Breadcrumb.vue, card/table/badge components already used on the Dashboard and Login pages). Extend/reuse existing components rather than creating near-duplicates with slightly different markup.

Animation conventions
Keep a single source of truth for timing/easing, e.g. resources/js/utils/motion.js — entrance animations 400–800ms, hover/ interaction animations 150–400ms, ease-out for entering, ease-in for exiting.
Prefer ApexCharts' built-in animation options for charts (already used in this project) instead of adding a new charting animation library.
Respect prefers-reduced-motion.
Keep animation subtle — this is enterprise software, not a landing page.
Scope discipline

When asked to redesign or animate "this page" or "this component," touch only that page/component and its directly reused sub-components. Don't proactively restyle unrelated pages in the same session unless asked — do one page/feature per task to keep changes reviewable and sessions cheap.

Database Conventions
All models use standard Laravel timestamps except StockMovement (no updated_at)
Item codes are auto-generated with category prefix (see code_prefix in categories)
Many-to-many relationships use explicit pivot tables (e.g., category_department)
Stock movements use polymorphic relations via reference_type/reference_id
Export & PDF Generation

Controllers provide export capabilities:

exportExcel(): Excel exports via Maatwebsite Excel (app/Exports/)
downloadPdf(): Single record PDF via DomPDF
downloadAllPdf(): Bulk PDF export of filtered records
Route Structure

All application routes are prefixed with /apps and require authentication:

/apps/dashboard              - Dashboard with charts and low stock alerts
/apps/items                  - CRUD for inventory items
/apps/categories             - CRUD for categories
/apps/stock-ins              - Stock in transactions
/apps/stock-outs             - Stock out transactions
/apps/stock-movements        - Read-only movement history
/apps/stock-opnames          - Physical stock counts
/apps/departments            - Department management
/apps/pics                   - PIC management
/apps/locations              - Location management
/apps/roles                  - Role management (permission protected)
/apps/permissions            - Permission list (permission protected)
/apps/users                  - User management
Authentication

Laravel Fortify handles authentication with:

Email/password login with rate limiting (5 attempts/minute)
Two-factor authentication support (TOTP)
Passkey (WebAuthn) support
Password reset via email
Custom logout response: App\Http\Responses\LogoutResponse redirects to login
Queue Configuration

Queue connection is database (see .env.example). Background jobs process stock movement observers and exports. Run php artisan queue:work during development.

IDE Helper

Laravel IDE Helper is installed for better autocomplete:

bash
php artisan ide-helper:generate    # Generate helper file
php artisan ide-helper:models      # Generate model docblocks
Testing Notes
PHPUnit 12.5 is configured
Run tests with composer test (clears config cache first) or php artisan test
Tests directory: tests/
Common Patterns
Adding a New Stock Transaction Type
Create migration for transaction table
Create model extending Illuminate\Database\Eloquent\Model
Create observer in app/Observers/ to log to StockMovement
Register observer in AppServiceProvider::boot()
Update StockMovement::$fillable if adding new fields
Create controller with CRUD and export methods
Add routes in routes/web.php under /apps prefix
Create Vue pages in resources/js/Pages/Apps/
Working with Stock Movements

Stock movements are append-only and should never be updated directly. They serve as an immutable audit log. When debugging stock discrepancies:

Query StockMovement filtered by item_id
Verify stock_before + qty = balance_after for each record
Check that observers fired correctly for all StockIn/StockOut records
Frontend Permission Checks
vue
<button v-if="hasAnyPermission(['items.create'])">
    Create Item
</button>

Access current user permissions via $page.props.auth.permissions (injected by HandleInertiaRequests middleware).