<?php

namespace Juzaweb\Modules\Api\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Juzaweb\Modules\Core\Facades\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $keys = [
            'title',
            'description',
            'sitename',
            'logo',
            'favicon',
            'banner',
            'language',
        ];

        return response()->json(Setting::gets($keys));
    }
}
