@extends('layouts.app')
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
                            <label class="fs-6 fw-bold mb-2">Nama Pekerjaan</label>
                            <input type="text" class="form-control" name="nama" {{ @$show }} placeholder="Masukan nama pekerjaan" value="{{ @$pekerjaan->nama }}">
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
