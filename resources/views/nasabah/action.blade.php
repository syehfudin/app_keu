<a class="btn btn-info" href="{{ route('nasabah.show',$nasabah->id) }}">Show</a>
@can('nasabah-edit')
    <a class="btn btn-primary" href="{{ route('nasabah.edit',$nasabah->id) }}">Edit</a>
@endcan
@can('nasabah-delete')
    {!! Form::open(['method' => 'DELETE','route' => ['nasabah.destroy', $nasabah->id],'style'=>'display:inline', 'onsubmit' => 'return confirm("Apakah anda yakin untuk menghapus data ini?")']) !!}
        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@endcan
