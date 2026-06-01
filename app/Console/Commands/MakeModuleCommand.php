<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

#[\Illuminate\Console\Attributes\Description('Create a new application module')]
#[\Illuminate\Console\Attributes\Signature('make:module {name : The name of the module (PascalCase)}')]
class MakeModuleCommand extends Command
{
    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $modulePath = app_path("Modules/{$name}");

        if (File::exists($modulePath)) {
            $this->error("Module [{$name}] already exists.");
            return self::FAILURE;
        }

        $this->createStructure($name, $modulePath);

        $this->components->info("Module [{$name}] created successfully.");
        $this->line("  <fg=gray>Location:</> app/Modules/{$name}");
        $this->line("  <fg=gray>Register your module by adding it to config/modules.php or enabling it via:</>");
        $this->line("  <fg=gray>  php artisan module enable {$name}</>");

        return self::SUCCESS;
    }

    private function createStructure(string $name, string $path): void
    {
        $dirs = [
            'Providers',
            'Http/Controllers',
            'Models',
            'Services',
            'resources/views',
            'resources/lang/en',
            'routes',
            'database/migrations',
            'database/seeders',
            'config',
            'tests',
        ];

        foreach ($dirs as $dir) {
            File::makeDirectory("{$path}/{$dir}", 0755, true);
        }

        $snake = Str::snake($name);

        File::put("{$path}/module.json", json_encode([
            'name' => $name,
            'version' => '1.0.0',
            'description' => "{$name} module",
            'dependencies' => [],
            'config' => [],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        File::put("{$path}/{$name}Module.php", $this->moduleClassStub($name));
        File::put("{$path}/Providers/{$name}ServiceProvider.php", $this->serviceProviderStub($name, $snake));
        File::put("{$path}/routes/web.php", "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n// Web routes for {$name} module\n");
        File::put("{$path}/routes/api.php", "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n// API routes for {$name} module\n");
        File::put("{$path}/config/{$snake}.php", "<?php\n\nreturn [\n    // {$name} module configuration\n];\n");
    }

    private function moduleClassStub(string $name): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$name};

use App\Modules\BaseModule;

class {$name}Module extends BaseModule
{
    protected function onEnable(): void {}

    protected function onDisable(): void {}

    protected function onInstall(): void {}

    protected function onUninstall(): void {}
}
PHP;
    }

    private function serviceProviderStub(string $name, string $snake): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$name}\\Providers;

use Illuminate\Support\ServiceProvider;

class {$name}ServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        \$this->loadViewsFrom(__DIR__.'/../resources/views', '{$snake}');
        \$this->loadTranslationsFrom(__DIR__.'/../resources/lang', '{$snake}');
        \$this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
PHP;
    }
}
