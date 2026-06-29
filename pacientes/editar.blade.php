@extends('layouts.app')
@section('title', 'Editar paciente')
@section('content')
    @include('pacientes._form', [
        'title' => 'Editar expediente',
        'subtitle' => 'Actualiza la información personal, de contacto y administrativa del paciente.',
        'action' => route('pacientes.update', ['paciente' => $paciente['key']]),
        'method' => 'PUT',
        'submitLabel' => 'Guardar cambios',
    ])
@endsection
