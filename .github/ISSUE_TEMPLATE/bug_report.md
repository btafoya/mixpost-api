---
name: Bug Report
about: Report a bug or unexpected behavior
title: '[BUG] '
labels: bug
assignees: ''
---

**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Make API call to '...'
2. With payload '...'
3. See error

**Expected behavior**
What you expected to happen.

**API Request**
```bash
curl -X POST https://example.com/api/mixpost/posts \
  -H "Authorization: Bearer ..." \
  -d '{...}'
```

**Response**
```json
{
  "message": "Error",
  "errors": {...}
}
```

**Environment:**
- Mixpost API Version: [e.g., 1.0.0]
- Mixpost Version: [e.g., 1.0.0]
- Laravel Version: [e.g., 10.47]
- PHP Version: [e.g., 8.2.0]

**Additional context**
Add any other context about the problem here.
