@extends('core::layouts.admin')

@section('content')
    <div id="token-alert-container"></div>

    <div class="row">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#create-key-modal">
                <i class="fas fa-plus"></i> {{ trans('api::app.create_key') }}
            </button>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <x-card title="{{ trans('api::app.api_keys') }}">
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
                            <label for="name">{{ trans('api::app.name') }}</label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('api::app.close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ trans('api::app.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{ $dataTable->scripts(null, ['nonce' => csp_script_nonce()]) }}

    <script type="text/javascript" nonce="{{ csp_script_nonce() }}">
        $(document).on('click', '.copy-to-clipboard', function () {
            var copyText = document.getElementById('api-token');
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert("{{ trans('api::app.copied') }}");
        });

        function createKeySuccess(form, response) {
            $('#create-key-modal').modal('hide');
            $('#create-key-modal form')[0].reset();

            if (response.token) {
                var alertHtml = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <h4>{{ trans('api::app.token_created') }}</h4>
                        <p>{{ trans('api::app.token_created_description') }}</p>
                        <div class="input-group">
                            <input type="text" class="form-control" value="${response.token}" readonly id="api-token">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary copy-to-clipboard" type="button">
                                    {{ trans('api::app.copy') }}
                                </button>
                            </div>
                        </div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                $('#token-alert-container').html(alertHtml);
            }

            // Reload DataTable
            $('#jw-datatable').DataTable().ajax.reload();
        }
    </script>
@endsection
