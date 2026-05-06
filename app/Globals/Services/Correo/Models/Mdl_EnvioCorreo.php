<?php

/**
 * Modelo de Envío de Correo (Wrapper de PHPMailer).
 * Adaptado del ERP SilmeAgroPVT para el Portal de Proveedores.
 */

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EnvioCorreo extends PHPMailer
{
    private $correoRemitente;
    private $passwordRemitente;
    public $correosDestino; // Array asociativo ["Nombre" => "correo@dominio.com"]
    public $RGBColor;
    private $redaccionCorreo;
    private $_haylogo;
    private $asunto;
    private $str_hostSMTP;
    private $debugEmail;
    private $debug;

    public function __construct(
        $hostSMTP,
        $correosDestino,
        $correoRemitente,
        $passwordRemitente,
        $asunto,
        $_haylogo = true,
        $rutaLogo = "assets/images/silmeAgroLogo1.png",
        $RGBColor = "#000000",
        $debug = 0
    ) {
        parent::__construct(true); // Habilitar excepciones

        $this->correosDestino = $correosDestino;
        $this->correoRemitente = $correoRemitente;
        $this->passwordRemitente = $passwordRemitente;
        $this->RGBColor = $RGBColor;
        $this->asunto = $asunto;
        $this->str_hostSMTP = $hostSMTP;
        $this->debug = $debug;
        $this->debugEmail = ($debug == 1) ? 2 : 0; // 2 para mensajes del cliente y servidor

        $this->CharSet = 'UTF-8';
        $this->isHTML(true);

        if ($_haylogo) {
            $this->_haylogo = $_haylogo;
            // Normalizar ruta del logo
            $rutaLogoNormalizada = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaLogo), DIRECTORY_SEPARATOR);

            // Buscar en la carpeta public del Portal (5 niveles arriba desde Models)
            $rutaBasePortal = dirname(__DIR__, 5);
            $rutaLogoFisica = $rutaBasePortal . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $rutaLogoNormalizada;

            if (file_exists($rutaLogoFisica)) {
                $this->addEmbeddedImage($rutaLogoFisica, 'logo_2u');
            } else {
                // Ruta de respaldo
                $rutaLogoRespaldo = $rutaBasePortal . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'silmeAgroLogo1.png';
                if (file_exists($rutaLogoRespaldo)) {
                    $this->addEmbeddedImage($rutaLogoRespaldo, 'logo_2u');
                } else {
                    $this->_haylogo = false;
                    if ($this->debug == 1) {
                        error_log("Logo no encontrado. Ruta: " . $rutaLogoFisica);
                    }
                }
            }
        }
    }

    /**
     * Agrega contenido HTML al cuerpo del correo.
     */
    public function crearContenido($text_contenido)
    {
        $this->redaccionCorreo .= $text_contenido;
    }

    /**
     * Adjunta archivos al correo.
     * @param array $Array_RutasArchivos ["ruta_fisica" => "nombre_en_correo.pdf"]
     */
    public function adjuntarArchivos($Array_RutasArchivos)
    {
        foreach ($Array_RutasArchivos as $ruta => $nombreArchivo) {
            if (file_exists($ruta)) {
                $this->addAttachment($ruta, $nombreArchivo);
            }
        }
    }

    /**
     * Envía el correo vía SMTP.
     */
    public function enviarCorreo()
    {
        try {
            $this->SMTPDebug = $this->debugEmail;
            $this->isSMTP();
            $this->Host = $this->str_hostSMTP;
            $this->SMTPAuth = true;
            $this->Username = $this->correoRemitente;
            $this->Password = $this->passwordRemitente;
            $this->SMTPSecure = 'tls';
            $this->Port = 587;

            $this->setFrom($this->correoRemitente, 'Portal de Proveedores EQX');

            foreach ($this->correosDestino as $nombre => $correo) {
                $this->addAddress($correo, is_string($nombre) ? $nombre : '');
            }

            $this->Subject = $this->asunto;
            $this->Body = $this->redaccionCorreo;

            if ($this->send()) {
                return ['1', "Correo Enviado Exitosamente."];
            } else {
                return ['0', "Problemas al Enviar Correo: " . $this->ErrorInfo];
            }
        } catch (Exception $e) {
            return ['0', "Error: " . $e->getMessage()];
        }
    }
}
