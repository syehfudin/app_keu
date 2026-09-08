@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush
@section('content')
@php
    $total_donasi = 0;
    setlocale(LC_MONETARY, 'en_US');
@endphp
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12 col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form {{ $title }}</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fs-6 fw-bold mb-2">
                            <span class="required">Nama Penghimpun</span>
                        </label>
                        <select class="form-control" {{ $show }}>
                            @foreach($relawan as $item)
                                <option value="{{ $item->id }}" {{ $item->id == @$transaksi->pegawai_id ? 'selected' : ''  }}>{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fs-6 fw-bold mb-2">Nama Nasabah</label>
                        <select class="form-control" {{ $show }}>
                            @foreach($donatur as $item)
                                <option value="{{ $item->id }}" {{ $item->id == $transaksi->donatur_id ? 'selected' : '' }}>{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <h5 class="card-title">Program</h5>
                    @foreach($transaksi_detail as $item)
                    <div class="mb-3 donasi">
                        <label class="fs-6 fw-bold mb-2">{{ $item->nama_program }}</label>
                        {!! Form::text('nominal_donasi[]', 'Rp '.number_format($item->nominal_donasi,0,',','.'), array('class' => 'form-control nominal', $show)) !!}
                        @php $total_donasi += $item->nominal_donasi @endphp
                    </div>
                    @endforeach
                    <div class="mb-3">
                        <label class="fs-6 fw-bold mb-2">Total Donasi</label>
                        {!! Form::text('total_donasi', 'Rp '.number_format($total_donasi,0,',','.'), array('placeholder' => 'Total donatur','class' => 'form-control total', 'readonly', @$show)) !!}
                    </div>
                    <div class="mb-3">
                        <label class="fs-6 fw-bold mb-2">Jenis Transaksi</label>
                        {!! Form::select('jenis_transaksi', array('cash' => 'Titip di Penghimpun', 'transfer' => 'Transfer ke Rek ULAMA'), $transaksi->jenis_transaksi, array('class' => 'form-control jt', $show)) !!}
                    </div>
                    @if($transaksi->jenis_transaksi == 'transfer')
                    <div class="mb-3">
                        <label class="fs-6 fw-bold mb-2">Bukti Transfer</label>
                        <img src="{{ asset($transaksi->path . $transaksi->nama_file) }}" class="img-fluid pe-2" alt="Unsplash">
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a class="btn btn-primary" href="{{ $redirectUrl }}"> Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('custom-js-files')
@endpush
