# Real Estate Management System

A comprehensive web-based application for managing real estate properties, agents, and users. Built with PHP and MySQL.

## Features

- **User Interface**: Buy, rent, and view details for flats and other properties.
- **Admin Dashboard**: Manage listings, agents, and system settings.
- **Agent Portal**: Dedicated section for real estate agents.
- **User Authentication**: Secure login, signup, and password recovery (`Forgote_password.php`).
- **Property Management**: Detailed listing views (`property_detail.php`), search functionality, and categorization.
- **Contact**: Integrated contact form for inquiries.

## Technology Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS
- **Server**: Apache (via XAMPP/WAMP)

## Installation & Setup

1.  **Server Environment**: Install a local server environment like [XAMPP](https://www.apachefriends.org/) or [WAMP](https://www.wampserver.com/).
2.  **Clone/Copy**: Place this project folder (`Real_Estate`) into your server's root directory (e.g., `C:\xampp\htdocs\` or `C:\wamp64\www\`).
3.  **Database Setup**:
    - Open phpMyAdmin (usually `http://localhost/phpmyadmin`).
    - Create a new database named `real_estate` (or matching the config in `UserInterface/All.php` or `SQL/real_estate.sql`).
    - Import the database schema from: `SQL/real_estate.sql`.
4.  **Run**:
    - Open your browser and navigate to `http://localhost/Real_Estate/UserInterface/index.php` (or `http://localhost/Real_Estate/`).

