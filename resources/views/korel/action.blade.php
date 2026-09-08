<a class="btn btn-info" href="{{ route('korel.show',$korel->id) }}">Show</a>
@can('korel-edit')
    <a class="btn btn-primary" href="{{ route('korel.edit',$korel->id) }}">Edit</a>
@endcan
@can('korel-delete')
    {!! Form::open(['method' => 'DELETE','route' => ['korel.destroy', $korel->id],'style'=>'display:inline', 'onsubmit' => 'return confirm("Apakah anda yakin untuk menghapus data ini?")']) !!}
        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@endcan
