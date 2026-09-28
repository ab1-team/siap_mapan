<div class="card-body">
    <h7 class="card-title" style="color:rgb(100, 121, 216); font-weight: 800;">UPLOAD LOGO</h7>
    <hr>

    <form action="/pengaturan/sop/logo/{{ $business->id }}" method="post"
        enctype="multipart/form-data" id="FormLogo">
        @csrf
        @method('PUT')
        <input type="file" name="logo_busines" id="logo_busines" class="d-none"
            accept="image/png,image/jpg,image/jpeg">

        {{-- Preview logo --}}
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="border rounded p-3 bg-light" style="min-height: 180px;">
                    @if(Session::get('logo'))
                        <img src="{{ asset('storage/logo/' . Session::get('logo')) }}" alt="Logo"
                            id="previewLogo"
                            style="max-width: 100%; max-height: 150px; object-fit: contain;">
                    @else
                        <div id="previewLogo"
                            class="d-flex align-items-center justify-content-center text-muted"
                            style="height: 150px;">
                            <span>Belum ada logo</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Info file yang dipilih (sebelum simpan) --}}
        <div id="fileInfo" class="mt-3 text-center" style="display: none;">
            <small class="text-muted">
                File dipilih: <strong id="fileName"></strong>
                (<span id="fileSize"></span>)
            </small>
        </div>

        {{-- Baris tombol --}}
        <div class="row mt-4">
            <div class="col-md-12 d-flex justify-content-end align-items-center">
                {{-- State 1: belum pilih file -> tampilkan "Edit Logo" saja.
                    Pakai <label for="logo_busines"> yg terikat ke <input type=file>:
                    cara PALING reliable untuk buka native file picker di semua
                    browser (tidak butuh JS). --}}
                <label for="logo_busines" id="EditLogo" class="btn btn-info mb-0"
                    style="cursor: pointer; user-select: none;">
                    <i class="fa fa-edit"></i>&nbsp;Edit Logo
                </label>

                {{-- State 2: sudah pilih file -> tampilkan Simpan & Batal --}}
                <div id="logoActions" style="display: none;">
                    <button type="button" id="BtnSimpanLogo" class="btn btn-dark">
                        <i class="fa fa-save"></i>&nbsp;Simpan
                    </button>
                    <button type="button" id="BtnBatalLogo" class="btn btn-secondary"
                        style="margin-left: 8px;">
                        <i class="fa fa-times"></i>&nbsp;Batal
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Jalankan setelah DOM & jQuery siap (partial ini bisa di-render
    // sebelum jQuery dimuat, sehingga perlu ready handler).
    $(function () {
        var $fileInput = $('#logo_busines');
        var $preview = $('#previewLogo');
        var $fileInfo = $('#fileInfo');
        var $fileName = $('#fileName');
        var $fileSize = $('#fileSize');
        var $btnEdit = $('#EditLogo');
        var $actions = $('#logoActions');
        var $btnSimpan = $('#BtnSimpanLogo');
        var $btnBatal = $('#BtnBatalLogo');

        // Snapshot preview awal (untuk rollback saat Batal)
        var originalIsImg = $preview.is('img');
        var originalSrc = originalIsImg ? $preview.attr('src') : '';

        function showNoLogo() {
            var html = '<div id="previewLogo" class="d-flex align-items-center justify-content-center text-muted" style="height: 150px;"><span>Belum ada logo</span></div>';
            $preview.replaceWith(html);
            $preview = $('#previewLogo');
        }

        function showImg(src) {
            var html = '<img src="' + src + '" alt="Logo" id="previewLogo" style="max-width: 100%; max-height: 150px; object-fit: contain;">';
            $preview.replaceWith(html);
            $preview = $('#previewLogo');
        }

        // ---- Klik "Edit Logo" ditangani oleh <label for="logo_busines"> ----
        // Tidak butuh handler click di sini, native HTML sudah cukup reliable.

        // ---- File dipilih -> preview, jangan upload dulu ----
        $fileInput.off('change').on('change', function () {
            var file = this.files && this.files[0];
            if (!file) return;

            // Validasi client (server tetap validasi)
            var allowed = ['image/png', 'image/jpg', 'image/jpeg'];
            if (allowed.indexOf(file.type) === -1) {
                if (window.Swal) Swal.fire('Gagal!', 'File harus berformat PNG / JPG / JPEG.', 'error');
                else alert('File harus berformat PNG / JPG / JPEG.');
                $fileInput.val('');
                return;
            }
            if (file.size > 4 * 1024 * 1024) {
                if (window.Swal) Swal.fire('Gagal!', 'Ukuran file maksimal 4MB.', 'error');
                else alert('Ukuran file maksimal 4MB.');
                $fileInput.val('');
                return;
            }

            // Preview
            var reader = new FileReader();
            reader.onload = function (ev) {
                showImg(ev.target.result);
            };
            reader.readAsDataURL(file);

            // Tampilkan info & tombol
            $fileName.text(file.name);
            $fileSize.text((file.size / 1024).toFixed(1) + ' KB');
            $fileInfo.show();
            $btnEdit.hide();
            $actions.show();
        });

        // ---- Klik Batal -> kembalikan ke kondisi awal ----
        $btnBatal.off('click').on('click', function () {
            $fileInput.val('');
            $fileInfo.hide();
            $actions.hide();
            $btnEdit.show();

            if (originalIsImg && originalSrc) {
                showImg(originalSrc);
            } else {
                showNoLogo();
            }
        });

        // ---- Klik Simpan -> upload ke server ----
        $btnSimpan.off('click').on('click', function () {
            if (!$fileInput.get(0).files.length) {
                if (window.Swal) Swal.fire('Oops!', 'Pilih file logo terlebih dahulu.', 'warning');
                else alert('Pilih file logo terlebih dahulu.');
                return;
            }

            var formData = new FormData($('#FormLogo')[0]);
            formData.append('_method', 'PUT');
            $btnSimpan.prop('disabled', true);
            $btnBatal.prop('disabled', true);

            $.ajax({
                url: $('#FormLogo').attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $btnSimpan.prop('disabled', false);
                    $btnBatal.prop('disabled', false);

                    if (response && response.success) {
                        if (window.Swal) Swal.fire('Berhasil!', response.msg || 'Logo berhasil diperbarui.', 'success');

                        // Refresh preview dengan path baru dari server (cache-busting)
                        var file = $fileInput.get(0).files[0];
                        var reader = new FileReader();
                        reader.onload = function (ev) {
                            var newSrc = ev.target.result;
                            showImg(newSrc);
                            // Snapshot ulang untuk Batal berikutnya
                            originalIsImg = true;
                            originalSrc = newSrc;
                        };
                        reader.readAsDataURL(file);

                        $fileInput.val('');
                        $fileInfo.hide();
                        $actions.hide();
                        $btnEdit.show();
                    } else {
                        var msg = (response && response.msg) ? response.msg : 'Logo gagal diperbarui';
                        if (window.Swal) Swal.fire('Gagal!', msg, 'error');
                        else alert(msg);
                    }
                },
                error: function () {
                    $btnSimpan.prop('disabled', false);
                    $btnBatal.prop('disabled', false);
                    if (window.Swal) Swal.fire('Oops!', 'Terjadi kesalahan saat upload.', 'error');
                    else alert('Terjadi kesalahan saat upload.');
                }
            });
        });
    });
</script>
