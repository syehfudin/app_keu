@extends('layouts.app')
@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12 col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form {{ $title }}</h3>
                </div>
                <div class="card-body">
                    <h5>List Transaksi</h5>
                    <input type="text" class="form-control" disabled name="" value="{{ $setoran->nama_penyetor }}">
                    <table class="table table-striped" id="datatable-setoran">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal Donasi</th>
                                <th>Nama Donatur</th>
                                <th>Nominal Donasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1 @endphp
                            @foreach($setoran_detail as $item)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ Date('d-m-Y', strtotime($item->tanggal)) }}</td>
                                <td>{{ $item->nama_donatur }}</td>
                                <td>Rp {{ number_format($item->total_donasi) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mb-3">
                        <label class="fs-6 fw-bold mb-2">
                            <span class="required">Total Setor</span>
                        </label>
                        <input type="text" name="total_setor" class="form-control" id="total_setor" value="Rp {{ number_format($setoran->total_setoran) }}" readonly>
                    </div>
                    <img src="{{ asset($setoran->path . $setoran->nama_file) }}" width="300px" class="img-fluid mb-2">
                </div>
                <div class="card-footer">
                    <a class="btn btn-primary" href="{{ $redirectUrl }}"> Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
