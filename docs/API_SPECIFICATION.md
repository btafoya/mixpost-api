# Mixpost REST API Add-on Specification

**Version**: 1.0
**Date**: 2025-10-23
**Purpose**: REST API extension for Mixpost to enable n8n integration

---

## 📋 Overview

This specification defines a REST API add-on for Mixpost that enables external workflow automation tools (like n8n) to interact with Mixpost programmatically. The API follows RESTful principles and Laravel best practices.

### Key Features
- ✅ Create, schedule, and publish social media posts
- ✅ Upload and manage media files
- ✅ Manage social media accounts
- ✅ Retrieve analytics and reports
- ✅ Manage tags and metadata
- ✅ Token-based authentication (Laravel Sanctum)
- ✅ Comprehensive error handling
- ✅ Full CRUD operations on all resources

---

## 🔐 Authentication

### Strategy: Laravel Sanctum API Tokens

**Token Generation**:
```http
POST /api/mixpost/auth/tokens
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "secret",
  "token_name": "n8n-integration",
  "abilities": ["*"]
}
```

**Response**:
```json
{
  "token": "1|abc123def456...",
  "token_type": "Bearer",
  "expires_at": null
}
```

**Usage**:
```http
Authorization: Bearer 1|abc123def456...
```

**Token Revocation**:
```http
DELETE /api/mixpost/auth/tokens/{token_id}
Authorization: Bearer 1|abc123def456...
```

---

## 📍 API Endpoints

Base URL: `/api/mixpost`

### 1. Posts

#### 1.1 List Posts
```http
GET /api/mixpost/posts
Authorization: Bearer {token}
```

**Query Parameters**:
- `page` (int): Page number (default: 1)
- `per_page` (int): Items per page (default: 20, max: 100)
- `status` (string): Filter by status (draft, scheduled, published, failed)
- `accounts[]` (array): Filter by account IDs
- `tags[]` (array): Filter by tag IDs
- `keyword` (string): Search in post content
- `from_date` (date): Filter from date (Y-m-d)
- `to_date` (date): Filter to date (Y-m-d)

**Response** (200 OK):
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "9b8c1234-5678-90ab-cdef-1234567890ab",
      "status": "scheduled",
      "schedule_status": "pending",
      "scheduled_at": "2025-10-24T10:00:00.000000Z",
      "published_at": null,
      "accounts": [
        {
          "id": 1,
          "uuid": "account-uuid",
          "name": "Twitter Account",
          "username": "@mycompany",
          "provider": "twitter",
          "errors": null,
          "provider_post_id": null
        }
      ],
      "versions": [
        {
          "id": 1,
          "account_id": null,
          "is_original": true,
          "content": [
            {
              "body": "Check out our latest update!",
              "media": [1, 2]
            }
          ]
        }
      ],
      "tags": [
        {
          "id": 1,
          "name": "marketing"
        }
      ],
      "created_at": "2025-10-23T15:30:00.000000Z",
      "updated_at": "2025-10-23T15:30:00.000000Z"
    }
  ],
  "links": {
    "first": "http://example.com/api/mixpost/posts?page=1",
    "last": "http://example.com/api/mixpost/posts?page=5",
    "prev": null,
    "next": "http://example.com/api/mixpost/posts?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 20,
    "to": 20,
    "total": 95
  }
}
```

#### 1.2 Get Single Post
```http
GET /api/mixpost/posts/{uuid}
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "data": {
    "id": 1,
    "uuid": "9b8c1234-5678-90ab-cdef-1234567890ab",
    "status": "scheduled",
    "schedule_status": "pending",
    "scheduled_at": "2025-10-24T10:00:00.000000Z",
    "published_at": null,
    "accounts": [...],
    "versions": [...],
    "tags": [...],
    "created_at": "2025-10-23T15:30:00.000000Z",
    "updated_at": "2025-10-23T15:30:00.000000Z"
  }
}
```

#### 1.3 Create Post
```http
POST /api/mixpost/posts
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "accounts": [1, 2, 3],
  "tags": [1, 2],
  "date": "2025-10-24",
  "time": "10:00",
  "versions": [
    {
      "is_original": true,
      "account_id": null,
      "content": [
        {
          "body": "Check out our latest blog post! #marketing #content",
          "media": [1, 2]
        }
      ]
    },
    {
      "is_original": false,
      "account_id": 2,
      "content": [
        {
          "body": "Custom version for Twitter with mentions @partner",
          "media": [1]
        }
      ]
    }
  ]
}
```

**Field Descriptions**:
- `accounts` (array, required): Array of account IDs to post to
- `tags` (array, optional): Array of tag IDs
- `date` (string, optional): Schedule date (Y-m-d format)
- `time` (string, optional): Schedule time (H:i format)
- `versions` (array, required): Array of post versions
  - `is_original` (boolean, required): Is this the default version?
  - `account_id` (int, nullable): Specific account for custom version
  - `content` (array, required): Array of content blocks
    - `body` (string, nullable): Post text (max 5000 chars)
    - `media` (array, optional): Array of media IDs

**Response** (201 Created):
```json
{
  "data": {
    "id": 1,
    "uuid": "9b8c1234-5678-90ab-cdef-1234567890ab",
    "status": "draft",
    "schedule_status": "pending",
    "scheduled_at": "2025-10-24T10:00:00.000000Z",
    "published_at": null,
    "accounts": [...],
    "versions": [...],
    "tags": [...],
    "created_at": "2025-10-23T15:30:00.000000Z",
    "updated_at": "2025-10-23T15:30:00.000000Z"
  },
  "message": "Post created successfully"
}
```

**Error Response** (422 Unprocessable Entity):
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "accounts": ["The accounts field is required."],
    "versions": ["The versions field must have at least 1 items."],
    "versions.0.content.0.body": ["The body may not be greater than 5000 characters."]
  }
}
```

