<a class="btn btn-info" href="{{ route('donatur.show',$donatur->id) }}">Show</a>
@can('donatur-edit')
    <a class="btn btn-primary" href="{{ route('donatur.edit',$donatur->id) }}">Edit</a>
@endcan
@can('donatur-delete')
    {!! Form::open(['method' => 'DELETE','route' => ['donatur.destroy', $donatur->id],'style'=>'display:inline', 'onsubmit' => 'return confirm("Apakah anda yakin untuk menghapus data ini?")']) !!}
        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@endcan
