# n8n Integration Examples for Mixpost REST API

**Version**: 1.0
**Date**: 2025-10-23
**Purpose**: Practical n8n workflow examples for Mixpost automation

---

## 🔧 Initial Setup

### 1. Generate API Token

**Step 1**: Create an API token using HTTP Request node

**Node Configuration**:
```json
{
  "method": "POST",
  "url": "https://your-domain.com/api/mixpost/auth/tokens",
  "authentication": "none",
  "options": {},
  "bodyParameters": {
    "parameters": [
      {
        "name": "email",
        "value": "admin@example.com"
      },
      {
        "name": "password",
        "value": "your-password"
      },
      {
        "name": "token_name",
        "value": "n8n-automation"
      }
    ]
  }
}
```

**Response**: Copy the `token` value from the response

### 2. Create API Credentials in n8n

**Step 1**: Go to **Credentials** → **New**
**Step 2**: Select **Header Auth**
**Step 3**: Configure:
```
Name: Mixpost API
Header Name: Authorization
Header Value: Bearer YOUR_TOKEN_HERE
```

---

## 📝 Example 1: Auto-Post When Blog is Published

**Scenario**: Automatically create social media posts when a new blog post is published

### Workflow Nodes:

#### 1. Webhook Trigger
```json
{
  "node": "Webhook",
  "type": "n8n-nodes-base.webhook",
  "method": "POST",
  "path": "blog-published",
  "responseMode": "responseNode"
}
```

#### 2. Extract Blog Data
```json
{
  "node": "Set",
  "type": "n8n-nodes-base.set",
  "values": {
    "title": "={{ $json.blog_title }}",
    "url": "={{ $json.blog_url }}",
    "excerpt": "={{ $json.blog_excerpt }}",
    "image_url": "={{ $json.featured_image }}"
  }
}
```

#### 3. Upload Featured Image to Mixpost
```json
{
  "node": "HTTP Request - Upload Image",
  "type": "n8n-nodes-base.httpRequest",
  "method": "POST",
  "url": "https://your-domain.com/api/mixpost/media/download",
  "authentication": "predefinedCredentialType",
  "nodeCredentialType": "mixpostApi",
  "sendBody": true,
  "bodyParameters": {
    "parameters": [
      {
        "name": "url",
        "value": "={{ $json.image_url }}"
      }
    ]
  }
}
```

#### 4. Create Social Media Post
```json
{
  "node": "HTTP Request - Create Post",
  "type": "n8n-nodes-base.httpRequest",
  "method": "POST",
  "url": "https://your-domain.com/api/mixpost/posts",
  "authentication": "predefinedCredentialType",
  "nodeCredentialType": "mixpostApi",
  "sendBody": true,
  "specifyBody": "json",
  "jsonBody": {
    "accounts": [1, 2, 3],
    "tags": [1],
    "date": "={{ $now.plus(1, 'hour').format('YYYY-MM-DD') }}",
    "time": "={{ $now.plus(1, 'hour').format('HH:mm') }}",
    "versions": [
      {
        "is_original": true,
        "account_id": null,
        "content": [
          {
            "body": "New blog post: {{ $node['Set'].json.title }}\n\nRead more: {{ $node['Set'].json.url }}",
            "media": ["={{ $node['HTTP Request - Upload Image'].json.data.id }}"]
          }
        ]
      }
    ]
  }
}
```

#### 5. Respond to Webhook
```json
{
  "node": "Respond to Webhook",
  "type": "n8n-nodes-base.respondToWebhook",
  "respondWith": "json",
  "responseBody": {
    "success": true,
    "post_uuid": "={{ $json.data.uuid }}",
    "scheduled_at": "={{ $json.data.scheduled_at }}"
  }
}
```

---

## 🎨 Example 2: AI Content Generation + Social Posting

**Scenario**: Generate AI content daily and post to social media

### Workflow Nodes:

