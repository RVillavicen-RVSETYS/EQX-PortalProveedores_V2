<?php

namespace App\Models;

use Config\BD_Connect;
use PDO;

class Login_Mdl {
    private $db;

    public function __construct() {
        $this->db = BD_Connect::getConnection();
    }

    public function verificarUsuario($usuario, $password) {
        $sql = "SELECT su.id, su.usuario, su.pass, su.idNivel, su.idSucursal, su.estatus, 
                       sn.nombre AS nivel_nombre, sn.orden AS nivel_orden
                FROM segusuarios su
                INNER JOIN segniveles sn ON su.idNivel = sn.id
                WHERE su.usuario = :usuario AND su.estatus = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar que se encontró un usuario y que la contraseña es correcta
        if ($usuarioData && password_verify($password, $usuarioData['pass'])) {
            return $usuarioData;  // Retornar datos del usuario si la autenticación es exitosa
        } else {
            return null;  // Retornar null si la autenticación falla
        }
    }

    public function obtenerPrimerArea($idNivel) {
        $sql = "SELECT sa.id, sa.nombre, sa.link
                FROM segareas sa
                INNER JOIN segdetnivel sdn ON sa.id = sdn.idArea
                WHERE sdn.idNivel = :idNivel
                ORDER BY sa.orden ASC
                LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idNivel', $idNivel, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
