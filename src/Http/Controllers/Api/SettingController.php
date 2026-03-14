<?php

namespace Juzaweb\Modules\Api\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Juzaweb\Modules\Core\Facades\Setting;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json(Setting::all());
    }
}