#### 1. Schedule Trigger
```json
{
  "node": "Schedule",
  "type": "n8n-nodes-base.scheduleTrigger",
  "rule": {
    "interval": [
      {
        "field": "cronExpression",
        "expression": "0 9 * * *"
      }
    ]
  }
}
```

#### 2. Generate Content with OpenAI
```json
{
  "node": "OpenAI",
  "type": "n8n-nodes-base.openAi",
  "resource": "text",
  "operation": "complete",
  "model": "gpt-4",
  "prompt": "Generate an engaging social media post about {{ $json.topic }}. Keep it under 280 characters and include relevant hashtags.",
  "maxTokens": 150
}
```

#### 3. Generate Image with DALL-E
```json
{
  "node": "OpenAI - Image",
  "type": "n8n-nodes-base.openAi",
  "resource": "image",
  "operation": "generate",
  "prompt": "Professional social media image for: {{ $node['OpenAI'].json.choices[0].text }}",
  "size": "1024x1024"
}
```

#### 4. Download and Upload Image
```json
{
  "node": "HTTP Request - Get Image",
  "type": "n8n-nodes-base.httpRequest",
  "method": "GET",
  "url": "={{ $node['OpenAI - Image'].json.data[0].url }}",
  "responseFormat": "file"
}
```

```json
{
  "node": "HTTP Request - Upload to Mixpost",
  "type": "n8n-nodes-base.httpRequest",
  "method": "POST",
  "url": "https://your-domain.com/api/mixpost/media",
  "authentication": "predefinedCredentialType",
  "nodeCredentialType": "mixpostApi",
  "sendBody": true,
  "contentType": "multipart-form-data",
  "bodyParameters": {
    "parameters": [
      {
        "name": "file",
        "inputDataFieldName": "data"
      }
    ]
  }
}
```

#### 5. Create and Schedule Post
```json
{
  "node": "HTTP Request - Create Post",
  "type": "n8n-nodes-base.httpRequest",
  "method": "POST",
  "url": "https://your-domain.com/api/mixpost/posts",
  "authentication": "predefinedCredentialType",
  "nodeCredentialType": "mixpostApi",
  "sendBody": true,
  "specifyBody": "json",
  "jsonBody": {
    "accounts": [1, 2, 3],
    "date": "={{ $now.format('YYYY-MM-DD') }}",
    "time": "={{ $now.plus(2, 'hours').format('HH:mm') }}",
    "versions": [
      {
        "is_original": true,
        "account_id": null,
        "content": [
          {
            "body": "={{ $node['OpenAI'].json.choices[0].text }}",
            "media": ["={{ $node['HTTP Request - Upload to Mixpost'].json.data.id }}"]
          }
        ]
      }
    ]
  }
}
```

---

## 🔄 Example 3: Cross-Platform Content Adaptation

**Scenario**: Create platform-specific versions of the same post

### Workflow Nodes:

#### 1. Manual Trigger
```json
{
  "node": "Manual Trigger",
  "type": "n8n-nodes-base.manualTrigger"
}
```

#### 2. Set Content
```json
{
  "node": "Set Content",
  "type": "n8n-nodes-base.set",
  "values": {
    "base_content": "Check out our latest product update!",
    "twitter_account_id": 1,
    "facebook_account_id": 2,
    "linkedin_account_id": 3,
    "media_id": 5
  }
}
```

