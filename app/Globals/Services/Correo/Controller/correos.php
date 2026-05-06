<?php

/**
 * Controlador para la generación y envío de correos electrónicos.
 * Basado en la arquitectura del ERP SilmeAgroPVT.
 */

require_once __DIR__ . "/../Models/Mdl_DirectorioCorreo.php";
require_once __DIR__ . "/../Models/Mdl_EnvioCorreo.php";

/**
 * Envía una notificación de rechazo de factura al proveedor.
 * 
 * @param array $datosProveedor - [Proveedor, Correo, RFC, RazonSocial]
 * @param int $acuse - Folio/Acuse de la factura
 * @param string $motivo - Descripción del motivo del rechazo
 * @param string $correoAdmin - (Opcional) Correo para copia al administrador
 */
function enviarCorreoRechazoFactura($datosProveedor, $acuse, $motivo, $correoAdmin = '')
{
    $debug = 0;

    // 1. Obtener credenciales SMTP (ID 1 es el estándar en el sistema)
    $obj_DCorreos = new Directorio($debug);
    $detCorreo = $obj_DCorreos->getDetCorreo(1);

    if (!$detCorreo) {
        error_log("[" . date("Y-m-d H:i:s") . "] No se encontraron credenciales SMTP en directorio_correos ID 1.");
        return ['0', "No se pudo obtener la configuración de correo."];
    }

    $smtp = $detCorreo["hostSMTP"];
    $correoRemitente = $detCorreo["correo"];
    $pass = $detCorreo["pass"];

    // 2. Preparar destinatarios (Proveedor + Copia Admin)
    $destino = [];
    $correoProv = trim($datosProveedor['Correo']);
    if (!empty($correoProv)) {
        $destino["Proveedor"] = $correoProv;
    }

    if (!empty($correoAdmin)) {
        $destino["Administrador"] = trim($correoAdmin);
    }

    if (empty($destino)) {
        return ['0', "No hay destinatarios válidos."];
    }

    // 3. Crear el objeto de correo
    $asunto = "Notificación de Rechazo de Factura - Acuse: " . $acuse;
    // Usamos color rojo oscuro (#8B0000) para el tema de rechazo
    $obj_correo = new EnvioCorreo($smtp, $destino, $correoRemitente, $pass, $asunto, true, "assets/images/silmeAgroLogo1.png", "#15244a", $debug);

    // 4. Generar el contenido HTML
    $textoContenido = crearContenidoHTMLRechazo($datosProveedor, $acuse, $motivo);
    $obj_correo->crearContenido($textoContenido);

    // 5. Enviar
    $respuesta = $obj_correo->enviarCorreo();

    return $respuesta;
}

/**
 * Genera el cuerpo HTML del correo de rechazo.
 */
