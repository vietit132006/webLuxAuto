@once
    @push('styles')
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite('resources/css/admin-car-models.css')
        @endif
    @endpush
@endonce