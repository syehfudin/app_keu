@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush
@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('tunai.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Bulan</label>
                            <select name="bulan" class="form-control" onchange="this.form.submit()">
                                @foreach($bulanList as $key => $val)
                                    <option value="{{ $key }}" {{ $key == $bulan ? 'selected' : '' }}>{{ $val }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tahun</label>
                            <select name="tahun" class="form-control" onchange="this.form.submit()">
                                @php
                                    $currentYear = date('Y');
                                    for ($y = $currentYear; $y >= $currentYear - 5; $y--):
                                @endphp
                                    <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                                @php endfor; @endphp
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                            <a href="{{ route('tunai.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalTransaksi, 0, ',', '.') }}</h3>
                    <p>Total Transaksi - {{ $bulanList[$bulan] }} {{ $tahun }}</p>
                </div>
                <i class="fas fa-receipt" style="font-size: 50px; opacity: 0.5; position: absolute; right: 15px; top: 15px;"></i>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>Rp {{ number_format($totalSemua, 0, ',', '.') }}</h3>
                    <p>Total Donasi - {{ $bulanList[$bulan] }} {{ $tahun }}</p>
                </div>
                <i class="fas fa-money-bill-wave" style="font-size: 50px; opacity: 0.5; position: absolute; right: 15px; top: 15px;"></i>
            </div>
        </div>
    </div>

    @php
        $colCount = 7;
        $isAdmin = auth()->user()->hasRole('Admin');
        if (!$isAdmin) { $colCount = 6; }
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> {{ $title }} - {{ $bulanList[$bulan] }} {{ $tahun }}</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-bordered" id="datatable-tunai">
                        <thead>
                            <tr>
                                <th width="50px">No</th>
                                <th>Nama Nasabah</th>
                                @role('Admin')
                                <th>Penghimpun</th>
                                @endrole
                                <th>No Telepon</th>
                                <th>Alamat</th>
                                <th class="text-center">Jumlah Transaksi</th>
                                <th class="text-right">Total Donasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                            <tr>
                                <td></td>
                                <td>{{ $item->nama }}</td>
                                @role('Admin')
                                <td>{{ $item->nama_penghimpun }}</td>
                                @endrole
                                <td>{{ $item->no_telepon ?? '-' }}</td>
                                <td>{{ $item->alamat ?? '-' }}</td>
                                <td class="text-center">
                                    @if($item->jumlah_transaksi > 0)
                                        <span class="badge badge-success">{{ $item->jumlah_transaksi }}</span>
                                    @else
                                        <span class="badge badge-secondary">0</span>
                                    @endif
                                </td>
                                <td class="text-right">Rp {{ number_format($item->total_donasi, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background-color: #f8f9fa;">
                                <td colspan="{{ $colCount - 2 }}" class="text-right">TOTAL</td>
                                <td class="text-center">{{ $totalTransaksi }}</td>
                                <td class="text-right">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
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
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script type="text/javascript">
    $('#datatable-tunai').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "dom": 'Bfrtip',
        "buttons": [
            { extend: 'copyHtml5', text: '<i class="fas fa-copy"></i> Copy', className: 'btn btn-sm btn-default' },
            { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-sm btn-success' },
            { extend: 'print', text: '<i class="fas fa-print"></i> Print', className: 'btn btn-sm btn-default' }
        ],
        "columnDefs": [{
            targets: 0,
            render: function(data, type, row, meta) {
                return meta.row + 1;
            }
        }],
        "order": [[5, 'desc']]
    });
</script>
@endpush