#### 1.4 Update Post
```http
PUT /api/mixpost/posts/{uuid}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**: Same as Create Post

**Response** (200 OK):
```json
{
  "data": {...},
  "message": "Post updated successfully"
}
```

#### 1.5 Delete Post
```http
DELETE /api/mixpost/posts/{uuid}
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "message": "Post deleted successfully"
}
```

#### 1.6 Schedule Post (Publish Draft)
```http
POST /api/mixpost/posts/{uuid}/schedule
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "date": "2025-10-24",
  "time": "10:00"
}
```

**Response** (200 OK):
```json
{
  "data": {
    "id": 1,
    "uuid": "9b8c1234-5678-90ab-cdef-1234567890ab",
    "status": "scheduled",
    "scheduled_at": "2025-10-24T10:00:00.000000Z",
    ...
  },
  "message": "Post scheduled successfully"
}
```

#### 1.7 Publish Post Immediately
```http
POST /api/mixpost/posts/{uuid}/publish
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "data": {
    "id": 1,
    "uuid": "9b8c1234-5678-90ab-cdef-1234567890ab",
    "status": "scheduled",
    "scheduled_at": "2025-10-23T15:35:00.000000Z",
    ...
  },
  "message": "Post queued for immediate publishing"
}
```

#### 1.8 Duplicate Post
```http
POST /api/mixpost/posts/{uuid}/duplicate
Authorization: Bearer {token}
```

**Response** (201 Created):
```json
{
  "data": {
    "id": 2,
    "uuid": "new-uuid",
    "status": "draft",
    ...
  },
  "message": "Post duplicated successfully"
}
```

#### 1.9 Bulk Delete Posts
```http
DELETE /api/mixpost/posts
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "posts": ["uuid1", "uuid2", "uuid3"]
}
```

**Response** (200 OK):
```json
{
  "message": "3 posts deleted successfully"
}
```

---

### 2. Media

#### 2.1 List Media
```http
GET /api/mixpost/media
Authorization: Bearer {token}
```

**Query Parameters**:
- `page` (int): Page number
- `per_page` (int): Items per page (default: 20, max: 100)
- `search` (string): Search by filename

**Response** (200 OK):
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "media-uuid",
      "name": "image.jpg",
      "mime_type": "image/jpeg",
      "size": 1024000,
      "size_total": 1024000,
      "disk": "public",
      "path": "mixpost/media/2025/10/image.jpg",
      "url": "http://example.com/storage/mixpost/media/2025/10/image.jpg",
      "conversions": {
        "thumb": "mixpost/media/2025/10/image-thumb.jpg",
        "medium": "mixpost/media/2025/10/image-medium.jpg"
      },
      "created_at": "2025-10-23T15:30:00.000000Z"
    }
  ],
  "meta": {...}
}
```

