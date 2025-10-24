# Mixpost REST API Add-on

<p align="center">
  <img src="https://raw.githubusercontent.com/inovector/mixpost/main/art/logo.svg" alt="Mixpost Logo" width="200">
</p>

<p align="center">
  <strong>REST API extension for Mixpost - Enable powerful automation with n8n and external integrations</strong>
</p>

<p align="center">
  <a href="https://packagist.org/packages/btafoya/mixpost-api"><img src="https://img.shields.io/packagist/v/btafoya/mixpost-api.svg?style=flat-square" alt="Latest Version on Packagist"></a>
  <a href="https://github.com/btafoya/mixpost-api/actions"><img src="https://img.shields.io/github/actions/workflow/status/btafoya/mixpost-api/tests.yml?branch=main&label=tests&style=flat-square" alt="GitHub Tests Action Status"></a>
  <a href="https://packagist.org/packages/btafoya/mixpost-api"><img src="https://img.shields.io/packagist/dt/btafoya/mixpost-api.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="https://github.com/btafoya/mixpost-api/blob/main/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="License"></a>
</p>

---

## 📖 About

**Mixpost REST API** is a powerful Laravel package that extends [Mixpost](https://github.com/inovector/mixpost) with comprehensive REST API capabilities. Built specifically to enable seamless integration with workflow automation tools like [n8n](https://n8n.io), this add-on transforms Mixpost into a fully API-driven social media management platform.

### 🎯 Perfect For

- **Workflow Automation**: Integrate with n8n, Zapier, Make.com
- **Custom Applications**: Build your own social media management tools
- **AI-Powered Content**: Auto-generate and publish content with AI services
- **Content Pipelines**: Automate blog-to-social workflows
- **Team Collaboration**: External tools can interact with Mixpost programmatically
- **Scheduled Automation**: Time-based social media campaigns

---

## ✨ Features

### 🚀 Complete API Coverage

- ✅ **Posts Management**: Full CRUD operations, scheduling, and publishing
- ✅ **Media Handling**: Upload files, download from URLs, manage library
- ✅ **Account Management**: List and manage social media accounts
- ✅ **Tags & Organization**: Create, update, delete tags
- ✅ **Analytics & Reports**: Dashboard metrics, account stats, post performance
- ✅ **Calendar Integration**: View and manage scheduled content
- ✅ **System Monitoring**: Check status, queue health, service configuration

### 🔐 Security & Performance

- ✅ **Laravel Sanctum Authentication**: Secure token-based API access
- ✅ **Rate Limiting**: Configurable request throttling (default: 60/min)
- ✅ **Permission System**: Token abilities for granular access control
- ✅ **HTTPS Enforcement**: Production-ready security
- ✅ **IP Whitelisting**: Optional IP restriction
- ✅ **Input Validation**: Comprehensive server-side validation

### 🎨 Developer Experience

- ✅ **RESTful Design**: Clean, predictable API structure
- ✅ **JSON Responses**: Consistent response formats
- ✅ **Pagination**: Efficient data handling for large datasets
- ✅ **Error Handling**: Detailed, actionable error messages
- ✅ **Comprehensive Documentation**: Full API specification and examples
- ✅ **n8n Ready**: Pre-built workflow examples included

---

## 📋 Requirements

- **PHP**: ^8.2
- **Laravel**: ^10.47 | ^11.0
- **Mixpost**: ^1.0 (installed automatically as dev dependency)
- **Laravel Sanctum**: ^3.0 | ^4.0

---

## 🚀 Installation

### Step 1: Install via Composer

```bash
composer require inovector/mixpost-api
```

### Step 2: Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=mixpost-api-config
```

This creates `config/mixpost-api.php` where you can customize:
- API prefix
- Rate limiting
- Token expiration
- Security settings
- Pagination defaults

### Step 3: Run Migrations

```bash
php artisan migrate
```

This creates the `personal_access_tokens` table for Laravel Sanctum.

### Step 4: Update User Model

Add the `HasApiTokens` trait to your User model:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    // ... rest of your model
}
```

### Step 5: You're Ready! 🎉

Your Mixpost installation now has REST API endpoints available at:

```
https://your-domain.com/api/mixpost/*
```

---

## 🔧 Configuration

### Environment Variables

Add to your `.env` file:

```env
# API Configuration
MIXPOST_API_RATE_LIMIT=60
MIXPOST_API_HTTPS_ONLY=true

# Token Configuration
MIXPOST_API_TOKEN_EXPIRATION=null  # null = never expire
```

### Configuration File

Edit `config/mixpost-api.php`:

```php
return [
    'prefix' => 'api/mixpost',

    'rate_limit' => [
        'enabled' => true,
        'max_attempts' => env('MIXPOST_API_RATE_LIMIT', 60),
        'decay_minutes' => 1,
    ],

    'token' => [
        'expiration' => env('MIXPOST_API_TOKEN_EXPIRATION'),
        'abilities_enabled' => true,
    ],

    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
    ],

    'security' => [
        'ip_whitelist_enabled' => false,
        'ip_whitelist' => [],
        'https_only' => env('MIXPOST_API_HTTPS_ONLY', true),
    ],
];
```

---

## 📚 Quick Start Guide

### 1. Generate API Token

**Using cURL:**

```bash
curl -X POST https://your-domain.com/api/mixpost/auth/tokens \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "your-password",
    "token_name": "n8n-integration"
  }'
```

**Response:**

```json
{
  "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz",
  "token_type": "Bearer",
  "expires_at": null
}
```

### 2. Use Token in Requests

```bash
curl -X GET https://your-domain.com/api/mixpost/posts \
  -H "Authorization: Bearer 1|abc123def456..." \
  -H "Accept: application/json"
```

### 3. Create a Post

```bash
curl -X POST https://your-domain.com/api/mixpost/posts \
  -H "Authorization: Bearer 1|abc123def456..." \
  -H "Content-Type: application/json" \
  -d '{
    "accounts": [1, 2, 3],
    "tags": [1],
    "date": "2025-10-24",
    "time": "10:00",
    "versions": [
      {
        "is_original": true,
        "account_id": null,
        "content": [
          {
            "body": "Check out our latest blog post! 🚀",
            "media": []
          }
        ]
      }
    ]
  }'
```

---

## 🎯 API Endpoints Overview

### Authentication
- `POST /api/mixpost/auth/tokens` - Generate API token
- `GET /api/mixpost/auth/tokens` - List user tokens
- `DELETE /api/mixpost/auth/tokens/{id}` - Revoke token

### Posts
- `GET /api/mixpost/posts` - List posts (with filtering & pagination)
- `POST /api/mixpost/posts` - Create post
- `GET /api/mixpost/posts/{uuid}` - Get post details
- `PUT /api/mixpost/posts/{uuid}` - Update post
- `DELETE /api/mixpost/posts/{uuid}` - Delete post
- `POST /api/mixpost/posts/{uuid}/schedule` - Schedule post
- `POST /api/mixpost/posts/{uuid}/publish` - Publish immediately
- `POST /api/mixpost/posts/{uuid}/duplicate` - Duplicate post
- `DELETE /api/mixpost/posts` - Bulk delete posts

### Media
- `GET /api/mixpost/media` - List media files
- `POST /api/mixpost/media` - Upload media file
- `POST /api/mixpost/media/download` - Download from URL
- `GET /api/mixpost/media/{uuid}` - Get media details
- `DELETE /api/mixpost/media/{uuid}` - Delete media file
- `DELETE /api/mixpost/media` - Bulk delete media

### Accounts
- `GET /api/mixpost/accounts` - List social media accounts
- `GET /api/mixpost/accounts/{uuid}` - Get account details
- `PUT /api/mixpost/accounts/{uuid}` - Update account
- `DELETE /api/mixpost/accounts/{uuid}` - Delete account

### Tags
- `GET /api/mixpost/tags` - List tags
- `POST /api/mixpost/tags` - Create tag
- `PUT /api/mixpost/tags/{id}` - Update tag
- `DELETE /api/mixpost/tags/{id}` - Delete tag

### Reports & Analytics
- `GET /api/mixpost/reports/dashboard` - Dashboard summary
- `GET /api/mixpost/reports/accounts/{uuid}` - Account analytics
- `GET /api/mixpost/reports/posts/{uuid}` - Post performance

### Calendar
- `GET /api/mixpost/calendar` - Get scheduled posts

### System
- `GET /api/mixpost/system/status` - System health check

**📖 Full API Documentation:** [API_SPECIFICATION.md](./docs/API_SPECIFICATION.md)

---

## 🔌 n8n Integration

### Quick Setup

1. **Create n8n Credential**
   - Type: `Header Auth`
   - Name: `Authorization`
   - Value: `Bearer YOUR_TOKEN_HERE`

2. **Use HTTP Request Node**
   - Method: `POST`
   - URL: `https://your-domain.com/api/mixpost/posts`
   - Authentication: Use the credential from step 1
   - Body: JSON with post data

### Example Workflows

#### 📝 Auto-Post from Blog Publication

```
Webhook (Blog Published)
  ↓
Upload Featured Image
  ↓
Create Social Post
  ↓
Publish Immediately
```

#### 🤖 AI Content Generation

```
Schedule Trigger (Daily 9am)
  ↓
OpenAI (Generate Content)
  ↓
DALL-E (Generate Image)
  ↓
Upload to Mixpost
  ↓
Create Scheduled Post
```

#### 📊 Performance Monitoring

```
Schedule Trigger (Daily 6pm)
  ↓
Get Dashboard Stats
  ↓
Check for Failed Posts
  ↓
Send Slack Notification
```

**📖 Complete n8n Examples:** [N8N_INTEGRATION_EXAMPLES.md](./docs/N8N_INTEGRATION_EXAMPLES.md)

---

## 📖 Documentation

### Core Documentation
- **[API Specification](./docs/API_SPECIFICATION.md)** - Complete endpoint reference
- **[Authentication Guide](./docs/AUTHENTICATION.md)** - Laravel Sanctum setup
- **[n8n Integration](./docs/N8N_INTEGRATION_EXAMPLES.md)** - Workflow examples
- **[Implementation Plan](./docs/IMPLEMENTATION_PLAN.md)** - Package architecture

### Example Use Cases

#### Use Case 1: Blog to Social Media
Automatically create social media posts when new blog articles are published.

#### Use Case 2: AI-Powered Content
Generate content with OpenAI and schedule posts across multiple platforms.

#### Use Case 3: Content Curation
Monitor RSS feeds and share relevant content automatically.

#### Use Case 4: Bulk Scheduling
Schedule multiple posts from CSV files or spreadsheets.

#### Use Case 5: Emergency Announcements
Publish urgent content immediately across all platforms.

---

## 🧪 Testing

### Run Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run specific test
vendor/bin/pest tests/Feature/PostsApiTest.php
```

### Example Test

```php
public function test_can_create_post_via_api()
{
    Sanctum::actingAs($this->user, ['*']);

    $response = $this->postJson('/api/mixpost/posts', [
        'accounts' => [1, 2],
        'versions' => [
            [
                'is_original' => true,
                'content' => [
                    ['body' => 'Test post', 'media' => []]
                ]
            ]
        ]
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure(['data', 'message']);
}
```

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Make your changes**
4. **Run tests** (`composer test`)
5. **Commit your changes** (`git commit -m 'Add amazing feature'`)
6. **Push to the branch** (`git push origin feature/amazing-feature`)
7. **Open a Pull Request**

### Development Guidelines

- Follow **PSR-12** coding standards
- Write **tests** for new features
- Update **documentation** for API changes
- Keep **backward compatibility** when possible
- Add **changelog entries** for notable changes

### Code Style

```bash
# Format code
composer format

# Check code style
./vendor/bin/pint --test
```

---

## 🐛 Security Vulnerabilities

If you discover a security vulnerability, please send an email to **security@example.com**. All security vulnerabilities will be promptly addressed.

**Do not** open public issues for security vulnerabilities.

---

## 📜 Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

### Recent Updates

**v1.0.0** (2025-10-23)
- Initial release
- Complete REST API for Mixpost
- Laravel Sanctum authentication
- n8n integration support
- Comprehensive documentation

---

## 🙏 Credits

- **[Mixpost](https://github.com/inovector/mixpost)** - The amazing social media management platform this extends
- **[Laravel](https://laravel.com)** - The PHP framework
- **[Laravel Sanctum](https://laravel.com/docs/sanctum)** - API authentication
- **[n8n](https://n8n.io)** - Workflow automation platform

### Built With

- [Laravel](https://laravel.com) - Web application framework
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - API token authentication
- [Pest PHP](https://pestphp.com) - Testing framework
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer

---

## 📄 License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.

```
MIT License

Copyright (c) 2025 Your Name

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software...
```

---

## 💬 Support

- **Documentation**: [docs/](./docs/)
- **Issues**: [GitHub Issues](https://github.com/yourusername/mixpost-api/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/mixpost-api/discussions)
- **Mixpost Discord**: [Join Community](https://mixpost.app/discord)

---

## 🌟 Star History

If you find this package useful, please consider giving it a ⭐ on GitHub!

[![Star History Chart](https://api.star-history.com/svg?repos=yourusername/mixpost-api&type=Date)](https://star-history.com/#yourusername/mixpost-api&Date)

---

## 🔗 Links

- **Mixpost**: [https://mixpost.app](https://mixpost.app)
- **Mixpost GitHub**: [https://github.com/inovector/mixpost](https://github.com/inovector/mixpost)
- **Mixpost Documentation**: [https://docs.mixpost.app](https://docs.mixpost.app)
- **n8n**: [https://n8n.io](https://n8n.io)
- **Laravel**: [https://laravel.com](https://laravel.com)

---

<p align="center">
  Made with ❤️ for the Mixpost community
</p>

<p align="center">
  <strong>Transform your social media management with powerful API automation</strong>
</p>
