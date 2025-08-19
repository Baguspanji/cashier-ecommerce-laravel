# Cashier E-commerce - Laravel PWA Application

[![Laravel](https://img.shields.io/badge/Laravel-12.24.0-red.svg)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.5.13-green.svg)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.2.2-blue.svg)](https://www.typescriptlang.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2.0.5-purple.svg)](https://inertiajs.com)
[![PWA](https://img.shields.io/badge/PWA-Ready-orange.svg)](https://web.dev/progressive-web-apps/)
[![Tests](https://img.shields.io/badge/Tests-202_passed-green.svg)](https://pestphp.com)

A modern, feature-rich Point of Sale (POS) system built with Laravel, Vue.js, and Progressive Web App capabilities. Designed for retail businesses with offline-first functionality and real-time synchronization.

## 🚀 Features

### ✅ **Core Application (Completed)**
- **🔐 Authentication System**: Complete user management with role-based access
- **📊 Interactive Dashboard**: Real-time analytics and sales overview
- **🛍️ Product Management**: Full CRUD with barcode support and categorization
- **💰 Transaction System**: Intuitive POS interface with multiple payment methods
- **📦 Stock Management**: Real-time inventory tracking with adjustment capabilities
- **📈 Reporting System**: Comprehensive reports with export functionality
- **🛒 Shopping Cart**: Dynamic cart with real-time calculations
- **👥 User Management**: Role-based user administration
- **🌙 Dark Mode**: Complete dark/light theme support

### 🔄 **PWA Features (65% Complete)**
- ✅ **Service Worker**: Auto-generated with intelligent caching strategies
- ✅ **Web App Manifest**: Installable as native app experience
- ✅ **Auto-Updates**: Seamless background updates with user notifications
- ✅ **Offline Caching**: Strategic caching for static assets and API responses
- 🔄 **Offline Transactions**: Work offline, sync when connected *(In Progress)*
- 🔄 **Background Sync**: Automatic data synchronization *(In Progress)*
- 🔄 **Conflict Resolution**: Smart handling of data conflicts *(Planned)*

## 🛠️ Technology Stack

### Backend
- **Laravel 12.24.0** - Modern PHP framework with latest features
- **PHP 8.3.24** - Latest PHP with performance improvements
- **SQLite** - Lightweight database (production-ready for MySQL/PostgreSQL)
- **Pest 3.8.2** - Modern testing framework (202 tests, 836 assertions)
- **Laravel Sanctum** - API authentication for sync functionality

### Frontend
- **Vue.js 3.5.13** - Progressive JavaScript framework with Composition API
- **TypeScript 5.2.2** - Type-safe JavaScript development
- **Inertia.js 2.0.5** - SPA experience without API complexity
- **Tailwind CSS 4.1.1** - Utility-first CSS framework
- **Vite** - Fast build tool with PWA plugin

### PWA & Development Tools
- **Vite PWA Plugin** - Automated PWA setup and service worker generation
- **Workbox** - Advanced caching and background sync strategies
- **Laravel Pint** - Code formatting and style enforcement
- **Playwright** - End-to-end testing for PWA functionality

## 📋 Prerequisites

- **PHP >= 8.3**
- **Node.js >= 18**
- **Composer >= 2.0**
- **npm/pnpm** (pnpm recommended)

## 🚀 Quick Start

### 1. Clone and Install

```bash
# Clone repository
git clone https://github.com/Baguspanji/cashier-ecommerce-laravel.git
cd cashier-ecommerce-laravel

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
# or with pnpm
pnpm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Database Setup

```bash
# Create SQLite database (or configure MySQL/PostgreSQL in .env)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

### 3. Development Setup

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start Vite development server
npm run dev
# or
pnpm dev

# Access application at http://localhost:8000
```

### 4. Production Build

```bash
# Build for production
npm run build

# Build with SSR support
npm run build:ssr
```

## 🧪 Testing

The application includes comprehensive testing coverage:

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/ProductTest.php

# Run frontend tests
npm run test

# Run E2E tests
npm run test:e2e
```

**Current Test Status:**
- ✅ **202 tests passed**
- ✅ **836 assertions**
- ✅ **100% critical path coverage**

## 📖 Documentation

### User Guides
- [Application Blueprint](docs/blueprint.md) - Complete feature overview and roadmap
- [PWA Implementation Guide](docs/pwa-implementation-guide.md) - Detailed PWA development guide

### API Documentation
- [API Endpoints](docs/api.md) - RESTful API documentation *(Coming Soon)*
- [Sync API](docs/sync-api.md) - Offline sync API specification *(Coming Soon)*

### Development Guides
- [Contributing Guide](CONTRIBUTING.md) - How to contribute to the project *(Coming Soon)*
- [Deployment Guide](docs/deployment.md) - Production deployment instructions *(Coming Soon)*

## 🏗️ Architecture Overview

### Database Schema
```sql
-- Core Tables (All Implemented)
users              # User authentication and roles
categories          # Product categorization
products           # Product catalog with barcode support
transactions       # Sales transactions
transaction_items  # Transaction line items
stock_movements    # Inventory tracking and audit trail

-- PWA Tables (In Development)
sync_logs          # Synchronization audit trail
-- Additional sync fields in existing tables
```

### Frontend Structure
```
resources/js/
├── components/     # Reusable Vue components
├── composables/    # Vue composition functions
├── layouts/        # Page layouts
├── pages/         # Inertia.js pages
├── types/         # TypeScript type definitions
└── tests/         # Frontend tests
```

### Backend Structure
```
app/
├── Http/Controllers/  # Request handling
├── Models/           # Eloquent models
├── Services/         # Business logic
├── Jobs/            # Background tasks
├── Data/            # Data transfer objects
└── Providers/       # Service providers
```

## 🔧 Configuration

### Environment Variables

```bash
# Application
APP_NAME="Cashier E-Commerce"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

# PWA Configuration
PWA_ENABLED=true
PWA_OFFLINE_SUPPORT=true
PWA_BACKGROUND_SYNC=true
```

### PWA Configuration

The PWA is configured through `vite.config.ts`:

```typescript
VitePWA({
  registerType: 'autoUpdate',
  workbox: {
    globPatterns: ['**/*.{js,css,html,ico,png,svg,webp}'],
    runtimeCaching: [
      // Intelligent caching strategies
    ]
  },
  manifest: {
    name: 'Cashier E-Commerce',
    short_name: 'Cashier App',
    // Complete manifest configuration
  }
})
```

## 🚀 Deployment

### Development Deployment

```bash
# Start development servers
php artisan serve &
npm run dev
```

### Production Deployment

```bash
# Optimize for production
composer install --optimize-autoloader --no-dev
npm run build

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations in production
php artisan migrate --force
```

### PWA Requirements

For full PWA functionality in production:

- ✅ **HTTPS**: Required for service worker registration
- ✅ **Valid SSL Certificate**: Ensures secure offline functionality
- ✅ **Proper Headers**: Cache-Control and service worker headers
- ✅ **CDN Configuration**: Optimized asset delivery

## 🛣️ Roadmap

### Phase 4: PWA & Offline Functionality (Current - 65% Complete)

**In Progress (Next 4 weeks):**
- 🔄 **IndexedDB Integration** - Offline data storage
- 🔄 **Background Sync** - Automatic data synchronization
- 🔄 **Offline Transactions** - Complete offline POS functionality
- 🔄 **Conflict Resolution** - Smart data conflict handling

**Timeline:**
- **Week 1**: Backend API and database enhancements
- **Week 2**: Frontend offline infrastructure
- **Week 3**: Advanced PWA features and optimization
- **Week 4**: Testing, production readiness, and deployment

### Future Enhancements
- 📱 **Mobile App** - React Native companion app
- 🔗 **External Integrations** - Payment gateways, accounting systems
- 📊 **Advanced Analytics** - Business intelligence and reporting
- 🌐 **Multi-location Support** - Chain store management
- 🔄 **Real-time Sync** - WebSocket-based live updates

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Workflow

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes and add tests
4. Run the test suite: `php artisan test && npm run test`
5. Format code: `vendor/bin/pint && npm run format`
6. Commit changes: `git commit -m 'Add amazing feature'`
7. Push to branch: `git push origin feature/amazing-feature`
8. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 📞 Support

For support and questions:

- 📧 **Email**: [support@example.com](mailto:support@example.com)
- 💬 **Issues**: [GitHub Issues](https://github.com/Baguspanji/cashier-ecommerce-laravel/issues)
- 📖 **Documentation**: [Project Wiki](https://github.com/Baguspanji/cashier-ecommerce-laravel/wiki)

---

**Built with ❤️ using Laravel, Vue.js, and modern web technologies.**
