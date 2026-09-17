@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush
@section('content')
@php
    $role = strtolower(Auth::user()->roles[0]->name);
    $pegawai_id = Auth::user()->pegawai_id;
    $punyaBukti = in_array($transaksi->jenis_transaksi, ['transfer', 'rek_ulama']) && $transaksi->nama_file;
@endphp
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12 col-lg-12">
            <form action="{{ $action }}" method="POST" autocomplete="off" enctype="multipart/form-data">
                @csrf
                {{-- Simpan pegawai_id & donatur_id asli (tidak bisa diubah saat edit) --}}
                {!! Form::hidden('pegawai_id', $transaksi->pegawai_id) !!}
                {!! Form::hidden('donatur_id', $transaksi->donatur_id) !!}
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Form {{ $title }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Tanggal <span class="text-primary">(dapat diubah)</span></label>
                            {!! Form::text('tanggal', @$transaksi->tanggal ? date('d-m-Y',  strtotime(@$transaksi->tanggal)) : date('d-m-Y'), array('class' => 'form-control', 'id' => 'datepicker')) !!}
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">
                                <span class="required">Nama Penghimpun</span> <span class="text-muted">(tidak dapat diubah)</span>
                            </label>
                            {{-- Tampil read-only; nilainya dikirim via hidden field pegawai_id di atas --}}
                            <select class="form-control" disabled>
                                @foreach($relawan as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == @$transaksi->pegawai_id ? 'selected' : '' }} >{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Nama Nasabah <span class="text-muted">(tidak dapat diubah)</span></label>
                            {{-- Tampil read-only; nilainya dikirim via hidden field donatur_id di atas --}}
                            <select class="form-control select2" disabled>
                                <option value="">{{ $transaksi->nama_donatur ?? '-' }}</option>
                            </select>
                        </div>
                        <h5>Program</h5>
                        @php $total_donasi = 0 @endphp
                        @foreach($program as $item)
                        <div class="mb-3 donasi">
                            <label class="fs-6 fw-bold mb-2">{{ $item->nama }} <span class="text-primary">(nominal dapat diubah)</span></label>
                            {!! Form::text('nominal_donasi[]', @$item->nominal_donasi, array('placeholder' => 'Masukan nominal yang akan didonasikan','class' => 'form-control nominal')) !!}
                            {!! Form::hidden('program_id[]', $item->id) !!}
                        </div>
                        @php
                            if(@$transaksi){
                                $nominal = @$item->nominal_donasi ? @$item->nominal_donasi : 0;
                                $total_donasi += $nominal;
                            }else{
                                $total_donasi = "";
                            }
                        @endphp
                        @endforeach
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Total Donasi</label>
                            {!! Form::text('total_donasi', @$total_donasi, array('placeholder' => 'Total donatur','class' => 'form-control total', 'readonly', @$show)) !!}
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Keterangan <span class="text-primary">(dapat diubah)</span></label>
                            {!! Form::textarea('keterangan', @$transaksi->keterangan, array('placeholder' => '','class' => 'form-control', 'rows' => '6')) !!}
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Jenis Transaksi <span class="text-primary">(dapat diubah)</span></label>
                            {!! Form::select('jenis_transaksi', array('cash' => 'Titip di Penghimpun', 'transfer' => 'Setoran Transfer', 'rek_ulama' => 'Setoran ke Rek Ulama'), $transaksi->jenis_transaksi, array('class' => 'form-control jt')) !!}
                        </div>
                        {{-- Bukti transfer: tampil & dapat diganti utk Setoran Transfer / Setoran ke Rek Ulama --}}
                        @if(in_array($transaksi->jenis_transaksi, ['transfer', 'rek_ulama']))
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Bukti Transfer <span class="text-primary">(dapat diganti)</span></label>
                            @if($punyaBukti)
                            <div class="mb-2">
                                <img src="{{ asset($transaksi->path . $transaksi->nama_file) }}" class="img-fluid rounded border" style="max-height:200px" alt="Bukti Transfer">
                                <small class="d-block text-muted mt-1">Bukti saat ini: {{ $transaksi->nama_file }}</small>
                            </div>
                            <label class="fs-6 mb-2 text-muted">Ganti dengan file baru (opsional)</label>
                            @else
                            <p class="text-muted mb-2">Bukti transfer belum tersedia — silakan upload</p>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        @if(!@$show)
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        @endif
                        <a class="btn btn-secondary" href="{{ $redirectUrl }}"> Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('custom-js-files')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('js/jquery.maskMoney.js') }}"></script>
<script type="text/javascript">
    $(".select2").select2();

    $( "#datepicker" ).datepicker({
        dateFormat: 'dd-mm-yy'
    });

    $(".jt").on('change', function() {
        let jt = $(this).val();
        setUpload(jt);
    });

    $(".nominal").maskMoney({prefix:'Rp ', allowNegative: true, thousands:',', affixesStay: true, precision: 0});

    $(".nominal").on('keyup', () => {
        let nominal = document.getElementsByClassName('nominal');
        let total_donasi = 0;
        for (let i = 0; i < nominal.length; i++) {
            let currency = nominal[i].value;
            let cur = Number(currency.replace(/[^0-9.-]+/g,""));

            total_donasi = total_donasi + cur;
        }

        let total = new Intl.NumberFormat().format(total_donasi)
        $(".total").val("Rp "+ total);
    });

    let setUpload = (jt) => {
        if(jt == 'cash'){
            $(".upload").attr('style', 'display:none');
        }else{
            $(".upload").attr('style', 'display:block');
        }
    }
</script>
@endpush