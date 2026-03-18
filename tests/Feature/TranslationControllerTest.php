<?php

namespace Juzaweb\Modules\Api\Tests\Feature;

use Juzaweb\Modules\Api\Tests\TestCase;

class TranslationControllerTest extends TestCase
{
    public function test_index_returns_translations_in_i18n_format(): void
    {
        $response = $this->getJson('api/v1/translations/en');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
        $response->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertIsArray($data);

        // Each key must be a non-empty string (i18n key: namespace::group.key or group.key)
        foreach (array_keys($data) as $key) {
            $this->assertIsString($key);
            $this->assertNotEmpty($key);
        }
    }

    public function test_index_returns_empty_data_for_unknown_locale(): void
    {
        $response = $this->getJson('api/v1/translations/xx');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
