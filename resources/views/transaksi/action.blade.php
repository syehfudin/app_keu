<a class="btn btn-info" href="{{ route('transaksi.show',$transaksi->id) }}">Show</a>
@can('transaksi-edit')
    <a class="btn btn-primary" href="{{ route('transaksi.edit',$transaksi->id) }}">Edit</a>
@endcan
@can('transaksi-delete')
    {!! Form::open(['method' => 'DELETE','route' => ['transaksi.destroy', $transaksi->id],'style'=>'display:inline', 'onsubmit' => 'return confirm("Apakah anda yakin untuk menghapus data ini?")']) !!}
        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@endcan
<a class="btn btn-success btn-md" href="{{ route('transaksi.importPdf', ['id' => $transaksi->id]) }}" target="_blank">
    <i class="fas fa-file-import"></i>
</a>