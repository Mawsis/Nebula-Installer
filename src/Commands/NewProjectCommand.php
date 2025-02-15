<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Utils\ConfigGenerator;
use App\Utils\FileGenerator;

class NewProjectCommand extends Command
{
    private string $targetPath;
    public function __construct($targetPath)
    {
        $this->targetPath = $targetPath;
        parent::__construct('new'); // Command name
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new Nebula project')
            ->addArgument('name', InputArgument::OPTIONAL, 'The project name', 'nebula-app');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectName = $input->getArgument('name');
        $projectPath = $this->targetPath;


        $io->title("🚀 Creating a new Nebula project: $projectName");

        // ✅ Step 1: Ask for project type (MVC or API)
        $projectType = $io->choice(
            'Is this a full MVC application or an API?',
            ['Full App (MVC)', 'API Only'],
            'Full App (MVC)' // Default
        );

        // ✅ Step 2: Ask for Database Type
        $databaseType = $io->choice(
            'Which database will you use?',
            ['MySQL', 'PostgreSQL', 'SQLite', 'None'],
            'MySQL' // Default
        );

        // ✅ Step 3: Confirm and Start Installation
        $io->section("📦 Setting up your Nebula project...");
        if (is_dir($projectPath)) {
            $io->error("Directory '$projectName' already exists!");
            return Command::FAILURE;
        }

        mkdir($projectPath, 0777, true);
        $io->success("Created project directory: $projectPath");

        // ✅ Step 4: Generate Files and Folders
        $this->generateProjectFiles($projectPath, $projectType, $databaseType, $io);

        $io->success("Nebula project setup complete! 🚀");
        $io->writeln("  cd $projectName && php -S localhost:8000 -t public");

        return Command::SUCCESS;
    }

    private function generateProjectFiles(string $path, string $projectType, string $databaseType, SymfonyStyle $io)
    {
        // Create directories
        $directories = [
            'config',
            'Controllers',
            'logs',
            'migrations',
            'Models',
            'Providers',
            'public',
            'routes',
        ];

        if ($projectType === 'Full App (MVC)') {
            $directories = array_merge($directories, [
                'Form',
                'Form/Data',
                'Views',
            ]);
        } else { // API Only
            $directories[] = 'Resources';
        }

        foreach ($directories as $dir) {
            mkdir("$path/$dir", 0777, true);
            $io->writeln("📂 Created: $dir/");
        }

        // Generate config files
        file_put_contents("$path/config/auth.php", ConfigGenerator::generateAuthConfig());
        file_put_contents("$path/config/cors.php", ConfigGenerator::generateCorsConfig());
        file_put_contents("$path/config/database.php", ConfigGenerator::generateDatabaseConfig());
        file_put_contents("$path/config/middlewares.php", ConfigGenerator::generateMiddlewaresConfig());
        file_put_contents("$path/config/providers.php", ConfigGenerator::generateProvidersConfig());
        file_put_contents("$path/config/validations.php", ConfigGenerator::generateValidationsConfig());

        // Generate core files
        file_put_contents("$path/Controllers/Controller.php", FileGenerator::generateController());
        file_put_contents("$path/migrations/m0001_initial.php", FileGenerator::generateMigration());
        file_put_contents("$path/Models/User.php", FileGenerator::generateUserModel());
        file_put_contents("$path/Providers/AppServiceProvider.php", FileGenerator::generateAppServiceProvider());
        if ($projectType === 'API Only') {
            file_put_contents("$path/Resources/UserResource.php", FileGenerator::generateUserResource());
            $io->writeln("📝 Created: Resources/UserResource.php");
        }
        if (
            $projectType === 'Full App (MVC)'
        ) {
            file_put_contents("$path/routes/web.php", FileGenerator::generateWebRoutes());
            $io->writeln("📝 Created: routes/web.php");
        } else {
            file_put_contents("$path/routes/api.php", FileGenerator::generateApiRoutes());
            $io->writeln("📝 Created: routes/api.php");
        }
        // Generate index.php
        file_put_contents("$path/public/index.php", FileGenerator::generateIndexFile());
        $io->writeln("📝 Created all project files.");
        // Generate composer.json
        file_put_contents("$path/composer.json", FileGenerator::generateComposerJson());
        $io->writeln("📝 Created: composer.json");
        // ✅ Generate `.env`, `.env.example`, and `migrations.php`
        file_put_contents("$path/.env", FileGenerator::generateEnvFile());
        file_put_contents("$path/.env.example", FileGenerator::generateEnvFile());
        file_put_contents("$path/migrations.php", FileGenerator::generateMigrationsFile());
        $io->writeln("📝 Created: .env, .env.example, migrations.php");

        // ✅ Install Dependencies
        $io->section("📦 Installing dependencies...");
        shell_exec("cd $path && composer install");
        $io->writeln("✅ Dependencies installed successfully.");

        $io->writeln("📝 Created all project files.");
    }
}