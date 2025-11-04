# 🔍 Voltimum - Product Search Platform

A modern Laravel application featuring advanced product search capabilities with Typesense, comprehensive testing, and a polished UI built with Livewire and Flux.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Database Structure](#database-structure)
- [Search Functionality](#search-functionality)
- [Development Tools](#development-tools)
- [Testing](#testing)
- [Contributing](#contributing)

## 🎯 Overview

This project demonstrates a production-ready Laravel application with:

- **Full-text search** powered by Typesense with nested field support
- **User authentication** with Laravel Fortify
- **Admin panel** with Filament v4
- **Real-time UI** with Livewire v3
- **Modern styling** with Tailwind CSS v4 and Flux UI
- **Comprehensive testing** with Pest PHP

The application allows users to search through products by multiple attributes including manufacturer details, distributor information, and item-specific data.

## ✨ Features

### 🔎 Advanced Search Engine
- **Multi-field search** across products, manufacturers, and distributors
- **Nested field indexing** supporting deep relationships
- **Real-time search** with debounced input
- **Faceted search capabilities** ready for filtering
- **Dark mode support** throughout the interface

### 🏗️ Architecture
- Clean separation of concerns with Controllers, Livewire components, and Views
- Scout integration for seamless search indexing
- Automatic model indexing via observers
- Optimized eager loading for performance

### 🎨 User Interface
- Beautiful, responsive design
- Dark mode compatible
- Product catalog with grid layout
- Detailed product view pages
- Breadcrumb navigation
- Loading states and empty states

## 🛠️ Technology Stack

| Category | Technology | Version |
|----------|-----------|---------|
| **Framework** | Laravel | v12 |
| **PHP** | PHP | 8.4+ |
| **Search** | Typesense | Latest |
| **Frontend** | Livewire | v3 |
| **UI Components** | Flux UI | v2 (Free) |
| **Styling** | Tailwind CSS | v4 |
| **Admin Panel** | Filament | v4 |
| **Testing** | Pest PHP | v4 |
| **Authentication** | Laravel Fortify | v1 |
| **Monitoring** | Laravel Telescope | v5 |
| **Monitoring** | Laravel Pulse | v1 |
| **Queue** | Laravel Horizon | v5 |
| **Code Quality** | PHPStan (Larastan) | v3 |
| **Code Quality** | Rector | v2 |
| **Code Style** | Laravel Pint | v1 |

## 📦 Requirements

- PHP 8.4 or higher
- Composer
- Node.js & NPM
- MySQL/PostgreSQL
- Typesense Server
- Laravel Herd (recommended) or Valet

## 🚀 Installation

### Quick Setup (Recommended)

```bash
composer setup
```

This command will:
- Set up the application key
- Run migrations
- Seed the database
- Install NPM dependencies
- Build assets

### Start Development

```bash
# Option 1: Using Laravel Herd (Recommended)
# Application will be available at https://voltimum.test
composer dev:herd

# Option 2: Using Composer Script
composer dev
```

## 🗄️ Database Structure

### Models & Relationships

```
Manufacturer
├── hasMany → Product

Product
├── belongsTo → Manufacturer
├── hasMany → Item

Distributor
├── hasMany → Item

Item
├── belongsTo → Product
├── belongsTo → Distributor
```

### Key Tables

- **manufacturers**: Company information (name, slug)
- **products**: Product catalog (name, slug, ean, description)
- **distributors**: Distribution partners (name, slug)
- **items**: Product availability per distributor (sku, price, available)
- **users**: Application users with authentication

## 🔍 Search Functionality

### Searchable Fields

Products can be searched by:

- **Product fields**: `name`, `slug`, `ean`, `description`
- **Manufacturer fields**: `name`, `slug`
- **Item fields**: `sku`
- **Distributor fields**: `name`, `slug`

### Search Features

- **Full-text search** across all indexed fields
- **Nested field search** for related models
- **Case-insensitive** matching
- **Partial word** matching
- **Multi-word** queries with AND logic
- **Real-time results** with 300ms debounce

### Example Searches

```php
// Search by product name
Product::search('Wireless Headphones')->get();

// Search by SKU
Product::search('SKU-12345')->get();

// Search by manufacturer
Product::search('Acme Corp')->get();

// Search with pagination
Product::search('keyboard')->paginate(12);
```

## 🧰 Development Tools

### Code Quality & Analysis

```bash
# Run static analysis with PHPStan
composer lint

# Test static analysis (CI mode)
composer lint:test
```

### Monitoring & Debugging

- **Laravel Telescope**: `/telescope` - Debug and monitor requests, queries, jobs, etc.
- **Laravel Pulse**: Monitor application performance and usage
- **Laravel Horizon**: Monitor queues (if using Redis)

### Admin Panel

- **Filament**: `/admin` - Full-featured admin panel for managing resources

## 🧪 Testing

### Run All Tests

```bash
composer test
```

## 📚 Useful Commands

### Scout Commands

```bash
# Import all products into search index
php artisan scout:import "App\Models\Product"

# Flush the search index
php artisan scout:flush "App\Models\Product"

# Delete and re-import all products
php artisan scout:flush "App\Models\Product"
php artisan scout:import "App\Models\Product"
```

### Database Commands

```bash
# Fresh migration with seeders
php artisan migrate:fresh --seed
```

### Cache Commands

```bash
# Clear all caches
php artisan optimize:clear
```