#### 3. Create Post with Multiple Versions
```json
{
  "node": "HTTP Request - Create Multi-Version Post",
  "type": "n8n-nodes-base.httpRequest",
  "method": "POST",
  "url": "https://your-domain.com/api/mixpost/posts",
  "authentication": "predefinedCredentialType",
  "nodeCredentialType": "mixpostApi",
  "sendBody": true,
  "specifyBody": "json",
  "jsonBody": {
    "accounts": [1, 2, 3],
    "date": "={{ $now.format('YYYY-MM-DD') }}",
    "time": "={{ $now.plus(1, 'hour').format('HH:mm') }}",
    "versions": [
      {
        "is_original": true,
        "account_id": null,
        "content": [
          {
            "body": "={{ $json.base_content }}",
            "media": ["={{ $json.media_id }}"]
          }
        ]
      },
      {
        "is_original": false,
        "account_id": "={{ $json.twitter_account_id }}",
        "content": [
          {
            "body": "🚀 {{ $json.base_content }} #ProductUpdate #Innovation",
            "media": ["={{ $json.media_id }}"]
          }
        ]
      },
      {
        "is_original": false,
        "account_id": "={{ $json.linkedin_account_id }}",
        "content": [
          {
            "body": "We're excited to announce our latest product update.\n\n{{ $json.base_content }}\n\nLearn more: [link]",
            "media": ["={{ $json.media_id }}"]
          }
        ]
      }
    ]
  }
}
```

---

## 📊 Example 4: Post Performance Monitoring

**Scenario**: Monitor post performance and send notifications

### Workflow Nodes:

#### 1. Schedule Trigger (Daily)
```json
{
  "node": "Schedule Daily",
  "type": "n8n-nodes-base.scheduleTrigger",
  "rule": {
    "interval": [
      {
        "field": "cronExpression",
        "expression": "0 18 * * *"
      }
    ]
  }
}
```

#### 2. Get Dashboard Summary
```json
{
  "node": "HTTP Request - Dashboard",
  "type": "n8n-nodes-base.httpRequest",
  "method": "GET",
  "url": "https://your-domain.com/api/mixpost/reports/dashboard",
  "authentication": "predefinedCredentialType",
  "nodeCredentialType": "mixpostApi",
  "qs": {
    "from_date": "={{ $now.minus(1, 'day').format('YYYY-MM-DD') }}",
    "to_date": "={{ $now.format('YYYY-MM-DD') }}"
  }
}
```

#### 3. Filter Failed Posts
```json
{
  "node": "IF - Has Failed Posts",
  "type": "n8n-nodes-base.if",
  "conditions": {
    "number": [
      {
        "value1": "={{ $json.data.posts.failed }}",
        "operation": "larger",
        "value2": 0
      }
    ]
  }
}
```

#### 4. Send Slack Notification
```json
{
  "node": "Slack",
  "type": "n8n-nodes-base.slack",
  "resource": "message",
  "operation": "post",
  "channel": "#social-media",
  "text": "⚠️ Daily Social Media Report\n\n📊 Posts Published: {{ $node['HTTP Request - Dashboard'].json.data.posts.published }}\n❌ Failed Posts: {{ $node['HTTP Request - Dashboard'].json.data.posts.failed }}\n📅 Scheduled: {{ $node['HTTP Request - Dashboard'].json.data.posts.scheduled }}\n\nPlease review failed posts in Mixpost."
}
```

---

## 🎯 Example 5: Bulk Post Scheduling from CSV

**Scenario**: Schedule multiple posts from a CSV file

### Workflow Nodes:

#### 1. Manual Trigger
```json
{
  "node": "Manual Trigger",
  "type": "n8n-nodes-base.manualTrigger"
}
```

#### 2. Read CSV File
```json
{
  "node": "Read Binary File",
  "type": "n8n-nodes-base.readBinaryFile",
  "filePath": "/path/to/posts.csv"
}
```

#### 3. CSV to JSON
```json
{
  "node": "Spreadsheet File",
  "type": "n8n-nodes-base.spreadsheetFile",
  "operation": "read",
  "binaryPropertyName": "data"
}
```

#### 4. Split Into Items
```json
{
  "node": "Split In Batches",
  "type": "n8n-nodes-base.splitInBatches",
  "batchSize": 1
}
```

