{{-- shared by create + edit. Expects $showroom, $action, $method, $submitLabel --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" novalidate>
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label for="model_name" class="form-label">Model name <span class="text-danger">*</span></label>
            <input type="text" id="model_name" name="model_name" maxlength="100"
                   value="{{ old('model_name', $showroom->model_name) }}"
                   class="form-control @error('model_name') is-invalid @enderror" placeholder="e.g. Q50">
            @error('model_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3 col-6">
            <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
            <input type="number" id="year" name="year" min="1990" max="{{ date('Y') + 2 }}"
                   value="{{ old('year', $showroom->year) }}"
                   class="form-control @error('year') is-invalid @enderror" placeholder="{{ date('Y') }}">
            @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3 col-6">
            <label for="price" class="form-label">Starting price ($) <span class="text-danger">*</span></label>
            <input type="number" id="price" name="price" min="0" step="0.01"
                   value="{{ old('price', $showroom->price) }}"
                   class="form-control @error('price') is-invalid @enderror" placeholder="37500">
            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3 col-6">
            <label for="display_order" class="form-label">Display order</label>
            <input type="number" id="display_order" name="display_order" min="0"
                   value="{{ old('display_order', $showroom->display_order) }}"
                   class="form-control @error('display_order') is-invalid @enderror">
            <div class="form-text">Lowest number shows first.</div>
            @error('display_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3 col-6">
            <label class="form-label d-block">Status</label>
            {{-- hidden 0 makes sure an unticked box is still submitted --}}
            <input type="hidden" name="status" value="0">
            <div class="form-check form-switch fs-5">
                <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1"
                       @checked(old('status', $showroom->status))>
                <label class="form-check-label fs-6" for="status">Active (show on website)</label>
            </div>
            @error('status') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="image" class="form-label">
                Vehicle image @unless ($showroom->exists) <span class="text-danger">*</span> @endunless
            </label>
            <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp"
                   class="form-control @error('image') is-invalid @enderror">
            <div class="form-text">JPG, PNG or WEBP, max 2 MB. A transparent PNG about 225 &times; 110 px looks best.</div>
            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label d-block">Preview</label>
            <div class="preview-box">
                <img id="imagePreview" alt="Preview"
                     src="{{ $showroom->image_url }}"
                     @unless ($showroom->image_url) hidden @endunless>
                <span id="previewEmpty" @if ($showroom->image_url) hidden @endif class="text-muted small">No image selected</span>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-purple">{{ $submitLabel }}</button>
        <a href="{{ route('admin.models.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>

@push('scripts')
<script>
    // live preview of the chosen file
    $('#image').on('change', function () {
        var file = this.files && this.files[0];
        if (!file) return;

        var reader = new FileReader();
        reader.onload = function (e) {
            $('#imagePreview').attr('src', e.target.result).prop('hidden', false);
            $('#previewEmpty').prop('hidden', true);
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush
