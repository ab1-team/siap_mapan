{{--
    BUG #3 FIX: Typeahead scripts dipindahkan dari layouts/base.blade.php
    agar tidak dieksekusi di SEMUA halaman. Sebelumnya 3 script typeahead
    dipasang di base.blade.php yang menyebabkan error JS saat element target
    (#PelunasanInstalasi, #TagihanBulanan, #carianggota) tidak ada di halaman,
    yang bisa menghentikan eksekusi script lain termasuk sidebar-toggle.js.

    Halaman yang membutuhkan include partial ini:
    - transaksi/pelunasan_instalasi.blade.php  -> butuh #PelunasanInstalasi
    - transaksi/tagihan_bulanan.blade.php      -> butuh #TagihanBulanan
                                                   + fungsi formTagihanBulanan()
                                                   + var global numFormat
--}}

{{-- Helper global numFormat (juga dipakai di transaksi.partials.installations
     yang di-render via Ajax dari controller, jadi tetap harus tersedia
     secara global saat halaman yang butuh ada). --}}
<script>
    var numFormat = new Intl.NumberFormat('en-EN', {
        minimumFractionDigits: 2
    });
</script>

<script>
    // ============================================================
    // 1) Typeahead PELUNASAN INSTALASI
    //    Target: #PelunasanInstalasi (halaman transaksi/pelunasan_instalasi)
    // ============================================================
    if (document.getElementById('PelunasanInstalasi')) {
        $('#PelunasanInstalasi').typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        }, {
            name: 'states',
            source: function(query, process) {
                if (query.length < 2) return;

                $.ajax({
                    url: '/installations/CariPelunasan_Instalasi',
                    method: 'GET',
                    data: {
                        query: query
                    },
                    dataType: 'json',
                    success: function(result) {
                        var states = [];
                        result.map(function(item) {
                            if (item.installation.length > 0) {
                                item.installation.map(function(instal) {
                                    states.push({
                                        id: instal.id,
                                        installation: instal,
                                        name: item.nama +
                                            ' - ' + instal.village.nama +
                                            ' - ' + instal.kode_instalasi +
                                            ' [' + item.nik + ']',
                                        value: instal.id
                                    })
                                })
                            }
                        });

                        process(states);
                    },
                    error: function(xhr, status, error) {
                        console.error("Terjadi kesalahan saat pemanggilan custommers:", error);
                        process([]);
                    }
                });
            },

            displayKey: 'name',
            autoSelect: true,
            fitToElement: true,
            items: 10

        }).bind('typeahead:selected', function(event, item) {
            var installation = item.installation
            var trx = installation.transaction

            var sum_total = 0;
            var rekening_debit = 0;
            var rekening_kredit = 0;

            trx.map(function(item) {
                rekening_debit = item.rekening_debit;
                rekening_kredit = item.rekening_kredit;
                sum_total += item.total;
            })

            var rek_debit = rekening_debit;
            var rek_kredit = rekening_kredit;
            var tagihan = (installation.biaya_instalasi);
            console.log(sum_total);

            $("#installation").val(installation.id);
            $("#order").val(installation.order);
            $("#kode_instalasi").val(installation.kode_instalasi);
            $("#alamat").val(installation.village.nama);
            $("#package").val(installation.package.kelas);
            $("#abodemen").val(numFormat.format(installation.abodemen));
            $("#biaya_sudah_dibayar").val(numFormat.format(sum_total));
            $("#tagihan").val(numFormat.format(tagihan));
            $("#pembayaran").val(numFormat.format(tagihan));
            $("#_total").val(numFormat.format(sum_total));
            $("#rek_debit").val(rek_debit);
            $("#rek_kredit").val(rek_kredit);

        });
    }
</script>