#### 5. Create Each Post
```json
{
  "node": "HTTP Request - Create Post",
  "type": "n8n-nodes-base.httpRequest",
  "method": "POST",
  "url": "https://your-domain.com/api/mixpost/posts",
  "authentication": "predefinedCredentialType",
  "nodeCredentialType": "mixpostApi",
  "sendBody": true,
  "specifyBody": "json",
  "jsonBody": {
    "accounts": "={{ $json.account_ids.split(',').map(id => parseInt(id)) }}",
    "tags": "={{ $json.tag_ids ? $json.tag_ids.split(',').map(id => parseInt(id)) : [] }}",
    "date": "={{ $json.schedule_date }}",
    "time": "={{ $json.schedule_time }}",
    "versions": [
      {
        "is_original": true,
        "account_id": null,
        "content": [
          {
            "body": "={{ $json.content }}",
            "media": "={{ $json.media_ids ? $json.media_ids.split(',').map(id => parseInt(id)) : [] }}"
          }
        ]
      }
    ]
  }
}
```

#### 6. Wait Between Requests
```json
{
  "node": "Wait",
  "type": "n8n-nodes-base.wait",
  "amount": 1,
  "unit": "seconds"
}
```

**CSV Format** (`posts.csv`):
```csv
content,account_ids,tag_ids,media_ids,schedule_date,schedule_time
"First post content",1,2,1,5,2025-10-24,10:00
"Second post content",2,3,1,2025-10-24,14:00
"Third post content",1,2,3,6,2025-10-25,09:00
```

---

## 🚨 Example 6: Emergency Post Publishing

**Scenario**: Immediately publish urgent announcements

### Workflow Nodes:

#### 1. Webhook Trigger
```json
{
  "node": "Webhook - Emergency",
  "type": "n8n-nodes-base.webhook",
  "method": "POST",
  "path": "emergency-post"
}
```

#### 2. Validate Input
```json
{
  "node": "IF - Is Emergency",
  "type": "n8n-nodes-base.if",
  "conditions": {
    "string": [
      {
        "value1": "={{ $json.priority }}",
        "operation": "equals",
        "value2": "emergency"
      }
    ]
  }
}
```

#### 3. Create Post
```json
{
  "node": "HTTP Request - Create Post",
  "type": "n8n-nodes-base.httpRequest",
  "method": "POST",
  "url": "https://your-domain.com/api/mixpost/posts",
  "authentication": "predefinedCredentialType",
  "nodeCredentialType": "mixpostApi",
  "sendBody": true,
  "specifyBody": "json",
  "jsonBody": {
    "accounts": "={{ $json.account_ids }}",
    "versions": [
      {
        "is_original": true,
        "account_id": null,
        "content": [
          {
            "body": "🚨 IMPORTANT: {{ $json.message }}",
            "media": []
          }
        ]
      }
    ]
  }
}
```

#### 4. Publish Immediately
```json
{
  "node": "HTTP Request - Publish Now",
  "type": "n8n-nodes-base.httpRequest",
  "method": "POST",
  "url": "https://your-domain.com/api/mixpost/posts/={{ $node['HTTP Request - Create Post'].json.data.uuid }}/publish",
  "authentication": "predefinedCredentialType",
  "nodeCredentialType": "mixpostApi"
}
```

---

## 🔍 Example 7: Content Curation from RSS Feeds

**Scenario**: Auto-share relevant content from RSS feeds

### Workflow Nodes:

#### 1. Schedule Trigger (Every 6 Hours)
```json
{
  "node": "Schedule",
  "type": "n8n-nodes-base.scheduleTrigger",
  "rule": {
    "interval": [
      {
        "field": "hours",
        "hoursInterval": 6
      }
    ]
  }
}
```

#### 2. RSS Feed Read
```json
{
  "node": "RSS Read",
  "type": "n8n-nodes-base.rssFeedRead",
  "url": "https://example.com/rss-feed"
}
```