#### 2.2 Get Single Media
```http
GET /api/mixpost/media/{uuid}
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "data": {
    "id": 1,
    "uuid": "media-uuid",
    "name": "image.jpg",
    ...
  }
}
```

#### 2.3 Upload Media
```http
POST /api/mixpost/media
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body**:
```
file: [binary file data]
```

**Supported Formats**:
- Images: jpg, jpeg, png, gif, webp
- Videos: mp4, mov, avi
- Max file size: Configured in Mixpost settings

**Response** (201 Created):
```json
{
  "data": {
    "id": 1,
    "uuid": "media-uuid",
    "name": "image.jpg",
    "mime_type": "image/jpeg",
    "size": 1024000,
    "url": "http://example.com/storage/mixpost/media/2025/10/image.jpg",
    ...
  },
  "message": "Media uploaded successfully"
}
```

#### 2.4 Upload Media from URL
```http
POST /api/mixpost/media/download
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "url": "https://example.com/image.jpg"
}
```

**Response** (201 Created):
```json
{
  "data": {...},
  "message": "Media downloaded successfully"
}
```

#### 2.5 Delete Media
```http
DELETE /api/mixpost/media/{uuid}
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "message": "Media deleted successfully"
}
```

#### 2.6 Bulk Delete Media
```http
DELETE /api/mixpost/media
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "media": ["uuid1", "uuid2", "uuid3"]
}
```

**Response** (200 OK):
```json
{
  "message": "3 media files deleted successfully"
}
```

---

### 3. Accounts

#### 3.1 List Accounts
```http
GET /api/mixpost/accounts
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "account-uuid",
      "name": "My Twitter Account",
      "username": "@mycompany",
      "provider": "twitter",
      "provider_id": "123456789",
      "authorized": true,
      "image": "https://pbs.twimg.com/profile_images/.../avatar.jpg",
      "data": {
        "followers_count": 1000,
        "following_count": 500
      },
      "created_at": "2025-01-15T10:00:00.000000Z",
      "updated_at": "2025-10-23T12:00:00.000000Z"
    }
  ]
}
```

#### 3.2 Get Single Account
```http
GET /api/mixpost/accounts/{uuid}
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "data": {
    "id": 1,
    "uuid": "account-uuid",
    ...
  }
}
```

#### 3.3 Update Account
```http
PUT /api/mixpost/accounts/{uuid}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "Updated Account Name"
}
```

**Response** (200 OK):
```json
{
  "data": {...},
  "message": "Account updated successfully"
}
```

#### 3.4 Delete Account
```http
DELETE /api/mixpost/accounts/{uuid}
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "message": "Account deleted successfully"
}
```

---

### 4. Tags

#### 4.1 List Tags
```http
GET /api/mixpost/tags
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "data": [
    {
      "id": 1,
      "name": "marketing",
      "created_at": "2025-10-23T15:30:00.000000Z",
      "updated_at": "2025-10-23T15:30:00.000000Z"
    }
  ]
}
```

#### 4.2 Create Tag
```http
POST /api/mixpost/tags
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "product-launch"
}
```

**Response** (201 Created):
```json
{
  "data": {
    "id": 2,
    "name": "product-launch",
    "created_at": "2025-10-23T15:30:00.000000Z",
    "updated_at": "2025-10-23T15:30:00.000000Z"
  },
  "message": "Tag created successfully"
}
```

#### 4.3 Update Tag
```http
PUT /api/mixpost/tags/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "updated-tag-name"
}
```

**Response** (200 OK):
```json
{
  "data": {...},
  "message": "Tag updated successfully"
}
```

#### 4.4 Delete Tag
```http
DELETE /api/mixpost/tags/{id}
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "message": "Tag deleted successfully"
}
```

---

### 5. Analytics & Reports

#### 5.1 Get Account Analytics
```http
GET /api/mixpost/reports/accounts/{account_uuid}
Authorization: Bearer {token}
```

**Query Parameters**:
- `from_date` (date): Start date (Y-m-d)
- `to_date` (date): End date (Y-m-d)
- `metrics[]` (array): Specific metrics (followers, engagement, impressions)

**Response** (200 OK):
```json
{
  "data": {
    "account": {
      "id": 1,
      "uuid": "account-uuid",
      "name": "Twitter Account",
      "provider": "twitter"
    },
    "period": {
      "from": "2025-10-01",
      "to": "2025-10-23"
    },
    "metrics": {
      "followers": {
        "current": 1000,
        "change": 50,
        "change_percentage": 5.0
      },
      "posts": {
        "total": 25,
        "published": 23,
        "failed": 2
      },
      "engagement": {
        "total": 500,
        "likes": 300,
        "comments": 100,
        "shares": 100
      }
    }
  }
}
```

#### 5.2 Get Post Analytics
```http
GET /api/mixpost/reports/posts/{post_uuid}
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "data": {
    "post": {
      "id": 1,
      "uuid": "post-uuid",
      "status": "published"
    },
    "accounts": [
      {
        "account_id": 1,
        "provider": "twitter",
        "provider_post_id": "123456789",
        "metrics": {
          "impressions": 1000,
          "likes": 50,
          "comments": 10,
          "shares": 5
        }
      }
    ]
  }
}
```

#### 5.3 Get Dashboard Summary
```http
GET /api/mixpost/reports/dashboard
Authorization: Bearer {token}
```

**Query Parameters**:
- `from_date` (date): Start date (Y-m-d)
- `to_date` (date): End date (Y-m-d)

**Response** (200 OK):
```json
{
  "data": {
    "period": {
      "from": "2025-10-01",
      "to": "2025-10-23"
    },
    "accounts": {
      "total": 5,
      "authorized": 5,
      "unauthorized": 0
    },
    "posts": {
      "total": 100,
      "draft": 10,
      "scheduled": 20,
      "published": 65,
      "failed": 5
    },
    "media": {
      "total": 150,
      "total_size": 524288000
    },
    "engagement": {
      "total": 5000,
      "likes": 3000,
      "comments": 1000,
      "shares": 1000
    }
  }
}
```

---

### 6. Calendar

#### 6.1 Get Calendar Posts
```http
GET /api/mixpost/calendar
Authorization: Bearer {token}
```

**Query Parameters**:
- `date` (string, required): Date in Y-m-d format
- `type` (string, required): View type (month, week)

**Response** (200 OK):
```json
{
  "data": {
    "date": "2025-10-23",
    "type": "month",
    "posts": [
      {
        "id": 1,
        "uuid": "post-uuid",
        "status": "scheduled",
        "scheduled_at": "2025-10-24T10:00:00.000000Z",
        "versions": [...],
        "accounts": [...]
      }
    ]
  }
}
```

---

### 7. System

#### 7.1 Get System Status
```http
GET /api/mixpost/system/status
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "data": {
    "version": "1.0.0",
    "environment": "production",
    "queue": {
      "connection": "redis",
      "status": "running",
      "jobs_pending": 5,
      "jobs_failed": 0
    },
    "services": {
      "configured": ["twitter", "facebook", "instagram"],
      "active": ["twitter", "facebook"]
    },
    "storage": {
      "disk": "public",
      "total_media": 150,
      "total_size": 524288000
    }
  }
}
```

---

## 📝 Standard Response Formats

### Success Response
```json
{
  "data": {...},
  "message": "Operation successful"
}
```

### Error Responses

#### 400 Bad Request
```json
{
  "message": "Invalid request parameters",
  "errors": {
    "field": ["Error description"]
  }
}
```

#### 401 Unauthorized
```json
{
  "message": "Unauthenticated"
}
```

#### 403 Forbidden
```json
{
  "message": "This action is unauthorized"
}
```

#### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

#### 422 Unprocessable Entity
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["Validation error description"]
  }
}
```

