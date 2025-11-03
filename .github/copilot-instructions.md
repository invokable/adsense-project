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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.14
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit <name>` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
</laravel-boost-guidelines>
