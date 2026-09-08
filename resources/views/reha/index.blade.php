@extends('layouts.app')
@push('custom-css-files')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush
@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center py-2 py-md-2">
            @can('reha-create')
                <a class="btn btn-success" href="{{ route('reha.create') }}"> Buat Report Harian</a>
            @endcan
        </div>
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped" id="datatable-program">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Nama Penghimpun</th>
                                    {{-- <th>Jenis Akad</th> --}}
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
        let dataUrl = "{{ route('reha.index_data') }}";
        let tableSelector = "datatable-program";

        dt = $("#" + tableSelector).DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "searching": true,
            "ajax": dataUrl,
            columns: [{
                    data: "DT_RowIndex",
                    name: "DT_RowIndex"
                },
                {
                    data: "tanggal",
                    name: "tanggal"
                },
                {
                    data: "nama_relawan",
                    name: "nama_relawan"
                },
                // {
                //     data: "pegawai_id",
                //     name: "pegawai_id"
                // },
                // {
                //     data: "jenis_akad",
                //     name: "jenis_akad"
                // },
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
