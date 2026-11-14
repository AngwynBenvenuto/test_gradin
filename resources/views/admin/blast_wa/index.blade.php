@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="content-wrapper" id="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="widget-box">
                <div class="widget-title clearfix">
                    <h1>@yield('title')</h1>
                    <div class="buttons">
                        <button id="btnUpload" class="btn btn-sm btn-primary lnj-color">
                            <i class="fas fa-file-excel"></i> Upload Excel
                        </button>
                        {{-- <a href="{{ route('admin.blast_wa.print_sample') }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-eye"></i> Lihat Contoh Cetakan
                        </a> --}}
                    </div>
                </div>
                <div class="widget-content clearfix">
                    <div class="bulk-actions d-flex align-items-center flex-wrap mb-3" style="gap:.4rem">
                        <button id="btnSendBulk" class="btn btn-sm btn-outline-success" disabled>
                            <i class="fab fa-whatsapp"></i> Kirim ke WA (Terpilih)
                        </button>
                        <button id="btnDownloadPdf" class="btn btn-sm btn-outline-primary" disabled>
                            <i class="fas fa-file-pdf"></i> Download PDF (Terpilih)
                        </button>
                        <button id="btnDownloadImages" class="btn btn-sm btn-outline-secondary" disabled>
                            <i class="fas fa-images"></i> Download Gambar (Terpilih)
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tbl" cellspacing="0" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="1%"><input type="checkbox" id="chkAll"></th>
                                    <th width="5%">Action</th>
                                    <th nowrap>No SPP</th>
                                    <th nowrap>No Tiket</th>
                                    <th nowrap>Kode Kebun</th>
                                    <th nowrap>Petani</th>
                                    <th nowrap>No WA Petani</th>
                                    <th nowrap>No Polisi</th>
                                    <th nowrap>Sopir</th>
                                    <th nowrap>Tgl Masuk</th>
                                    <th nowrap>Bruto</th>
                                    <th nowrap>Tarra</th>
                                    <th nowrap>Netto</th>
                                    <th nowrap>Rendemen</th>
                                    <th nowrap>Rp/Ton</th>
                                    <th nowrap>Dibayar</th>
                                    <th nowrap>Status Kirim</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Upload Excel --}}
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="uploadForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Upload Excel Timbangan</h5>
          <button type="button" class="btn-close btnCloseUpload" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <a href="{{ route('admin.blast_wa.template') }}" class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-file-download"></i> Download Format Excel
            </a>
          </div>
          <div class="form-group mb-2">
            <label class="control-label">File Excel (.xlsx, .xls, .csv) <span style="color:red">*</span></label>
            <input class="form-control" type="file" name="file" accept=".xlsx,.xls,.csv" required>
            {{-- <small class="text-muted d-block mt-1">
                Header kolom wajib (sesuai <code>format_blast.xlsx</code>): No Tiket, No Polisi, Nama Supir, Nama Petani,
                Pol, Brix, Fiber, Rendemen, Tanggal Masuk, Jam Masuk, Tanggal Keluar, Jam Keluar, Bruto, Tarra, Netto.
                <br>Kolom <code>No WA Petani</code> dan <code>Trash</code> opsional (boleh tidak ada di excel) &mdash;
                No WA Petani diisi manual lewat tombol <strong>Edit No WA</strong> di tabel sebelum tiket dikirim.
            </small> --}}
          </div>
          <div id="uploadResult"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btnCloseUpload" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSubmitUpload"><i class="fas fa-upload"></i> Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('extend_script')
    <link href="{{ asset('media/plugins/sweetalert/sweetalert.css') }}" rel="stylesheet" media="screen">
    <script src="{{ asset('media/plugins/sweetalert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('media/plugins/html2canvas/html2canvas.min.js') }}"></script>
    <script src="{{ asset('media/plugins/jszip/jszip.min.js') }}"></script>
@endsection

