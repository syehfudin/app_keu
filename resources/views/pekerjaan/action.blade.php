<a class="btn btn-info" href="{{ route('pekerjaan.show', $pekerjaan->id) }}">Show</a>
@can('pekerjaan-edit')
    <a class="btn btn-primary" href="{{ route('pekerjaan.edit', $pekerjaan->id) }}">Edit</a>
@endcan
@can('pekerjaan-delete')
    {!! Form::open([
        'method' => 'DELETE',
        'route' => ['pekerjaan.destroy', $pekerjaan->id],
        'style' => 'display:inline',
        'onsubmit' => 'return confirm("Apakah anda yakin untuk menghapus data ini?")',
    ]) !!}
    {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@endcan
