# 🏦 Advanced Accounting & ERP Dashboard

An enterprise-grade accounting and financial management system built with **Laravel 12** and **Filament v3**. This system provides a robust foundation for managing complex financial structures, multi-currency transactions, and hierarchical account auditing.

---

## ✨ Key Features

### 📖 Financial Core
*   **Hierarchical Chart of Accounts (COA):** A sophisticated multi-level account structure with parent-child relationships.
*   **Interactive Account Tree:** Visual representation of the financial tree for easy navigation and management (powered by `filament-tree`).
*   **Journal Vouchers & Entries:** Comprehensive journal entry management with detailed line items and audit trails.
*   **Simple & Complex Entries:** Support for quick single-entry vouchers as well as standard double-entry accounting.

### 💱 Multi-Currency & Trading
*   **Currency Management:** Support for multiple currencies with real-time or manual price tracking.
*   **Currency Exchange:** Dedicated modules for buying and selling currencies, integrated directly into the financial ledger.

### 🔐 Security & Governance
*   **Role-Based Access Control (RBAC):** Granular permissions management using **Spatie Laravel Permission**.
*   **OTP Verification:** Enhanced security for sensitive actions via One-Time Passwords.
*   **Audit Trail:** Automated logging of all financial changes and user actions.
*   **Session Management:** Fiscal year and accounting session control.

### 🤖 Intelligent Features
*   **AI-Powered Actions:** Integrated AI assistance for accounting insights and automated tasks (powered by `filament-ai-action`).
*   **Automated Backups:** Configurable system for database and file persistence.

---

## 🛠️ Technology Stack

*   **Framework:** [Laravel 12.x](https://laravel.com)
*   **Admin Panel:** [Filament v5](https://filamentphp.com)
*   **Database:** MySQL / MariaDB
*   **UI Components:** Tailwind CSS, Alpine.js, Blade Icons
*   **Accounting Tree:** Solution Forest Filament Tree
*   **Security:** Laravel Sanctum, Spatie Permission
*   **AI:** Pixelworx AI Action

---

## 🚀 Installation & Setup

### Prerequisites
*   PHP ^8.3
*   Composer
*   Node.js & NPM

### Setup Steps
The project includes a streamlined setup script:

1.  **Clone and Enter:**
    ```bash
    cd my-dashboard
    ```

2.  **Automated Setup:**
    ```bash
    composer run setup
    ```
    *This script installs dependencies, generates the application key, runs migrations, and builds assets.*

3.  **Create Admin User:**
    ```bash
    php artisan make:filament-user
    ```

4.  **Run Dev Server:**
    ```bash
    composer run dev
    ```

---

## 📂 Project Structure

*   `app/Models/Account.php`: Core logic for the Chart of Accounts and hierarchy.
*   `app/Models/Entry.php`: Financial journal entry definitions.
*   `app/Filament/Resources/`: All administrative interfaces for Accounts, Entries, and Currencies.
*   `database/migrations/`: Database schema for the ERP system.

---

## 📄 License
This system is proprietary and all rights are reserved.
