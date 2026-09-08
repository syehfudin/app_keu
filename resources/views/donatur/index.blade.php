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
    <div class="row">
        <div class="col-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped" id="datatable-donatur">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Penghimpun</th>
                                <th>Nama Nasabah</th>
                                <th>No Telp</th>
                                <th>Pekerjaan</th>
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
    let dataUrl = "{{ route('donatur.index_data') }}";
    let tableSelector = "datatable-donatur";

    dt = $("#" + tableSelector).DataTable({
        order: [1, 'asc'],
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "searching": true,
        "ajax": dataUrl,
        columns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "nama_relawan", name: "nama_relawan" },
            { data: "nama_donatur", name: "nama_donatur" },
            { data: "no_telepon", name: "no_telepon" },
            { data: "pekerjaan", name: "pekerjaan" },
            {
                data: "action",
                name: "action",
                orderable: true,
                searchable: true,
            },
        ],
    });
    table = dt.$;
</script>
@endpush
