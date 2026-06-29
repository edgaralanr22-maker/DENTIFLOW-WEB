@extends('layouts.app')
@section('title', 'Nuevo paciente')
@section('content')
    @include('pacientes._form', [
        'title' => 'Nuevo paciente',
        'subtitle' => 'Crea un expediente completo con información de contacto y datos administrativos.',
        'action' => route('pacientes.store'),
        'method' => 'POST',
        'paciente' => [],
        'submitLabel' => 'Crear expediente',
    ])
@endsection
