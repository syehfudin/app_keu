@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
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
                                <span class="required">Koordinator Relawan</span>
                            </label>
                            <select class="form-control select2" name="kepala" {{ @$id ? 'disabled' : '' }}>
                                <option value=""></option>
                                @foreach($kepala as $item)
                                <option value="{{ $item->id }}" {{ @$id == $item->id ? 'selected' : '' }} >{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">
                                <span class="required">List Relawan</span>
                            </label>
                            <select class="form-control select2" multiple name="bawahan[]" {{ @$show }}>
                                @foreach($bawahan as $item)
                                <option value="{{ $item->id }}" {{ $item->cek }}>{{ $item->nama }}</option>
                                @endforeach
                            </select>
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
<!-- Select2 -->
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {
    $(".select2").select2();
});
</script>
@endpush
