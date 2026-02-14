<?php

namespace Juzaweb\Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use Juzaweb\Modules\Api\Http\DataTables\ApiKeysDataTable;
use Juzaweb\Modules\Core\Http\Controllers\AdminController;

class ApiKeyController extends AdminController
{
    public function index(ApiKeysDataTable $dataTable)
    {
        return $dataTable->render('api::index', [
            'title' => trans('api::app.api_keys'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token = $request->user()->createToken($request->input('name'));

        return $this->success(
            [
                'message' => trans('api::app.created_successfully'),
                'token' => $token->plainTextToken,
            ]
        );
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->tokens()->where('id', $id)->delete();

        return response()->json(
            [
                'status' => 'success',
                'message' => trans('api::app.deleted_successfully'),
            ]
        );
    }
}
