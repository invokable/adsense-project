# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel console application for sending Google AdSense revenue reports via email. It's built on Laravel 12.0 and designed to run as scheduled commands rather than a web application.

## Common Commands

```bash
# Run the AdSense report command
php artisan ads:report

# Run tests
composer test
# or
php artisan test

# Code formatting with Laravel Pint
vendor/bin/pint

# Install dependencies
composer install

# Generate application key
php artisan key:generate
```

## Architecture

### Core Components

- **AdSenseCommand** (`app/Console/Commands/AdSenseCommand.php`): Main Artisan command that fetches yesterday's AdSense data
- **AdSenseNotification** (`app/Notifications/AdSenseNotification.php`): Email notification class for sending reports

### API Integration

- Uses Google AdSense Management API via OAuth 2.0 authentication
- Leverages `revolution/laravel-google-sheets` package for Google API integration
- Token-based authentication with automatic refresh capability

### Configuration Files

- `config/ads.php`: AdSense API token management
- `config/google.php`: Google OAuth configuration and scopes  
- `config/mail.php`: Email configuration for notifications

### Environment Variables

Key environment variables for setup:
```bash
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT=
ADS_ACCESS_TOKEN=
ADS_REFRESH_TOKEN=
MAIL_TO_ADDRESS=
MAIL_TO_NAME=
```

## Data Flow

1. Authenticate using Google OAuth 2.0 access tokens
2. Fetch yesterday's AdSense data (page views, clicks, revenue)
3. Process and format report data
4. Send email notification with results

## Development Notes

- Uses SQLite database by default
- Laravel Pint for PSR code formatting
- Security: Never commit `.env` files or `client_secret_*.json` files
- Token refresh logic is implemented for long-term operation

## OAuth Setup

The project requires Google Cloud Console setup with AdSense Management API enabled. See README.md for detailed OAuth setup instructions using `oauth2l` CLI tool.