#### 3. Filter New Items
```json
{
  "node": "IF - Is Recent",
  "type": "n8n-nodes-base.if",
  "conditions": {
    "dateTime": [
      {
        "value1": "={{ $json.pubDate }}",
        "operation": "afterDate",
        "value2": "={{ $now.minus(6, 'hours').toISO() }}"
      }
    ]
  }
}
```

#### 4. Format Content
```json
{
  "node": "Set",
  "type": "n8n-nodes-base.set",
  "values": {
    "title": "={{ $json.title }}",
    "link": "={{ $json.link }}",
    "description": "={{ $json.description.substring(0, 200) }}..."
  }
}
```

#### 5. Create Post
```json
{
  "node": "HTTP Request - Share Article",
  "type": "n8n-nodes-base.httpRequest",
  "method": "POST",
  "url": "https://your-domain.com/api/mixpost/posts",
  "authentication": "predefinedCredentialType",
  "nodeCredentialType": "mixpostApi",
  "sendBody": true,
  "specifyBody": "json",
  "jsonBody": {
    "accounts": [1, 2],
    "tags": [3],
    "date": "={{ $now.plus(30, 'minutes').format('YYYY-MM-DD') }}",
    "time": "={{ $now.plus(30, 'minutes').format('HH:mm') }}",
    "versions": [
      {
        "is_original": true,
        "account_id": null,
        "content": [
          {
            "body": "📖 {{ $json.title }}\n\n{{ $json.description }}\n\nRead more: {{ $json.link }}",
            "media": []
          }
        ]
      }
    ]
  }
}
```

---

## 🛠️ Common Patterns & Best Practices

### Error Handling

**Add Error Workflow**:
```json
{
  "node": "Error Trigger",
  "type": "n8n-nodes-base.errorTrigger",
  "continueOnFail": false
}
```

**Log Errors**:
```json
{
  "node": "Send Error to Slack",
  "type": "n8n-nodes-base.slack",
  "channel": "#errors",
  "text": "❌ Mixpost API Error\n\nWorkflow: {{ $workflow.name }}\nNode: {{ $json.node.name }}\nError: {{ $json.error.message }}"
}
```

### Rate Limiting

**Add Wait Node Between Bulk Operations**:
```json
{
  "node": "Wait",
  "type": "n8n-nodes-base.wait",
  "amount": 1,
  "unit": "seconds"
}
```

### Retry Logic

**Configure HTTP Request Node Retries**:
```json
{
  "retry": {
    "maxTries": 3,
    "waitBetweenTries": 5000
  }
}
```

---

## 📚 Useful n8n Expressions

### Date Formatting
```javascript
// Today at 10:00 AM
{{ $now.set({hour: 10, minute: 0}).format('YYYY-MM-DD HH:mm') }}

// Tomorrow at same time
{{ $now.plus(1, 'day').format('YYYY-MM-DD HH:mm') }}

// Next Monday
{{ $now.plus(7, 'days').startOf('week').plus(1, 'day').format('YYYY-MM-DD') }}
```

### Array Manipulation
```javascript
// Convert comma-separated string to integer array
{{ $json.account_ids.split(',').map(id => parseInt(id.trim())) }}

// Filter empty values
{{ $json.media_ids.split(',').filter(id => id).map(id => parseInt(id)) }}
```

### String Operations
```javascript
// Truncate to 280 characters (Twitter)
{{ $json.content.substring(0, 280) }}

// Add hashtags
{{ $json.content + ' ' + $json.hashtags.split(',').map(tag => '#' + tag).join(' ') }}
```

---

## 🎯 Next Steps

1. **Test Workflows**: Start with simple workflows and gradually add complexity
2. **Monitor Performance**: Use n8n's execution logs to track success/failure
3. **Optimize**: Batch operations where possible to reduce API calls
4. **Document**: Keep your workflow documentation up to date
5. **Backup**: Export your workflows regularly

---

**Document Version**: 1.0
**Last Updated**: 2025-10-23
