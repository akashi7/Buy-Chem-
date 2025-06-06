# Buy Chem Japan Authentication System

A secure multi-step registration and authentication system built with Laravel and PostgreSQL.

## Tech Stack

-   Laravel 10.x
-   PostgreSQL
-   Laravel Sanctum for API Authentication
-   Two-Factor Authentication (2FA) via Email

## Setup Instructions

1. Clone the repository:

```bash
git clone <repository-url>
cd <project-directory>
```

2. Install dependencies:

```bash
composer install
```

3. Set up environment variables:

```bash
cp .env.example .env
```

4. Configure your database in `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Configure mail settings in `.env` for 2FA:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

6. Run migrations:

```bash
php artisan migrate
```

7. Start the development server:

```bash
php artisan serve
```

## API Endpoints

### Registration Wizard

#### 1. Step 1 - Personal Information

```http
POST /api/register/step1
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Doe",
    "gender": "male",
    "date_of_birth": "1990-01-01",
    "email": "john.doe@example.com",
    "nationality": "Japan",
    "phone_number": "8012345678"
}
```

Response:

```json
{
    "message": "Step 1 completed successfully",
    "unique_identifier": "uuid-string",
    "current_step": 1
}
```

#### 2. Step 2 - Address Information

```http
POST /api/register/{unique_identifier}/step2
Content-Type: application/json

{
    "address_line1": "123 Main St",
    "city": "Tokyo",
    "state": "Tokyo",
    "postal_code": "100-0001",
    "country": "Japan"
}
```

Response:

```json
{
    "message": "Step 2 completed successfully",
    "unique_identifier": "uuid-string",
    "current_step": 2
}
```

#### 3. Step 3 - Two-Factor Authentication

Generate 2FA Code:

```http
POST /api/2fa/generate
Content-Type: application/json

{
    "email": "john.doe@example.com"
}
```

Response:

```json
{
    "message": "2FA code sent successfully"
}
```

Verify 2FA Code:

```http
POST /api/2fa/verify
Content-Type: application/json

{
    "email": "john.doe@example.com",
    "code": "123456"
}
```

Response:

```json
{
    "message": "2FA code verified successfully",
    "current_step": 3,
    "unique_identifier": "uuid-string"
}
```

#### 4. Step 4 - Password Setup

```http
POST /api/register/{unique_identifier}/step4
Content-Type: application/json

{
    "password": "your_password",
    "password_confirmation": "your_password"
}
```

Response:

```json
{
    "message": "Password updated successfully",
    "current_step": 4,
    "unique_identifier": "uuid-string"
}
```

#### 5. Step 5 - Review and Terms

```http
POST /api/register/{unique_identifier}/step5
Content-Type: application/json

{
    "terms_accepted": true
}
```

Response:

```json
{
    "message": "Registration completed successfully",
    "current_step": 5,
    "unique_identifier": "uuid-string"
}
```

### Authentication

#### Login

```http
POST /api/login
Content-Type: application/json

{
    "email": "john.doe@example.com",
    "password": "your_password"
}
```

Response:

```json
{
    "message": "Login successful",
    "access_token": "token-string",
    "token_type": "Bearer",
    "user": {
        "email": "john.doe@example.com",
        "name": "John Doe",
        "unique_identifier": "uuid-string"
    }
}
```

#### Logout

```http
POST /api/logout
Authorization: Bearer {token}
```

Response:

```json
{
    "message": "Successfully logged out"
}
```

### Registration Status and Management

#### Check Registration Status

```http
GET /api/register/{unique_identifier}
```

Response:

```json
{
    "current_step": 3,
    "steps_completed": {
        "step1": true,
        "step2": true,
        "step3": true,
        "step4": false,
        "step5": false
    },
    "registration_data": {
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com"
        // ... other registration data
    }
}
```

#### Resume Registration

```http
GET /api/register/resume
Authorization: Bearer {token}
```

Response:

```json
{
    "current_step": 3,
    "unique_identifier": "uuid-string",
    "registration_data": {
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com"
        // ... other registration data
    }
}
```

## Design Decisions

1. **Multi-step Registration**

    - Breaks down the registration process into manageable steps
    - Allows users to save progress and resume later
    - Validates each step independently

2. **Two-Factor Authentication**

    - Implements 2FA via email for enhanced security
    - Generates time-sensitive codes
    - Verifies user's email ownership

3. **Secure Password Management**

    - Enforces strong password requirements
    - Uses bcrypt for password hashing
    - Implements password confirmation

4. **Token-based Authentication**

    - Uses Laravel Sanctum for API authentication
    - Provides secure token management
    - Implements token expiration

5. **Error Handling**
    - Provides clear error messages
    - Implements proper HTTP status codes
    - Includes validation feedback

## Security Features

1. **Rate Limiting**

    - Implements login attempt throttling
    - Protects against brute force attacks
    - Configurable attempt limits

2. **Input Validation**

    - Validates all user inputs
    - Sanitizes data before storage
    - Prevents SQL injection

3. **CSRF Protection**
    - Implements Laravel's CSRF protection
    - Secures form submissions
    - Protects against cross-site request forgery

## Environment Variables

Required environment variables:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

SANCTUM_STATEFUL_DOMAINS=localhost:8000
SESSION_DOMAIN=localhost
```

---
