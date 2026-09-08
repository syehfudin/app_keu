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
                                <label class="fs-6 fw-bold mb-2">ID Penghimpun</label>
                                {!! Form::text('nip', @$user->pegawai->nip, [
                                    'placeholder' => 'Masukan id penghimpun',
                                    'class' => 'form-control',
                                    @$show,
                                ]) !!}
                            </div>
                            <div class="mb-3">
                                <label class="fs-6 fw-bold mb-2">Nama Penghimpun</label>
                                {!! Form::text('nama', @$user->pegawai->nama, [
                                    'placeholder' => 'Masukan nama penghimpun',
                                    'class' => 'form-control',
                                    @$show,
                                ]) !!}
                            </div>
                            <div class="mb-3">
                                <label class="fs-6 fw-bold mb-2">Alamat</label>
                                {!! Form::textarea('alamat', @$user->pegawai->alamat, [
                                    'placeholder' => 'Masukan alamat penghimpun',
                                    'class' => 'form-control',
                                    'rows' => '4',
                                    @$show,
                                ]) !!}
                            </div>
                            <div class="mb-3">
                                <label class="fs-6 fw-bold mb-2">Username</label>
                                {!! Form::text('username', @$user->username, [
                                    'placeholder' => 'Masukan username',
                                    'class' => 'form-control',
                                    @$show,
                                ]) !!}
                            </div>
                            @if (!@$show)
                                <div class="mb-3">
                                    <label class="fs-6 fw-bold mb-2">Password</label>
                                    {!! Form::password('password', ['placeholder' => 'Password', 'class' => 'form-control']) !!}
                                </div>
                                <div class="mb-3">
                                    <label class="fs-6 fw-bold mb-2">Konfirmasi Password</label>
                                    {!! Form::password('confirm-password', ['placeholder' => 'Ulangi Password', 'class' => 'form-control']) !!}
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="fs-6 fw-bold mb-2">Role</label>
                                {!! Form::select('roles[]', $roles, @$userRole, ['class' => 'form-control', @$show]) !!}
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    {!! Form::checkbox('default', true, @$user->pegawai->default, ['class' => 'form-check-input', @$show]) !!} Default
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            @if (!@$show)
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
