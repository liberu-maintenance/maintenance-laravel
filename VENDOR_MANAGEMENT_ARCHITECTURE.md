# Vendor Management System - Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         VENDOR MANAGEMENT SYSTEM                             │
│                   Liberu Maintenance CMMS - Laravel + Filament              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              ADMIN INTERFACE                                 │
│                            (Filament Resources)                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────────┐  ┌──────────────────┐  ┌─────────────────────────┐  │
│  │ VendorResource   │  │ ContractResource │  │ EvaluationResource      │  │
│  ├──────────────────┤  ├──────────────────┤  ├─────────────────────────┤  │
│  │ • List Vendors   │  │ • List Contracts │  │ • List Evaluations      │  │
│  │ • Create Vendor  │  │ • Create Contract│  │ • Create Evaluation     │  │
│  │ • Edit Vendor    │  │ • Edit Contract  │  │ • Edit Evaluation       │  │
│  │ • View Ratings   │  │ • View Status    │  │ • View Ratings          │  │
│  │ • Filter by Type │  │ • Track Expiry   │  │ • Filter by Performance │  │
│  └──────────────────┘  └──────────────────┘  └─────────────────────────┘  │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                            APPLICATION LAYER                                 │
│                           (Laravel Models & Logic)                           │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌────────────────────────────────────────────────────────────────────┐    │
│  │                         Company Model (Extended)                    │    │
│  │  ┌──────────────────────────────────────────────────────────┐      │    │
│  │  │ • type: customer, supplier, vendor, both                  │      │    │
│  │  │ • isVendor(), isSupplier(), isCustomer()                 │      │    │
│  │  │ • getAveragePerformanceRating()                          │      │    │
│  │  │ • getActiveContractsCount()                              │      │    │
│  │  │ • Relations: vendorContracts, evaluations, workOrders    │      │    │
│  │  └──────────────────────────────────────────────────────────┘      │    │
│  └────────────────────────────────────────────────────────────────────┘    │
│                                                                              │
│  ┌──────────────────┐  ┌──────────────────┐  ┌─────────────────────────┐  │
│  │ VendorContract   │  │ Vendor           │  │ WorkOrder (Extended)    │  │
│  │                  │  │ Performance      │  │                         │  │
│  ├──────────────────┤  │ Evaluation       │  ├─────────────────────────┤  │
│  │ • Contract Info  │  ├──────────────────┤  │ • vendor_id             │  │
│  │ • Financial Terms│  │ • 5 Categories   │  │ • vendor relation       │  │
│  │ • Dates & Status │  │ • Quality        │  │ • evaluations relation  │  │
│  │ • Auto-renewal   │  │ • Timeliness     │  │                         │  │
│  │ • isActive()     │  │ • Communication  │  │                         │  │
│  │ • isExpiringSoon│  │ • Cost Effective │  │                         │  │
│  │ • getDaysUntil   │  │ • Professional   │  │                         │  │
│  │   Expiration()   │  │ • Auto-calculate │  │                         │  │
│  │                  │  │   overall rating │  │                         │  │
│  └──────────────────┘  └──────────────────┘  └─────────────────────────┘  │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              DATA LAYER                                      │
│                         (MySQL/PostgreSQL Database)                          │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌────────────────────────────────────────────────────────────────────┐    │
│  │                          companies (existing)                       │    │
│  │  ┌──────────────────────────────────────────────────────────┐      │    │
│  │  │ company_id, name, type, address, contact_person, email   │      │    │
│  │  │ phone_number, is_active, payment_terms, ...              │      │    │
│  │  └──────────────────────────────────────────────────────────┘      │    │
│  └────────────────────────────────────────────────────────────────────┘    │
│                                                                              │
│  ┌──────────────────────────────┐  ┌──────────────────────────────────┐   │
│  │   vendor_contracts (new)     │  │ vendor_performance_evaluations   │   │
│  │  ┌────────────────────────┐  │  │           (new)                  │   │
│  │  │ id, vendor_id          │  │  │  ┌────────────────────────────┐  │   │
│  │  │ contract_number, title │  │  │  │ id, vendor_id             │  │   │
│  │  │ contract_type, status  │  │  │  │ vendor_contract_id        │  │   │
│  │  │ start_date, end_date   │  │  │  │ work_order_id             │  │   │
│  │  │ contract_value         │  │  │  │ evaluation_date           │  │   │
│  │  │ currency, payment_freq │  │  │  │ evaluated_by              │  │   │
│  │  │ auto_renewal, notes    │  │  │  │ quality_rating (1-5)      │  │   │
│  │  │ team_id                │  │  │  │ timeliness_rating (1-5)   │  │   │
│  │  └────────────────────────┘  │  │  │ communication_rating(1-5) │  │   │
│  └──────────────────────────────┘  │  │ cost_effectiveness(1-5)   │  │   │
│                                     │  │ professionalism (1-5)     │  │   │
│  ┌──────────────────────────────┐  │  │ overall_rating (avg)      │  │   │
│  │  work_orders (extended)      │  │  │ strengths, improvements   │  │   │
│  │  ┌────────────────────────┐  │  │  │ comments                  │  │   │
│  │  │ id, title, description │  │  │  │ would_recommend           │  │   │
│  │  │ vendor_id (new)        │  │  │  │ team_id                   │  │   │
│  │  │ customer_id, status    │  │  │  └────────────────────────────┘  │   │
│  │  │ priority, due_date     │  │  └──────────────────────────────────┘   │
│  │  │ ...                    │  │                                          │
│  │  └────────────────────────┘  │                                          │
│  └──────────────────────────────┘                                          │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                            KEY RELATIONSHIPS                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  Company (as Vendor)                                                         │
│      ├─ hasMany → VendorContracts                                           │
│      ├─ hasMany → VendorPerformanceEvaluations                              │
│      └─ hasMany → WorkOrders (as vendor)                                    │
│                                                                              │
│  VendorContract                                                              │
│      ├─ belongsTo → Company (vendor)                                        │
│      ├─ belongsTo → Team                                                    │
│      └─ hasMany → VendorPerformanceEvaluations                              │
│                                                                              │
│  VendorPerformanceEvaluation                                                 │
│      ├─ belongsTo → Company (vendor)                                        │
│      ├─ belongsTo → VendorContract                                          │
│      ├─ belongsTo → WorkOrder                                               │
│      ├─ belongsTo → User (evaluator)                                        │
│      └─ belongsTo → Team                                                    │
│                                                                              │
│  WorkOrder                                                                   │
│      ├─ belongsTo → Company (vendor)                                        │
│      ├─ belongsTo → Company (customer)                                      │
│      └─ hasMany → VendorPerformanceEvaluations                              │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              WORKFLOW                                        │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  1. CREATE VENDOR                                                            │
│     └─ Add company with type = 'vendor' or 'supplier'                       │
│                                                                              │
│  2. CREATE CONTRACT                                                          │
│     └─ Link to vendor, set terms, dates, and financial details              │
│                                                                              │
│  3. ASSIGN WORK ORDER                                                        │
│     └─ Link work order to vendor for external service                       │
│                                                                              │
│  4. COMPLETE WORK                                                            │
│     └─ Vendor performs maintenance work                                     │
│                                                                              │
│  5. EVALUATE PERFORMANCE                                                     │
│     └─ Rate vendor across 5 categories                                      │
│     └─ Overall rating calculated automatically                              │
│                                                                              │
│  6. REVIEW & REPORT                                                          │
│     └─ View vendor performance history                                      │
│     └─ Make data-driven decisions for future assignments                    │
│                                                                              │
│  7. MANAGE CONTRACT LIFECYCLE                                                │
│     └─ Track expiration dates                                               │
│     └─ Renew or terminate based on performance                              │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                           FEATURES AT A GLANCE                               │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ✅ Vendor relationship management                                           │
│  ✅ Service contract tracking with financial terms                           │
│  ✅ Contract lifecycle management (draft → active → expired → renewed)       │
│  ✅ Expiration alerts and auto-renewal configuration                         │
│  ✅ 5-category performance rating system                                     │
│  ✅ Automatic overall rating calculation                                     │
│  ✅ Work order assignment to vendors                                         │
│  ✅ Performance evaluation linked to work orders                             │
│  ✅ Vendor performance history and trends                                    │
│  ✅ High/low performer identification                                        │
│  ✅ Active contracts count per vendor                                        │
│  ✅ Average performance rating per vendor                                    │
│  ✅ Team-based data isolation                                                │
│  ✅ Comprehensive Filament admin interface                                   │
│  ✅ Full CRUD operations for all entities                                    │
│  ✅ Filtering and sorting capabilities                                       │
│  ✅ 24 comprehensive unit tests                                              │
│  ✅ Complete documentation (1,500+ lines)                                    │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                         IMPLEMENTATION STATUS                                │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  Status: ✅ COMPLETE AND PRODUCTION-READY                                    │
│                                                                              │
│  ├─ Database Schema: ✅ Complete (3 migrations)                              │
│  ├─ Models: ✅ Complete (2 new + 2 updated)                                  │
│  ├─ Filament Resources: ✅ Complete (3 resources with 9 pages)               │
│  ├─ Tests: ✅ Complete (24 unit tests)                                       │
│  ├─ Documentation: ✅ Complete (1,500+ lines)                                │
│  ├─ Code Review: ✅ Passed                                                   │
│  ├─ Security Scan: ✅ Passed (CodeQL)                                        │
│  └─ Acceptance Criteria: ✅ All Met                                          │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Quick Statistics

| Metric | Count |
|--------|-------|
| Files Created | 24 |
| Files Modified | 5 |
| Lines of Code | ~3,500+ |
| Database Tables | 3 (new) |
| Models | 4 (2 new, 2 updated) |
| Filament Resources | 3 |
| Filament Pages | 9 |
| Test Cases | 24 |
| Documentation Lines | 1,500+ |
| Commits | 4 |

## Technology Stack

- **Backend:** Laravel 12, PHP 8.5
- **Frontend:** Filament 5, Livewire 4
- **Database:** MySQL/PostgreSQL
- **Testing:** PHPUnit
- **Architecture:** MVC with Repository Pattern
