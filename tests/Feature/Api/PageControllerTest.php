<?php

namespace Juzaweb\Modules\Api\Tests\Feature\Api;

use Juzaweb\Modules\Core\Models\Pages\Page;
use Juzaweb\Modules\Api\Tests\TestCase;
use Illuminate\Support\Str;

class PageControllerTest extends TestCase
{
    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../../../vendor/juzaweb/core/database/migrations');
    }

    public function testShow()
    {
        $page = new Page();
        $page->fill([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'Test Content',
            'description' => 'Test Description',
        ]);
        $page->save();

        $response = $this->getJson("/api/v1/pages/{$page->slug}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $page->id);
        $response->assertJsonPath('data.title', $page->title);
        $response->assertJsonPath('data.slug', $page->slug);
    }
}
