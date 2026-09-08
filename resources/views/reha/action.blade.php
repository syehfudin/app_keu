<a class="btn btn-info" href="{{ route('reha.show', $reha->id) }}">Show</a>
@can('reha-edit')
    <a class="btn btn-primary" href="{{ route('reha.edit', $reha->id) }}">Edit</a>
@endcan
@can('reha-delete')
    {!! Form::open([
        'method' => 'DELETE',
        'route' => ['reha.destroy', $reha->id],
        'style' => 'display:inline',
        'onsubmit' => 'return confirm("Apakah anda yakin untuk menghapus data ini?")',
    ]) !!}
    {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@endcan
