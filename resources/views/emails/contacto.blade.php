<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
    </head>
    <body style="font-family: sans-serif; color: #292524; margin: 0; padding: 0; background: #f5f5f4;">
        <div style="max-width: 560px; margin: 0 auto; padding: 32px 24px;">
            <h2 style="font-size: 20px; margin: 0 0 4px;">Nuevo mensaje de contacto</h2>
            <p style="font-size: 13px; color: #78716c; margin: 0 0 24px;">Recibido a través del formulario de Aliste.info</p>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
                <tr>
                    <td style="padding: 4px 0; font-size: 13px; color: #78716c; width: 90px;">Nombre</td>
                    <td style="padding: 4px 0; font-size: 14px;">{{ $nombreRemitente }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; font-size: 13px; color: #78716c;">Email</td>
                    <td style="padding: 4px 0; font-size: 14px;">{{ $emailRemitente }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; font-size: 13px; color: #78716c;">Asunto</td>
                    <td style="padding: 4px 0; font-size: 14px;">{{ $asunto }}</td>
                </tr>
            </table>

            <div style="background: white; border-radius: 12px; padding: 20px; font-size: 14px; line-height: 1.6; white-space: pre-line;">{{ $descripcion }}</div>

            <p style="font-size: 12px; color: #a8a29e; margin-top: 24px;">Puedes responder directamente a este email, se enviará a {{ $emailRemitente }}.</p>
        </div>
    </body>
</html>
