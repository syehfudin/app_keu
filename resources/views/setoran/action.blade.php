<a class="btn btn-info" href="{{ route('setoran.show',$setoran->id) }}">Show</a>
@can('setoran-edit')
    <a class="btn btn-primary" href="{{ route('setoran.edit',$setoran->id) }}">Edit</a>
@endcan
@can('setoran-delete')
    {!! Form::open(['method' => 'DELETE','route' => ['setoran.destroy', $setoran->id],'style'=>'display:inline', 'onsubmit' => 'return confirm("Apakah anda yakin untuk menghapus data ini?")']) !!}
        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@endcan
