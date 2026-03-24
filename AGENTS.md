# AGENTS.md

## Build & Run Commands

```bash
# Start the full application stack (nginx, php, mysql, phpmyadmin)
docker compose up -d

# Stop the application stack
docker compose down

# Install dependencies (autoloader only, no third-party packages)
composer install

# Regenerate autoloader
composer dump-autoload

# Access the application
# Web: http://localhost:8080
# phpMyAdmin: http://localhost:8081
# MySQL: localhost:3307 (root/dev)
```

## Testing & Linting

**No testing framework is currently configured.** PHPUnit is referenced in `.gitignore` but not installed.

**No linting tools are configured.** The `.gitignore` references PHP-CS-Fixer and PHPStan cache files, indicating future intent.

## Project Architecture

This is a **custom PHP micro-framework** with manual MVC pattern:

- **Framework**: No framework — pure PHP 8.2 with `spl_autoload_register` and `composer.json` PSR-4 autoloading (`App\` → `src/`)
- **Database**: PDO Singleton (`App\Database\Database`) wrapping MySQL
- **Models**: Active Record pattern with static finders (`findById`, `findByUserId`, `findAllByUserId`)
- **Routing**: Flat array in `public/index.php`, 13 routes mapping URLs to controller methods
- **Frontend**: Bootstrap 5.3.2 (CDN), jsPDF for client-side PDF, vanilla ES modules
- **Docker**: PHP 8.2-FPM, nginx:alpine, MySQL 8.0, phpMyAdmin

## Code Style Conventions

### Namespacing
- Root: `App\`
- Sub-namespaces: `App\Controllers`, `App\Models`, `App\Services`, `App\Database`
- Directory structure mirrors namespace (PSR-4 compliant)

### Naming Conventions
| Type | Convention | Examples |
|------|------------|----------|
| Controllers | `{Entity}Controller`, PascalCase | `AuthController`, `QuoteController` |
| Models | Singular entity, PascalCase | `User`, `Client`, `Quote`, `QuoteItem` |
| Services | `{Purpose}Service`, PascalCase | `CsrfService`, `ValidationService` |
| Model properties | snake_case (match DB columns) | `$user_id`, `$quote_number`, `$created_at` |
| Methods | camelCase | `findById()`, `verifyPassword()`, `saveQuote()` |
| Constants | UPPER_SNAKE_CASE | `DB_HOST`, `SESSION_KEY`, `TOKEN_LENGTH` |

### Type Declarations
- PHP type hints on all method parameters and return types (`int $id`, `: void`, `: ?self`)
- Properties have PHP 8.2 typed declarations (`public int $id`, `public ?string $address`)
- No `declare(strict_types=1)` used

### Formatting
- 4-space indentation
- Opening braces on same line as declarations (K&R style for methods)
- No trailing whitespace
- PHP files end with closing `?>` tag omitted

### Error Handling
- Controllers throw `\RuntimeException` or `\Exception` with French messages
- Model methods return `null` on not found, `false` on failure
- Use `header('Location: ...')` for redirects with `exit;` immediately after
- Session errors stored in `$_SESSION['error']` or `$_SESSION['errors']` array

### Security Patterns
- CSRF tokens: Form-specific tokens via `CsrfService::generateTokenFor($formName)`
- Input validation: `ValidationService` for email, ID, date validation
- Output escaping: `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')` in views
- Password hashing: `password_hash()` with `PASSWORD_DEFAULT`
- Session: `session_regenerate_id(true)` after auth, HttpOnly + SameSite cookies

## View Layer

- Views use output buffering: `ob_start()` at top, `ob_get_clean()` into `$content`
- Shared layout: `src/Views/layout.php` includes `$content` variable
- French language for UI strings and user-facing messages
- Forms include `csrf_token` hidden field (form-specific)

## Database

- Schema defined in `sql/schema.sql` with seed data
- Tables: `users`, `companies`, `clients`, `quotes`, `quote_items`
- Connection config: `config/database.php` (constants: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`)
- Internal Docker port: 3306 (not 3307)

## Important Notes

- **No third-party packages** — only PHP built-in functions and PDO
- **French UI** — all user-facing strings are in French
- **Test users**: `admin@test.com` / `dev123` has company and clients
- **Static methods**: `AuthController::requireAuth()`, `AuthController::getUserId()` for access control
- **Model pattern**: `create()` returns object, `update()` and `delete()` are instance methods