<script>
    // ============================================================
    // 2) Typeahead TAGIHAN BULANAN + formTagihanBulanan()
    //    Target: #TagihanBulanan (halaman transaksi/tagihan_bulanan)
    // ============================================================
    var dataCustomer; // state bersama untuk halaman tagihan_bulanan

    function formTagihanBulanan(installation) {
        $.get('/installations/usage/' + installation.kode_instalasi, (result) => {
            if (result.success) {
                $('#accordion').html(result.view)
            } else {
                $('#accordion').html(result.view)
            }

            dataCustomer = {
                item: installation,
                rek_debit: result.rek_debit,
                rek_kredit: result.rek_kredit,
            }
        })
    }

    if (document.getElementById('TagihanBulanan')) {
        $('#TagihanBulanan').typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        }, {
            name: 'states',
            source: function(query, process) {
                if (query.length < 2) return;

                $.ajax({
                    url: '/installations/CariTagihan_bulanan',
                    method: 'GET',
                    data: {
                        query: query
                    },
                    dataType: 'json',
                    success: function(result) {
                        var states = [];
                        result.map(function(item) {
                            let inisial = item.package_inisial ? '-' + item.package_inisial : '';
                            let kategori = item.kategori == 1 ? 'Air' : 'Sampah';
                            states.push({
                                kode_instalasi: item.kode_instalasi,
                                name: item.nama + ' - ' + item.kode_instalasi +
                                    inisial + ' [' + item.nik + '] ' + '- ( ' + kategori + ' )',
                                value: item.kode_instalasi,
                                item: item,
                            });
                        });
                        process(states);
                    },
                    error: function(xhr, status, error) {
                        console.error("Terjadi kesalahan saat pemanggilan custommers:", error);
                        process([]);
                    }
                });
            },

            displayKey: 'name',
            autoSelect: true,
            fitToElement: true,
            items: 10

        }).bind('typeahead:selected', function(event, item) {
            formTagihanBulanan(item.item);
        });
    }
</script>

<script>
    // ============================================================
    // 3) Typeahead CARI ANGGOTA (pemakaian)
    //    Target: #carianggota (halaman yang form-nya pakai id ini).
    //
    //    NOTE: Element #carianggota saat ini tidak ditemukan di
    //    view manapun. Script tetap di-attach (di dalam if-check)
    //    agar tidak error, tapi hanya berjalan saat element ada.
    //    Endpoint /usages/cari_anggota tetap hidup.
    // ============================================================
    if (document.getElementById('carianggota')) {
        $('#carianggota').typeahead({
            hint: true,
            highlight: true,
            minLength: 2
        }, {
            name: 'states',
            source: function(query, process) {
                if (query.length < 2) return;

                $.ajax({
                    url: '/usages/cari_anggota',
                    method: 'GET',
                    data: {
                        query: query
                    },
                    dataType: 'json',
                    success: function(result) {
                        var states = result.map(function(item) {
                            return {
                                id: item.customer.kode_instalasi,
                                name: `${item.customer.nama} [${item.customer.kode_instalasi}]`,
                                value: item.customer.kode_instalasi,
                                data: item
                            };
                        });

                        process(states);
                    },
                    error: function(xhr, status, error) {
                        console.error("Terjadi kesalahan saat memanggil pelanggan:", error);
                        process([]);
                    }
                });
            },
            displayKey: 'name',
            autoSelect: true,
            fitToElement: true,
            items: 10
        }).bind('typeahead:selected', function(event, item) {
            var data = item.data;
            var usage = data.usage;

            var nilai_awal = usage ? usage.akhir || 0 : 0;

            $('#awal').val(nilai_awal);
            $('#customer_id').val(data.customer.customer_id);
            $('#id_instalasi').val(data.customer.id);
        });

        // Handler validasi nilai akhir (awalnya di base, tetap di sini
        // karena element .hitungan diasumsikan hanya ada di halaman
        // yang pakai #carianggota)
        $(document).on('change', '.hitungan', function() {
            var awal = parseFloat($('#awal').val()) || 0;
            var akhir = parseFloat($('#akhir').val()) || 0;
            var jarak_awal = parseFloat($('#jarak_awal').val()) || 0;

            if (akhir <= awal || akhir === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Nilai akhir tidak valid',
                    text: 'Nilai akhir harus lebih besar dari nilai awal.',
                    confirmButtonText: 'Coba lagi'
                });
                $('#jumlah').val('');
                return;
            }

            var selisih = akhir - awal;

            if (selisih >= jarak_awal) {
                $('#jumlah').val(selisih);
                $('#awal').val(awal);
            } else {
                $('#jumlah').val();
                alert('Selisih tidak memenuhi syarat jarak minimum.');
            }
        });
    }
</script>
