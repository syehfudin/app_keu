@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.min.css') }}">
@endpush
@section('content')
<div class="container-fluid">
    <style>
        .table-freeze-wrapper{max-height:500px;overflow:auto;position:relative;}
        .table-freeze{margin:0;border-collapse:separate;border-spacing:0;}
        .table-freeze th,.table-freeze td{white-space:nowrap;font-size:11px;padding:4px 6px;}
        /* Kolom bulan: lebar sempit */
        .table-freeze th:nth-child(n+4),.table-freeze td:nth-child(n+4){min-width:65px;max-width:75px;}
        /* === FREEZE KOLOM (left) - berlaku untuk tbody + tfoot === */
        .table-freeze tbody td:nth-child(1),.table-freeze tfoot td:nth-child(1){position:sticky;left:0;min-width:35px;max-width:35px;background:#f8f9fa;z-index:2;}
        .table-freeze tbody td:nth-child(2),.table-freeze tfoot td:nth-child(2){position:sticky;left:35px;min-width:130px;max-width:130px;background:#f8f9fa;z-index:2;border-right:2px solid #dee2e6;overflow:hidden;text-overflow:ellipsis;}
        .table-freeze tbody td:nth-child(3),.table-freeze tfoot td:nth-child(3){position:sticky;left:165px;min-width:70px;max-width:70px;background:#f8f9fa;z-index:2;border-right:2px solid #dee2e6;}
        /* === FREEZE HEADER ROW 1 (nama bulan) - top:0 === */
        .table-freeze thead tr:first-child th{position:sticky;top:0;background:#f8f9fa;z-index:3;}
        .table-freeze thead tr:first-child th:nth-child(1){position:sticky;left:0;top:0;min-width:35px;max-width:35px;background:#f8f9fa;z-index:7;}
        .table-freeze thead tr:first-child th:nth-child(2){position:sticky;left:35px;top:0;min-width:130px;max-width:130px;background:#f8f9fa;z-index:7;border-right:2px solid #dee2e6;}
        .table-freeze thead tr:first-child th:nth-child(3){position:sticky;left:165px;top:0;min-width:70px;max-width:70px;background:#f8f9fa;z-index:7;border-right:2px solid #dee2e6;}
        /* === FREEZE HEADER ROW 2 (Nsb Tunai/Nominal) - top:28px === */
        .table-freeze thead tr:nth-child(2) th{position:sticky;top:28px;background:#f8f9fa;z-index:3;}
        .table-freeze thead tr:nth-child(2) th:nth-child(1){position:sticky;left:0;top:28px;min-width:35px;max-width:35px;background:#f8f9fa;z-index:7;}
        .table-freeze thead tr:nth-child(2) th:nth-child(2){position:sticky;left:35px;top:28px;min-width:130px;max-width:130px;background:#f8f9fa;z-index:7;border-right:2px solid #dee2e6;}
        .table-freeze thead tr:nth-child(2) th:nth-child(3){position:sticky;left:165px;top:28px;min-width:70px;max-width:70px;background:#f8f9fa;z-index:7;border-right:2px solid #dee2e6;}
        /* === FREEZE TFOOT (total) - bottom:0 + left untuk corner === */
        .table-freeze tfoot tr td{position:sticky;bottom:0;background:#f8f9fa;z-index:3;}
        .table-freeze tfoot tr td:nth-child(1){position:sticky;left:0;bottom:0;min-width:35px;max-width:35px;background:#f8f9fa;z-index:5;}
        .table-freeze tfoot tr td:nth-child(2){position:sticky;left:35px;bottom:0;min-width:130px;max-width:130px;background:#f8f9fa;z-index:5;border-right:2px solid #dee2e6;}
        .table-freeze tfoot tr td:nth-child(3){position:sticky;left:165px;bottom:0;min-width:70px;max-width:70px;background:#f8f9fa;z-index:5;border-right:2px solid #dee2e6;}
    </style>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('home') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Filter Tanggal</label>
                            <input type="text" name="tanggal" id="filterTanggal" class="form-control" value="{{ $tanggalInput }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i> Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('home') }}" class="btn btn-secondary btn-block"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 1: Harian Penghimpun + Harian Supervisor --}}
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-success">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-day"></i> Report Harian Penghimpun - {{ $tanggalDisplay }}</h3><div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div></div>
                <div class="card-body p-0">
                    <table class="table table-striped table-bordered mb-0">
                        <thead><tr><th>No</th><th>Nama Penghimpun</th><th class="text-center">Jumlah Nasabah</th><th class="text-right">Nominal</th></tr></thead>
                        <tbody>
                            @foreach($dailyPenghimpun as $i => $row)
                            <tr><td>{{ $i+1 }}</td><td>{{ $row->nama_penghimpun }}</td><td class="text-center">{{ $row->jumlah_nasabah }}</td><td class="text-right">Rp {{ number_format($row->total_nominal, 0, ',', '.') }}</td></tr>
                            @endforeach
                            @if($dailyPenghimpun->isEmpty())<tr><td colspan="4" class="text-center text-muted">Tidak ada data</td></tr>@endif
                        </tbody>
                        @if($dailyPenghimpun->isNotEmpty())
                        <tfoot><tr style="font-weight:bold;background:#f8f9fa;"><td colspan="2" class="text-right">TOTAL</td><td class="text-center">{{ $dailyPenghimpun->sum('jumlah_nasabah') }}</td><td class="text-right">Rp {{ number_format($dailyPenghimpun->sum('total_nominal'), 0, ',', '.') }}</td></tr></tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        @if($isAdminOrManager)
        <div class="col-lg-6">
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-day"></i> Report Supervisor Harian - {{ $tanggalDisplay }}</h3><div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div></div>
                <div class="card-body p-0">
                    <table class="table table-striped table-bordered mb-0">
                        <thead><tr><th>No</th><th>Nama Supervisor</th><th class="text-center">Nasabah Terdaftar</th><th class="text-center">Jumlah Nasabah</th><th class="text-right">Nominal</th></tr></thead>
                        <tbody>
                            @foreach($dailySupervisor as $i => $row)
                            <tr><td>{{ $i+1 }}</td><td>{{ $row->nama_supervisor }}</td><td class="text-center">{{ $row->jumlah_nasabah_terdaftar }}</td><td class="text-center">{{ $row->jumlah_nasabah }}</td><td class="text-right">Rp {{ number_format($row->total_nominal, 0, ',', '.') }}</td></tr>
                            @endforeach
                            @if($dailySupervisor->isEmpty())<tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>@endif
                        </tbody>
                        @if($dailySupervisor->isNotEmpty())
                        <tfoot><tr style="font-weight:bold;background:#f8f9fa;"><td colspan="3" class="text-right">TOTAL</td><td class="text-center">{{ $dailySupervisor->sum('jumlah_nasabah') }}</td><td class="text-right">Rp {{ number_format($dailySupervisor->sum('total_nominal'), 0, ',', '.') }}</td></tr></tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ROW 2: Bulanan Penghimpun + Bulanan Supervisor --}}
    <div class="row mt-3">
        <div class="col-lg-6">
            <div class="card card-info">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt"></i> Report Bulanan Penghimpun - {{ $bulanDisplay }}</h3><div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div></div>
                <div class="card-body p-0">
                    <table class="table table-striped table-bordered mb-0">
                        <thead><tr><th>No</th><th>Nama Penghimpun</th><th class="text-center">Nasabah Terdaftar</th><th class="text-center">Jumlah Nasabah</th><th class="text-right">Nominal</th></tr></thead>
                        <tbody>
                            @foreach($monthlyPenghimpun as $i => $row)
                            <tr><td>{{ $i+1 }}</td><td>{{ $row->nama_penghimpun }}</td><td class="text-center">{{ $row->jumlah_nasabah_terdaftar }}</td><td class="text-center">{{ $row->jumlah_nasabah }}</td><td class="text-right">Rp {{ number_format($row->total_nominal, 0, ',', '.') }}</td></tr>
                            @endforeach
                            @if($monthlyPenghimpun->isEmpty())<tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>@endif
                        </tbody>
                        @if($monthlyPenghimpun->isNotEmpty())
                        <tfoot><tr style="font-weight:bold;background:#f8f9fa;"><td colspan="3" class="text-right">TOTAL</td><td class="text-center">{{ $monthlyPenghimpun->sum('jumlah_nasabah') }}</td><td class="text-right">Rp {{ number_format($monthlyPenghimpun->sum('total_nominal'), 0, ',', '.') }}</td></tr></tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        @if($isAdminOrManager)
        <div class="col-lg-6">
            <div class="card card-warning">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt"></i> Report Supervisor Bulanan - {{ $bulanDisplay }}</h3><div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div></div>
                <div class="card-body p-0">
                    <table class="table table-striped table-bordered mb-0">
                        <thead><tr><th>No</th><th>Nama Supervisor</th><th class="text-center">Nasabah Terdaftar</th><th class="text-center">Jumlah Nasabah</th><th class="text-right">Nominal</th></tr></thead>
                        <tbody>
                            @foreach($monthlySupervisor as $i => $row)
                            <tr><td>{{ $i+1 }}</td><td>{{ $row->nama_supervisor }}</td><td class="text-center">{{ $row->jumlah_nasabah_terdaftar }}</td><td class="text-center">{{ $row->jumlah_nasabah }}</td><td class="text-right">Rp {{ number_format($row->total_nominal, 0, ',', '.') }}</td></tr>
                            @endforeach
                            @if($monthlySupervisor->isEmpty())<tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>@endif
                        </tbody>
                        @if($monthlySupervisor->isNotEmpty())
                        <tfoot><tr style="font-weight:bold;background:#f8f9fa;"><td colspan="3" class="text-right">TOTAL</td><td class="text-center">{{ $monthlySupervisor->sum('jumlah_nasabah') }}</td><td class="text-right">Rp {{ number_format($monthlySupervisor->sum('total_nominal'), 0, ',', '.') }}</td></tr></tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ROW 3: Tahunan Penghimpun (all roles) --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-success">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar"></i> Report Tahunan per Penghimpun - {{ $selectedYear }}</h3><div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div></div>
                <div class="card-body p-0">
                    <div class="table-freeze-wrapper">
                        <table class="table table-striped table-bordered table-sm table-freeze">
                            <thead>
                                <tr>
                                    <th rowspan="2">No</th>
                                    <th rowspan="2">Nama Penghimpun</th>
                                    <th rowspan="2" class="text-center">Nasabah<br>Terdaftar</th>
                                    <th colspan="2" class="text-center">Jan</th><th colspan="2" class="text-center">Feb</th><th colspan="2" class="text-center">Mar</th><th colspan="2" class="text-center">Apr</th><th colspan="2" class="text-center">Mei</th><th colspan="2" class="text-center">Jun</th><th colspan="2" class="text-center">Jul</th><th colspan="2" class="text-center">Agu</th><th colspan="2" class="text-center">Sep</th><th colspan="2" class="text-center">Okt</th><th colspan="2" class="text-center">Nov</th><th colspan="2" class="text-center">Des</th>
                                </tr>
                                <tr>
                                    @for($m=1;$m<=12;$m++)<th class="text-center">Nsb Tunai</th><th class="text-right">Nominal</th>@endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($yearlyPenghimpun as $i => $ph)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $ph->nama_penghimpun }}</td>
                                    <td class="text-center"><strong>{{ $ph->jumlah_nasabah_terdaftar }}</strong></td>
                                    @for($m=1;$m<=12;$m++)
                                        <td class="text-center">{{ $ph->{'m'.$m.'_nasabah'} }}</td>
                                        <td class="text-right" style="font-size:11px;">Rp {{ number_format($ph->{'m'.$m.'_nominal'}, 0, ',', '.') }}</td>
                                    @endfor
                                </tr>
                                @endforeach
                                @if($yearlyPenghimpun->isEmpty())<tr><td colspan="29" class="text-center text-muted">Tidak ada data</td></tr>@endif
                            </tbody>
                            @if($yearlyPenghimpun->isNotEmpty())
                            <tfoot style="font-weight:bold;background:#f8f9fa;">
                                <tr>
                                    <td colspan="3" class="text-right">TOTAL</td>
                                    @for($m=1;$m<=12;$m++)
                                        <td class="text-center">{{ $yearlyPenghimpun->sum('m'.$m.'_nasabah') }}</td>
                                        <td class="text-right" style="font-size:11px;">Rp {{ number_format($yearlyPenghimpun->sum('m'.$m.'_nominal'), 0, ',', '.') }}</td>
                                    @endfor
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 4: Tahunan Supervisor (admin/manager only) --}}
    @if($isAdminOrManager)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-danger">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar"></i> Report Tahunan per Supervisor - {{ $selectedYear }}</h3><div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div></div>
                <div class="card-body p-0">
                    <div class="table-freeze-wrapper">
                        <table class="table table-striped table-bordered table-sm table-freeze">
                            <thead>
                                <tr>
                                    <th rowspan="2">No</th>
                                    <th rowspan="2">Nama Supervisor</th>
                                    <th rowspan="2" class="text-center">Nasabah<br>Terdaftar</th>
                                    <th colspan="2" class="text-center">Jan</th><th colspan="2" class="text-center">Feb</th><th colspan="2" class="text-center">Mar</th><th colspan="2" class="text-center">Apr</th><th colspan="2" class="text-center">Mei</th><th colspan="2" class="text-center">Jun</th><th colspan="2" class="text-center">Jul</th><th colspan="2" class="text-center">Agu</th><th colspan="2" class="text-center">Sep</th><th colspan="2" class="text-center">Okt</th><th colspan="2" class="text-center">Nov</th><th colspan="2" class="text-center">Des</th>
                                </tr>
                                <tr>
                                    @for($m=1;$m<=12;$m++)<th class="text-center">Nsb Tunai</th><th class="text-right">Nominal</th>@endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($yearlySupervisor as $i => $sup)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $sup->nama_supervisor }}</td>
                                    <td class="text-center"><strong>{{ $sup->jumlah_nasabah_terdaftar }}</strong></td>
                                    @for($m=1;$m<=12;$m++)
                                        <td class="text-center">{{ $sup->{'m'.$m.'_nasabah'} }}</td>
                                        <td class="text-right" style="font-size:11px;">Rp {{ number_format($sup->{'m'.$m.'_nominal'}, 0, ',', '.') }}</td>
                                    @endfor
                                </tr>
                                @endforeach
                                @if($yearlySupervisor->isEmpty())<tr><td colspan="29" class="text-center text-muted">Tidak ada data</td></tr>@endif
                            </tbody>
                            @if($yearlySupervisor->isNotEmpty())
                            <tfoot style="font-weight:bold;background:#f8f9fa;">
                                <tr>
                                    <td colspan="3" class="text-right">TOTAL</td>
                                    @for($m=1;$m<=12;$m++)
                                        <td class="text-center">{{ $yearlySupervisor->sum('m'.$m.'_nasabah') }}</td>
                                        <td class="text-right" style="font-size:11px;">Rp {{ number_format($yearlySupervisor->sum('m'.$m.'_nominal'), 0, ',', '.') }}</td>
                                    @endfor
                                </tr>
                                <tr style="color:#28a745;">
                                    <td colspan="3" class="text-right">TOTAL SUDAH SETOR</td>
                                    @for($m=1;$m<=12;$m++)
                                        <td></td>
                                        <td class="text-right" style="font-size:11px;">Rp {{ number_format($yearlySupervisor->sum('m'.$m.'_sudah'), 0, ',', '.') }}</td>
                                    @endfor
                                </tr>
                                <tr style="color:#dc3545;">
                                    <td colspan="3" class="text-right">TOTAL BELUM SETOR</td>
                                    @for($m=1;$m<=12;$m++)
                                        <td></td>
                                        <td class="text-right" style="font-size:11px;">Rp {{ number_format($yearlySupervisor->sum('m'.$m.'_belum'), 0, ',', '.') }}</td>
                                    @endfor
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 5: Report Setoran per Supervisor per Bulan (admin/manager only) --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-info">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-exchange-alt"></i> Report Setoran per Supervisor per Bulan - {{ $selectedYear }}</h3><div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div></div>
                <div class="card-body p-0">
                    @php
                        $bulanShort = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
                    @endphp
                    <div class="table-freeze-wrapper" style="max-height:600px;">
                        <table class="table table-bordered table-sm mb-0 table-freeze" style="font-size:11px;">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="text-center" style="min-width:35px;">No</th>
                                    <th rowspan="2" style="min-width:80px;">Bulan</th>
                                    <th rowspan="2" style="min-width:150px;">Nama Supervisor</th>
                                    <th rowspan="2" style="min-width:70px;" class="text-center">Status</th>
                                    @for($m=1;$m<=12;$m++)<th colspan="2" class="text-center" style="min-width:130px;">{{ $bulanShort[$m] }}</th>@endfor
                                    <th colspan="2" class="text-center" style="min-width:130px;background:#e9ecef;">TOTAL</th>
                                </tr>
                                <tr>
                                    @for($m=1;$m<=12;$m++)<th class="text-right" style="min-width:65px;color:#28a745;">Sudah</th><th class="text-right" style="min-width:65px;color:#dc3545;">Belum</th>@endfor
                                    <th class="text-right" style="min-width:65px;color:#28a745;background:#e9ecef;">Sudah</th>
                                    <th class="text-right" style="min-width:65px;color:#dc3545;background:#e9ecef;">Belum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 0; @endphp
                                @foreach($yearlySupervisor as $sup)
                                    @php $no++; $totalSudah=0; $totalBelum=0; for($m=1;$m<=12;$m++){$totalSudah+=$sup->{'m'.$m.'_sudah'};$totalBelum+=$sup->{'m'.$m.'_belum'};} @endphp
                                    <tr style="font-weight:bold;background:#f0f7ff;"><td class="text-center">{{ $no }}</td><td colspan="2">{{ $sup->nama_supervisor }}</td><td class="text-center"><i class="fas fa-user-tie"></i></td>@for($m=1;$m<=12;$m++)<td colspan="2" style="background:#e9ecef;"></td>@endfor<td colspan="2" style="background:#e9ecef;"></td></tr>
                                    <tr><td></td><td></td><td></td><td class="text-center" style="color:#28a745;font-size:10px;">Sudah</td>@for($m=1;$m<=12;$m++)<td class="text-right">{{ $sup->{'m'.$m.'_sudah'}>0?number_format($sup->{'m'.$m.'_sudah'},0,',','.'):'-' }}</td><td></td>@endfor<td class="text-right" style="background:#e9ecef;font-weight:bold;">{{ number_format($totalSudah,0,',','.') }}</td><td style="background:#e9ecef;"></td></tr>
                                    <tr><td></td><td></td><td></td><td class="text-center" style="color:#dc3545;font-size:10px;">Belum</td>@for($m=1;$m<=12;$m++)<td></td><td class="text-right">{{ $sup->{'m'.$m.'_belum'}>0?number_format($sup->{'m'.$m.'_belum'},0,',','.'):'-' }}</td>@endfor<td style="background:#e9ecef;"></td><td class="text-right" style="background:#e9ecef;font-weight:bold;">{{ number_format($totalBelum,0,',','.') }}</td></tr>
                                @endforeach
                                @if($yearlySupervisor->isEmpty())<tr><td colspan="31" class="text-center text-muted">Tidak ada data</td></tr>@endif
                            </tbody>
                            <tfoot style="font-weight:bold;background:#f8f9fa;">
                                <tr style="color:#28a745;"><td colspan="4" class="text-right">TOTAL SUDAH SETOR</td>@for($m=1;$m<=12;$m++)<td class="text-right">{{ number_format($yearlySupervisor->sum('m'.$m.'_sudah'),0,',','.') }}</td><td></td>@endfor<td class="text-right">{{ number_format(collect(range(1,12))->sum(function($m)use($yearlySupervisor){return $yearlySupervisor->sum('m'.$m.'_sudah');}),0,',','.') }}</td><td></td></tr>
                                <tr style="color:#dc3545;"><td colspan="4" class="text-right">TOTAL BELUM SETOR</td>@for($m=1;$m<=12;$m++)<td></td><td class="text-right">{{ number_format($yearlySupervisor->sum('m'.$m.'_belum'),0,',','.') }}</td>@endfor<td></td><td class="text-right">{{ number_format(collect(range(1,12))->sum(function($m)use($yearlySupervisor){return $yearlySupervisor->sum('m'.$m.'_belum');}),0,',','.') }}</td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
@push('custom-js-files')
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {
    $("#filterTanggal").datepicker({ dateFormat: 'dd-mm-yy', onSelect: function() { this.form.submit(); } });
});
</script>
@endpush