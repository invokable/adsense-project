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

- **AdSenseCommand** (`app/Console/Commands/AdSenseCommand.php`): Main Artisan command that orchestrates report generation and email sending
- **AdSenseReport** (`app/AdSenseReport.php`): Service class that handles Google AdSense API integration and data fetching
- **AdSenseNotification** (`app/Notifications/AdSenseNotification.php`): Email notification class for formatting and sending reports

### API Integration

- Uses Google AdSense Management API via OAuth 2.0 authentication
- Leverages `revolution/laravel-google-sheets` package for Google API integration
- Token-based authentication with automatic refresh capability

### Configuration Files

- `config/ads.php`: AdSense API token management and metrics configuration
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

1. AdSenseCommand receives execution request
2. AdSenseReport authenticates using Google OAuth 2.0 access tokens
3. Fetch month-to-date AdSense data (page views, clicks, CPC, revenue)
4. Convert API response from object to array format
5. AdSenseNotification formats data into email with:
   - Total performance metrics
   - Daily average metrics  
   - Recent 7 days detailed breakdown
6. Send email notification with formatted report

## Development Notes

- Uses SQLite database by default
- Laravel Pint for PSR code formatting
- Security: Never commit `.env` files or `client_secret_*.json` files
- Token refresh logic is implemented for long-term operation

## Testing

Comprehensive test suite covering all major components:

- **Feature Tests**: End-to-end command execution testing
- **Unit Tests**: Individual class and method testing
- **Mock Integration**: Google API calls are mocked for reliable testing

### Test Structure

- `tests/Feature/AdSenseCommandTest.php`: Tests command execution and notification sending
- `tests/Unit/AdSenseNotificationTest.php`: Tests email formatting and metric calculation
- `tests/Unit/AdSenseReportTest.php`: Tests API integration and data conversion

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file  
php artisan test tests/Unit/AdSenseNotificationTest.php

# Run tests with coverage (if configured)
php artisan test --coverage
```

## Scheduling

The application uses GitHub Actions for automated execution:

- **Frequency**: Every 3 hours (`0 */3 * * *`)
- **Workflow**: `.github/workflows/cron.yml`
- **Environment**: All required secrets must be configured in GitHub repository settings

## OAuth Setup

The project requires Google Cloud Console setup with AdSense Management API enabled. See README.md for detailed OAuth setup instructions using `oauth2l` CLI tool.

## Configuration Management

### AdSense Metrics

Configurable metrics in `config/ads.php`:
```php
'metrics' => [
    'PAGE_VIEWS',
    'CLICKS', 
    'COST_PER_CLICK',
    'ESTIMATED_EARNINGS',
]
```

Add or remove metrics as needed. The system automatically handles array indexing through the `getMetricValue()` helper method.