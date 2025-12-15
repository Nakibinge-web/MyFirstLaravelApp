# 💰 Personal Financial Tracker

A comprehensive web application for managing personal finances, tracking expenses, setting budgets, and achieving financial goals.

![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)

## 📋 Table of Contents

- [Features](#features)
- [Technologies Used](#technologies-used)
- [System Requirements](#system-requirements)
- [Installation](#installation)
  - [Traditional Installation](#traditional-installation)
  - [Docker Installation](#docker-installation-recommended)
- [Configuration](#configuration)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

### 💳 Transaction Management
- Create, read, update, and delete transactions
- Categorize income and expenses
- Filter and search transactions
- Daily and weekly spending summaries
- Export transaction data

### 📊 Budget Tracking
- Set monthly, weekly, or yearly budgets
- Real-time budget utilization tracking
- Visual progress indicators
- Budget alerts and notifications
- Multiple budget periods support

### 🎯 Financial Goals
- Create and track savings goals
- Progress visualization
- Deadline reminders
- Goal achievement notifications
- Estimated completion dates

### 📈 Reports & Analytics
- 7-day spending trends
- Income vs. expense charts
- Category-wise breakdown
- Monthly financial summaries
- Net worth tracking
- Savings rate calculation

### 🔔 Notifications
- Budget exceeded alerts
- Budget warning notifications (80% threshold)
- Goal deadline reminders
- Real-time toast notifications
- In-app notification center

### 👤 User Management
- Secure authentication
- Email verification
- Profile management
- Password updates
- Multi-currency support (31 currencies)
- Account deletion with data export

### 🎨 User Interface
- Responsive design (mobile-first)
- Modern glassmorphism effects
- Smooth animations and transitions
- Dark mode sidebar
- Interactive charts and graphs
- Keyboard shortcuts

## 🛠️ Technologies Used

### Backend Framework
- **Laravel 10.x** - PHP web application framework
  - Eloquent ORM for database operations
  - Blade templating engine
  - Authentication scaffolding
  - Form request validation
  - Database migrations and seeders
  - Queues and jobs support

### Frontend Technologies

#### CSS Frameworks & Libraries
- **Tailwind CSS 3.x** - Utility-first CSS framework
  - Custom color schemes
  - Responsive design utilities
  - Animation classes
  - Custom components

#### JavaScript Libraries
- **jQuery 3.6.0** - DOM manipulation and AJAX
- **jQuery Validation 1.19.5** - Form validation
- **Chart.js** - Data visualization and charts
- **Alpine.js 3.x** - Lightweight JavaScript framework for dropdowns and modals

### Database
- **MySQL 8.0+** - Relational database management system
  - Foreign key constraints
  - Indexes for performance
  - Cascade delete operations
  - Transaction support

### Development Tools

#### Package Managers
- **Composer** - PHP dependency manager
- **NPM/Yarn** - JavaScript package manager

#### Version Control
- **Git** - Source code management
- **GitHub** - Repository hosting

#### Code Quality
- **PHP CS Fixer** - PHP code style fixer
- **ESLint** - JavaScript linting
- **PHPStan** - PHP static analysis

### Server Requirements
- **PHP 8.2+**
  - OpenSSL PHP Extension
  - PDO PHP Extension
  - Mbstring PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension
  - Ctype PHP Extension
  - JSON PHP Extension
  - BCMath PHP Extension

- **Web Server**
  - Apache 2.4+ or Nginx 1.18+
  - mod_rewrite enabled (Apache)

- **Database**
  - MySQL 8.0+ or MariaDB 10.3+

### Third-Party Services & APIs
- **CDN Services**
  - Tailwind CSS CDN
  - jQuery CDN
  - Chart.js CDN
  - Alpine.js CDN

### Security Features
- **CSRF Protection** - Laravel's built-in CSRF tokens
- **Password Hashing** - Bcrypt algorithm
- **SQL Injection Prevention** - Eloquent ORM and prepared statements
- **XSS Protection** - Blade template escaping
- **Rate Limiting** - Throttle middleware
- **Session Security** - Secure session management
- **Input Validation** - Form request validation

## 💻 System Requirements

### Minimum Requirements
- PHP 8.2 or higher
- MySQL 8.0 or MariaDB 10.3
- Composer 2.x
- Node.js 16.x (for asset compilation)
- 512 MB RAM
- 100 MB disk space

### Recommended Requirements
- PHP 8.3
- MySQL 8.0
- 1 GB RAM
- 500 MB disk space
- SSD storage

## 📦 Installation

You can install the application using either Docker (recommended) or traditional installation.

### Docker Installation (Recommended)

Docker provides a consistent development environment with all dependencies pre-configured. No need to install PHP, MySQL, Node.js, or other dependencies on your local machine.

#### Quick Start with Docker

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/financial-tracker.git
   cd financial-tracker
   ```

2. **Start Docker Environment**
   ```bash
   # Copy environment file
   cp .env.docker.example .env.docker
   
   # Build and start containers
   docker-compose up -d
   
   # Install dependencies
   docker-compose exec php composer install
   docker-compose exec node npm install
   
   # Generate application key
   docker-compose exec php php artisan key:generate
   
   # Run migrations
   docker-compose exec php php artisan migrate --seed
   ```

3. **Access the Application**
   - Application: http://localhost
   - phpMyAdmin: http://localhost:8080
   - Vite Dev Server: http://localhost:5173

#### Quick Setup with Makefile

If you have `make` installed:
```bash
make setup
```

This single command handles everything: building containers, installing dependencies, generating keys, and running migrations.

#### Docker Services Included

- **PHP 8.2-FPM** - Laravel application runtime
- **Nginx** - Web server
- **MySQL 8.0** - Database
- **Redis** - Cache and session storage
- **phpMyAdmin** - Database management interface
- **Node.js 20** - Asset compilation with Vite HMR

For detailed Docker documentation, troubleshooting, and advanced usage, see:
- **[README.docker.md](README.docker.md)** - Complete Docker setup guide and troubleshooting
- **[DOCKER_ARCHITECTURE.md](DOCKER_ARCHITECTURE.md)** - Detailed architecture diagrams and technical documentation
- **[PERFORMANCE_GUIDE.md](PERFORMANCE_GUIDE.md)** - Performance optimization guide and best practices

---

### Traditional Installation

If you prefer to install dependencies directly on your machine:

#### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/financial-tracker.git
cd financial-tracker
```

#### 2. Install PHP Dependencies
```bash
composer install
```

#### 3. Install JavaScript Dependencies
```bash
npm install
# or
yarn install
```

#### 4. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

#### 5. Configure Database
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=financial_tracker
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### 6. Run Migrations
```bash
php artisan migrate
```

#### 7. Seed Database (Optional)
```bash
php artisan db:seed
```

#### 8. Start Development Server
```bash
php artisan serve
```

Visit: `http://localhost:8000`

## ⚙️ Configuration

### Currency Settings
The application supports 31 currencies:
- USD, EUR, GBP, JPY, CNY, INR, CAD, AUD, CHF, SEK
- NZD, KRW, SGD, HKD, NOK, MXN, BRL, ZAR, RUB, TRY
- AED, SAR, THB, IDR, MYR, PHP, PLN, DKK, CZK, HUF, UGX

Configure default currency in user profile.

### Email Configuration
Edit `.env` for email notifications:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@financialtracker.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Cache Configuration
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

## 🚀 Usage

### Demo Credentials
```
Email: demo@financialtracker.com
Password: password
```

### Creating Your First Transaction
1. Navigate to **Transactions** page
2. Click **+ Add Transaction**
3. Fill in the details:
   - Amount
   - Category
   - Type (Income/Expense)
   - Date
   - Description
4. Click **Create Transaction**

### Setting Up a Budget
1. Go to **Budgets** page
2. Click **+ Add Budget**
3. Select:
   - Category
   - Amount limit
   - Period (Monthly/Weekly/Yearly)
   - Start date
4. Click **Create Budget**

### Creating a Financial Goal
1. Navigate to **Goals** page
2. Click **+ Add Goal**
3. Enter:
   - Goal name
   - Target amount
   - Target date
   - Description (optional)
4. Click **Create Goal**

## 📁 Project Structure

```
financial-tracker/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── RegisterController.php
│   │   │   ├── BudgetController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── GoalController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── ReportController.php
│   │   │   └── TransactionController.php
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   ├── BudgetRequest.php
│   │   │   ├── CategoryRequest.php
│   │   │   ├── GoalRequest.php
│   │   │   └── TransactionRequest.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Budget.php
│   │   ├── Category.php
│   │   ├── Goal.php
│   │   ├── Notification.php
│   │   ├── Transaction.php
│   │   └── User.php
│   ├── Services/
│   │   ├── BudgetService.php
│   │   ├── GoalService.php
│   │   ├── NotificationService.php
│   │   ├── ReportService.php
│   │   └── TransactionService.php
│   ├── Helpers/
│   │   ├── CurrencyHelper.php
│   │   └── helpers.php
│   └── Policies/
│       ├── BudgetPolicy.php
│       ├── CategoryPolicy.php
│       ├── GoalPolicy.php
│       └── TransactionPolicy.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── css/
│   │   └── app.css
│   └── js/
│       ├── app.js
│       ├── notifications.js
│       ├── sidebar.js
│       └── validation.js
├── resources/
│   └── views/
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── budgets/
│       ├── categories/
│       ├── components/
│       │   ├── keyboard-shortcuts.blade.php
│       │   ├── logout-modal.blade.php
│       │   └── notification-dropdown.blade.php
│       ├── dashboard.blade.php
│       ├── goals/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── notifications/
│       ├── profile/
│       ├── reports/
│       └── transactions/
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
│   ├── Feature/
│   └── Unit/
├── .env.example
├── composer.json
├── package.json
└── README.md
```

## 🗄️ Database Schema

### Users Table
```sql
- id (primary key)
- name
- email (unique)
- password
- currency (default: USD)
- email_verified_at
- remember_token
- timestamps
```

### Categories Table
```sql
- id (primary key)
- user_id (foreign key)
- name
- type (income/expense)
- icon
- color
- timestamps
```

### Transactions Table
```sql
- id (primary key)
- user_id (foreign key)
- category_id (foreign key)
- amount
- type (income/expense)
- date
- description
- timestamps
- soft deletes
```

### Budgets Table
```sql
- id (primary key)
- user_id (foreign key)
- category_id (foreign key)
- amount
- period (monthly/weekly/yearly)
- start_date
- end_date
- timestamps
```

### Goals Table
```sql
- id (primary key)
- user_id (foreign key)
- name
- target_amount
- current_amount
- target_date
- status (active/completed/paused)
- description
- timestamps
```

### Notifications Table
```sql
- id (primary key)
- user_id (foreign key)
- type
- title
- message
- icon
- color
- is_read
- read_at
- timestamps
```

## 📚 API Documentation

### Authentication Endpoints
```
POST   /register          - Register new user
POST   /login             - User login
GET    /logout            - User logout
```

### Transaction Endpoints
```
GET    /transactions                    - List all transactions
POST   /transactions                    - Create transaction
GET    /transactions/{id}               - View transaction
PUT    /transactions/{id}               - Update transaction
DELETE /transactions/{id}               - Delete transaction
GET    /transactions/daily-summary      - Daily summary
GET    /transactions/weekly-summary     - Weekly summary
```

### Budget Endpoints
```
GET    /budgets           - List all budgets
POST   /budgets           - Create budget
GET    /budgets/{id}      - View budget
PUT    /budgets/{id}      - Update budget
DELETE /budgets/{id}      - Delete budget
```

### Goal Endpoints
```
GET    /goals             - List all goals
POST   /goals             - Create goal
GET    /goals/{id}        - View goal
PUT    /goals/{id}        - Update goal
DELETE /goals/{id}        - Delete goal
POST   /goals/{id}/toggle - Toggle goal status
POST   /goals/{id}/progress - Update goal progress
```

### Notification Endpoints
```
GET    /notifications                - List notifications
GET    /notifications/unread         - Get unread notifications
POST   /notifications/{id}/read      - Mark as read
POST   /notifications/read-all       - Mark all as read
DELETE /notifications/{id}           - Delete notification
POST   /notifications/check-alerts   - Check for new alerts
```

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Run with Coverage
```bash
php artisan test --coverage
```

### Test Categories
- **Unit Tests**: Model logic, services, helpers
- **Feature Tests**: HTTP requests, authentication, CRUD operations
- **Browser Tests**: UI interactions, JavaScript functionality

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards
- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add tests for new features
- Update documentation

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- **Your Name** - *Initial work* - [YourGitHub](https://github.com/yourusername)

## 🙏 Acknowledgments

- Laravel Framework
- Tailwind CSS
- Chart.js
- jQuery
- Alpine.js
- All contributors and supporters

## 📞 Support

For support, email support@financialtracker.com or open an issue on GitHub.

## 🔗 Links

- [Documentation](https://docs.financialtracker.com)
- [Demo](https://demo.financialtracker.com)
- [Issue Tracker](https://github.com/yourusername/financial-tracker/issues)
- [Changelog](CHANGELOG.md)

## 🗺️ Roadmap

### Version 2.0 (Planned)
- [ ] Mobile app (React Native)
- [ ] Bank account integration
- [ ] Recurring transactions
- [ ] Bill reminders
- [ ] Investment tracking
- [ ] Tax calculation
- [ ] Multi-user households
- [ ] Data export/import
- [ ] API for third-party integrations
- [ ] Advanced analytics and AI insights

---

**Made with ❤️ by the Financial Tracker Team**
