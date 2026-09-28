<div class="card-body">
    <h7 class="card-title" style="color:rgb(100, 121, 216); font-weight: 800;">UPLOAD LOGO</h7>
    <hr>

    {{-- Preview --}}
    <div class="text-center mb-4">
        @if(Session::get('logo'))
            <img src="{{ asset('storage/logo/' . Session::get('logo')) }}?v={{ time() }}"
                alt="Logo" id="previewLogo"
                style="max-width: 240px; max-height: 150px; object-fit: contain; border: 1px solid #ddd; padding: 12px; border-radius: 6px; background: #fff;">
        @else
            <div id="previewLogo"
                style="height: 150px; display: flex; align-items: center; justify-content: center; border: 1px dashed #ccc; border-radius: 6px; color: #999; background: #fafafa;">
                Belum ada logo
            </div>
        @endif
    </div>

    {{-- Status --}}
    <div id="logoStatus" class="text-center mb-2" style="display: none;">
        <small class="text-info"><i class="fa fa-spinner fa-spin"></i> Mengupload...</small>
    </div>

    {{-- Tombol pilih file --}}
    <div class="text-center">
        <input type="file" id="logoInput" accept="image/png,image/jpg,image/jpeg" style="display: none;">

        {{-- Tombol trigger file picker - inline onclick, no jQuery needed --}}
        <button type="button" class="btn btn-info" onclick="document.getElementById('logoInput').click();">
            <i class="fa fa-edit"></i>&nbsp;Edit Logo
        </button>
    </div>
</div>

<script>
    // Handler file input: pilih file -> preview -> langsung upload otomatis.
    // Pakai vanilla JS untuk keandalan maksimal (no jQuery dependency).
    (function () {
        var input = document.getElementById('logoInput');
        var preview = document.getElementById('previewLogo');
        var status = document.getElementById('logoStatus');
        var originalSrc = preview && preview.tagName === 'IMG' ? preview.src : '';

        if (!input) {
            console.error('[logo] #logoInput tidak ditemukan');
            return;
        }

        input.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file) return;

            // Validasi client
            var allowed = ['image/png', 'image/jpg', 'image/jpeg'];
            if (allowed.indexOf(file.type) === -1) {
                alert('File harus berformat PNG / JPG / JPEG.');
                input.value = '';
                return;
            }
            if (file.size > 4 * 1024 * 1024) {
                alert('Ukuran file maksimal 4MB.');
                input.value = '';
                return;
            }

            // Preview cepat
            var reader = new FileReader();
            reader.onload = function (e) {
                if (preview && preview.tagName === 'IMG') {
                    preview.src = e.target.result;
                } else {
                    var img = document.createElement('img');
                    img.id = 'previewLogo';
                    img.src = e.target.result;
                    img.alt = 'Logo';
                    img.style.maxWidth = '240px';
                    img.style.maxHeight = '150px';
                    img.style.objectFit = 'contain';
                    img.style.border = '1px solid #ddd';
                    img.style.padding = '12px';
                    img.style.borderRadius = '6px';
                    img.style.background = '#fff';
                    preview.parentNode.replaceChild(img, preview);
                    preview = img;
                }
            };
            reader.readAsDataURL(file);

            // Upload ke server
            uploadFile(file);
        });

        function uploadFile(file) {
            var form = document.getElementById('FormLogo');
            var actionUrl = form ? form.action : '/pengaturan/sop/logo/{{ $business->id }}';

            // Cari CSRF token dari meta tag (Laravel standard)
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfInput = document.querySelector('input[name="_token"]');
            var csrf = csrfMeta ? csrfMeta.getAttribute('content')
                : (csrfInput ? csrfInput.value : '');

            var fd = new FormData();
            fd.append('logo_busines', file);
            fd.append('_token', csrf);
            fd.append('_method', 'PUT');

            status.style.display = 'block';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', actionUrl, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            if (csrf) xhr.setRequestHeader('X-CSRF-TOKEN', csrf);

            xhr.onload = function () {
                status.style.display = 'none';
                input.value = '';

                var resp;
                try { resp = JSON.parse(xhr.responseText); }
                catch (e) { resp = { success: false, msg: 'Response tidak valid' }; }

                if (xhr.status >= 200 && xhr.status < 400 && resp.success) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: resp.msg || 'Logo berhasil diperbarui.',
                            timer: 1200,
                            showConfirmButton: false
                        });
                    }
                    setTimeout(function () { window.location.reload(); }, 1200);
                } else {
                    // Rollback preview
                    if (originalSrc && preview) preview.src = originalSrc;
                    var msg = resp.msg || ('Upload gagal (HTTP ' + xhr.status + ')');
                    if (window.Swal) Swal.fire('Gagal!', msg, 'error');
                    else alert(msg);
                }
            };

            xhr.onerror = function () {
                status.style.display = 'none';
                input.value = '';
                if (originalSrc && preview) preview.src = originalSrc;
                alert('Terjadi kesalahan jaringan saat upload.');
            };

            xhr.send(fd);
        }
    })();
</script>

{{-- Form hidden untuk action URL & CSRF (selector #FormLogo) --}}
<form id="FormLogo" action="/pengaturan/sop/logo/{{ $business->id }}" method="POST"
    enctype="multipart/form-data" style="display: none;">
    @csrf
    @method('PUT')
</form>
