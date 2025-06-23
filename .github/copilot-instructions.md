# GitHub Copilot Instructions

This file provides guidance for GitHub Copilot and other AI coding agents when working with this Laravel AdSense reporting console application.

## Project Context

This is a **Laravel 12.0 console application** (not a web application) designed to:
- Fetch Google AdSense revenue data via API
- Send automated email reports with metrics analysis  
- Run on scheduled intervals (every 3 hours) via GitHub Actions
- Support multi-language email templates (Japanese/English)

**Framework**: Laravel 12.0 Console Application  
**Language**: PHP 8.4  
**Purpose**: Automated AdSense revenue reporting via email

## Architecture Overview

### Core Components (3-layer architecture)

1. **Command Layer**: `app/Console/Commands/AdSenseCommand.php`
   - Main entry point: `php artisan ads:report`
   - Orchestrates the reporting workflow
   - Handles dependency injection and error management

2. **Service Layer**: `app/AdSenseReport.php` 
   - Google AdSense API integration
   - OAuth 2.0 authentication with token refresh
   - Data transformation (API objects → arrays)

3. **Notification Layer**: `app/Notifications/AdSenseNotification.php`
   - Email formatting and templating
   - Multi-language support (ja/en)
   - Metric calculations and data presentation

### Data Flow
```
AdSenseCommand → AdSenseReport → Google AdSense API
                       ↓
              Array Data Conversion  
                       ↓
AdSenseNotification → Email Report → Recipient
```

## Development Workflows

### Essential Commands
```bash
# Run the main command
php artisan ads:report

# Testing
composer test                    # Run all tests
php artisan test                # Alternative test command
php artisan test tests/Unit/AdSenseNotificationTest.php  # Specific test

# Code Quality
vendor/bin/pint                 # Format code (PSR-12)
vendor/bin/pint --test          # Check formatting without changes

# Setup
composer install                # Install dependencies
cp .env.example .env            # Environment setup
php artisan key:generate        # Generate app key
```

### Testing Strategy

**100% Core Coverage** with three test categories:

1. **Unit Tests** (`tests/Unit/`)
   - `AdSenseNotificationTest.php`: Email formatting, metric calculations
   - `AdSenseReportTest.php`: API integration with mocked Google services
   - All Google API calls are mocked for reliability

2. **Feature Tests** (`tests/Feature/`)
   - `AdSenseCommandTest.php`: End-to-end command execution
   - Integration testing with notification sending

3. **Testing Patterns**
   - Use Mockery for external API dependencies
   - Mock Google OAuth flow and AdSense API responses
   - Test both Japanese and English locales
   - Validate email content structure and metric calculations

## Configuration Management

### Key Configuration Files

- **`config/ads.php`**: AdSense API tokens and metrics configuration
- **`config/google.php`**: Google OAuth settings and scopes
- **`config/mail.php`**: Email delivery configuration (Amazon SES)

### Environment Variables (Required)

```bash
# Google OAuth Credentials
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret  
GOOGLE_REDIRECT=http://localhost/

# AdSense API Tokens
ADS_ACCESS_TOKEN=your_access_token
ADS_REFRESH_TOKEN=your_refresh_token

# Email Configuration (Amazon SES)
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=sender@example.com
MAIL_FROM_NAME="AdSense Reports"
MAIL_TO_ADDRESS=recipient@example.com
MAIL_TO_NAME="Report Recipient"

# AWS SES Credentials  
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=us-east-1

# Application Settings
APP_LOCALE=ja                   # Supports 'ja' or 'en'
```

### Configurable AdSense Metrics

Modify `config/ads.php` to customize reported metrics:
```php
'metrics' => [
    'PAGE_VIEWS',           // Page views
    'CLICKS',               // Clicks  
    'COST_PER_CLICK',       // Cost per click
    'ESTIMATED_EARNINGS',   // Estimated earnings
    // 'AD_REQUESTS',       // Ad requests (optional)
    // 'AD_REQUESTS_CTR',   // Click-through rate (optional) 
    // 'PAGE_RPM',          // RPM (optional)
],
```

## Code Conventions & Patterns

### Laravel Standards
- Follow **PSR-12** coding standards (enforced by Laravel Pint)
- Use Laravel's dependency injection for services
- Implement proper error handling with try-catch blocks
- Use Laravel's configuration system (`config()` helper)

### Project-Specific Patterns

