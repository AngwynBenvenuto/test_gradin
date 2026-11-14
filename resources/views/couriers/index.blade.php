<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kurir</title>
    <link rel="stylesheet" href="{{ asset('media/css/bootstrap-5/bootstrap.min.css') }}">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Data Kurir</h4>
        <button class="btn btn-primary" id="btnAdd">+ Tambah Kurir</button>
    </div>

    <form id="filter" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">Cari nama</label>
                <input type="search" name="search" class="form-control" placeholder="Nama kurir...">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Level</label>
                <div class="d-flex gap-2 pt-1">
                    @foreach ([1, 2, 3, 4, 5] as $l)
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="level" value="{{ $l }}" id="lv{{ $l }}"><label class="form-check-label" for="lv{{ $l }}">{{ $l }}</label></div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Urutkan</label>
                <select name="sort" class="form-select">
                    <option value="name:asc">Nama (A-Z)</option>
                    <option value="name:desc">Nama (Z-A)</option>
                    <option value="registered_at:desc">Tanggal daftar (terbaru)</option>
                    <option value="registered_at:asc">Tanggal daftar (terlama)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Per halaman</label>
                <select name="per_page" class="form-select"><option>10</option><option>25</option><option>50</option></select>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Kode</th><th>Nama</th><th>Telepon</th><th>Level</th><th>Kendaraan</th><th>Terdaftar</th><th>Status</th><th></th></tr></thead>
                <tbody id="rows"></tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small id="info" class="text-muted"></small>
            <ul class="pagination pagination-sm mb-0" id="pager"></ul>
        </div>
    </div>
</div>

<div class="modal fade" id="modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="form">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="formErr"></div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Kode *</label><input name="code" class="form-control" required maxlength="30"></div>
                    <div class="col-md-8"><label class="form-label">Nama *</label><input name="name" class="form-control" required minlength="2" maxlength="150"></div>
                    <div class="col-md-4"><label class="form-label">Telepon *</label><input name="phone" class="form-control" required></div>
                    <div class="col-md-5"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Level *</label><select name="level" class="form-select">@foreach ([1,2,3,4,5] as $l)<option>{{ $l }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Kendaraan</label>
                        <select name="vehicle_type" class="form-select"><option value="">-</option>@foreach (['motorcycle','car','van','truck','bicycle'] as $v)<option>{{ $v }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Plat nomor</label><input name="license_plate" class="form-control" maxlength="20"></div>
                    <div class="col-md-4 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked><label class="form-check-label" for="isActive">Aktif</label></div></div>
                    <div class="col-12"><label class="form-label">Alamat</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                    <div class="col-12"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
        </form>
    </div>
</div>

<script src="{{ asset('media/js/bootstrap-5/popper.js') }}"></script>
<script src="{{ asset('media/js/bootstrap-5/bootstrap.min.js') }}"></script>
<script>
const API = @json(url('/api/couriers'));
const $ = s => document.querySelector(s);
const filter = $('#filter'), form = $('#form');
const modal = new bootstrap.Modal('#modal');
let page = 1, editId = null;

const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const fmtDate = d => d ? new Date(d).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) : '-';

async function api(url, opt = {}) {
    const res = await fetch(url, {...opt, headers: {'Accept':'application/json', 'Content-Type':'application/json'}});
    const json = await res.json().catch(() => ({}));
    if (!res.ok) throw {status: res.status, json};
    return json;
}

async function load() {
    const f = new FormData(filter), [sort, direction] = f.get('sort').split(':');
    const q = new URLSearchParams({sort, direction, per_page: f.get('per_page'), page});
    if (f.get('search').trim()) q.set('search', f.get('search').trim());
    const lv = f.getAll('level'); if (lv.length) q.set('level', lv.join(','));
    try {
        const r = await api(`${API}?${q}`);
        $('#rows').innerHTML = r.data.length ? r.data.map(c => `<tr>
            <td>${esc(c.code)}</td><td>${esc(c.name)}</td><td>${esc(c.phone)}</td><td>${c.level}</td>
            <td>${esc(c.vehicle_type || '-')} ${esc(c.license_plate || '')}</td><td>${fmtDate(c.registered_at)}</td>
            <td><span class="badge text-bg-${c.is_active ? 'success' : 'secondary'}">${c.is_active ? 'Aktif' : 'Nonaktif'}</span></td>
            <td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary" data-edit="${c.id}">Edit</button>
            <button class="btn btn-sm btn-outline-danger" data-del="${c.id}" data-name="${esc(c.name)}">Hapus</button></td></tr>`).join('')
            : '<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data</td></tr>';
        $('#info').textContent = r.total ? `${r.from}-${r.to} dari ${r.total}` : '';
        pager(r.current_page, r.last_page);
    } catch (e) {
        $('#rows').innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data</td></tr>';
    }
}

function pager(cur, last) {
    const items = [];
    for (let p = Math.max(1, cur - 2); p <= Math.min(last, cur + 2); p++) items.push([p, p, p === cur, false]);
    items.unshift(['‹', cur - 1, false, cur <= 1]);
    items.push(['›', cur + 1, false, cur >= last]);
    $('#pager').innerHTML = last < 1 ? '' : items.map(([t, p, act, dis]) =>
        `<li class="page-item ${act ? 'active' : ''} ${dis ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${p}">${t}</a></li>`).join('');
}

$('#pager').addEventListener('click', e => {
    e.preventDefault();
    const p = e.target.dataset.page;
    if (p) { page = +p; load(); }
});
let t; // debounce typing in search box
filter.addEventListener('input', e => {
    clearTimeout(t);
    t = setTimeout(() => { page = 1; load(); }, e.target.name === 'search' ? 350 : 0);
});
filter.addEventListener('submit', e => e.preventDefault());

function openForm(c) {
    editId = c?.id ?? null;
    form.reset();
    $('#formErr').classList.add('d-none');
    $('#modalTitle').textContent = c ? 'Edit Kurir' : 'Tambah Kurir';
    if (c) for (const el of form.elements) {
        if (!el.name) continue;
        if (el.type === 'checkbox') el.checked = c[el.name]; else el.value = c[el.name] ?? '';
    }
    modal.show();
}

$('#btnAdd').onclick = () => openForm();
$('#rows').addEventListener('click', async e => {
    const b = e.target.closest('button');
    if (!b) return;
    if (b.dataset.edit) openForm((await api(`${API}/${b.dataset.edit}`)).data);
    if (b.dataset.del && confirm(`Hapus kurir "${b.dataset.name}"?`)) {
        await api(`${API}/${b.dataset.del}`, {method: 'DELETE'});
        load();
    }
});

form.addEventListener('submit', async e => {
    e.preventDefault();
    const d = Object.fromEntries(new FormData(form));
    d.is_active = form.is_active.checked;
    ['email', 'vehicle_type', 'license_plate', 'address', 'notes'].forEach(k => { if (d[k] === '') d[k] = null; });
    try {
        await api(editId ? `${API}/${editId}` : API, {method: editId ? 'PUT' : 'POST', body: JSON.stringify(d)});
        modal.hide();
        load();
    } catch (err) {
        const box = $('#formErr');
        box.textContent = err.json?.errors ? Object.values(err.json.errors).flat().join(' ') : 'Gagal menyimpan';
        box.classList.remove('d-none');
    }
});

load();
</script>
</body>
</html>
