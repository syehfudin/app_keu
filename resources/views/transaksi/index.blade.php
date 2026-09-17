@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
<style>
    /* Konsistensi tampilan kolom Jenis Donasi dengan kolom lain */
    #datatable-transaksi td {
        vertical-align: middle;
    }
    #datatable-transaksi thead th {
        font-size: 1rem;
        font-weight: 700;
        text-align: center;
        vertical-align: middle !important;
    }
    .jenis-donasi-cell {
        font-size: 0.9rem;
        line-height: 1.5;
    }
    .jenis-donasi-cell div {
        padding: 1px 0;
    }
    /* Sticky header tabel agar header tetap terlihat saat scroll */
    #datatable-transaksi thead th {
        background-color: #f4f6f9;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    #transaksi_filter input[type="search"] {
        width: 220px;
        display: inline-block;
        margin-left: 8px;
    }
</style>
@endpush
@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center py-2 py-md-2">
        @can('transaksi-create')
        <a class="btn btn-success" href="{{ route('transaksi.create') }}"> Tambah Transaksi</a>
        @endcan
    </div>
    <div class="row">
        <div class="col-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Transaksi</h3>
                </div>
                <div class="card-body">
                    <form id="filterForm" class="row">
                        <div class="col-md-3">
                            <label class="fs-6 fw-bold mb-2">Tanggal Mulai</label>
                            <input type="text" class="form-control datepicker-filter" id="tanggal_mulai" name="tanggal_mulai" placeholder="dd-mm-yyyy" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label class="fs-6 fw-bold mb-2">Tanggal Sampai</label>
                            <input type="text" class="form-control datepicker-filter" id="tanggal_sampai" name="tanggal_sampai" placeholder="dd-mm-yyyy" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="fs-6 fw-bold mb-2">Penghimpun</label>
                            <select class="form-control" id="filter_pegawai_id" name="pegawai_id">
                                <option value="">Semua Penghimpun</option>
                                @foreach($penghimpunList as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="d-flex w-100">
                                <button type="button" class="btn btn-primary flex-fill mr-1" id="btnFilter">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <button type="button" class="btn btn-secondary" id="btnReset" title="Reset Filter">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped" id="datatable-transaksi">
                        <thead>
                            <tr align="center">
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Penghimpun</th>
                                <th>Nama Nasabah</th>
                                <th>Jenis Donasi</th>
                                <th>Jenis Pembayaran</th>
                                <th>Total Donasi</th>
                                <th>Keterangan</th>
                                <th width="280px">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-right">Total Semua Donasi:</th>
                                <th class="text-right" id="footer_total_donasi">Rp 0</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
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
<script src="{{ asset('js/jquery.maskMoney.js') }}"></script>
<script type="text/javascript">
    let dataUrl = "{{ route('transaksi.index_data') }}";
    let tableSelector = "datatable-transaksi";

    // Inisialisasi jQuery UI datepicker untuk filter (sudah loaded via layout)
    $(".datepicker-filter").datepicker({
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true,
    });

    dt = $("#" + tableSelector).DataTable({
        order: [1, 'desc'],
        columnDefs: [
            {
                // Tampilkan jenis donasi dengan baris program: nominal
                targets: [4],
                className: 'jenis-donasi-cell',
                orderable: false
            },
            {
                targets: [6],
                render: function (data, type, row) {
                    if (type === 'display' || type === 'filter') {
                        let total = new Intl.NumberFormat().format(data)
                        return 'Rp. ' + total;
                    }
                    return data;
                }
            },
            {
                // Kolom No(0), Tanggal(1), Penghimpun(2), Jenis Donasi(4),
                // Jenis Pembayaran(5), Keterangan(7), Action(8) TIDAK ikut
                // pencarian. Hanya kolom Nama Nasabah (3) yang dapat dicari
                // lewat search box, agar pencarian hanya mencocokkan nama
                // nasabah, bukan kolom lain.
                targets: [0, 1, 2, 4, 5, 7, 8],
                searchable: false
            },
            {
                // Kolom No & Action tidak bisa diurutkan
                targets: [0, 8],
                orderable: false
            }
        ],
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "searching": true,
        // Kirim parameter filter tambahan via AJAX data
        "ajax": {
            url: dataUrl,
            type: "GET",
            data: function (d) {
                d.tanggal_mulai = $("#tanggal_mulai").val();
                d.tanggal_sampai = $("#tanggal_sampai").val();
                d.pegawai_id = $("#filter_pegawai_id").val();
            },
            dataSrc: function (json) {
                // Hitung total donasi dari seluruh baris hasil filter
                // dan tampilkan di footer tabel.
                let total = 0;
                (json.data || []).forEach(function (row) {
                    total += parseFloat(row.total_donasi) || 0;
                });
                $("#footer_total_donasi").text('Rp. ' + new Intl.NumberFormat().format(total));
                return json.data;
            }
        },
        columns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "tanggal_donasi", name: "tanggal_donasi" },
            { data: "nama_relawan", name: "nama_relawan" },
            { data: "nama_donatur", name: "nama_donatur" },
            { data: "jenis_donasi", name: "jenis_donasi" },
            { data: "jenis_transaksi", name: "jenis_transaksi" },
            { data: "total_donasi", name: "total_donasi", class: 'text-right' },
            { data: "keterangan", name: "keterangan" },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
            },
        ],
    });

    // Tombol Filter: trigger reload AJAX dengan parameter baru
    $("#btnFilter").on('click', function() {
        dt.ajax.reload();
    });

    // Auto-reload saat dropdown penghimpun berubah (UX cepat)
    $("#filter_pegawai_id").on('change', function() {
        dt.ajax.reload();
    });

    // Tombol Reset: kosongkan semua filter lalu reload
    $("#btnReset").on('click', function() {
        $("#tanggal_mulai").val('');
        $("#tanggal_sampai").val('');
        $("#filter_pegawai_id").val('').trigger('change');
        dt.search('').draw();
        dt.ajax.reload();
    });

    // Reset juga ketika search box dikosongkan
    $('input[type="search"]').on('input', function() {
        if (!$(this).val()) {
            dt.search('').draw();
        }
    });

    table = dt.$;
</script>
@endpush