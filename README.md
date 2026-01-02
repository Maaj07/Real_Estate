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

## How to Push to GitHub

**Note:** Git is not currently detected on this system. You must install Git first.

1.  **Install Git**: Download and install [Git for Windows](https://git-scm.com/download/win).
2.  **Initialize Repository**:
    Open a terminal (Command Prompt or PowerShell) in this folder and run:
    ```bash
    git init
    git add .
    git commit -m "Initial commit of Real Estate System"
    ```
3.  **Push to GitHub**:
    - Create a new empty repository on GitHub.
    - Run the commands provided by GitHub (replace `YOUR_USERNAME` and `REPO_NAME`):
    ```bash
    git branch -M main
    git remote add origin https://github.com/YOUR_USERNAME/REPO_NAME.git
    git push -u origin main
    ```
