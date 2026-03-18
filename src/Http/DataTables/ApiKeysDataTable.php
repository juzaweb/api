<?php

namespace Juzaweb\Modules\Api\Http\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Juzaweb\Modules\Api\Models\ApiKey;
use Juzaweb\Modules\Core\DataTables\Action;
use Juzaweb\Modules\Core\DataTables\Column;
use Juzaweb\Modules\Core\DataTables\DataTable;
use Yajra\DataTables\EloquentDataTable;

class ApiKeysDataTable extends DataTable
{
    protected string $actionUrl = 'api-keys/bulk';

    public function query(ApiKey $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('user_id', request()->user()->id)
            ->where('user_type', get_class(request()->user()));
    }

    public function getColumns(): array
    {
        return [
            Column::make('name')->title(trans('api::app.name')),
            Column::createdAt(),
            Column::make('last_used_at')->title(trans('api::app.last_used_at')),
            Column::actions(),
        ];
    }

    public function actions($row): array
    {
        return [
            Action::delete()->action('delete'),
        ];
    }

    public function renderColumns(EloquentDataTable $builder): EloquentDataTable
    {
        $builder->editColumn('last_used_at', function ($row) {
            return $row->last_used_at ? $row->last_used_at->format('Y-m-d H:i:s') : '';
        });

        return $builder;
    }
}
