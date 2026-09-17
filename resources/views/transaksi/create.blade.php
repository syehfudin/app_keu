@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush
@section('content')
@php
    $role = strtolower(Auth::user()->roles[0]->name);
    $pegawai_id = Auth::user()->pegawai_id;
    // Role yang harus memilih penghimpun dahulu sebelum memilih nasabah.
    // Untuk role-role ini, dropdown nasabah diisi dinamis via AJAX.
    $needPegawaiSelect = in_array($role, ['admin', 'supervisor', 'manager']);
@endphp
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
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Tanggal</label>
                            {!! Form::text('tanggal', @$transaksi->tanggal ? date('d-m-Y',  strtotime(@$transaksi->tanggal)) : date('d-m-Y'), array('class' => 'form-control', 'id' => 'datepicker')) !!}
                        </div>
                        @if($role != 'penghimpun')
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">
                                <span class="required">Nama Penghimpun</span>
                            </label>
                            <select class="form-control" name="pegawai_id" id="pegawai_id">
                                @foreach($relawan as $item)
                                    @php
                                        $selected = "";
                                        if(@$transaksi){
                                            if($item->id == @$transaksi->pegawai_id){
                                                $selected = 'selected';
                                            }
                                        }else{
                                            if($item->default == true){
                                                $selected = 'selected';
                                            }
                                        }
                                    @endphp
                                    <option value="{{ $item->id }}" {{ $selected }} >{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                            {!! Form::hidden('pegawai_id', $pegawai_id, ['id' => 'pegawai_id']) !!}
                        @endif
                        {{-- Status Nasabah: UI dihilangkan (selalu 'lama') --}}
                        @if(!@$transaksi)
                        <input type="hidden" name="status" value="lama">
                        @endif
                        <div class="mb-3 ">
                            <label class="fs-6 fw-bold mb-2">Nama Nasabah</label>
                            <select class="form-control select2" name="donatur_id" id="donatur_id">
                                <option value="">Pilih Nasabah ...</option>
                                @foreach($donatur as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == @$transaksi->donatur_id ? 'selected' : '' }}>{{ $item->nama }}</option>
                                @endforeach
                            </select>
                            @if($needPegawaiSelect)
                            <small class="form-text text-muted" id="donatur_hint">
                                Pilih Penghimpun dahulu, daftar nasabah akan dimuat otomatis.
                            </small>
                            @endif
                        </div>
                        <h5>Program</h5>
                        @php $total_donasi = 0 @endphp
                        @foreach($program as $item)
                        <div class="mb-3 donasi">
                            <label class="fs-6 fw-bold mb-2">{{ $item->nama }}</label>
                            {!! Form::text('nominal_donasi[]', @$item->nominal_donasi, array('placeholder' => 'Masukan nominal yang akan didonasikan','class' => 'form-control nominal')) !!}
                            {!! Form::hidden('program_id[]', $item->id) !!}
                        </div>
                        @php
                            if(@$transaksi){
                                $nominal = @$item->nominal_donasi ? @$item->nominal_donasi : 0;
                                $total_donasi += $nominal;
                            }else{
                                $total_donasi = "";
                            }
                        @endphp
                        @endforeach
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Total Donasi</label>
                            {!! Form::text('total_donasi', @$total_donasi, array('placeholder' => 'Total donatur','class' => 'form-control total', 'readonly', @$show)) !!}
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Keterangan</label>
                            {!! Form::textarea('keterangan', @$transaksi->keterangan, array('placeholder' => '','class' => 'form-control', 'rows' => '6')) !!}
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Jenis Transaksi</label>
                            {!! Form::select('jenis_transaksi', array('cash' => 'Titip di Penghimpun', 'transfer' => 'Setoran Transfer', 'rek_ulama' => 'Setoran ke Rek Ulama'), [], array('class' => 'form-control jt')) !!}
                        </div>
                        <div class="mb-3 upload">
                            <label class="fs-6 fw-bold mb-2">Upload File Bukti Transfer</label>
                            <input
                                type="file"
                                name="image"
                                class="form-control @error('image') is-invalid @enderror">
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
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('js/jquery.maskMoney.js') }}"></script>
<script type="text/javascript">
    $(".select2").select2();
    $( "#datepicker" ).datepicker({
        dateFormat: 'dd-mm-yy'
    });
    $(".jt").on('change', function() {
        let jt = $(this).val();
        setUpload(jt);
    });

    $(".pekerjaan").on('click', function(){
        let status = $(this).val();
        if(status == 'lainnya'){
            $(".lainnya").attr("disabled", false);
        }else{
            $(".lainnya").attr("disabled", true);
            $(".lainnya").val('');
        }
    });

    $(".nominal").maskMoney({prefix:'Rp ', allowNegative: true, thousands:',', affixesStay: true, precision: 0});

    $(".nominal").on('keyup', () => {
        let nominal = document.getElementsByClassName('nominal');
        let total_donasi = 0;
        for (let i = 0; i < nominal.length; i++) {
            let currency = nominal[i].value;
            let cur = Number(currency.replace(/[^0-9.-]+/g,""));
            console.log(cur);

            total_donasi = total_donasi + cur;
        }

        let total = new Intl.NumberFormat().format(total_donasi)
        $(".total").val("Rp "+ total);
    });


    let setUpload = (jt) => {
        if(jt == 'cash'){
            $(".upload").attr('style', 'display:none');
        }else{
            $(".upload").attr('style', 'display:block');
        }
    }

    // ============================================================
    // Filter dinamis: pilih Penghimpun -> muat daftar Nasabah
    // ============================================================
    let needPegawaiSelect = {{ $needPegawaiSelect ? 'true' : 'false' }};

    let loadDonaturByPegawai = (pegawaiId) => {
        let $select = $("#donatur_id");
        $select.empty().append('<option value="">Pilih Nasabah ...</option>');

        if (!pegawaiId) {
            $select.trigger('change');
            return;
        }

        $select.append('<option value="" disabled>Memuat daftar nasabah...</option>');
        $select.trigger('change');

        $.ajax({
            url: "{{ route('transaksi.getDonaturByPegawai', ['pegawai_id' => ':PEGAWAI_ID:']) }}".replace(':PEGAWAI_ID:', pegawaiId),
            type: "GET",
            dataType: "json",
            success: function(data) {
                $select.empty().append('<option value="">Pilih Nasabah ...</option>');
                if (data && data.length > 0) {
                    $.each(data, function(i, item) {
                        $select.append('<option value="'+item.id+'">'+item.nama+'</option>');
                    });
                } else {
                    $select.append('<option value="" disabled>Tidak ada nasabah untuk penghimpun ini</option>');
                }
                $select.trigger('change');
            },
            error: function(xhr, status, error) {
                $select.empty().append('<option value="">Pilih Nasabah ...</option>');
                $select.append('<option value="" disabled>Gagal memuat nasabah</option>');
                $select.trigger('change');
                console.error("AJAX getDonaturByPegawai error:", status, error);
            }
        });
    };

    if (needPegawaiSelect) {
        // Saat dropdown Penghimpun berubah, muat ulang daftar Nasabah
        $("#pegawai_id").on('change', function() {
            let pegawaiId = $(this).val();
            loadDonaturByPegawai(pegawaiId);
        });

        // Saat form baru (bukan edit): jika penghimpun pertama sudah
        // terpilih/default, langsung muat nasabahnya. Karena di kode
        // tidak ada pegawai.default=true, dropdown awal biasanya kosong
        // (placeholder "Pilih Penghimpun" tidak ada - tambahkan).
        let transaksiExists = "{{ @$transaksi }}";
        if (!transaksiExists) {
            // Tambah placeholder "Pilih Penghimpun ..." jika belum ada
            // (option pertama dengan value kosong sebagai placeholder).
            // Hanya tambah jika belum ada option value="".
            if ($("#pegawai_id option[value='']").length === 0) {
                $("#pegawai_id").prepend('<option value="">Pilih Penghimpun ...</option>');
            }
            // Jika penghimpun awal belum ada yang selected (semua default=false),
            // reset ke placeholder.
            if (!$("#pegawai_id option:selected").length || $("#pegawai_id").val() === "") {
                $("#pegawai_id").val('').trigger('change');
                $("#donatur_id").empty().append('<option value="">Pilih Penghimpun dahulu...</option>').trigger('change');
            } else {
                // Ada penghimpun terpilih (mis. via default), langsung muat nasabah
                loadDonaturByPegawai($("#pegawai_id").val());
            }
        }
    }

    let transaksi = "{{ @$transaksi }}";
    if(transaksi){
        setUpload('{{ @$transaksi->jenis_transaksi }}');
    }else{
        @role('Penghimpun')
        @else
        @endrole
        setUpload('cash');
    }
</script>
@endpush