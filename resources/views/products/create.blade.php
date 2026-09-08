@extends('layouts.app')
@section('content')
<div class="container-fluid p-0">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Tambah Data Relawan</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('products.index') }}"> Back</a>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-lg-12">
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <select class="form-control" name="jabatan">
                                <option value="">Pilih jabatan ...</option>
                                <option value="relawan">Relawan</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ID Pegawai</label>
                            <input type="text" class="form-control" nama="nip" placeholder="Masukan nama relawan">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Relawan</label>
                            <input type="text" class="form-control" name="nama" placeholder="Masukan nama relawan">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" name="no_telepon" placeholder="Masukan nomot telepon relawan">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" placeholder="Masukan Alamat Relawan"></textarea>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                                <button type="submit" class="btn btn-lg btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