1. **Notification Data Structure**:
   ```php
   // Expected array structure from AdSenseReport
   [
       'totals' => ['cells' => [[], ['value' => '1000'], ...]],
       'averages' => ['cells' => [[], ['value' => '50'], ...]],
       'rows' => [...]
   ]
   ```

2. **Metric Access Pattern**:
   ```php
   // Use getMetricValue() helper for consistent metric access
   $earnings = $this->getMetricValue('ESTIMATED_EARNINGS', $source);
   ```

3. **Locale Handling**:
   ```php
   // Support both Japanese and English templates
   $locale = config('app.locale', 'en');
   $template = $locale === 'ja' ? 'mail.ja.adsense-report' : 'mail.en.adsense-report';
   ```

### API Integration Patterns

1. **OAuth Token Management**:
   ```php
   // Always set access token and refresh before API calls
   Google::setAccessToken($token);
   Google::fetchAccessTokenWithRefreshToken();
   ```

2. **AdSense API Calls**:
   ```php
   // Standard report generation pattern
   $optParams = [
       'metrics' => config('ads.metrics'),
       'dimensions' => 'DATE',
       'orderBy' => '-DATE',
       'dateRange' => 'MONTH_TO_DATE',
   ];
   ```

## Security & Best Practices

### Security Guidelines
- **Never commit** `.env` files or `client_secret_*.json` files
- Use GitHub Secrets for all sensitive environment variables
- Implement automatic token refresh for long-term operation
- Use Amazon SES for reliable email delivery

### Error Handling
- Implement graceful error handling for API failures
- Log errors appropriately without exposing sensitive data
- Provide meaningful error messages for debugging

### Performance Considerations
- Use efficient array operations for data transformation
- Minimize API calls through proper caching strategies
- Optimize email template rendering

## Automation & CI/CD

### GitHub Actions Workflows

1. **Automated Execution** (`.github/workflows/cron.yml`)
   - Runs every 3 hours: `0 */3 * * *`
   - Uses production environment variables from GitHub Secrets
   - Executes `php artisan ads:report`

2. **Code Quality** (`.github/workflows/lint.yml`)
   - Runs Laravel Pint for PSR-12 compliance
   - Triggers on push/PR to main/develop branches

3. **Testing** (`.github/workflows/tests.yml`)
   - Runs full test suite with PHPUnit
   - Generates code coverage reports
   - Integrates with Qlty for quality tracking

### Required GitHub Secrets
All environment variables listed above must be configured as repository secrets for automated execution.

## Common Development Tasks

### Adding New Metrics
1. Update `config/ads.php` metrics array
2. Update test data in `AdSenseNotificationTest.php`
3. Verify email template handles new metrics appropriately

### Modifying Email Templates
- Templates located in `resources/views/mail/ja/` and `resources/views/mail/en/`
- Test both locale variations
- Ensure metric calculations remain accurate

### API Integration Changes
- Always mock Google API calls in tests
- Update both unit and feature tests
- Verify token refresh logic continues to work

### Troubleshooting OAuth Issues
- Use `oauth2l` CLI tool for token generation (see README.md)
- Verify Google Cloud Console API enablement
- Check OAuth consent screen configuration

## Dependencies & Packages

### Key Dependencies
- **`laravel/framework: ^12.0`**: Core Laravel framework
- **`revolution/laravel-google-sheets: ^7.1`**: Google API integration
- **`aws/aws-sdk-php: ^3.344`**: Amazon SES email delivery

### Development Dependencies  
- **`laravel/pint: ^1.13`**: Code formatting (PSR-12)
- **`phpunit/phpunit: ^11.5.3`**: Testing framework
- **`mockery/mockery: ^1.6`**: Mocking library for tests

## AI Agent Guidelines

When working with this codebase:

1. **Maintain Architecture**: Preserve the 3-layer separation (Command → Service → Notification)
2. **Test Coverage**: Always add/update tests for any code changes
3. **Follow Patterns**: Use existing patterns for metric access, locale handling, and API integration
4. **Security First**: Never expose sensitive data, always use environment variables
5. **Documentation**: Update relevant documentation when making significant changes
6. **Minimal Changes**: Make surgical modifications rather than large refactors
7. **Consistency**: Follow existing coding standards and conventions

This application is production-ready and actively used for automated reporting. Prioritize stability and maintainability in all modifications.