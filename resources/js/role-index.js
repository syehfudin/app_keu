$(document).ready(function(){
    dt = $(tableSelector).DataTable({
        order: [
            [0, 'asc'],
        ],
        processing: true,
        serverSide: true,
        paging: true,
        ordering: true,
        searching: false,
        info: true,
        responsive: true,
        ajax: {
            url: dataUrl, // Defined in the blade file
            // data: function (d) {
            //     d.search_periode = $(tableSearchPeriode).val();
            //     d.search_unit_kerja = $(tableSearchUnitKerja).val();
            //     d.search_pegawai = $(tableSearchPegawai).val();
            // }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name', visible: false },
        ]
    });
    table = dt.$;

    // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
    dt.on('draw', function () {
        KTMenu.createInstances();
    });
});