<?php

namespace Juzaweb\Modules\Api\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Juzaweb\Modules\Api\Http\DataTables\ApiKeysDataTable;
use Juzaweb\Modules\Api\Models\ApiKey;
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

        $model = new ApiKey;
        $model->fill($request->only(['name']));
        $model->user_id = $request->user()->id;
        $model->user_type = get_class($request->user());
        $model->save();

        return $this->success(
            [
                'message' => trans('api::app.created_successfully'),
                'token' => $model->plain_text_key,
            ]
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $model = ApiKey::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->where('user_type', get_class($request->user()))
            ->firstOrFail();

        $model->delete();

        return response()->json(
            [
                'status' => 'success',
                'message' => trans('api::app.deleted_successfully'),
            ]
        );
    }
}
