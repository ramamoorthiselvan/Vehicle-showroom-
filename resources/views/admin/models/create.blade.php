@extends('admin.layout')

@section('title', 'Add Model')

@section('content')
    <h1 class="h4 mb-3">Add Model</h1>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            @include('admin.models._form', [
                'action' => route('admin.models.store'),
                'method' => 'POST',
                'submitLabel' => 'Save Model',
            ])
        </div>
    </div>
@endsection
