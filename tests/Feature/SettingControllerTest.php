<?php

namespace Juzaweb\Modules\Api\Tests\Feature;

use Juzaweb\Modules\Api\Tests\TestCase;

class SettingControllerTest extends TestCase
{
    public function test_index_returns_settings()
    {
        $response = $this->getJson('api/v1/settings');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'title',
                'description',
                'sitename',
                'logo',
                'favicon',
                'banner',
                'language',
                'captcha',
                'captcha_site_key',
            ],
        ]);
    }
}
