@extends('layouts.app')
@section('content')
<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fas fa-check"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fas fa-ban"></i> {{ session('error') }}
    </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-user-plus"></i> Import Data Penghimpun</h3></div>
                <div class="card-body">
                    <p>Download template, isi data, lalu upload file Excel.</p>
                    <div class="mb-3">
                        <a href="{{ route('import.template.penghimpun') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Download Template Penghimpun
                        </a>
                    </div>
                    <form action="{{ route('import.penghimpun') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih File Excel (.xlsx, .xls, .csv)</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        </div>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin ingin import data penghimpun?')">
                            <i class="fas fa-upload"></i> Import Penghimpun
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-users"></i> Import Data Nasabah</h3></div>
                <div class="card-body">
                    <p>Download template, isi data, lalu upload file Excel.</p>
                    <div class="mb-3">
                        <a href="{{ route('import.template.nasabah') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Download Template Nasabah
                        </a>
                    </div>
                    <form action="{{ route('import.nasabah') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih File Excel (.xlsx, .xls, .csv)</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        </div>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin ingin import data nasabah?')">
                            <i class="fas fa-upload"></i> Import Nasabah
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-warning">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> Petunjuk Import</h3></div>
                <div class="card-body">
                    <h5>Import Penghimpun:</h5>
                    <ol>
                        <li>Download template Excel penghimpun</li>
                        <li>Isi data: NIP, Nama, Alamat, Username, Password</li>
                        <li>Hapus baris contoh pada template</li>
                        <li>Upload file Excel yang sudah diisi</li>
                        <li>Role akan otomatis di-set sebagai <strong>Penghimpun</strong></li>
                        <li>Username dan NIP harus unik (tidak boleh duplikat)</li>
                    </ol>
                    <hr>
                    <h5>Import Nasabah:</h5>
                    <ol>
                        <li>Download template Excel nasabah</li>
                        <li>Isi data: Nama Penghimpun, Nama Nasabah, Alamat, No Telepon, Pekerjaan</li>
                        <li>Pastikan <strong>Nama Penghimpun</strong> sudah terdaftar di sistem</li>
                        <li>Pilih Pekerjaan dari list yang tersedia (lihat sheet "List Pekerjaan" di template)</li>
                        <li>Hapus baris contoh pada template</li>
                        <li>Upload file Excel yang sudah diisi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection