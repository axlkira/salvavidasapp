<?php
/**
 * Script para limpiar emojis de la tabla de mensajes y corregir problemas de collation
 * Ejecutar desde el navegador o mediante PHP CLI
 */

// Inicializar conexión a la base de datos
require_once 'vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configuración de la base de datos
$host = $_ENV['DB_HOST'] ?? 'localhost';
$database = $_ENV['DB_DATABASE'] ?? 'laravel';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conectado a la base de datos correctamente.<br>";
    
    // Función para sanitizar contenido eliminando emojis
    function sanitizeContent($content) {
        if (empty($content)) {
            return '';
        }
        
        // Convertir a UTF-8 si no lo es ya
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        
        // Eliminar caracteres invisibles problemáticos
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $content);
        
        // Eliminar TODOS los emojis y caracteres especiales que pueden causar problemas con utf8mb3
        // Esta expresión regular elimina todos los caracteres fuera del BMP (Basic Multilingual Plane)
        $content = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $content);
        
        // Eliminar emojis que están en el BMP (Basic Multilingual Plane)
        $emoji_pattern = '/[\x{1F600}-\x{1F64F}|\x{1F300}-\x{1F5FF}|\x{1F680}-\x{1F6FF}|\x{1F700}-\x{1F77F}|\x{1F780}-\x{1F7FF}|\x{1F800}-\x{1F8FF}|\x{1F900}-\x{1F9FF}|\x{1FA00}-\x{1FA6F}|\x{1FA70}-\x{1FAFF}|\x{2600}-\x{26FF}|\x{2700}-\x{27BF}]/u';
        $content = preg_replace($emoji_pattern, '', $content);
        
        // Si las expresiones regulares fallan, usar un método más agresivo para garantizar compatibilidad
        if ($content === null) {
            // Mantener solo caracteres ASCII y caracteres latinos extendidos (incluye ñ y acentos)
            $content = preg_replace('/[^\x20-\x7E\xA0-\xFF]/u', '', $content);
        }
        
        return $content;
    }
    
    // 1. Alterar la tabla para usar la colación correcta
    $alterTableQuery = "ALTER TABLE messages CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
    $pdo->exec($alterTableQuery);
    echo "Tabla messages convertida a utf8_general_ci.<br>";
    
    // 2. Obtener todos los mensajes
    $messagesQuery = "SELECT id, content FROM messages";
    $stmt = $pdo->query($messagesQuery);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Encontrados " . count($messages) . " mensajes para procesar.<br>";
    
    // 3. Limpiar y actualizar cada mensaje
    $countUpdated = 0;
    $updateStmt = $pdo->prepare("UPDATE messages SET content = ? WHERE id = ?");
    
    foreach ($messages as $message) {
        $sanitizedContent = sanitizeContent($message['content']);
        
        // Solo actualizar si el contenido ha cambiado
        if ($sanitizedContent !== $message['content']) {
            $updateStmt->execute([$sanitizedContent, $message['id']]);
            $countUpdated++;
        }
    }
    
    echo "Actualizados $countUpdated mensajes para eliminar emojis y caracteres especiales.<br>";
    echo "Proceso completado con éxito. La base de datos ahora debería estar libre de problemas de collation.<br>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
