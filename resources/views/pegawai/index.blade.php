@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush
@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center py-2 py-md-2">
        @can('pegawai-create')
        <a class="btn btn-success" href="{{ route('pegawai.create') }}"> Tambah Penghimpun</a>
        @endcan
    </div>

    {{-- ===== Filter: Role & Nama Penghimpun ===== --}}
    <div class="row">
        <div class="col-12 col-lg-12">
            <div class="card card-outline card-info">
                <div class="card-body">
                    <form method="GET" id="form-filter-pegawai" onsubmit="applyPegawaiFilter(); return false;">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold mb-1">Role</label>
                                <select class="form-control" id="filter_role">
                                    <option value="">- Semua Role -</option>
                                    @foreach($roleList as $r)
                                        <option value="{{ $r }}">{{ $r }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold mb-1">Nama Penghimpun</label>
                                <input type="text" class="form-control" id="filter_nama" placeholder="Cari nama penghimpun...">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                                <button type="button" class="btn btn-secondary" onclick="resetPegawaiFilter()"><i class="fas fa-redo"></i> Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-12">
            <div class="card card-primary">
                <div class="card-body">
                    <table class="table table-striped" id="datatable-pegawai">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Penghimpun</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Role</th>
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
<script src="{{ asset('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script type="text/javascript">
    let dataUrl = "{{ route('pegawai.index_data') }}";
    let tableSelector = "datatable-pegawai";

    dt = $("#" + tableSelector).DataTable({
        "order": [[1, 'asc']],
        "responsive": true, "lengthChange": false, "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "searching": false,
        "ajax": {
            "url": dataUrl,
            "data": function (d) {
                d.filter_role = $("#filter_role").val() || "";
                d.filter_nama = $("#filter_nama").val() || "";
            }
        },
        columns: [
            { data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false },
            { data: "nip", name: "nip" },
            { data: "nama", name: "nama" },
            { data: "username", name: "username" },
            { data: "role", name: "role" },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
            },
        ],
    });
    table = dt.$;

    let applyPegawaiFilter = () => dt.draw();

    let resetPegawaiFilter = () => {
        $("#filter_role").val('');
        $("#filter_nama").val('');
        dt.draw();
    };
</script>
@endpush