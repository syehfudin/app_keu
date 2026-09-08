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
                                <label class="fs-6 fw-bold mb-2">Tanggal</label>
                                {!! Form::text('tanggal', @$reha->tanggal ? date('d-m-Y', strtotime(@$reha->tanggal)) : date('d-m-Y'), [
                                    'class' => 'form-control',
                                    'id' => 'datepicker',
                                ]) !!}
                            </div>
                            @if ($role != 'relawan')
                                <div class="mb-3">
                                    <label class="fs-6 fw-bold mb-2">
                                        <span class="required">Nama Relawan</span>
                                    </label>
                                    <select class="form-control" name="pegawai_id">
                                        @foreach ($relawan as $item)
                                            @php
                                                $selected = '';
                                                if (@$transaksi) {
                                                    if ($item->id == @$transaksi->pegawai_id) {
                                                        $selected = 'selected';
                                                    }
                                                } else {
                                                    if ($item->default == true) {
                                                        $selected = 'selected';
                                                    }
                                                }
                                            @endphp
                                            <option value="{{ $item->id }}" {{ $selected }}>{{ $item->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                {!! Form::hidden('pegawai_id', $pegawai_id) !!}
                            @endif
                            <div class="mb-3">
                                <label class="fs-6 fw-bold mb-2" style="font-size: 1.5em;">RENCANA KUNJUNGAN</label>
                                <div class="d-flex align-items-start mt-3">
                                    <div style="flex: 1;">
                                        <label> Donatur Lama : </label>
                                        <input type="number" class="form-control" name="renku_donatur_lama"
                                            {{ @$show }} value="{{ @$reha->renku_donatur_lama ?? 0 }}">
                                    </div>
                                    <div style="flex: 1; margin-left: 10px;">
                                        <label> Donatur Baru : </label>
                                        <input type="number" class="form-control" name="renku_donatur_baru"
                                            {{ @$show }} value="{{ @$reha->renku_donatur_baru ?? 0 }}">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="fs-6 fw-bold mb-2" style="font-size: 1.5em;">REALISASI</label>
                                <div class="d-flex align-items-start mt-3">
                                    <div style="flex: 1;">
                                        <label> Donatur Lama : </label>
                                        <input type="number" class="form-control" name="realisasi_donatur_lama"
                                            {{ @$show }} value="{{ @$reha->realisasi_donatur_lama ?? 0 }}">
                                    </div>
                                    <div style="flex: 1; margin-left: 10px;">
                                        <label> Donatur Baru : </label>
                                        <input type="number" class="form-control" name="realisasi_donatur_baru"
                                            {{ @$show }} value="{{ @$reha->realisasi_donatur_baru ?? 0 }}">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="fs-6 fw-bold mb-2" style="font-size: 1.5em;">RENCANA FU</label>
                                <div class="d-flex align-items-start mt-3">
                                    <div style="flex: 1;">
                                        <label> Donatur Lama : </label>
                                        <input type="number" class="form-control" name="fu_donatur_lama"
                                            {{ @$show }} value="{{ @$reha->fu_donatur_lama ?? 0 }}">
                                    </div>
                                    <div style="flex: 1; margin-left: 10px;">
                                        <label> Donatur Baru : </label>
                                        <input type="number" class="form-control" name="fu_donatur_baru"
                                            {{ @$show }} value="{{ @$reha->fu_donatur_baru ?? 0 }}">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="fs-6 fw-bold mb-2" style="font-size: 1.5em;">DEAL HARI INI</label>
                                <div class="d-flex align-items-start mt-3">
                                    <div style="flex: 1;">
                                        <label> Donatur Lama : </label>
                                        <input type="number" class="form-control" name="deal_donatur_lama"
                                            {{ @$show }} value="{{ @$reha->deal_donatur_lama ?? 0 }}">
                                    </div>
                                    <div style="flex: 1; margin-left: 10px;">
                                        <label> Donatur Baru : </label>
                                        <input type="number" class="form-control" name="deal_donatur_baru"
                                            {{ @$show }} value="{{ @$reha->deal_donatur_baru ?? 0 }}">
                                    </div>

                                </div>
                            </div>
                            {{-- <div class="mb-3 mt-3">
                                <label class="fs-6 fw-bold mb-2">Jenis Akad</label>
                                <input type="text" class="form-control" name="jenis_akad" {{ @$show }}
                                    value="{{ @$reha->jenis_akad }}">
                            </div> --}}

                            <div class="mb-3">
                                <button type="button" class="btn btn-primary" id="add_jenis_akad">Tambah Jenis
                                    Akad</button>
                            </div>

                            <div id="jenis_akad">
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
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('custom-js-files')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.7/handlebars.min.js" crossorigin="anonymous"
        integrity="sha384-dzyupbI5ULkaeg4hBWhkXonQFoXGJvULMzDu6qStcgOkh+6BDdNN9NGGfhmY4ODA"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var template = Handlebars.compile($("#details-template_transaksi").html());

            var stringToHTML = function(str) {
                var dom = document.createElement('div');
                dom.className = 'fv-row list_indikator';
                dom.innerHTML = str;
                return dom;
            };

            $("#add_jenis_akad").on('click', function() {
                let content = document.getElementById("jenis_akad");
                let element = stringToHTML(template());
                content.append(element);

            });

            $("#jenis_akad").on('click', ".delete", function() {
                var id = this;
                $(this).parents('.list_jenisAkad').remove();

            });


        });
    </script>
    <script id="details-template_transaksi" type="text/x-handlebars-template">
   
        <div class="row mb-3 list_jenisAkad">
            <div class="col-md-10">
                <input type="text" class="form-control" placeholder="Jenis Akad" name="jenis_akad[]" {{ @$show }}>
            </div>
            
            <div class="col-md-2">
                <button type="button" class="btn btn-danger delete">Delete</button>
            </div>
        </div>
</script>
@endpush
