<a class="btn btn-info" href="{{ route('pegawai.show',$user->id) }}">Show</a>
@can('role-edit')
    <a class="btn btn-primary" href="{{ route('pegawai.edit',$user->id) }}">Edit</a>
@endcan
@can('role-delete')
    {!! Form::open(['method' => 'DELETE','route' => ['pegawai.destroy', $user->id],'style'=>'display:inline', 'onsubmit' => 'return confirm("Apakah anda yakin untuk menghapus data ini?")']) !!}
        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@endcan