@extends('layouts.app')
@section('content')
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
                        @if(strtolower(Auth::user()->roles[0]->name) == 'admin')
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">
                                <span class="required">Nama Relawan</span>
                            </label>
                            <select class="form-control" name="pegawai_id" id="pegawai_id">
                                @foreach($relawan as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                            {!! Form::hidden('pegawai_id', Auth::user()->pegawai_id, array('id' => 'pegawai_id')) !!}
                        @endif
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" id="add_transaksi">Tambah</button>
                        </div>
                        <h5>List Transaksi</h5>
                        <div id="transaksi">
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">
                                <span class="required">Total Setor</span>
                            </label>
                            <input type="hidden" name="total_setor" class="form-control" id="ttl_setor" readonly>
                            <input type="text" class="form-control" id="total_setor" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Upload File Bukti Transfer</label>
                            <input
                                type="file"
                                name="file"
                                id="inputImage"
                                class="form-control @error('image') is-invalid @enderror">
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="float-right">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                        <a class="btn btn-primary" href="{{ $redirectUrl }}"> Back</a>
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
$(document).ready(function(){
    var template  = Handlebars.compile($("#details-template_transaksi").html());

    var stringToHTML = function (str) {
        var dom = document.createElement('div');
        dom.className = 'fv-row list_indikator';
        dom.innerHTML = str;
        return dom;
    };

    $("#transaksi").on('change', '.transaksi_id', function() {
        let list_transaksi= document.getElementsByClassName('list_transaksi');
        let nominal = $('option:selected', this).attr('nominal');
        let i = $('.transaksi_id').index(this);
        list_transaksi[i].getElementsByClassName("nominal")[0].setAttribute('value', "Rp "+ conCurrency(nominal));
        total_setor();
    });

    $("#add_transaksi").on('click', function() {
        let content = document.getElementById("transaksi");
        let element = stringToHTML(template());
        content.append(element);

    });

    $("#transaksi").on('click', ".delete", function(){
        var id = this;
        $(this).parents('.list_transaksi').remove();
        total_setor();
    });

    let total_setor = () => {
        let transaksi= document.getElementsByClassName('list_transaksi');
        let total_setor = 0;
        for (let i = 0; i < transaksi.length; i++) {
            nominal = conCurrToNum(transaksi[i].querySelector('input[name="nominal"]').value);
            total_setor += nominal;
        }
        let curr = '';
        if(total_setor == 0){
            curr = '';
        }else{
             curr = "Rp "+ conCurrency(total_setor);
        }
        $("#ttl_setor").val(total_setor);
        $("#total_setor").val(curr);
    }


    let conCurrency = (num) => {
        return new Intl.NumberFormat().format(num);
    }

    let conCurrToNum = (curr) => {
        return Number(curr.replace(/[^0-9.-]+/g,""));
    }
});
</script>
<script id="details-template_transaksi" type="text/x-handlebars-template">
    <div class="row mb-3 list_transaksi">
        <div class="col-6">
            <label class="fs-6 fw-bold mb-2">
                <span class="required">Transaksi</span>
            </label>
            <select class="form-control transaksi_id" name="transaksi_id[]">
                <option value="" nominal="0">Pilih Transaksi ...</option>
                @foreach($transaksi as $item)
                    <option value="{{ $item->id }}" nominal="{{ $item->total_donasi }}">{{ $item->tanggal .' - '. $item->nama_donatur }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label class="fs-6 fw-bold mb-2">
                <span class="required">Total Nominal Donasi</span>
            </label>
            <input type="text" name="nominal" class="form-control nominal" value="" readonly>
        </div>
        <div class="col-md-1">
            <label class="fs-6 fw-bold mb-2">
                <span class="required">&nbsp;</span>
            </label>
            <button type="button" class="btn btn-outline-danger delete">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 align-middle me-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </button>
            <label class="fs-6 fw-bold mb-2">&nbsp;
            </label>
        </div>
    </div>
</script>
@endpush
