# Juzaweb API Module

The **Juzaweb API Module** (`juzaweb/api`) provides a robust authentication mechanism for Juzaweb CMS applications. It introduces API Key authentication via the `x-api-key` header while seamlessly falling back to Laravel Passport for OAuth2 token-based authentication. This module also includes an administrative interface for managing API keys.

## Features

- **Dual Authentication**: Support for both API Keys (`x-api-key`) and OAuth2 Access Tokens (Laravel Passport).
- **Secure Key Management**: API Keys are automatically hashed (SHA-256) upon creation. Plain-text keys are shown only once.
- **Admin Interface**: Built-in interface to create, list, and revoke API keys within the Juzaweb admin panel.
- **Expiration & Revocation**: Support for key expiration dates and manual revocation.
- **Usage Tracking**: Tracks the last usage timestamp for each API key.
- **Configurable**: Easy configuration via standard Laravel config files.

## Installation

You can install the package via composer:

```bash
composer require juzaweb/api
```

## Configuration

### 1. Publish Configuration

Publish the configuration file to `config/jw-api.php`:

```bash
php artisan vendor:publish --tag=api-config
```

### 2. Run Migrations

Run the migrations to create the necessary tables (`api_keys`, `oauth_clients`, etc.):

```bash
php artisan migrate
```

### 3. Configure Authentication Guard

Update your `config/auth.php` to use the `juzaweb` driver for your API guard. This driver prioritizes the `x-api-key` header and falls back to Passport's `passport` driver if no key is present.

```php
'guards' => [
    'api' => [
        'driver' => 'juzaweb',
        'provider' => 'users',
    ],
    // ...
],
```

## Usage

### Authentication

To authenticate a request using an API Key, include the `x-api-key` header in your HTTP request:

```http
GET /api/user HTTP/1.1
Host: your-app.com
Accept: application/json
x-api-key: YOUR_GENERATED_API_KEY
```

If the `x-api-key` is valid, the request will be authenticated as the user associated with that key. If the header is missing or invalid, the guard will attempt to authenticate using a Bearer token (Laravel Passport).

### Managing API Keys

1.  Log in to the Juzaweb Admin Panel.
2.  Navigate to **Settings > API Keys** (or the configured menu location).
3.  Click **Add New** to generate a new API Key.
4.  **Important**: Copy the generated key immediately. It will not be shown again.
5.  You can view the list of active keys, their expiration status, and last usage time.
6.  To revoke a key, simply delete it from the list.

## License

The Juzaweb API Module is open-sourced software licensed under the [GPL-2.0 license](LICENSE).