function crearContenidoHTMLRechazo($datos, $acuse, $motivo)
{
    $numProveedor = htmlspecialchars($datos['IdProveedor'], ENT_QUOTES, 'UTF-8');
    $proveedor = htmlspecialchars($datos['Proveedor'], ENT_QUOTES, 'UTF-8');
    $rfc = htmlspecialchars($datos['RFC'], ENT_QUOTES, 'UTF-8');
    $razonSocialEmpresa = htmlspecialchars($datos['RazonSocial'], ENT_QUOTES, 'UTF-8');
    $folioAcuse = htmlspecialchars($acuse, ENT_QUOTES, 'UTF-8');
    $fechaActual = htmlspecialchars($datos['FechaVal'], ENT_QUOTES, 'UTF-8');
    $motivoRechazo = nl2br(htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8'));

    return "
    <div style='margin:0; padding:24px 0; font-family:Segoe UI, Arial, sans-serif; color:#243127;'>
        <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='100%' style='width:100%; border-collapse:collapse;'>
            <tr>
                <td align='center' style='padding:0 16px;'>
                    <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='680' style='width:680px; max-width:100%; border-collapse:separate; background:#ffffff; border:1px solid #ebdbd7; border-radius:18px; overflow:hidden;'>
                        <!-- Header -->
                        <tr>
                            <td style='padding:0; background: #15244a;'>
                                <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='100%' style='width:100%; border-collapse:collapse;'>
                                    <tr>
                                        <td style='padding:28px 32px 18px 32px;'>
                                            <img src='cid:logo_2u' alt='Logo' style='max-width:170px; max-height:100px; display:block; margin-bottom:18px;'>
                                            <div style='font-size:12px; letter-spacing:1.8px; text-transform:uppercase; color:#dff5dc; font-weight:700; margin-bottom:8px;'>Aviso de Portal de Proveedores</div>
                                            <div style='font-size:28px; line-height:34px; font-weight:800; color:#ffffff; margin-bottom:10px;'>Factura Rechazada</div>
                                            <div style='font-size:15px; line-height:24px; color:#dff5dc;'>Se ha revisado su documento y se ha detectado una inconsistencia que requiere su atención.</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- Body -->
                        <tr>
                            <td style='padding:30px 32px 14px 32px;'>
                                <div style='font-size:15px; line-height:25px; color:#39463d;'>
                                    <span style='font-weight:700; color:#17351f;'>Estimado proveedor: </span>
                                    <span style='color:#026b1e; font-weight:700;'>$numProveedor $proveedor</span>
                                </div>
                                <div style='margin-top:12px; font-size:15px; line-height:25px; color:#4b5d51;'>
                                    Le informamos que la factura enviada a través de nuestro portal ha sido <strong>rechazada</strong> por el área administrativa.
                                </div>
                            </td>
                        </tr>
                        <!-- Datos Tarjetas -->
                        <tr>
                            <td style='padding:0 32px 8px 32px;'>
                                <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='100%' style='width:100%; border-collapse:separate; border-spacing:0 12px;'>
                                    <tr>
                                        <td width='50%' style='padding-right:6px; vertical-align:top;'>
                                            <div style='background:#f7fbf5; border:1px solid #ebdbd7; border-radius:14px; padding:16px 18px;'>
                                                <div style='font-size:11px; text-transform:uppercase; letter-spacing:1.4px; color:#6f8574; font-weight:700; margin-bottom:6px;'>No. de Acuse</div>
                                                <div style='font-size:22px; line-height:28px; color:#026b1e; font-weight:800;'>$folioAcuse</div>
                                            </div>
                                        </td>
                                        <td width='50%' style='padding-left:6px; vertical-align:top;'>
                                            <div style='background:#f7fbf5; border:1px solid #ebdbd7; border-radius:14px; padding:16px 18px;'>
                                                <div style='font-size:11px; text-transform:uppercase; letter-spacing:1.4px; color:#6f8574; font-weight:700; margin-bottom:6px;'>Fecha de Rechazo</div>
                                                <div style='font-size:18px; line-height:28px; color:#026b1e; font-weight:700;'>$fechaActual Hrs.</div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- Motivo del Rechazo -->
                        <tr>
                            <td style='padding:10px 32px 8px 32px;'>
                                <div style='font-size:13px; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:#c0392b; margin-bottom:14px;'>Motivo del Rechazo</div>
                                <div style='background:#fdeaea; border:1px solid #c0392b; border-radius:12px; padding:18px; font-size:15px; line-height:24px; color:#4a1520; font-style:italic;'>
                                    $motivoRechazo
                                </div>
                            </td>
                        </tr>
                        <!-- Instrucciones -->
                        <tr>
                            <td style='padding:12px 32px 0 32px;'>
                                <div style='font-size:14px; line-height:22px; color:#4b5d51;'>
                                    <strong>¿Qué debe hacer ahora?</strong><br>
                                    Por favor, revise el motivo detallado arriba, realice las correcciones necesarias en su factura y proceda a cargarla nuevamente, utilizando su misma orden de compra y sus numeros de Recepción a través del portal en la sección correspondiente.
                                </div>
                            </td>
                        </tr>
                        <!-- Footer -->
                        <tr>
                            <td style='padding:24px 32px 30px 32px;'>
                                <br>
                                <div style='font-size:15px; line-height:24px; color:#4b5d51;'>Agradecemos su pronta atención para agilizar su proceso de pago.</div>
                                <br>
                                <div style='font-size:15px; line-height:24px; color:#4b5d51;'>Atentamente: </div>
                                <div style='margin-top:16px; font-size:16px; line-height:25px; color:#17351f; font-weight:700;'>$razonSocialEmpresa</div>
                                <div style='font-size:13px; line-height:20px; color:#6f8574;'>RFC: $rfc</div>
                            </td>
                        </tr>
                    </table>
                    <div style='padding:20px; font-size:11px; color:#999; text-align:center;'>
                        Este es un correo automático, por favor no responda a este mensaje.
                    </div>
                </td>
            </tr>
        </table>
    </div>";
}
