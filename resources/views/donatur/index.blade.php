@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush
@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center py-2 py-md-2">
        @can('donatur-create')
        <a class="btn btn-success" href="{{ route('donatur.create') }}"> Tambah Nasabah</a>
        @endcan
    </div>

    {{-- ===== Filter: Manager / Supervisor / Penghimpun + Search Nama Nasabah ===== --}}
    <div class="row">
        <div class="col-12 col-lg-12">
            <div class="card card-outline card-info">
                <div class="card-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold mb-1">Manager</label>
                            <select class="form-control" id="filter_manager">
                                <option value="">- Semua Manager -</option>
                                @foreach($managers as $m)
                                    <option value="{{ $m->nama }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold mb-1">Supervisor</label>
                            <select class="form-control" id="filter_supervisor">
                                <option value="">- Semua Supervisor -</option>
                                @foreach($supervisors as $s)
                                    <option value="{{ $s->id }}">{{ $s->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold mb-1">Penghimpun</label>
                            <select class="form-control" id="filter_penghimpun">
                                <option value="">- Semua Penghimpun -</option>
                                @foreach($penghimpuns as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold mb-1">Cari Nama Nasabah</label>
                            <input type="text" class="form-control" id="filter_nama" placeholder="Ketik nama nasabah..." oninput="applyDonaturFilterInstant()">
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-primary" onclick="applyDonaturFilter()"><i class="fas fa-filter"></i> Filter</button>
                        <button type="button" class="btn btn-secondary" onclick="resetDonaturFilter()"><i class="fas fa-redo"></i> Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped" id="datatable-donatur">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Manager</th>
                                <th>Supervisor</th>
                                <th>Penghimpun</th>
                                <th>Nama Nasabah</th>
                                <th width="280px">Action</th>
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
@push('custom-css-files')
<style>
    .search-nasabah-highlight { font-weight: bold; color: #d63384; }
</style>
@endpush
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
    let dataUrl = "{{ route('donatur.index_data') }}";
    let tableSelector = "datatable-donatur";

    // ===== CLIENT-SIDE DataTables: load sekali, search/filter instan =====
    dt = $("#" + tableSelector).DataTable({
        "responsive": true,
        "pageLength": 20,
        "lengthChange": false,
        "autoWidth": false,
        "processing": true,
        "serverSide": false,
        "searching": true,
        "deferRender": true,
        "order": [[4, 'asc']],
        "ajax": dataUrl,
        columns: [
            { data: null, orderable: false, searchable: false },
            { data: "nama_manager", searchable: false },
            { data: "nama_supervisor", searchable: false },
            { data: "nama_relawan", searchable: false },
            { data: "nama_donatur", searchable: true },
            { data: "action", orderable: false, searchable: false },
        ],
        columnDefs: [
            { targets: 0, render: function(data, type, row, meta) { return meta.row + 1; } }
        ],
        "language": {
            "search": "Filter cepat:",
            "searchPlaceholder": "Ketik untuk memfilter nama nasabah...",
            "processing": "Memuat data nasabah..."
        }
    });
    table = dt.$;

    // ===== SEARCH HANYA NAMA NASABAH: pakai dt.search() custom + column filter =====
    // Karena global search akan men-filter hanya kolom searchable (nama_donatur),
    // cukup panggil dt.search() per keystroke (instant, client-side).
    let applyDonaturFilterInstant = () => dt.search($("#filter_nama").val());

    let applyDonaturFilter = () => dt.draw();

    let resetDonaturFilter = () => {
        $("#filter_manager").val('');
        $("#filter_supervisor").val('');
        $("#filter_penghimpun").val('');
        $("#filter_nama").val('');
        dt.search('').draw();
    };
</script>
@endpush