@extends('layouts.app')

@section('title', 'Bienvenida')

@section('content')
<div class="container text-center mt-5">
    <!-- Título principal -->
    <h1 class="display-4 fw-bold text-primary">¡Bienvenido/a a Nuestra Plataforma!</h1>
    <p class="lead mt-3 mb-4">
        Estamos emocionados de tenerte aquí. Explora nuestras funcionalidades y aprovecha al máximo las herramientas que hemos preparado para ti.
    </p>
    <div>
        <a href="#" class="btn btn-primary btn-lg">
            <i class="bi bi-arrow-right-circle"></i> Empezar Ahora
        </a>
    </div>
</div>
@endsection
