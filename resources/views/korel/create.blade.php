@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    .opt-role { font-weight: 600; }
    .opt-role small { font-weight: 400; color: #6c757d; }
    #selected-count { min-width: 28px; text-align: center; }
</style>
@endpush
@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12 col-lg-12">
            <form action="{{ $action }}" method="POST" autocomplete="off">
                @csrf
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-sitemap"></i> {{ $title }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info py-2">
                            <i class="fas fa-info-circle"></i>
                            Pilih <strong>Koordinator</strong>, lalu daftar bawahan akan menyesuaikan dengan jabatannya
                            (Direktur→DirOps→General Manager→Manager→Supervisor→Penghimpun).
                        </div>

                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">
                                <span class="text-danger">*</span> Kepala / Koordinator
                            </label>
                            <select class="form-control select2" name="kepala" id="select-kepala" {{ @$id ? 'disabled' : '' }}
                                    @if(@$show) disabled @endif>
                                <option value="">-- Pilih Koordinator --</option>
                                @foreach($kepala as $item)
                                <option value="{{ $item->id }}" {{ @$id == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }} {{ !empty($item->role) ? '['.$item->role.']' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">
                                <span class="text-danger">*</span> List Bawahan
                                <span class="badge badge-secondary ml-2" id="selected-count">0</span> dipilih
                                <span id="spinner-bawahan" class="ml-2 text-primary" style="display:none">
                                    <i class="fas fa-spinner fa-spin"></i> Memuat...
                                </span>
                            </label>
                            <select class="form-control select2" multiple name="bawahan[]" id="select-bawahan"
                                    @if(@$show) disabled @endif
                                    data-placeholder="-- Pilih Bawahan --">
                                @foreach($bawahan as $item)
                                <option value="{{ $item->id }}" {{ $item->cek }}>{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        @if(!@$show)
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        @endif
                        <a class="btn btn-secondary" href="{{ $redirectUrl }}"><i class="fas fa-arrow-left"></i> Kembali</a>
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
    var URL_BAWAHAN = "{{ route('korel.get_bawahan') }}";
    var $kepala = $('#select-kepala');
    var $bawahan = $('#select-bawahan');

    $('.select2').select2();

    // inisialisasi bawahan awal (mode create: kosong; mode edit/show: sudah di-render server)
    initBawahan();

    // saat kepala berubah -> muat ulang list bawahan
    $kepala.on('change', function() {
        loadBawahan($(this).val(), true);
    });

    $bawahan.on('change', updateCount); // select2 select/unselect tidak selalu trigger change, gunakan binding select2
    $bawahan.on('select2:select select2:unselect', updateCount);
    $kepala.on('select2:select select2:unselect', updateCount);

    function initBawahan() {
        // pada mode show/edit (id terisi), bawahan sudah dirender server & terpilih -> jangan reset
        var isEditMode = "{{ @$id ? 1 : 0 }}";
        if (isEditMode == '1') {
            updateCount();
            return;
        }
        // mode create: bila kepala terpilih di URL/prefill, muat; else kosongkan
        var k = $kepala.val();
        if (k) { loadBawahan(k, false); } else { clearBawahan(); }
    }

    function loadBawahan(kepalaId, replace) {
        $('#spinner-bawahan').show();
        $.get(URL_BAWAHAN, { kepala: kepalaId, selected: getSelectedIds() }, function(res) {
            $bawahan.empty();
            $.each(res.bawahan, function(i, b) {
                var opt = $('<option></option>')
                    .val(b.id)
                    .text(b.nama + (b.role ? ' [' + b.role + ']' : ''));
                if (b.cek === 'selected') { opt.prop('selected', true); }
                $bawahan.append(opt);
            });
            $bawahan.trigger('change');
            $("#select-bawahan").trigger('change.select2');
            updateCount();
        }).fail(function() {
            alert('Gagal memuat daftar bawahan. Coba lagi.');
        }).always(function() {
            $('#spinner-bawahan').hide();
        });
    }

    function clearBawahan() {
        $bawahan.empty();
        $bawahan.trigger('change');
        updateCount();
    }

    function getSelectedIds() {
        return ($bawahan.val() || []).join(',');
    }

    function updateCount() {
        var n = $bawahan.val() ? $bawahan.val().length : 0;
        $("#selected-count").text(n);
        var cls = n === 0 ? 'badge-secondary' : 'badge-success';
        $("#selected-count").removeClass('badge-secondary badge-success').addClass(cls);
    }
});
</script>
@endpush
