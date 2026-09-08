@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush
@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12 col-lg-12">
            <form action="{{ $action }}" method="POST" autocomplete="off">
                @csrf
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Form {{ $title }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">
                                <span class="required">Nama Penghimpun</span>
                            </label>
                            <select class="form-control" name="pegawai_id">
                                <option value="">Pilih Penghimpun ...</option>
                                @foreach($relawan as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == @$donatur->pegawai_id ? 'selected' : '' }}>{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Nama Nasabah</label>
                            {!! Form::text('nama', @$donatur->nama, array('placeholder' => 'Masukan nama donatur','class' => 'form-control', @$show)) !!}
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Nomor HP Nasabah</label>
                            {!! Form::text('no_telepon', @$donatur->no_telepon, array('placeholder' => 'Masukan nomor hp donatur','class' => 'form-control', @$show)) !!}
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Alamat</label>
                            {!! Form::textarea('alamat', @$donatur->alamat, array('placeholder' => 'Masukan alamat donatur','class' => 'form-control', 'rows' => '4', @$show)) !!}
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Pekerjaan</label>
                            @foreach($pekerjaan as $item)
                            <label class="form-check">
                                {!! Form::radio('pekerjaan', $item->nama, @$donatur->pekerjaan == $item->nama ? true : false, array('class' => 'form-check-input pekerjaan', @$show)) !!}
                                <span class="form-check-label">
                                    {{ $item->nama }}
                                </span>
                            </label>
                            @endforeach
                            <label class="form-check">
                                {!! Form::radio('pekerjaan', 'lainnya', explode("-",@$donatur->pekerjaan)[0] == 'lainnya' ? true : false, array('class' => 'form-check-input pekerjaan', @$show)) !!}
                                <span class="form-check-label">
                                    Lainnya
                                    <input type="text" name="lainnya" class="form-control w-25 lainnya" value="{{ @explode('-',@$donatur->pekerjaan)[1] }}">
                                </span>
                            </label>
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
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('custom-js-files')
<script type="text/javascript">
    $(".pekerjaan").on('click', function(){
        let status = $(this).val();
        if(status == 'lainnya'){
            $(".lainnya").attr("disabled", false);
        }else{
            $(".lainnya").attr("disabled", true);
            $(".lainnya").val('');
        }
    });
</script>
@endpush
