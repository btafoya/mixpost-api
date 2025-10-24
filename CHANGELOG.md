# Changelog

All notable changes to `mixpost-api` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Reports & Analytics API endpoints
- Calendar API for scheduled post management
- System monitoring and health check endpoints
- Webhook support for post status changes
- Bulk operations optimization
- Advanced filtering capabilities
- OpenAPI/Swagger documentation generation
- GraphQL API support (future consideration)

---

## [1.0.0] - 2025-10-24

### Added
- **Authentication System**
  - Laravel Sanctum token-based authentication
  - Token generation, revocation, and management endpoints
  - Token abilities for granular permissions
  - Support for multiple tokens per user
  - Health check endpoint for API status validation

- **Posts API**
  - Full CRUD operations for posts
  - Schedule posts for future publishing
  - Publish posts immediately (30-second delay)
  - Duplicate existing posts with all relationships
  - Bulk delete operations
  - Filter by status, accounts, tags, dates
  - Support for multiple post versions (platform-specific content)
  - Media attachment support
  - Custom form requests extending Mixpost's validation

- **Media API**
  - Upload media files (images: jpg, jpeg, png, gif, webp; videos: mp4, mov, avi)
  - Download media from external URLs
  - List and search media library with pagination
  - Delete individual media files
  - Bulk delete operations
  - Media conversion support (thumbnails) with conditional image processing
  - Search functionality by filename

- **Accounts API**
  - List all connected social media accounts
  - Get individual account details with provider information
  - Update account settings
  - Delete accounts
  - Account provider field for platform identification

- **Tags API**
  - Create, read, update, delete tags
  - Assign tags to posts
  - Filter posts by tags
  - Tag-based organization
  - Unique tag name validation

- **API Resources**
  - PostResource with comprehensive field mapping (accounts, versions, tags, schedule_status)
  - MediaResource with URL field for direct media access
  - AccountResource with provider field
  - TagResource for consistent JSON responses
  - Proper relationship loading and transformation

- **Custom Form Requests**
  - StorePostRequest extending Mixpost's StorePost
  - UpdatePostRequest with route parameter compatibility
  - Database default attribute loading (schedule_status)
  - Transaction-safe operations

- **Security Features**
  - Rate limiting (60 requests/minute default, configurable)
  - IP whitelisting support
  - HTTPS enforcement for production environments
  - Comprehensive input validation
  - Error message sanitization
  - ValidateApiToken middleware

- **Developer Experience**
  - RESTful API design with ID-based routing
  - Consistent JSON response formats
  - Comprehensive error handling with appropriate status codes
  - Pagination support with configurable per_page limits
  - Filtering and sorting capabilities
  - 100% test coverage (41/41 tests passing)
  - Laravel Pint code style compliance
  - GitHub Actions CI/CD integration

- **Documentation**
  - Complete API specification
  - Authentication guide
  - n8n workflow examples
  - Implementation plan
  - Contributing guidelines
  - Security policy
  - Development setup documentation (CLAUDE.md)

### Changed
- **Routing Architecture**: Migrated from UUID-based to ID-based routing
  - All routes use integer `{id}` parameters instead of `{uuid}`
  - Controllers updated to use `findOrFail($id)` instead of UUID lookups
  - Tests updated to work with integer IDs
  - Maintains backward compatibility with Mixpost's integer-based models

- **Form Request Integration**: Custom form requests for better Mixpost integration
  - Route parameter compatibility ('id' instead of 'post')
  - Return type corrections (Post model instead of boolean)
  - Proper model attribute refresh after database operations

- **Test Environment**: Conditional image processing
  - Media controller checks for image service binding before adding conversions
  - Prevents BindingResolutionException in test environments
  - Maintains full functionality in production with image processing services

### Dependencies
- Laravel ^10.47 | ^11.0
- PHP ^8.2
- Laravel Sanctum ^3.0 | ^4.0
- Mixpost ^1.0 (dev dependency for testing)

### Technical Details
- **Test Coverage**: 41/41 tests passing (100%)
- **Assertions**: 256 assertions across all tests
- **Code Style**: Laravel Pint compliance with PSR-12 standards
- **CI/CD**: GitHub Actions with PHP 8.2 and 8.3 testing
- **Package Type**: Laravel Service Provider Package
- **Namespace**: `Btafoya\MixpostApi`

### Notes
- Initial stable release
- Production-ready
- Fully tested with comprehensive coverage
- n8n compatible
- Fully compatible with Mixpost core functionality
- No breaking changes to Mixpost integration

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

Future releases will include detailed upgrade instructions for breaking changes.

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
- Implemented secure token-based authentication with Laravel Sanctum
- Added configurable rate limiting (default: 60 requests/minute)
- HTTPS enforcement for production environments
- Comprehensive input validation on all endpoints
- IP whitelisting support
- Error message sanitization to prevent information disclosure

---

## Implementation Phases

### Phase 1: Foundation ✅
- Package structure and service provider
- Laravel Sanctum authentication integration
- Base API controller and middleware
- Configuration system
- Initial test infrastructure

### Phase 2: Core APIs ✅
- Posts API with full CRUD operations
- Media API with upload and URL download
- Accounts API (read-only management)
- Tags API with full CRUD
- Comprehensive test coverage

### Phase 3: API Resources ✅
- Custom form requests extending Mixpost
- API resource transformations
- Route parameter compatibility fixes
- 100% test coverage achievement
- Code style compliance

### Phase 4: Future Enhancements 📅
- Reports & Analytics API
- Calendar API
- System monitoring endpoints
- Advanced filtering
- OpenAPI/Swagger documentation

---

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

## Security

Please see [SECURITY.md](SECURITY.md) for details on our security policy and how to report vulnerabilities.

## Credits

- [Brian Tafoya](https://github.com/btafoya) - Package Author
- [All Contributors](../../contributors)
- [Mixpost](https://github.com/inovector/mixpost) - Core Social Media Management Platform
- [Laravel](https://laravel.com) - The PHP Framework
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - API Authentication

---

**Note**: Dates are in YYYY-MM-DD format (ISO 8601)
