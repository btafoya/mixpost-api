# Changelog

All notable changes to `mixpost-api` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Webhook support for post status changes
- Bulk operations optimization
- Advanced filtering capabilities
- OpenAPI/Swagger documentation generation
- GraphQL API support (future consideration)

---

## [1.0.0] - 2025-10-23

### Added
- **Authentication System**
  - Laravel Sanctum token-based authentication
  - Token generation, revocation, and management endpoints
  - Token abilities for granular permissions
  - Support for multiple tokens per user

- **Posts API**
  - Full CRUD operations for posts
  - Schedule posts for future publishing
  - Publish posts immediately
  - Duplicate existing posts
  - Bulk delete operations
  - Filter by status, accounts, tags, dates
  - Support for multiple post versions (platform-specific content)
  - Media attachment support

- **Media API**
  - Upload media files (images, videos)
  - Download media from external URLs
  - List and search media library
  - Delete media files
  - Bulk delete operations
  - Media conversion support (thumbnails)

- **Accounts API**
  - List all connected social media accounts
  - Get individual account details
  - Update account settings
  - Delete accounts
  - Account analytics integration

- **Tags API**
  - Create, read, update, delete tags
  - Assign tags to posts
  - Filter posts by tags
  - Tag-based organization

- **Reports & Analytics API**
  - Dashboard summary metrics
  - Account-specific analytics
  - Post performance data
  - Engagement metrics (likes, comments, shares)
  - Time-range filtering
  - Follower growth tracking

- **Calendar API**
  - View scheduled posts by date
  - Month and week view support
  - Filter by accounts and tags

- **System API**
  - System status monitoring
  - Queue health checks
  - Service configuration status
  - Storage information

- **Security Features**
  - Rate limiting (60 requests/minute default)
  - IP whitelisting support
  - HTTPS enforcement
  - Input validation
  - Error message sanitization

- **Developer Experience**
  - RESTful API design
  - Consistent JSON response formats
  - Comprehensive error handling
  - Pagination support
  - Filtering and sorting
  - Detailed API documentation
  - n8n integration examples

- **Documentation**
  - Complete API specification
  - Authentication guide
  - n8n workflow examples
  - Implementation plan
  - Contributing guidelines
  - Security policy

### Dependencies
- Laravel ^10.47 | ^11.0 | ^12.0
- PHP ^8.2
- Laravel Sanctum ^3.0 | ^4.0
- Mixpost ^1.0

### Notes
- Initial stable release
- Production-ready
- Fully tested
- n8n compatible
- Backward compatible with Mixpost

---

## Version History

### Version Naming Convention
- **Major (X.0.0)**: Breaking changes, major features, architecture changes
- **Minor (x.X.0)**: New features, enhancements, backward compatible
- **Patch (x.x.X)**: Bug fixes, security patches, documentation updates

### Support Policy
- **Latest Version**: Full support, regular updates
- **Previous Major Version**: Security fixes only for 12 months
- **Older Versions**: No longer supported

---

## Upgrade Guide

### From 0.x to 1.0

This is the initial stable release. No upgrade path needed.

---

## Breaking Changes

### 1.0.0
None (initial release)

---

## Deprecations

### 1.0.0
None (initial release)

---

## Security Fixes

### 1.0.0
- Implemented secure token-based authentication
- Added rate limiting
- HTTPS enforcement
- Input validation

---

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

## Security

Please see [SECURITY.md](SECURITY.md) for details on our security policy and how to report vulnerabilities.

## Credits

- [All Contributors](../../contributors)
- [Mixpost](https://github.com/inovector/mixpost)
- [Laravel](https://laravel.com)

---

**Note**: Dates are in YYYY-MM-DD format
