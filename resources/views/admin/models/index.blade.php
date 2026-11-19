@extends('admin.layout')

@section('title', 'Model Showroom')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Model Showroom</h1>
        <a href="{{ route('admin.models.create') }}" class="btn btn-purple">+ Add Model</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px">Order</th>
                        <th style="width:110px">Image</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-end" style="width:150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($models as $model)
                        <tr>
                            <td>{{ $model->display_order }}</td>
                            <td>
                                @if ($model->image_url)
                                    <img src="{{ $model->image_url }}" alt="{{ $model->title }}" class="thumb">
                                @endif
                            </td>
                            <td class="fw-medium">{{ $model->model_name }}</td>
                            <td>{{ $model->year }}</td>
                            <td>{{ $model->formatted_price }}</td>
                            <td>
                                @if ($model->status)
                                    <span class="badge text-bg-success">Active</span>
                                @else
                                    <span class="badge text-bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.models.edit', $model) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <button type="button" class="btn btn-sm btn-outline-danger js-delete"
                                        data-action="{{ route('admin.models.destroy', $model) }}"
                                        data-name="{{ $model->title }}">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No models yet. <a href="{{ route('admin.models.create') }}">Add the first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $models->links() }}</div>

    {{-- one shared delete confirmation --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content" id="deleteForm">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Delete model</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete <strong id="deleteName"></strong>? The image will be removed too.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        var modal = new bootstrap.Modal('#deleteModal');

        $('.js-delete').on('click', function () {
            $('#deleteForm').attr('action', $(this).data('action'));
            $('#deleteName').text($(this).data('name'));
            modal.show();
        });
    });
</script>
@endpush
