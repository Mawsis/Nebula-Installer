<?php

namespace App\Utils;

class ConfigGenerator
{
    public static function generateAuthConfig(): string
    {
        return <<<PHP
<?php

return [
    'userClass' => App\Models\User::class,
    'jwt_secret' => \$_ENV['JWT_SECRET'],
    'jwt_expiration' => 3600,
];
PHP;
    }

    public static function generateCorsConfig(): string
    {
        return <<<PHP
<?php

return [
    'allowed_origins' => explode(',', \$_ENV['CORS_ALLOW_ORIGIN'] ?? '*'),
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization'],
];
PHP;
    }

    public static function generateDatabaseConfig(): string
    {
        return <<<PHP
<?php

return [
    'dsn' => \$_ENV['DB_CONNECTION'] . ":host=" . \$_ENV['DB_HOST'] . ";dbname=" . \$_ENV['DB_NAME'],
    'user' => \$_ENV['DB_USER'],
    'password' => \$_ENV['DB_PASSWORD'],
];
PHP;
    }

    public static function generateMiddlewaresConfig(): string
    {
        return <<<PHP
<?php

return [
    'json' => Nebula\Core\Middlewares\JsonMiddleware::class,
    'auth' => Nebula\Core\Middlewares\AuthMiddleware::class,
    'csrf' => Nebula\Core\Middlewares\CsrfMiddleware::class,
    'jwt' => Nebula\Core\Middlewares\JwtMiddleware::class,
    'cors' => Nebula\Core\Middlewares\CorsMiddleware::class,
];
PHP;
    }

    public static function generateProvidersConfig(): string
    {
        return <<<PHP
<?php

return [
    App\Providers\AppServiceProvider::class,
];
PHP;
    }

    public static function generateValidationsConfig(): string
    {
        return <<<PHP
<?php

return [
    "required" => Nebula\Core\Validation\RequiredValidation::class,
    "min" => Nebula\Core\Validation\MinValidation::class,
    "max" => Nebula\Core\Validation\MaxValidation::class,
    "email" => Nebula\Core\Validation\EmailValidation::class,
    "unique" => Nebula\Core\Validation\UniqueValidation::class,
    "exists" => Nebula\Core\Validation\ExistsValidation::class,
];
PHP;
    }
}