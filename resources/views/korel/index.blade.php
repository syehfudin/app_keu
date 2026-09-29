@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
<style>
    .stat-icon { width: 48px; height: 48px; border-radius: .5rem; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; }
    .list-bawahan-cell { font-size: .875rem; }
</style>
@endpush
@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between py-2 py-md-2">
        <div>
            <h5 class="mb-0 text-primary"><i class="fas fa-sitemap"></i> Struktur Koordinator Penghimpun</h5>
        </div>
        @can('korel-create')
        <a class="btn btn-success" href="{{ route('korel.create') }}"><i class="fas fa-plus"></i> Tambah Koordinator Penghimpun</a>
        @endcan
    </div>

    {{-- ===== Kartu Statistik ===== --}}
    <div class="row mt-3">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-primary elevation-1" style="width:60px;height:60px;"><i class="fas fa-user-tie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Koordinator Aktif</span>
                    <span class="info-box-number">{{ $totalKoordinator }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-teal elevation-1" style="width:60px;height:60px;"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Bawahan Terpetakan</span>
                    <span class="info-box-number">{{ $totalBawahan }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-success elevation-1" style="width:60px;height:60px;"><i class="fas fa-user-cog"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Potensi Koordinator</span>
                    <span class="info-box-number">{{ $totalPegawaiKoordinator }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-warning elevation-1" style="width:60px;height:60px;"><i class="fas fa-user-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Penghimpun Tanpa Koordinator</span>
                    <span class="info-box-number">{{ $penghimpunTanpaKoordinator }} <small>/ {{ $totalPenghimpun }}</small></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12 col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Daftar Koordinator &amp; Bawahannya</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover" id="datatable-korel">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Koordinator</th>
                                <th>Role</th>
                                <th>Jumlah Downline</th>
                                <th>Nama Downline</th>
                                <th class="text-center" width="150px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('custom-js-files')
<!-- DataTables  & Plugins -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script type="text/javascript">
    let dataUrl = "{{ route('korel.index_data') }}";
    let tableSelector = "datatable-korel";

    // Peta warna role untuk badge koordinator
    const ROLE_BADGE = {
        'admin': 'bg-dark',
        'direktur': 'bg-danger',
        'dirops': 'bg-danger',
        'general manager': 'bg-warning text-dark',
        'manager': 'bg-info',
        'supervisor': 'bg-primary',
        'penghimpun': 'bg-secondary',
    };
    const roleBadgeClass = function(role) {
        if (!role) return 'bg-secondary';
        const key = (role || '').toLowerCase();
        return ROLE_BADGE[key] || 'bg-secondary';
    };

    let dt = $("#" + tableSelector).DataTable({
        // Default sort berdasarkan role_order (kolom hidden index 5):
        // Direktur -> DirOps -> General Manager -> Manager -> Supervisor
        "order": [[5, 'asc']],
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "searching": true,
        "pageLength": 10,
        "ajax": dataUrl,
        columns: [
            { data: "DT_RowIndex", name: "DT_RowIndex", searchable: false, orderable: false },
            { data: "nama_atasan", name: "nama_atasan" },
            { data: "role_atasan", name: "role_atasan", searchable: false },
            { data: "jml_bawahan", name: "jml_bawahan", searchable: false, className: "text-center" },
            { data: "list_bawahan", name: "list_bawahan" },
            // kolom tersembunyi untuk sorting role (dipakai server-side order)
            { data: "role_order", name: "role_order", orderable: true, searchable: false, visible: false },
            { data: "action", name: "action", orderable: false, searchable: false, className: "text-center" },
        ],
        columnDefs: [
            {   // Nama koordinator (no badge lagi, pindah ke kolom Role)
                targets: 1,
                render: function(data, type, row) {
                    if (type !== 'display') return data;
                    return '<strong>' + data + '</strong>';
                }
            },
            {   // Role dengan badge
                targets: 2,
                render: function(data, type, row) {
                    if (type !== 'display') return data;
                    let badge = roleBadgeClass(data);
                    let label = data ? data : '—';
                    return '<span class="badge ' + badge + '">' + label + '</span>';
                }
            },
            {   // Jumlah downline
                targets: 3,
                render: function(data, type, row) {
                    if (type !== 'display') return data;
                    let n = parseInt(data || 0, 10);
                    let cls = n === 0 ? 'badge badge-secondary' : (n <= 10 ? 'badge badge-primary' : 'badge badge-success');
                    return '<span class="' + cls + '" style="font-size:1rem;">' + n + '</span>';
                }
            },
            {   // Nama downline
                targets: 4,
                render: function(data, type, row) {
                    if (type !== 'display') return data;
                    if (!data || !data.trim()) {
                        return '<span class="text-muted"><i class="fas fa-minus"></i></span>';
                    }
                    let list = data.split(',');
                    let shown = list.slice(0, 6).join(', ');
                    let more = list.length > 6 ? ' <span class="text-muted">(+' + (list.length - 6) + ')</span>' : '';
                    return '<span class="list-bawahan-cell">' + shown + more + '</span>';
                }
            }
        ],
        "language": {
            "search": "Cari:",
            "searchPlaceholder": "Nama koordinator / downline...",
            "processing": "Memuat data struktur...",
            "emptyTable": "Belum ada koordinator terdaftar."
        }
    });
    table = dt.$;
</script>
@endpush
