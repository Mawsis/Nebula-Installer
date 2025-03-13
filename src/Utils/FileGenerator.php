<?php

namespace App\Utils;

class FileGenerator
{
    public static function generateController(): string
    {
        return <<<PHP
<?php

namespace App\Controllers;

class Controller
{
    //
}
PHP;
    }

    public static function generateMigration(): string
    {
        return <<<PHP
<?php

use Nebula\Core\Application;
use Nebula\Core\Facades\DB;
use Nebula\Core\Migration;

class m0001_initial extends Migration
{
    public function up()
    {
        \$SQL = "CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR (255) NOT NULL,
                username VARCHAR (255) NOT NULL,
                password VARCHAR (255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE = INNODB;";
        DB::execute(\$SQL);
    }

    public function down()
    {
        \$SQL = "DROP TABLE users;";
        DB::execute(\$SQL);
    }
}
PHP;
    }

    public static function generateUserModel(): string
    {
        return <<<PHP
<?php

namespace App\Models;

use Nebula\Core\UserModel;

class User extends UserModel
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    public string \$username = '';
    public string \$email = '';
    public string \$password = '';
    public string \$confirmPassword = '';
    public int \$status = self::STATUS_INACTIVE;
    public ?int \$id;
    public string \$created_at;
    public array \$posts;

    public function save()
    {
        \$this->password = password_hash(\$this->password, PASSWORD_DEFAULT);
        \$this->status = self::STATUS_INACTIVE;
        return parent::save();
    }

    public static function tableName(): string
    {
        return 'users';
    }

    public static function attributes(): array
    {
        return ['username', 'email', 'password', 'status'];
    }

    public static function primaryKey(): string
    {
        return "id";
    }

    public function getDisplayName(): string
    {
        return \$this->username;
    }

    public function labels(): array
    {
        return [
            'confirmPassword' => "Password Confirmation",
            'email' => 'Email'
        ];
    }
}
PHP;
    }

    public static function generateAppServiceProvider(): string
    {
        return <<<PHP
<?php

namespace App\Providers;

use Nebula\Core\Auth;
use Nebula\Core\Container;
use Nebula\Core\Database;
use Nebula\Core\Handler;
use Nebula\Core\Logger;
use Nebula\Core\Router;
use Nebula\Core\ServiceProvider;
use Nebula\Core\Session;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        Container::singleton('db', function () {
            return new Database();
        });

        Container::bind('auth', function () {
            return new Auth();
        });

        Container::singleton('logger', function () {
            return Logger::getLogger();
        });

        Container::singleton('session', function () {
            return new Session();
        });

        Container::singleton('handler', function () {
            return new Handler();
        });

        Container::singleton('route', function () {
            return new Router();
        });
    }
}
PHP;
    }



    public static function generateIndexFile(): string
    {
        return <<<PHP
<?php

use Nebula\Core\Application;
use Nebula\Core\Config;
use Dotenv\Dotenv;

// Start output buffering to prevent "headers already sent" issues
ob_start();

require_once __DIR__ . "/../vendor/autoload.php";

// Register shutdown function for fatal errors
register_shutdown_function(function () {
    \$error = error_get_last();

    if (\$error && in_array(\$error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);

        // Clear any previous output
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Pass error details to the fatal view
        global \$caughtError;
        \$caughtError = \$error;
        require_once __DIR__ . "/../views/errors/fatal.php";
        exit;
    }
});

// Handle Uncaught Exceptions
set_exception_handler(function (Throwable \$exception) {
    http_response_code(500);
    error_log(\$exception); // Log the error for debugging

    // Clear any previous output
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Pass exception details to the fatal view
    global \$caughtError;
    \$caughtError = [
        'message' => \$exception->getMessage(),
        'file' => \$exception->getFile(),
        'line' => \$exception->getLine(),
        'trace' => \$exception->getTraceAsString()
    ];
    require_once __DIR__ . "/../views/errors/fatal.php";
    exit;
});

\$dotenv = Dotenv::createImmutable(dirname(__DIR__), null, false);
\$dotenv->safeLoad();

Config::load(dirname(__DIR__) . '/config');

\$app = new Application(dirname(__DIR__));

require_once __DIR__ . "/../routes/api.php";

\$app->run();
PHP;
    }
    public static function generateUserResource(): string
    {
        return <<<PHP
<?php

namespace App\Resources;

use Nebula\Core\BaseResource;

class UserResource extends BaseResource
{
    public function toArray(): array
    {
        return [
            'id' => \$this->resource->id,
            'username' => \$this->resource->username,
            'email' => \$this->resource->email,
        ];
    }
}
PHP;
    }
    public static function generateWebRoutes(): string
    {
        return <<<PHP
<?php

use Nebula\Core\Facades\Route;
use Nebula\Core\Response;

Route::get('/', function (Response \$response) {
    return \$response->render('home', ['message' => 'Hello, World!']);
});
PHP;
    }


    public static function generateApiRoutes(): string
    {
        return <<<PHP
<?php

use Nebula\Core\Facades\Route;
use Nebula\Core\Response;

Route::get('/', function (Response \$response) {
    return \$response->json(['message' => 'Hello, World!']);
});
PHP;
    }
    public static function generateComposerJson(): string
    {
        return <<<JSON
{
    "name": "app/nebula-project",
    "description": "A Nebula PHP Project",
    "type": "project",
    "autoload": {
        "psr-4": {
            "App\\\\": "./"
        }
    },
    "require": {
        "php": ">=8.0",
        "mawsis/nebula-php": "dev-main",
        "vlucas/phpdotenv": "^5.6"
    },
    "scripts": {
        "post-install-cmd": [
            "@php migrations.php migrate"
        ]
    }
}
JSON;
    }

    public static function generateMigrationsFile(): string
    {
        return <<<PHP
<?php

use Nebula\Core\Application;
use Nebula\Core\Config;
use Nebula\Core\Container;
use Nebula\Core\Database;
use Nebula\Core\Facades\DB;
use Dotenv\Dotenv;

require_once __DIR__ . "/vendor/autoload.php";

\$dotenv = Dotenv::createImmutable(__DIR__);
\$dotenv->load();

Config::load(__DIR__ . "/config");

\$app = new Application(rootPath: __DIR__);
Container::singleton('db', function () {
    return new Database();
});

\$command = \$argv[1] ?? null;

switch (\$command) {
    case 'migrate':
        DB::applyMigrations();
        break;
    case 'rollback':
        DB::rollbackMigrations();
        break;
    default:
        echo "Available commands: \\n";
        echo "  php migrations.php migrate    # Apply migrations\\n";
        echo "  php migrations.php rollback   # Rollback last batch\\n";
        break;
}
PHP;
    }

    public static function generateEnvFile(): string
    {
        return <<<ENV
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:random_generated_key_here

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nebula
DB_USERNAME=root
DB_PASSWORD=
ENV;
    }
}
