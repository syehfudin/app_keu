<a class="btn btn-info" href="{{ route('program.show', $program->id) }}">Show</a>
@can('program-edit')
    <a class="btn btn-primary" href="{{ route('program.edit', $program->id) }}">Edit</a>
@endcan
@can('program-delete')
    {!! Form::open([
        'method' => 'DELETE',
        'route' => ['program.destroy', $program->id],
        'style' => 'display:inline',
        'onsubmit' => 'return confirm("Apakah anda yakin untuk menghapus data ini?")',
    ]) !!}
    {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@endcan
