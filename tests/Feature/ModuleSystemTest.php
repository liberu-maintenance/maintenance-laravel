<?php

namespace Tests\Feature;

use App\Modules\ModuleManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleSystemTest extends TestCase
{
    use RefreshDatabase;

    protected ModuleManager $moduleManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->moduleManager = app(ModuleManager::class);
    }

    /** @test */
    public function it_can_list_all_modules()
    {
        $modules = $this->moduleManager->all();
        $this->assertNotNull($modules);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $modules);
    }

    /** @test */
    public function it_returns_null_for_non_existent_module()
    {
        $module = $this->moduleManager->get('NonExistentModule');
        $this->assertNull($module);
    }

    /** @test */
    public function it_returns_false_for_non_existent_modules()
    {
        $result = $this->moduleManager->enable('NonExistentModule');
        $this->assertFalse($result);

        $result = $this->moduleManager->disable('NonExistentModule');
        $this->assertFalse($result);

        $module = $this->moduleManager->get('NonExistentModule');
        $this->assertNull($module);
    }

    /** @test */
    public function it_returns_false_for_installing_non_existent_module()
    {
        $result = $this->moduleManager->install('NonExistentModule');
        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_false_for_uninstalling_non_existent_module()
    {
        $result = $this->moduleManager->uninstall('NonExistentModule');
        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_empty_array_for_non_existent_module_info()
    {
        $info = $this->moduleManager->getModuleInfo('NonExistentModule');
        $this->assertIsArray($info);
        $this->assertEmpty($info);
    }

    /** @test */
    public function it_can_check_if_module_exists()
    {
        $this->assertFalse($this->moduleManager->has('NonExistentModule'));
    }

    /** @test */
    public function enabled_modules_returns_collection()
    {
        $enabled = $this->moduleManager->enabled();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $enabled);
    }

    /** @test */
    public function disabled_modules_returns_collection()
    {
        $disabled = $this->moduleManager->disabled();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $disabled);
    }
}
