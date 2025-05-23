<?php

// Esta es una prueba para verificar que las rutas de Laravel funcionan correctamente
require __DIR__.'/../vendor/autoload.php';

echo "<h1>Test de Rutas de Laravel</h1>";
echo "<p>Este archivo verifica la configuración del sistema de rutas de Laravel.</p>";

// Intentar cargar las rutas definidas
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    echo "<h2>Rutas Registradas:</h2>";
    echo "<ul>";
    foreach ($routes as $route) {
        $methods = implode('|', $route->methods());
        echo "<li>{$methods} {$route->uri()} -> {$route->getActionName()}</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<h2>Error al cargar rutas:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

// Mostrar info sobre las rutas que queremos acceder
echo "<h2>URLs a probar:</h2>";
echo "<ol>";
echo "<li><a href='/salvavidasapp/public/index.php/demo'>Demo Dashboard</a></li>";
echo "<li><a href='index.php/demo'>Demo (Relativo)</a></li>";
echo "<li><a href='index.php/salvavidasapp/dashboard'>Dashboard</a></li>";
echo "<li><a href='index.php/salvavidasapp/chat'>Chat</a></li>";
echo "<li><a href='index.php/salvavidasapp/risk'>Evaluación de Riesgo</a></li>";
echo "</ol>";

// Información del servidor
echo "<h2>Información del Servidor:</h2>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "</pre>";