@section('footer_js')
<script>
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': AppHelper.csrfToken } });

    var TABLE = '#tbl';
    var BASE_URL = "{{ url('/blast-wa') }}";
    var UPLOAD_URL = "{{ route('admin.blast_wa.upload') }}";
    var SEND_BULK_URL = "{{ route('admin.blast_wa.send_bulk') }}";
    var PDF_BULK_URL = "{{ route('admin.blast_wa.pdf_bulk') }}";
    var selectedIds = new Set();
    var editingWaId = null; // id baris yang lagi diedit no WA-nya, atau null
    var WA_REGEX = /^\+62[0-9]{8,13}$/;

    function num(v) {
        return (v === null || v === undefined || v === '') ? '-' : v;
    }

    function sentBadge(sent, sentAt, lastError) {
        if (sent) {
            return '<span class="badge bg-success" title="' + (sentAt || '') + '">Terkirim</span>';
        }
        if (lastError) {
            return '<span class="badge bg-danger" title="' + escapeHtml(lastError) + '">Gagal</span>';
        }
        return '<span class="badge bg-secondary">Belum</span>';
    }

    function escapeHtml(v) {
        return String(v == null ? '' : v).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function waPetaniCell(v, row) {
        var id = String(row.id);
        if (editingWaId === id) {
            return '' +
                '<div class="input-group input-group-sm" style="min-width:190px">' +
                    '<input type="text" class="form-control waInlineInput" data-id="' + id + '" value="' + escapeHtml(v) + '" placeholder="+6281234567890">' +
                    '<button class="btn btn-success btnWaSave" data-id="' + id + '" type="button" title="Simpan"><i class="fas fa-check"></i></button>' +
                    '<button class="btn btn-secondary btnWaCancel" type="button" title="Batal"><i class="fas fa-times"></i></button>' +
                '</div>';
        }

        var display = v ? escapeHtml(v) : '<span class="badge bg-danger">Belum diisi</span>';
        if (row.status_send) {
            return display;
        }
        return display + ' <button class="btn btn-sm btn-link p-0 btnWaEdit" data-id="' + id + '" title="Edit No WA"><i class="fas fa-pen"></i></button>';
    }

    $.ct.appTable(TABLE, {
        source: "{{ route('admin.blast_wa.fetch') }}",
        columns: [
            { data: 'id', className: 'no-vis', formatter: (id, row) => {
                if (row.status_send) return '';
                var checked = selectedIds.has(String(id)) ? ' checked' : '';
                return '<input type="checkbox" class="rowCheck" value="' + id + '"' + checked + '>';
            }},
            { type: 'actions', className: 'no-vis', actions: [
                { label: 'Print', class: 'btnPrint', data: row => ({ id: row.id }) }
            ]},
            { data: 'no_spp', formatter: v => num(v) },
            { data: 'no_tiket', formatter: v => num(v) },
            { data: 'kodekebun', formatter: v => num(v) },
            { data: 'nama_petani', formatter: v => num(v) },
            { data: 'no_wa_petani', formatter: (v, row) => waPetaniCell(v, row) },
            { data: 'no_polisi', formatter: v => num(v) },
            { data: 'nama_sopir', formatter: v => num(v) },
            { data: 'tanggal_masuk', formatter: v => num(v) },
            { data: 'bruto', formatter: v => num(v) },
            { data: 'tarra', formatter: v => num(v) },
            { data: 'netto', formatter: v => num(v) },
            { data: 'rendemen', formatter: v => num(v) },
            { data: 'thp_per_ton', formatter: v => num(v) },
            { data: 'dibayar_hari', formatter: v => num(v) },
            { combine: ['status_send', 'sent_at', 'last_send_error'], formatter: (v) => sentBadge(v.status_send, v.sent_at, v.last_send_error) }
        ],
        filters: [
            { name: 'dibayar_hari', label: 'Dibayar', type: 'text' }
        ],
        responsive: true,
        order: [[3, 'desc']]
    });

    function updateSendBulkButton() {
        var count = selectedIds.size;
        $('#btnSendBulk')
            .prop('disabled', count === 0)
            .html('<i class="fab fa-whatsapp"></i> Kirim ke WA' + (count > 0 ? ' (' + count + ' dipilih)' : ' (Terpilih)'));
        $('#btnDownloadImages')
            .prop('disabled', count === 0)
            .html('<i class="fas fa-images"></i> Download Gambar' + (count > 0 ? ' (' + count + ' dipilih)' : ' (Terpilih)'));
        $('#btnDownloadPdf')
            .prop('disabled', count === 0)
            .html('<i class="fas fa-file-pdf"></i> Download PDF' + (count > 0 ? ' (' + count + ' dipilih)' : ' (Terpilih)'));
    }

    function syncChkAllState() {
        var eligible = 0;
        var checked = 0;
        $(TABLE).DataTable().rows({ page: 'current' }).every(function () {
            var d = this.data();
            if (d.status_send) return;
            eligible++;
            if (selectedIds.has(String(d.id))) checked++;
        });
        var $chkAll = $('#chkAll');
        $chkAll.prop('checked', eligible > 0 && checked === eligible);
        $chkAll.prop('indeterminate', checked > 0 && checked < eligible);
    }

    $(TABLE).on('draw.dt', function () { syncChkAllState(); });

    $('#chkAll').on('change', function () {
        var checked = this.checked;
        $(TABLE).DataTable().rows({ page: 'current' }).every(function () {
            var d = this.data();
            if (d.status_send) return;
            if (checked) selectedIds.add(String(d.id)); else selectedIds.delete(String(d.id));
        });
        $(TABLE).DataTable().rows({ page: 'current' }).invalidate('data').draw(false);
        updateSendBulkButton();
    });

    $(TABLE).on('change', '.rowCheck', function () {
        var id = String($(this).val());
        if (this.checked) selectedIds.add(id); else selectedIds.delete(id);
        syncChkAllState();
        updateSendBulkButton();
    });

    function reload() { $(TABLE).DataTable().ajax.reload(null, false); }

    // --- Upload Excel ---

    function showUploadModal() { var el = document.getElementById('uploadModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).show() : $(el).modal('show'); }
    function hideUploadModal() { var el = document.getElementById('uploadModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).hide() : $(el).modal('hide'); }

    $('#btnUpload').on('click', function () {
        document.getElementById('uploadForm').reset();
        $('#uploadResult').empty();
        showUploadModal();
    });

    $(document).on('click', '.btnCloseUpload', hideUploadModal);

    $('#uploadForm').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        var $btn = $('#btnSubmitUpload').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');

        $.ajax({
            url: UPLOAD_URL,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (res) {
            if (res.status === 'success') {
                var html = '<div class="alert alert-success">' + res.message + '</div>';
                if (res.data && res.data.skipped && res.data.skipped.length) {
                    html += '<div class="alert alert-warning"><small>' + res.data.skipped.join('<br>') + '</small></div>';
                }
                $('#uploadResult').html(html);
                reload();
            } else {
                $('#uploadResult').html('<div class="alert alert-danger">' + (res.errMsg || 'Gagal upload.') + '</div>');
            }
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal upload.';
            $('#uploadResult').html('<div class="alert alert-danger">' + msg + '</div>');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload');
        });
    });

    // --- Edit No WA (inline di datatable) ---

    $(TABLE).on('click', '.btnWaEdit', function () {
        editingWaId = String($(this).data('id'));
        $(TABLE).DataTable().rows().invalidate('data').draw(false);
        setTimeout(function () { $(TABLE + ' .waInlineInput').trigger('focus'); }, 0);
    });

    $(TABLE).on('click', '.btnWaCancel', function () {
        editingWaId = null;
        $(TABLE).DataTable().rows().invalidate('data').draw(false);
    });

    $(TABLE).on('keydown', '.waInlineInput', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $(this).closest('.input-group').find('.btnWaSave').trigger('click');
        } else if (e.key === 'Escape') {
            e.preventDefault();
            editingWaId = null;
            $(TABLE).DataTable().rows().invalidate('data').draw(false);
        }
    });

    $(TABLE).on('click', '.btnWaSave', function () {
        var id = $(this).data('id');
        var value = $.trim($(TABLE).find('.waInlineInput[data-id="' + id + '"]').val());

        if (!WA_REGEX.test(value)) {
            Swal.fire({ icon: 'error', title: 'Format Salah', text: 'Nomor WhatsApp harus diawali +62, contoh: +6281234567890.' });
            return;
        }

        $.post(BASE_URL + '/' + id + '/updatePhone', { no_wa_petani: value }, function (res) {
            if (res.status === 'success') {
                editingWaId = null;
                reload();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal menyimpan.' });
            }
        }, 'json').fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal menyimpan.';
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
        });
    });

    // --- Print ---

    $(TABLE).on('click', '.btnPrint', function () {
        window.open(BASE_URL + '/' + $(this).data('id') + '/print?autoprint=1', '_blank');
    });

    $('#btnSendBulk').on('click', function () {
        if (selectedIds.size === 0) return;
        var ids = Array.from(selectedIds);
        Swal.fire({
            icon: 'question',
            title: 'Kirim ' + ids.length + ' tiket ke WhatsApp?',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post(SEND_BULK_URL, { ids: ids }, function (res) {
                if (res.status === 'success') {
                    var html = res.message;
                    if (res.data && res.data.failed && res.data.failed.length) {
                        html += '<br><small>' + res.data.failed.join('<br>') + '</small>';
                    }
                    Swal.fire({ icon: 'success', title: 'Selesai', html: html });
                    selectedIds.clear();
                    updateSendBulkButton();
                    reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal mengirim.' });
                }
            }, 'json').fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal mengirim.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            });
        });
    });

    // --- Download Gambar (Terpilih) ---
    // Render halaman /print di iframe tersembunyi lalu di-screenshot pakai
    // html2canvas (server tidak punya Ghostscript/ImageMagick buat convert
    // PDF->gambar), hasilnya selalu di-zip (JSZip) jadi 1 file .zip.

    // Ukuran cetakan A5 (sama seperti @page A5 di print.blade.php) dalam
    // CSS px @ 96dpi, supaya gambar hasil download proporsinya pas kertas A5
    // (148mm x 210mm), bukan kotak sembarang.
    var A5_WIDTH_PX = 559;
    var A5_HEIGHT_PX = 794;

    function captureTicketAsPngBlob(id) {
        return new Promise(function (resolve, reject) {
            var iframe = document.createElement('iframe');
            iframe.style.position = 'fixed';
            iframe.style.left = '-99999px';
            iframe.style.top = '0';
            iframe.style.width = A5_WIDTH_PX + 'px';
            iframe.style.height = A5_HEIGHT_PX + 'px';
            iframe.style.border = '0';

            function cleanupAndReject(err) {
                if (iframe.parentNode) document.body.removeChild(iframe);
                reject(err);
            }

            iframe.onload = function () {
                setTimeout(function () {
                    var doc;
                    try {
                        doc = iframe.contentDocument || iframe.contentWindow.document;
                    } catch (e) {
                        cleanupAndReject(e);
                        return;
                    }
                    // Lebar tetap A5 biar proporsi/line-wrap sama dengan cetakan,
                    // tapi tinggi ikut konten sebenarnya -- supaya tidak ada area
                    // putih kosong kalau kontennya lebih pendek dari 1 halaman A5.
                    var contentHeight = doc.body.scrollHeight || A5_HEIGHT_PX;
                    html2canvas(doc.documentElement, {
                        backgroundColor: '#ffffff',
                        scale: 2,
                        width: A5_WIDTH_PX,
                        height: contentHeight,
                        windowWidth: A5_WIDTH_PX,
                        windowHeight: contentHeight
                    }).then(function (canvas) {
                        canvas.toBlob(function (blob) {
                            document.body.removeChild(iframe);
                            if (blob) resolve(blob); else reject(new Error('Gagal membuat gambar.'));
                        }, 'image/png');
                    }).catch(cleanupAndReject);
                }, 400); // beri waktu gambar (logo/watermark) selesai dimuat sebelum discreenshot
            };
            iframe.onerror = function () { cleanupAndReject(new Error('Gagal memuat halaman cetakan.')); };

            iframe.src = BASE_URL + '/' + id + '/print';
            document.body.appendChild(iframe);
        });
    }

    $('#btnDownloadImages').on('click', function () {
        if (selectedIds.size === 0) return;

        var ids = Array.from(selectedIds);
        var rowsById = {};
        $(TABLE).DataTable().rows().data().each(function (d) { rowsById[String(d.id)] = d; });

        var $btn = $(this).prop('disabled', true);
        var originalHtml = $btn.html();

        var zip = new JSZip();
        var failed = [];

        function setProgress(done) {
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Memproses ' + done + '/' + ids.length + '...');
        }
        setProgress(0);

        function next(index) {
            if (index >= ids.length) {
                if (Object.keys(zip.files).length === 0) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak ada gambar yang berhasil dibuat.' });
                    $btn.prop('disabled', false).html(originalHtml);
                    return;
                }
                zip.generateAsync({ type: 'blob' }).then(function (content) {
                    var url = URL.createObjectURL(content);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'blastwa-cetakan-' + Date.now() + '.zip';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    $btn.prop('disabled', false).html(originalHtml);
                    if (failed.length) {
                        Swal.fire({ icon: 'warning', title: 'Selesai (sebagian gagal)', html: failed.join('<br>') });
                    }
                });
                return;
            }

            var id = ids[index];
            var row = rowsById[id];
            var fileName = 'timbangan-' + (row && row.no_tiket ? row.no_tiket : id) + '-' + (row && row.nama_petani ? row.nama_petani : 'unknown') + '.png';

            captureTicketAsPngBlob(id).then(function (blob) {
                zip.file(fileName, blob);
            }).catch(function () {
                failed.push((row && row.no_tiket ? row.no_tiket : id) + ': gagal dibuat gambarnya');
            }).then(function () {
                setProgress(index + 1);
                next(index + 1);
            });
        }

        next(0);
    });

    // --- Download PDF (Terpilih) ---
    // Server-side: pdfBulk() render PDF-nya persis sama dengan pdf() satuan
    // (view & setPaper sama), cuma dibungkus + di-zip. Di sini cuma minta
    // hasil zip-nya lalu download.

    $('#btnDownloadPdf').on('click', function () {
        if (selectedIds.size === 0) return;

        var ids = Array.from(selectedIds);
        var $btn = $(this).prop('disabled', true);
        var originalHtml = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

        $.ajax({
            url: PDF_BULK_URL,
            type: 'POST',
            data: { ids: ids },
            xhrFields: { responseType: 'blob' }
        }).done(function (blob) {
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'blastwa-pdf-' + Date.now() + '.zip';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal mengunduh PDF.' });
        }).always(function () {
            $btn.prop('disabled', false).html(originalHtml);
        });
    });
});
</script>
@endsection
