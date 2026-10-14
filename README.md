# Entrepreneur Portal - Launch Your Startup Journey

interactive-umair-finalcode is an entrepreneur portal with business resources and community features.

![PHP](https://img.shields.io/badge/PHP-2563EB?style=flat-square) ![MySQL](https://img.shields.io/badge/MySQL-0F172A?style=flat-square)

## Overview

The data model covers `admins`, `agent_business_resources`, `agent_profiles`, `business_categories`, `educational_resources`, `feedback`, `forum_posts`, `messages`, `users`.

## Application areas

| Area | Pages in the repository |
| --- | --- |
| Admin | [`admin/admin_add_resource.php`](admin/admin_add_resource.php), [`admin/admin_business_resources.php`](admin/admin_business_resources.php), [`admin/admin_edit_resource.php`](admin/admin_edit_resource.php), [`admin/admin_home.php`](admin/admin_home.php), [`admin/admin_login.php`](admin/admin_login.php) |
| Agent | [`agent/add_resource.php`](agent/add_resource.php), [`agent/agent_feedbacks.php`](agent/agent_feedbacks.php), [`agent/agent_messages.php`](agent/agent_messages.php), [`agent/business_categories.php`](agent/business_categories.php), [`agent/dashboard.php`](agent/dashboard.php) |
| Root Pages | [`login.php`](login.php), [`register.php`](register.php) |
| User | [`user/agent_profile_view.php`](user/agent_profile_view.php), [`user/dashboard.php`](user/dashboard.php), [`user/forum.php`](user/forum.php), [`user/message.php`](user/message.php) |

## Getting started

### 1. Get the project

```bash
git clone https://github.com/nasirabbas827/interactive-umair-finalcode.git
cd interactive-umair-finalcode
```

### 2. Configure PHP and the database

1. Use a local PHP server with MySQL or MariaDB, such as XAMPP.
2. Import [`Database/interactive-umair_db.sql`](Database/interactive-umair_db.sql) into your local database server.
3. Set your local database connection details in [`admin/config.php`](admin/config.php), [`agent/config.php`](agent/config.php), [`config.php`](config.php), [`user/config.php`](user/config.php).
4. Keep the existing asset and upload directory paths intact.

### 3. Start the application

With the database running, start a local development server from the repository root:

```bash
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000/index.php. If using Apache/XAMPP instead, place the project in its document root and open the matching localhost folder.

## Repository guide

| Path | Purpose |
| --- | --- |
| [`Database`](Database) | Database scripts |
| [`config.php`](config.php) | Application/database configuration |
| [`css`](css) | Stylesheets |
| [`images`](images) | Image assets |
| [`index.php`](index.php) | Web entry page |
| [`uploads`](uploads) | Upload storage |

## Development notes

These setup instructions are based on the repository source. The application has not been executed as part of this documentation update; verify dependencies and configuration in your local environment.
