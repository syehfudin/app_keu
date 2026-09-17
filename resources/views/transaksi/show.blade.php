@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
<style>
    .detail-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 0.15rem;
    }
    .detail-value {
        font-size: 1.05rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 0;
        word-break: break-word;
    }
    .info-box-transaksi {
        border-radius: 0.5rem;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    .badge-jenis {
        font-size: 0.95rem;
        padding: 0.45em 0.9em;
        border-radius: 0.35rem;
    }
    .table-program th, .table-program td {
        vertical-align: middle;
    }
    .card-detail-header {
        border-bottom: 1px solid rgba(0,0,0,.08);
    }
</style>
@endpush
@section('content')
@php
    $total_donasi = $transaksi_detail->sum('nominal_donasi');
    $jenisLabel = [
        'cash'      => 'Titip di Penghimpun',
        'transfer'  => 'Setoran Transfer',
        'rek_ulama' => 'Setoran ke Rek Ulama',
    ];
    $badgeClass = [
        'cash'      => 'badge-success',
        'transfer'  => 'badge-primary',
        'rek_ulama' => 'badge-warning',
    ];
    // Bukti transfer ditampilkan untuk Setoran Transfer & Setoran ke Rek Ulama
    $punyaBukti = in_array($transaksi->jenis_transaksi, ['transfer', 'rek_ulama']) && $transaksi->nama_file;
@endphp
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            <div class="card card-primary">
                <div class="card-header card-detail-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-receipt mr-1"></i> {{ $title }}
                        &nbsp;
                        <span class="badge badge-secondary">#{{ $transaksi->id }}</span>
                    </h3>
                </div>
                <div class="card-body">
                    {{-- ===== Ringkasan utama ===== --}}
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="info-box-transaksi p-3 mb-3">
                                <p class="detail-label">Tanggal Transaksi</p>
                                <p class="detail-value">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    {{ $transaksi->tanggal ? date('d-m-Y', strtotime($transaksi->tanggal)) : '-' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="info-box-transaksi p-3 mb-3">
                                <p class="detail-label">Jenis Pembayaran</p>
                                <p class="detail-value">
                                    <span class="badge badge-jenis {{ $badgeClass[$transaksi->jenis_transaksi] ?? 'badge-secondary' }}">
                                        {{ $jenisLabel[$transaksi->jenis_transaksi] ?? $transaksi->jenis_transaksi }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="info-box-transaksi p-3 mb-3">
                                <p class="detail-label">Nama Penghimpun</p>
                                <p class="detail-value">
                                    <i class="fas fa-user-tie mr-1"></i>{{ $transaksi->nama_relawan ?? '-' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="info-box-transaksi p-3 mb-3">
                                <p class="detail-label">Nama Nasabah</p>
                                <p class="detail-value">
                                    <i class="fas fa-user mr-1"></i>{{ $transaksi->nama_donatur ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Jenis Donasi (Program) ===== --}}
                    <div class="card card-outline card-primary mt-2">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">Jenis Donasi</h3>
                        </div>
                        <div class="card-body p-0">
                            @if($transaksi_detail->count())
                            <table class="table table-sm table-striped table-program mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:40px" class="text-center">No</th>
                                        <th>Program</th>
                                        <th class="text-right" style="width:180px">Nominal Donasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transaksi_detail as $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->nama_program }}</td>
                                        <td class="text-right">Rp {{ number_format($item->nominal_donasi, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-right">Total Donasi:</th>
                                        <th class="text-right text-danger font-weight-bold" style="font-size:1.1rem">
                                            Rp {{ number_format($total_donasi, 0, ',', '.') }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                            @else
                            <p class="text-muted text-center py-3 mb-0">
                                <i class="far fa-file-alt"></i> Tidak ada detail donasi
                            </p>
                            @endif
                        </div>
                    </div>

                    {{-- ===== Keterangan ===== --}}
                    <div class="info-box-transaksi p-3 mt-3">
                        <p class="detail-label">Keterangan</p>
                        <p class="detail-value" style="font-weight:400; white-space:pre-line;">
                            {{ $transaksi->keterangan ?: '-' }}
                        </p>
                    </div>

                    {{-- ===== Bukti Transfer (Setoran Transfer & Setoran ke Rek Ulama) ===== --}}
                    @if($punyaBukti)
                    <div class="card card-outline card-primary mt-3">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-file-image mr-1"></i> Bukti Transfer
                                <small class="text-muted">({{ $jenisLabel[$transaksi->jenis_transaksi] }})</small>
                            </h3>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ asset($transaksi->path . $transaksi->nama_file) }}"
                                 class="img-fluid rounded border" style="max-height:400px"
                                 alt="Bukti Transfer">
                            <div class="mt-2">
                                <a href="{{ asset($transaksi->path . $transaksi->nama_file) }}"
                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-search-plus"></i> Lihat Ukuran Penuh
                                </a>
                            </div>
                        </div>
                    </div>
                    @elseif(in_array($transaksi->jenis_transaksi, ['transfer', 'rek_ulama']))
                    <div class="card card-outline card-warning mt-3">
                        <div class="card-body text-center text-muted py-3">
                            <i class="far fa-file-excel"></i> Bukti transfer belum tersedia untuk transaksi ini
                            ({{ $jenisLabel[$transaksi->jenis_transaksi] }})
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a class="btn btn-secondary" href="{{ $redirectUrl }}">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <div>
                        <a class="btn btn-success" href="{{ route('transaksi.importPdf', ['id' => $transaksi->id]) }}" target="_blank">
                            <i class="fas fa-print"></i> Print / PDF
                        </a>
                        @can('transaksi-edit')
                        <a class="btn btn-primary" href="{{ route('transaksi.edit', $transaksi->id) }}">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('custom-js-files')
@endpush