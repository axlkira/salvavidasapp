<?php
/**
 * Función para sanitizar contenido y eliminar emojis
 * Esta función se puede copiar al ChatController.php
 */

/**
 * Sanitiza el contenido del mensaje para eliminar caracteres problemáticos
 * 
 * @param string $content Contenido a sanitizar
 * @return string Contenido sanitizado
 */
private function sanitizeContent($content)
{
    if (empty($content)) {
        return '';
    }
    
    // Convertir a UTF-8 si no lo es ya
    if (!mb_check_encoding($content, 'UTF-8')) {
        $content = mb_convert_encoding($content, 'UTF-8');
    }
    
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
