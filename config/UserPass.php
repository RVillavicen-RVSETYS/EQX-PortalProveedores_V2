<?php
/**
 * Script para cambiar la contraseña de un usuario
 * 
 * INSTRUCCIONES:
 * 1. Asigna el ID del usuario en la variable $idUsuario
 * 2. Asigna la nueva contraseña en la variable $password
 * 3. Ejecuta este archivo directamente desde el navegador o línea de comandos
 */

// ============================================
// CONFIGURACIÓN - Edita estas variables
// ============================================
$idUsuario = 7;        // ID del usuario en la tabla segusuarios
$password = 'Administradora.2026';  // Nueva contraseña en texto plano

// ============================================
// NO EDITAR A PARTIR DE AQUÍ
// ============================================

// Incluir archivos necesarios
define('INCLUDE_CHECK', true);
require_once 'BD_Connect.php';
require_once 'constantes.php';

// Verificar que las variables estén definidas
if (empty($idUsuario) || empty($password)) {
    die("ERROR: Debes definir tanto \$idUsuario como \$password antes de ejecutar el script.\n");
}

try {
    echo "========================================\n";
    echo "CAMBIO DE CONTRASEÑA DE USUARIO\n";
    echo "========================================\n\n";
    
    echo "ID Usuario: $idUsuario\n";
    echo "Nueva Contraseña: [OCULTA POR SEGURIDAD]\n\n";
    
    // Verificar que el usuario existe
    $sqlVerificar = "SELECT id, usuario FROM segusuarios WHERE id = :idUsuario";
    $stmtVerificar = BD_Connect::prepare($sqlVerificar);
    $stmtVerificar->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $stmtVerificar->execute();
    $usuario = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        die("ERROR: No se encontró un usuario con ID: $idUsuario\n");
    }
    
    echo "Usuario encontrado: {$usuario['usuario']}\n";
    echo "Procesando...\n\n";
    
    // Hashear la contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    if (!$passwordHash) {
        die("ERROR: No se pudo generar el hash de la contraseña.\n");
    }
    
    // Actualizar la contraseña en la base de datos
    $sql = "UPDATE segusuarios SET pass = :password WHERE id = :idUsuario";
    $stmt = BD_Connect::prepare($sql);
    $stmt->bindParam(':password', $passwordHash, PDO::PARAM_STR);
    $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();
    
    $filasAfectadas = $stmt->rowCount();
    
    if ($filasAfectadas > 0) {
        echo "========================================\n";
        echo "✓ CONTRASEÑA ACTUALIZADA EXITOSAMENTE\n";
        echo "========================================\n\n";
        echo "Usuario: {$usuario['usuario']}\n";
        echo "ID: $idUsuario\n";
        echo "Filas afectadas: $filasAfectadas\n";
        echo "\nLa contraseña ha sido cambiada correctamente.\n";
        echo "Hash generado: " . substr($passwordHash, 0, 30) . "...\n";
    } else {
        echo "========================================\n";
        echo "⚠ ADVERTENCIA\n";
        echo "========================================\n\n";
        echo "No se actualizó ninguna fila. Verifica que el ID del usuario sea correcto.\n";
    }
    
} catch (PDOException $e) {
    echo "========================================\n";
    echo "✗ ERROR EN LA BASE DE DATOS\n";
    echo "========================================\n\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    
    $timestamp = date("Y-m-d H:i:s");
    error_log("[$timestamp] config/UserPass.php -> Error: " . $e->getMessage(), 3, LOG_FILE_BD);
    
} catch (Exception $e) {
    echo "========================================\n";
    echo "✗ ERROR GENERAL\n";
    echo "========================================\n\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    
    $timestamp = date("Y-m-d H:i:s");
    error_log("[$timestamp] config/UserPass.php -> Error: " . $e->getMessage(), 3, LOG_FILE);
    
} finally {
    // Cerrar la conexión
    BD_Connect::closeConnection();
    echo "\n========================================\n";
    echo "Proceso finalizado.\n";
    echo "========================================\n";
}
?>

