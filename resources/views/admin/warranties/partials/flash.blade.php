@if(session('success'))
    <div class="after-alert">
        <span>{{ session('success') }}</span>
        <button type="button" class="after-close" onclick="this.parentElement.remove()" aria-label="Đóng">&times;</button>
    </div>
@endif

@if($errors->any())
    <div class="after-error">
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        <button type="button" class="after-close" onclick="this.parentElement.remove()" aria-label="Đóng">&times;</button>
    </div>
@endif
