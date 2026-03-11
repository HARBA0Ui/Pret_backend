<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte accepte</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
                    <tr>
                        <td>
                            <h2 style="margin:0 0 16px 0;font-size:22px;line-height:1.3;">Tunisair Admin</h2>
                            <p style="margin:0 0 12px 0;font-size:14px;line-height:1.6;">
                                Bonjour {{ $employee->name ?? 'Employe' }},
                            </p>
                            <p style="margin:0 0 12px 0;font-size:14px;line-height:1.6;">
                                Votre compte est accepte.
                            </p>
                            <p style="margin:0 0 12px 0;font-size:14px;line-height:1.6;">
                                Vous pouvez maintenant vous connecter au portail avec votre email et votre mot de passe.
                            </p>
                            <p style="margin:18px 0 0 0;font-size:14px;line-height:1.6;">
                                Tunisair Admin
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
