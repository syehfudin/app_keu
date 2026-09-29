<a class="btn btn-sm btn-info" href="{{ route('korel.show', $korel->id) }}" title="Lihat Detail"><i class="fas fa-eye"></i></a>
@can('korel-edit')
    <a class="btn btn-sm btn-primary" href="{{ route('korel.edit', $korel->id) }}" title="Ubah"><i class="fas fa-edit"></i></a>
@endcan
@can('korel-delete')
    {!! Form::open(['method' => 'DELETE', 'route' => ['korel.destroy', $korel->id], 'style' => 'display:inline', 'onsubmit' => 'return confirm("Hapus koordinator ini beserta semua bawahannya?")']) !!}
        <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
    {!! Form::close() !!}
@endcan
