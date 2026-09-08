@extends('layouts.app')
@push('custom-css-files')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link href="{{ asset('css/magnific-popup.css') }}" rel="stylesheet">
    <style>
        #portfolio {
          background: #fff;
          padding: 30px 0;
        }
        #portfolio .portfolio-overlay {
          position: absolute;
          top: 0;
          right: 0;
          bottom: 0;
          left: 0;
          width: 100%;
          height: 100%;
          opacity: 1;
          transition: all ease-in-out 0.4s;
        }
        #portfolio .portfolio-item {
          overflow: hidden;
          position: relative;
          padding: 0;
          vertical-align: middle;
          text-align: center;
        }
        #portfolio .portfolio-item h2 {
          color: #ffffff;
          font-size: 24px;
          margin: 0;
          text-transform: capitalize;
          font-weight: 700;
        }
        #portfolio .portfolio-item img {
          transition: all ease-in-out 0.4s;
          width: 100%;
        }
        #portfolio .portfolio-item:hover img {
          -webkit-transform: scale(1.1);
          transform: scale(1.1);
        }
        #portfolio .portfolio-item:hover .portfolio-overlay {
          opacity: 1;
          background: rgba(0, 0, 0, 0.7);
        }
        #portfolio .portfolio-info {
          position: absolute;
          top: 50%;
          left: 50%;
          -webkit-transform: translate(-50%, -50%);
          transform: translate(-50%, -50%);
        }
        #gambar{
            height:100px;
        }
    </style>
@endpush
@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center py-2 py-md-2">
        @can('setoran-create')
        <a class="btn btn-success" href="{{ route('setoran.create') }}"> Tambah setoran</a>
        @endcan
    </div>
    <div class="row">
        <div class="col-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped" id="datatable-setoran">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Penyetor</th>
                                <th>Total Donasi</th>
                                <th>Bukti Setor</th>
                                <th width="280px">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('custom-js-files')
<!-- DataTables  & Plugins -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('js/jquery.magnific-popup.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    let dataUrl = "{{ route('setoran.index_data') }}";
    let tableSelector = "datatable-setoran";

    let routeAsset = "{{ asset('') }}";
    $('.portfolio-popup').magnificPopup({
        type: 'image',
        removalDelay: 300,
        mainClass: 'mfp-fade',
        gallery: {
        enabled: true
        },
        zoom: {
        enabled: true,
        duration: 300,
        easing: 'ease-in-out',
        opener: function (openerElement) {
            return openerElement.is('img') ? openerElement : openerElement.find('img');
        }
        }
    });
    dt = $("#" + tableSelector).DataTable({
        columnDefs: [
            {
                targets: [1],
                render: function (data, type, row) {
                    var datePart = data.match(/\d+/g),
                        year = datePart[0],
                        month = datePart[1],
                        day = datePart[2];

                    return day+'-'+month+'-'+year;
                }
            }
        ],
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "searching": true,
        "ajax": dataUrl,
        columns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "tanggal_setoran", name: "tanggal_setoran" },
            { data: "nama_pegawai", name: "nama_pegawai" },
            { data: "total_donasi", name: "total_donasi", render: $.fn.dataTable.render.number( ',', '.', 0, 'Rp ' ) },
            { data: "image_url" ,
              "render": function (data, type, row) {
                    return `<div id="portfolio">
                                <a href="${routeAsset + row.path + row.nama_file}" class="portfolio-popup">
                                    <img src="${routeAsset + row.path + row.nama_file}" alt="your image" class="img-fluid" id="gambar">
                                </a>
                            </div>`;
                }
                // <img src="'+ routeAsset + row.path + row.nama_file + '" width="80px">';}
            },
            {
                data: "action",
                name: "action",
                orderable: true,
                searchable: true,
            },
        ],
        "fnDrawCallback": function () {
            $('.portfolio-popup').magnificPopup({
                type: 'image',
                removalDelay: 300,
                mainClass: 'mfp-fade',
                gallery: {
                    enabled: true
                },
                zoom: {
                    enabled: true,
                    duration: 300,
                    easing: 'ease-in-out',
                    opener: function (openerElement) {
                    return openerElement.is('img') ? openerElement : openerElement.find('img');
                    }
                }
            });
        }
    });

    // $("#" + tableSelector).imagePopup({
    //     //overlay: "rgba(0, 100, 0, 0.5)"

    //     closeButton:{
    //         src: routeAsset+"images/close.png",
    //         width: "40px",
    //         height:"40px"
    //     },
    //     imageBorder: "15px solid #ffffff",
    //     borderRadius: "10px",
    //     imageWidth: "500px",
    //     imageHeight: "400px",
    //     imageCaption: {
    //         exist: true,
    //         color: "#ffffff",
    //         fontSize: "40px"
    //     },
    //     open: function(){
    //         console.log("opened");
    //     },
    //     close: function(){
    //         console.log("closed");
    //     }
    // });
    table = dt.$;
</script>
@endpush
