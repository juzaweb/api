<?php

namespace Juzaweb\Modules\Api\Tests\Feature\API;

use Juzaweb\Modules\Api\Tests\TestCase;
use Juzaweb\Modules\Core\Models\Pages\Page;

class PageControllerTest extends TestCase
{
    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../../../vendor/juzaweb/core/database/migrations');
    }

    public function test_show()
    {
        $page = new Page;
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
