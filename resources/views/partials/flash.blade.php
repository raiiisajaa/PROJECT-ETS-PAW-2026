@if (session('success'))
    <div class="alert alert-success" data-auto-hide>✅ {{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-error" data-auto-hide>⚠️ {{ session('error') }}</div>
@endif

@if (session('status'))
    <div class="alert alert-info">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-error">
        <strong>Periksa kembali isian Anda:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
