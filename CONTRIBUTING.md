# Contributing to Mixpost REST API

First off, thank you for considering contributing to Mixpost REST API! It's people like you that make this tool better for everyone.

## 🌟 How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When you create a bug report, include as many details as possible:

**Bug Report Template:**

```markdown
**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Make API call to '...'
2. With payload '...'
3. See error

**Expected behavior**
What you expected to happen.

**Actual behavior**
What actually happened.

**API Request**
```bash
curl -X POST https://example.com/api/mixpost/posts \
  -H "Authorization: Bearer ..." \
  -d '{"accounts": [1]}'
```

**Response**
```json
{
  "message": "Error occurred",
  "errors": {...}
}
```

**Environment:**
- Mixpost API Version: [e.g., 1.0.0]
- Mixpost Version: [e.g., 1.0.0]
- Laravel Version: [e.g., 10.47]
- PHP Version: [e.g., 8.2.0]
- Database: [e.g., MySQL 8.0]

**Additional context**
Add any other context about the problem here.
```

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. Create an issue and provide the following:

**Enhancement Template:**

```markdown
**Is your feature request related to a problem?**
A clear description of what the problem is. Ex. I'm always frustrated when [...]

**Describe the solution you'd like**
A clear description of what you want to happen.

**Describe alternatives you've considered**
Alternative solutions or features you've considered.

**API Design (if applicable)**
How would this work in the API?
```http
POST /api/mixpost/new-endpoint
{
  "example": "data"
}
```

**Use case**
Why would this be useful? What problem does it solve?

**Additional context**
Screenshots, mockups, or examples.
```

### Pull Requests

1. **Fork the Repository**
   ```bash
   git clone https://github.com/yourusername/mixpost-api.git
   cd mixpost-api
   ```

2. **Create a Branch**
   ```bash
   git checkout -b feature/amazing-feature
   # or
   git checkout -b fix/bug-fix
   ```

3. **Make Your Changes**
   - Write clean, readable code
   - Follow PSR-12 coding standards
   - Add tests for new features
   - Update documentation

4. **Run Tests**
   ```bash
   composer test
   ```

5. **Format Code**
   ```bash
   composer format
   ```

6. **Commit Your Changes**
   ```bash
   git add .
   git commit -m "Add amazing feature"
   ```

   **Commit Message Format:**
   ```
   <type>: <subject>

   <body>

   <footer>
   ```

   **Types:**
   - `feat`: New feature
   - `fix`: Bug fix
   - `docs`: Documentation changes
   - `style`: Code style changes (formatting)
   - `refactor`: Code refactoring
   - `test`: Adding or updating tests
   - `chore`: Maintenance tasks

   **Examples:**
   ```
   feat: Add bulk post scheduling endpoint

   - Added POST /api/mixpost/posts/bulk-schedule
   - Supports scheduling up to 100 posts at once
   - Includes validation and error handling

   Closes #123
   ```

7. **Push to GitHub**
   ```bash
   git push origin feature/amazing-feature
   ```

8. **Open a Pull Request**
   - Use a clear, descriptive title
   - Reference any related issues
   - Provide a detailed description
   - Include screenshots if applicable

---

## 📋 Development Guidelines

### Code Style

We follow **PSR-12** coding standards. Use Laravel Pint to format code:

```bash
# Format all files
./vendor/bin/pint

# Check formatting without making changes
./vendor/bin/pint --test
```

### Testing

All new features **must** include tests:

```php
<?php

namespace Inovector\MixpostApi\Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class NewFeatureTest extends TestCase
{
    public function test_new_feature_works()
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->postJson('/api/mixpost/new-endpoint', [
            'data' => 'test'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'message']);
    }
}
```

Run tests before submitting:

```bash
# Run all tests
composer test

# Run specific test
vendor/bin/pest tests/Feature/NewFeatureTest.php

# Run with coverage
composer test-coverage
```

### Documentation

Update documentation for any changes:

1. **API Specification** - `docs/API_SPECIFICATION.md`
2. **README** - `README.md`
3. **Changelog** - `CHANGELOG.md`
4. **Code Comments** - PHPDoc blocks for all methods

**PHPDoc Example:**

```php
/**
 * Create a new post via API
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\JsonResponse
 *
 * @throws \Illuminate\Validation\ValidationException
 */
public function store(Request $request): JsonResponse
{
    // Implementation
}
```

### API Design Principles

