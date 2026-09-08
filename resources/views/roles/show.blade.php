@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form {{ $title }}</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fs-6 fw-bold mb-2">Nama Role</label>
                        {!! Form::text('name', $role->name, array('placeholder' => 'Nama Role','class' => 'form-control', 'disabled')) !!}
                    </div>
                    <div class="mb-3">
                        <label class="fs-6 fw-bold mb-2">Permissions :</label>
                        @if(!empty($rolePermissions))
                            @foreach($rolePermissions as $v)
                                <label class="label label-success">{{ $v->name }},</label>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <a class="btn btn-primary" href="{{ $redirectUrl }}"> Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
