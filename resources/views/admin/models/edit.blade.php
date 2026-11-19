@extends('admin.layout')

@section('title', 'Edit Model')

@section('content')
    <h1 class="h4 mb-3">Edit Model &ndash; {{ $showroom->title }}</h1>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            @include('admin.models._form', [
                'action' => route('admin.models.update', $showroom),
                'method' => 'PUT',
                'submitLabel' => 'Update Model',
            ])
        </div>
    </div>
@endsection
