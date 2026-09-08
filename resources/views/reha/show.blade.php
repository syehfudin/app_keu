@extends('layouts.app')
@section('content')
    @php
        $role = strtolower(Auth::user()->roles[0]->name);
        $pegawai_id = Auth::user()->pegawai_id;
    @endphp
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12 col-lg-12">
                <form action="{{ $action }}" method="POST" autocomplete="off">
                    @csrf
                    @method('POST')
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
                                    @foreach ($relawan as $item)
                                        <option value="{{ $item->id }}"
                                            {{ $item->id == @$transaksi->pegawai_id ? 'selected' : '' }}>{{ $item->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @foreach ($reha as $item)
                                <div class="mb-3">
                                    <label class="fs-6 fw-bold mb-2" style="font-size: 1.5em;">RENCANA KUNJUNGAN</label>
                                    <div class="d-flex align-items-start mt-3">
                                        <div style="flex: 1;">
                                            <label> Nasabah Lama : </label>
                                            <input type="number" class="form-control" name="renku_donatur_lama[]"
                                                {{ @$show }} value="{{ @$item->renku_donatur_lama ?? 0 }}">
                                        </div>
                                        <div style="flex: 1; margin-left: 10px;">
                                            <label> Nasabah Baru : </label>
                                            <input type="number" class="form-control" name="renku_donatur_baru[]"
                                                {{ @$show }} value="{{ @$item->renku_donatur_baru ?? 0 }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="fs-6 fw-bold mb-2" style="font-size: 1.5em;">REALISASI</label>
                                    <div class="d-flex align-items-start mt-3">
                                        <div style="flex: 1;">
                                            <label> Nasabah Lama : </label>
                                            <input type="number" class="form-control" name="realisasi_donatur_lama"
                                                {{ @$show }} value="{{ @$item->realisasi_donatur_lama ?? 0 }}">
                                        </div>
                                        <div style="flex: 1; margin-left: 10px;">
                                            <label> Nasabah Baru : </label>
                                            <input type="number" class="form-control" name="realisasi_donatur_baru"
                                                {{ @$show }} value="{{ @$item->realisasi_donatur_baru ?? 0 }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="fs-6 fw-bold mb-2" style="font-size: 1.5em;">RENCANA FU</label>
                                    <div class="d-flex align-items-start mt-3">
                                        <div style="flex: 1;">
                                            <label> Nasabah Lama : </label>
                                            <input type="number" class="form-control" name="fu_donatur_lama"
                                                {{ @$show }} value="{{ @$item->fu_donatur_lama ?? 0 }}">
                                        </div>
                                        <div style="flex: 1; margin-left: 10px;">
                                            <label> Nasabah Baru : </label>
                                            <input type="number" class="form-control" name="fu_donatur_baru"
                                                {{ @$show }} value="{{ @$item->fu_donatur_baru ?? 0 }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="fs-6 fw-bold mb-2" style="font-size: 1.5em;">DEAL HARI INI</label>
                                    <div class="d-flex align-items-start mt-3">
                                        <div style="flex: 1;">
                                            <label> Nasabah Lama : </label>
                                            <input type="number" class="form-control" name="deal_donatur_lama"
                                                {{ @$show }} value="{{ @$item->deal_donatur_lama ?? 0 }}">
                                        </div>
                                        <div style="flex: 1; margin-left: 10px;">
                                            <label> Nasabah Baru : </label>
                                            <input type="number" class="form-control" name="deal_donatur_baru"
                                                {{ @$show }} value="{{ @$item->deal_donatur_baru ?? 0 }}">
                                        </div>
                                    </div>
                                </div>


                                <div class="mb-3 mt-3">
                                    <label class="fs-6 fw-bold mb-2">Jenis Akad</label>
                                    @foreach (json_decode($item->jenis_akad) as $jenis_akad)
                                        <input type="text" class="form-control mb-3" name="jenis_akad[]" {{ @$show }}
                                            value="{{ $jenis_akad }}">
                                    @endforeach
                                </div>
                            @endforeach



                            <div class="card-footer">
                                <a class="btn btn-primary" href="{{ $redirectUrl }}"> Back</a>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    @endsection