1. **RESTful**: Follow REST conventions
2. **Consistent**: Use consistent naming and patterns
3. **Versioned**: Consider backward compatibility
4. **Documented**: Update API docs
5. **Tested**: Full test coverage
6. **Secure**: Validate inputs, sanitize outputs

**Good API Design:**
```http
POST /api/mixpost/posts
GET /api/mixpost/posts/{uuid}
PUT /api/mixpost/posts/{uuid}
DELETE /api/mixpost/posts/{uuid}
```

**Avoid:**
```http
POST /api/mixpost/createPost
GET /api/mixpost/getPost?id=123
```

### Performance

- Optimize database queries (use eager loading)
- Implement pagination for list endpoints
- Cache where appropriate
- Use resource classes for transformations
- Avoid N+1 queries

**Example:**
```php
// Good: Eager loading
$posts = Post::with(['accounts', 'versions', 'tags'])
    ->paginate(20);

// Bad: N+1 queries
$posts = Post::paginate(20);
foreach ($posts as $post) {
    $post->accounts; // Separate query for each post
}
```

### Security

- **Never** expose sensitive data in responses
- Validate **all** user inputs
- Use **parameterized queries** (Eloquent does this)
- Sanitize error messages
- Follow Laravel security best practices

---

## 🏗️ Project Structure

```
mixpost-api/
├── src/
│   ├── Http/
│   │   ├── Controllers/Api/    ← API controllers
│   │   ├── Middleware/         ← Custom middleware
│   │   ├── Requests/           ← Form requests
│   │   └── Resources/          ← API resources
│   ├── Providers/              ← Service providers
│   └── Routes/                 ← Route definitions
├── tests/
│   └── Feature/                ← Feature tests
├── docs/                       ← Documentation
└── config/                     ← Configuration files
```

---

## 🧪 Testing Checklist

Before submitting a PR, ensure:

- [ ] All tests pass (`composer test`)
- [ ] Code follows PSR-12 (`composer format`)
- [ ] New features have tests
- [ ] Documentation is updated
- [ ] No breaking changes (or clearly documented)
- [ ] Commit messages are clear
- [ ] PR description is detailed

---

## 📝 Documentation Guidelines

### README Updates

- Keep it concise and scannable
- Update examples if API changes
- Add new features to feature list
- Update installation instructions if needed

### API Documentation

Follow this format for new endpoints:

```markdown
#### 1.X New Endpoint Name

```http
POST /api/mixpost/new-endpoint
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "field": "value"
}
```

**Response** (200 OK):
```json
{
  "data": {...},
  "message": "Success"
}
```

**Error Response** (422 Unprocessable Entity):
```json
{
  "message": "Validation failed",
  "errors": {...}
}
```
```

---

## 🎯 Good First Issues

Looking for something to work on? Check issues labeled:

- `good first issue` - Good for newcomers
- `help wanted` - We need help with these
- `documentation` - Documentation improvements
- `enhancement` - Feature requests

---

## 💬 Communication

- **GitHub Issues**: Bug reports and feature requests
- **GitHub Discussions**: Questions and general discussion
- **Pull Requests**: Code contributions
- **Discord**: Join the [Mixpost Discord](https://mixpost.app/discord)

---

## 🏆 Recognition

Contributors will be:

- Listed in `CHANGELOG.md` for their contributions
- Mentioned in release notes
- Added to GitHub contributors page
- Acknowledged in project documentation

---

## 📜 Code of Conduct

### Our Pledge

We pledge to make participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

### Our Standards

**Positive behavior:**
- Using welcoming and inclusive language
- Being respectful of differing viewpoints
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards other members

**Unacceptable behavior:**
- Trolling, insulting/derogatory comments, personal attacks
- Public or private harassment
- Publishing others' private information
- Other conduct which could reasonably be considered inappropriate

### Enforcement

Instances of abusive, harassing, or otherwise unacceptable behavior may be reported by contacting the project team. All complaints will be reviewed and investigated promptly and fairly.

---

## ❓ Questions?

Feel free to:

- Open a [GitHub Discussion](https://github.com/yourusername/mixpost-api/discussions)
- Join the [Mixpost Discord](https://mixpost.app/discord)
- Review existing [documentation](./docs/)

---

## 🙏 Thank You!

Your contributions make this project better for everyone. We appreciate your time and effort!

---

**Happy Contributing! 🚀**
