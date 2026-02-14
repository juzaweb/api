@extends('core::layouts.admin')

@section('content')
    @if(session('token'))
        <div class="alert alert-success">
            <h4>{{ trans('api::app.token_created') }}</h4>
            <p>{{ trans('api::app.token_created_description') }}</p>
            <div class="input-group">
                <input type="text" class="form-control" value="{{ session('token') }}" readonly id="api-token">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">
                        {{ trans('api::app.copy') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <x-card title="{{ trans('api::app.api_keys') }}">
                <template #header-right>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#create-key-modal">
                        <i class="fas fa-plus"></i> {{ trans('api::app.create_key') }}
                    </button>
                </template>

                {{ $dataTable->table() }}
            </x-card>
        </div>
    </div>

    <div class="modal fade" id="create-key-modal" tabindex="-1" role="dialog" aria-labelledby="create-key-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="create-key-modal-label">{{ trans('api::app.create_key') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.api.keys.store') }}" method="post" class="form-ajax" data-success="createKeySuccess">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">{{ trans('cms::app.name') }}</label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('cms::app.close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ trans('cms::app.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{ $dataTable->scripts(null, ['nonce' => csp_script_nonce()]) }}

    <script type="text/javascript" nonce="{{ csp_script_nonce() }}">
        function copyToClipboard() {
            var copyText = document.getElementById("api-token");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert("{{ trans('api::app.copied') }}");
        }

        function createKeySuccess(response) {
            $('#create-key-modal').modal('hide');
            if (response.token) {
                location.reload();
            } else {
                $('#jw-datatable').DataTable().ajax.reload();
            }
        }
    </script>
@endsection
