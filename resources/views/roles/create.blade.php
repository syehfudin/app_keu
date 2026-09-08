@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-lg-12">
            {!! Form::open(array('route' => 'roles.store','method'=>'POST')) !!}
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Form {{ $title }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Nama Role</label>
                            {!! Form::text('name', null, array('placeholder' => 'Nama Role','class' => 'form-control')) !!}
                        </div>
                        <div class="mb-3">
                            <label class="fs-6 fw-bold mb-2">Permission</label>
                            <table class="table table-hover my-0">
                                <thead>
                                    <tr>
                                        <th>Form</th>
                                        <th class="d-none d-xl-table-cell text-center" colspan="4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permission as $value)
                                    <tr>
                                        <td>{{ $value->name }}</td>
                                        @php 
                                            $list_id = explode(",",$value->list_id) ? explode(",",$value->list_id) : [];
                                            $list_role = explode(",",$value->list_role) ? explode(",",$value->list_role) : [];
                                            $n = 0;
                                        @endphp
                                        @foreach($list_id as $id)
                                            <td>
                                                {{ Form::checkbox('permission[]', $id, false, array('class' => 'name')) }}
                                                {{ $list_role[$n] }}
                                            </td>
                                        @php $n++ @endphp
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="float-right">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                        <a class="btn btn-primary" href="{{ $redirectUrl }}"> Back</a>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection
