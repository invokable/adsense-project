# Google AdSense Report

[![Maintainability](https://qlty.sh/badges/cd9eeb28-893d-498a-8d10-e227728cb2f7/maintainability.svg)](https://qlty.sh/gh/invokable/projects/adsense-project)
[![Code Coverage](https://qlty.sh/badges/cd9eeb28-893d-498a-8d10-e227728cb2f7/test_coverage.svg)](https://qlty.sh/gh/invokable/projects/adsense-project)

This project is a sample implementation for sending Google AdSense revenue reports via email, built with [laravel-console-starter](https://github.com/invokable/laravel-console-starter)

## 🚀 Features

- **Automated AdSense Reporting**: Fetches month-to-date revenue data from Google AdSense API
- **Email Notifications**: Sends formatted reports with total metrics, daily averages, and recent 7-day breakdown
- **Scheduled Execution**: Runs automatically every 3 hours via GitHub Actions
- **Configurable Metrics**: Easy customization of reported AdSense metrics
- **Comprehensive Testing**: Full test coverage with mocked API integration

## 🛠 Tech Stack

- **Framework**: Laravel 12.0 (Console Application)
- **Language**: PHP 8.4
- **API Integration**: Google AdSense Management API
- **Authentication**: OAuth 2.0 with automatic token refresh
- **Email**: Laravel Mail with Amazon SES
- **Testing**: PHPUnit with Mockery
- **Code Quality**: Laravel Pint (PSR-12)
- **CI/CD**: GitHub Actions

## 📋 Architecture

### Core Components

1. **AdSenseCommand** (`app/Console/Commands/AdSenseCommand.php`)
    - Main Artisan command that orchestrates the reporting process
    - Handles dependency injection and error handling

2. **AdSenseReport** (`app/AdSenseReport.php`)
    - Service class for Google AdSense API integration
    - Manages OAuth authentication and token refresh
    - Converts API responses to usable array format

3. **AdSenseNotification** (`app/Notifications/AdSenseNotification.php`)
    - Formats and sends email reports
    - Generates Japanese-language reports with ¥ currency
    - Includes total metrics, averages, and daily breakdowns

### Configuration

- **`config/ads.php`**: AdSense API credentials and metric definitions
- **`config/google.php`**: Google OAuth configuration
- **`config/mail.php`**: Email delivery settings

### Data Flow

```
AdSenseCommand → AdSenseReport → Google AdSense API
                      ↓
           Array Data Conversion
                      ↓
AdSenseNotification → Email Report → User
```

## 🧪 Testing

Comprehensive test suite with 100% core functionality coverage:

```bash
# Run all tests
composer test

# Run specific test suite
php artisan test tests/Unit/AdSenseNotificationTest.php

# Code formatting
vendor/bin/pint
```

**Test Coverage:**

- Command execution and notification sending
- Email content formatting and metric calculation
- API integration with mocked Google services
- Configuration management and error handling

## ⚡ Quick Start

```bash
# Clone and install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your AdSense API credentials (see OAuth setup below)
# Edit .env file with your Google credentials

# Run the report command
php artisan ads:report

# Run tests
composer test
```

## 🔧 Environment Variables

Required environment variables in `.env`:

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
```

## 📊 Metrics Configuration

Customize reported metrics in `config/ads.php`:

```php
'metrics' => [
    'PAGE_VIEWS',           // Page views
    'CLICKS',               // Clicks
    'COST_PER_CLICK',       // Cost per click
    'ESTIMATED_EARNINGS',   // Estimated earnings
    // 'AD_REQUESTS',       // Ad requests
    // 'AD_REQUESTS_CTR',   // Click-through rate
    // 'PAGE_RPM',          // RPM
],
```

## How to Obtain AdSense API Access & Refresh Tokens Using `oauth2l` (No Web Server Required)

This guide walks you through the complete process to obtain AdSense Management API access and refresh tokens without
running a web server.

### Why This Setup Is Required

Since this project is a console application that runs without a web interface, traditional OAuth authentication flows
cannot be used. The AdSense API requires at least one-time user authorization to access account data, and service
accounts are not supported for AdSense API access.

The solution is to:

1. **One-time authorization**: Authenticate once to obtain a refresh token
2. **Long-term access**: Use the refresh token to automatically obtain new access tokens indefinitely
3. **No web server needed**: While you could set up a web server for OAuth flow, `oauth2l` provides a simpler
   command-line approach

Once you have the refresh token, your console application can run completely autonomously, refreshing access tokens as
needed without any user interaction.

### Setup Process

You'll create a Google Cloud project, configure OAuth, use `oauth2l` to authorize, and store the credentials in a `.env`
file.

---

### ✅ Step 1: Create a Project in Google Cloud Console

1. Go to [Google Cloud Console](https://console.cloud.google.com/).
2. Click the project dropdown in the top bar → **"New Project"**.
3. Name your project and click **Create**.

---

### ✅ Step 2: Enable the AdSense Management API

1. In the Cloud Console, navigate to **APIs & Services > Library**.
2. Search for **"AdSense Management API"**.
3. Click on it, then click **"Enable"**.

---

### ✅ Step 3: Configure OAuth Consent Screen

1. Go to **APIs & Services > OAuth consent screen**.
2. Choose **"External"** for user type, then click **Create**.
3. Fill in required app information:
    - App name, support email
    - Developer contact email
4. Under **Scopes**, click **"Add or Remove Scopes"** and include:
   ```
   https://www.googleapis.com/auth/adsense.readonly
   ```
5. Under **Test Users**, add your Google account email.
6. Save and continue to publish the consent screen.

---

### ✅ Step 4: Create OAuth Client ID

1. Go to **APIs & Services > Credentials**.
2. Click **"Create Credentials" > "OAuth Client ID"**.
3. Choose **"Desktop app"** for Application Type.
4. Name it and click **Create**.
5. Download the client credentials JSON file (e.g., `client_secret_XXX.json`) and save it securely.

---

### ✅ Step 5: Install `oauth2l` CLI

You can install `oauth2l` from the official GitHub repository:
https://github.com/google/oauth2l

---

### ✅ Step 6: Fetch Access and Refresh Tokens

Run the following command in your terminal:

```bash
oauth2l fetch \
  --scope https://www.googleapis.com/auth/adsense.readonly \
  --credentials ./client_secret_XXX.json \
  --output_format json
```

This will launch a browser asking you to authorize access with your Google account. After successful login and consent,
a JSON response like below will appear:

```json
{
    "access_token": "...access-token...",
    "expiry": "2025-06-22T11:51:37.242796+09:00",
    "refresh_token": "...refresh-token...",
    "scope": "https://www.googleapis.com/auth/adsense.readonly",
    "token_type": "Bearer"
}
```

---

### ✅ Step 7: Store Tokens in `.env` File

Copy the access and refresh tokens into a `.env` file:

```dotenv
GOOGLE_CLIENT_ID=client_id_from_json
GOOGLE_CLIENT_SECRET=client_secret_from_json
GOOGLE_REDIRECT=http://localhost/

ADS_ACCESS_TOKEN=...access-token...
ADS_REFRESH_TOKEN=...refresh-token...
```

These tokens can now be used by your application or script to make authorized requests to the AdSense API. The access
token will expire, but you can use the refresh token to obtain a new one programmatically.

`GOOGLE_REDIRECT` can be anything, so just set it as you like.
---

### 🔒 Tips

- **Security**: Never commit `client_secret_XXX.json` or `.env` files to version control.
- **Token Refreshing**: You can use `oauth2l fetch` again or implement automatic refresh logic using the refresh token.
- **Scope Adjustments**: If you need to change scopes, update the OAuth consent screen and reauthorize.

---

### 🎉 Done!

You now have full access to the AdSense API with long-term credentials, without needing to run a web server. Use the
stored tokens to authenticate your scripts or apps easily.

## LICENSE

MIT License
