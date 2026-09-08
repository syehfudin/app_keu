@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush
@section('content')
@php
    $role = strtolower(Auth::user()->roles[0]->name);
    $pegawai_id = Auth::user()->pegawai_id;
@endphp
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12 col-lg-12">
            <form action="{{ $action }}" method="POST" autocomplete="off" enctype="multipart/form-data">
                @csrf
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Form {{ $title }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Tanggal</label>
                            {!! Form::text('tanggal', @$transaksi->tanggal ? date('d-m-Y',  strtotime(@$transaksi->tanggal)) : date('d-m-Y'), array('class' => 'form-control', 'id' => 'datepicker')) !!}
                        </div>
                        @if($role != 'relawan')
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">
                                <span class="required">Nama Penghimpun</span>
                            </label>
                            <input type="hidden" name="pegawai_id" value="{{ $transaksi->pegawai_id }}">
                            <select class="form-control" name="pegawai_id" disabled>
                                @foreach($relawan as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == @$transaksi->pegawai_id ? 'selected' : '' }} >{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                            {!! Form::hidden('pegawai_id', $pegawai_id) !!}
                        @endif
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Nama Nasabah</label>
                            <select class="form-control select2" name="donatur_id">
                                <option value="">Pilih Nasabah ...</option>
                                @foreach($donatur as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == @$transaksi->donatur_id ? 'selected' : '' }}>{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <h5>Program</h5>
                        @php $total_donasi = 0 @endphp
                        @foreach($program as $item)
                        <div class="mb-3 donasi">
                            <label class="fs-6 fw-bold mb-2">{{ $item->nama }}</label>
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
                            <label class="fs-6 fw-bold mb-2">Keterangan</label>
                            {!! Form::textarea('keterangan', @$transaksi->keterangan, array('placeholder' => '','class' => 'form-control', 'rows' => '6')) !!}
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Jenis Transaksi</label>
                            {!! Form::select('jenis_transaksi', array('cash' => 'Titip di Penghimpun', 'transfer' => 'Setoran Transfer'), [], array('class' => 'form-control jt')) !!}
                        </div>
                        <div class="mb-3 upload">
                            <label class="fs-6 fw-bold mb-2">Upload File Bukti Transfer</label>
                            <input
                                type="file"
                                name="image"
                                class="form-control @error('image') is-invalid @enderror">
                        </div>
                    </div>
                    <div class="card-footer">
                        @if(!@$show)
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        @endif
                        <a class="btn btn-primary" href="{{ $redirectUrl }}"> Back</a>
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
    $(".status").on('click', function(){
        let status = $(this).val();
        setDonatur(status);
    });

    $(".jt").on('change', function() {
        let jt = $(this).val();
        setUpload(jt);
    });

    $(".pekerjaan").on('click', function(){
        let status = $(this).val();
        if(status == 'lainnya'){
            $(".lainnya").attr("disabled", false);
        }else{
            $(".lainnya").attr("disabled", true);
            $(".lainnya").val('');
        }
    });

    $(".nominal").maskMoney({prefix:'Rp ', allowNegative: true, thousands:',', affixesStay: true, precision: 0});

    $(".nominal").on('keyup', () => {
        let nominal = document.getElementsByClassName('nominal');
        let total_donasi = 0;
        for (let i = 0; i < nominal.length; i++) {
            let currency = nominal[i].value;
            let cur = Number(currency.replace(/[^0-9.-]+/g,""));
            console.log(cur);

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

    let setDonatur = (status) => {
        let donatur_baru = document.getElementsByClassName('donatur_baru');
        let donatur_lama = document.getElementsByClassName('donatur_lama');
        if(status == 'baru'){
            $(".donatur_baru").attr('style', 'display:block');
            $(".donatur_lama").attr('style', 'display:none')
        }else{
            $(".donatur_baru").attr('style', 'display:none');
            $(".donatur_lama").attr('style', 'display:block')
        }
    }

    let transaksi = "{{ @$transaksi }}";
    if(transaksi){
        setDonatur('lama');
        setUpload('{{ @$transaksi->jenis_transaksi }}');
    }else{
        setDonatur('baru');
        setUpload('cash');
    }
</script>
@endpush