#### 500 Internal Server Error
```json
{
  "message": "An error occurred while processing your request",
  "error": "Error details (only in debug mode)"
}
```

---

## 🔄 Rate Limiting

**Default Limits**:
- 60 requests per minute per token
- Configurable via Mixpost settings

**Response Headers**:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1729785600
```

**Rate Limit Exceeded** (429 Too Many Requests):
```json
{
  "message": "Too many requests. Please try again later.",
  "retry_after": 30
}
```

---

## 📦 Pagination

All list endpoints support pagination:

**Request**:
```http
GET /api/mixpost/posts?page=2&per_page=50
```

**Response Structure**:
```json
{
  "data": [...],
  "links": {
    "first": "http://example.com/api/mixpost/posts?page=1",
    "last": "http://example.com/api/mixpost/posts?page=5",
    "prev": "http://example.com/api/mixpost/posts?page=1",
    "next": "http://example.com/api/mixpost/posts?page=3"
  },
  "meta": {
    "current_page": 2,
    "from": 51,
    "last_page": 5,
    "per_page": 50,
    "to": 100,
    "total": 235
  }
}
```

---

## 🎯 Common Use Cases for n8n

### Use Case 1: Create Scheduled Post from Blog Publication
```json
{
  "workflow": "When new blog post published",
  "action": "POST /api/mixpost/posts",
  "body": {
    "accounts": [1, 2, 3],
    "tags": [1],
    "date": "{{$now.plus(1, 'hour').format('YYYY-MM-DD')}}",
    "time": "{{$now.plus(1, 'hour').format('HH:mm')}}",
    "versions": [
      {
        "is_original": true,
        "account_id": null,
        "content": [
          {
            "body": "New blog post: {{$node.BlogPost.json.title}} - {{$node.BlogPost.json.url}}",
            "media": []
          }
        ]
      }
    ]
  }
}
```

### Use Case 2: Upload Image and Create Post
```json
{
  "workflow": "Generate image → Upload → Create post",
  "steps": [
    {
      "step": 1,
      "action": "POST /api/mixpost/media/download",
      "body": {
        "url": "{{$node.GenerateImage.json.image_url}}"
      }
    },
    {
      "step": 2,
      "action": "POST /api/mixpost/posts",
      "body": {
        "accounts": [1, 2],
        "versions": [
          {
            "is_original": true,
            "content": [
              {
                "body": "Check out our latest visual!",
                "media": ["{{$node.UploadMedia.json.data.id}}"]
              }
            ]
          }
        ]
      }
    }
  ]
}
```

### Use Case 3: Publish Post Immediately
```json
{
  "workflow": "Emergency announcement",
  "action": "POST /api/mixpost/posts/{uuid}/publish"
}
```

### Use Case 4: Monitor Post Status
```json
{
  "workflow": "Check post published successfully",
  "action": "GET /api/mixpost/posts/{uuid}",
  "condition": "{{$json.data.status}} === 'published'"
}
```

---

## 🔒 Security Considerations

1. **HTTPS Only**: All API requests must use HTTPS in production
2. **Token Security**: Store tokens securely, never commit to repositories
3. **Token Rotation**: Implement periodic token rotation
4. **IP Whitelisting**: Optional IP restriction for API access
5. **CORS**: Configure appropriate CORS policies
6. **Input Validation**: All inputs validated on server side
7. **Rate Limiting**: Prevent abuse with request throttling

---

## 🚀 Implementation Phases

### Phase 1: Core API (Required for n8n)
- ✅ Authentication (Sanctum tokens)
- ✅ Posts CRUD
- ✅ Media upload & management
- ✅ Basic error handling

### Phase 2: Extended Features
- ✅ Accounts management
- ✅ Tags management
- ✅ Analytics & reports
- ✅ Calendar endpoints

### Phase 3: Advanced Features
- ✅ Webhooks for post status changes
- ✅ Bulk operations optimization
- ✅ Advanced filtering & search
- ✅ Rate limiting customization

---

## 📚 Next Steps

1. Review this specification
2. Confirm requirements alignment
3. Implement Laravel API routes & controllers
4. Create API documentation (OpenAPI/Swagger)
5. Build n8n integration examples
6. Test with n8n workflows
7. Deploy as Mixpost add-on package

---

**Document Version**: 1.0
**Last Updated**: 2025-10-23
**Maintained By**: Mixpost REST API Add-on Team
