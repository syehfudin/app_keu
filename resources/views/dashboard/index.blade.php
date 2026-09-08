@extends('layouts.app')
@push('custom-css-files')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush

@section('content')
    <style>

    </style>
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-9">
            </div>
            <div class="col-sm-3">
                <input type="text" name="tanggal" id="tanggal" class="form-control" value="{{ date('d-m-Y') }}">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Report Data Harian <span class="tgl"></span></h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped" id="datatable-daily">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Program</th>
                                    <th>Jumlah</th>
                                    <th>Nominal</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right"></th>
                                    <th></th>
                                    <th class="text-right"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Report Data Bulan <span class="bulan"></span></h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped" id="datatable-monthly">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Program</th>
                                    <th>Jumlah</th>
                                    <th>Nominal</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right"></th>
                                    <th></th>
                                    <th class="text-right"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Report Data Tahun <span class="tahun"></span></h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped table-scroll" id="datatable-yearly">
                            <thead>
                                <tr>
                                    <th class="sticky-cols" colspan="4">Perolehan LAZ Unggul Amanah <span
                                            class="tahun"></span></th>
                                    <th colspan="2">Januari</th>
                                    <th colspan="2">Februari</th>
                                    <th colspan="2">Maret</th>
                                    <th colspan="2">April</th>
                                    <th colspan="2">Mei</th>
                                    <th colspan="2">Juni</th>
                                    <th colspan="2">Juli</th>
                                    <th colspan="2">Agustus</th>
                                    <th colspan="2">September</th>
                                    <th colspan="2">Oktober</th>
                                    <th colspan="2">November</th>
                                    <th colspan="2">Desember</th>
                                </tr>
                                <tr>
                                    <th>No</th>
                                    <th>Item</th>
                                    <th>Jumlah Sementara JWT</th>
                                    <th>Jumlah Sementara Nominal</th>
                                    <th>Nominal</th>
                                    <th>JW</th>
                                    <th>Nominal</th>
                                    <th>JW</th>
                                    <th>Nominal</th>
                                    <th>JW</th>
                                    <th>Nominal</th>
                                    <th>JW</th>
                                    <th>Nominal</th>
                                    <th>JW</th>
                                    <th>Nominal</th>
                                    <th>JW</th>
                                    <th>Nominal</th>
                                    <th>JW</th>
                                    <th>Nominal</th>
                                    <th>JW</th>
                                    <th>Nominal</th>
                                    <th>JW</th>
                                    <th>Nominal</th>
                                    <th>JW</th>
                                    <th>Nominal</th>
                                    <th>JW</th>
                                    <th>Nominal</th>
                                    <th>JW</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2"></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            {{-- <div class="col-sm-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Report Data Tahun <span class="tahun"></span></h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div> --}}
            <div class="col-sm-12">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Line Chart</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="chartjs-line"
                                style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('custom-js-files')
    <!-- date-range-picker -->
    <script src="{{ asset('plugins/inputmask/jquery.inputmask.min.js') }}"></script>
    <script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
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
        var csrfToken = "{{ csrf_token() }}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        // loadTanggal("{{ date('Y-m-d') }}");

        let dataUrl = "{{ route('dashboard.data') }}";
        let dataChartUrl = "{{ route('dashboard.dataChart') }}";
        let tabledaily = "datatable-daily";
        let tablemonhtly = "datatable-monthly";
        let tableyearly = "datatable-yearly";
        let columns = [{
                data: "DT_RowIndex",
                name: "DT_RowIndex"
            },
            {
                data: "nama_program",
                name: "nama_program"
            },
            {
                data: "count_nominal",
                name: "count_nominal"
            },
            {
                data: "sum_nonimal",
                name: "sum_nonimal",
                className: "text-right"
            },
        ];
        let column_yearly = [{
                data: "DT_RowIndex",
                name: "DT_RowIndex"
            },
            {
                data: "nama_program",
                name: "nama_program"
            },
            {
                data: "count_nominal",
                name: "count_nominal"
            },
            {
                data: "jan",
                name: "jan"
            },
            {
                data: "feb",
                name: "feb"
            },
            {
                data: "mar",
                name: "mar"
            },
            {
                data: "apr",
                name: "apr"
            },
            {
                data: "mei",
                name: "mei"
            },
            {
                data: "jun",
                name: "jun"
            },
            {
                data: "jul",
                name: "jul"
            },
            {
                data: "agu",
                name: "agu"
            },
            {
                data: "sep",
                name: "sep"
            },
            {
                data: "okt",
                name: "okt"
            },
            {
                data: "nov",
                name: "nov"
            },
            {
                data: "des",
                name: "des"
            },
            {
                data: "sum_nonimal",
                name: "sum_nonimal",
                className: "text-right"
            },
        ];
    </script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
