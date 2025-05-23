<?php

echo '<h1>SalvaVidas App Demo</h1>';
echo '<p>Esta es una página de demostración para verificar que el directorio público es accesible.</p>';
echo '<p>Por favor, intenta acceder a las siguientes rutas:</p>';
echo '<ul>';
echo '<li><a href="/salvavidasapp/public/index.php/demo">Demo Dashboard</a></li>';
echo '<li><a href="/salvavidasapp/public/index.php/salvavidasapp/dashboard">Dashboard</a></li>';
echo '<li><a href="/salvavidasapp/public/index.php/salvavidasapp/chat">Chat</a></li>';
echo '<li><a href="/salvavidasapp/public/index.php/salvavidasapp/risk">Evaluación de Riesgo</a></li>';
echo '</ul>';

echo '<p>Alternativas directas (si las anteriores no funcionan):</p>';
echo '<ul>';
echo '<li><a href="index.php/demo">Demo (Relativo)</a></li>';
echo '<li><a href="index.php/salvavidasapp/dashboard">Dashboard (Relativo)</a></li>';
echo '<li><a href="index.html">Página de Bienvenida HTML</a></li>';
echo '</ul>';
echo '<p>Información del servidor:</p>';
echo '<pre>';
echo 'PHP Version: ' . PHP_VERSION . "\n";
echo 'Server Software: ' . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo 'Document Root: ' . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo 'Script Filename: ' . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo '</pre>';
