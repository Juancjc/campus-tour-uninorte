<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;background:#031329;color:#eef8ff;font-family:Arial,sans-serif">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#031329;padding:32px 12px">
        <tr><td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#082044;border:1px solid #164f82;border-radius:24px;overflow:hidden">
                <tr><td align="center" style="padding:28px 28px 12px"><img src="{{ rtrim($appUrl, '/') }}/images/campus-tour-logo.png" alt="Campus Tour UniNorte 2026" width="320" style="max-width:100%;height:auto"></td></tr>
                <tr><td style="padding:12px 32px 36px;text-align:center">
                    <p style="margin:0;color:#16b9ff;font-size:12px;font-weight:bold;letter-spacing:2px">VOCÊ ESTÁ DENTRO</p>
                    <h1 style="margin:12px 0 0;font-size:32px;line-height:1.15;color:#ffffff">Bem-vindo ao Campus Tour UniNorte 2026!</h1>
                    <p style="margin:18px 0;color:#c9def2;font-size:17px;line-height:1.6">Olá, {{ $user->name }}! Seu primeiro desafio já está esperando. Explore programação e segurança digital — e suba no ranking.</p>
                    <a href="{{ rtrim($appUrl, '/') }}/dashboard" style="display:inline-block;margin-top:10px;padding:15px 26px;border-radius:12px;background:#087ff5;color:#ffffff;text-decoration:none;font-weight:bold">ACESSAR CAMPUS TOUR</a>
                    <p style="margin:28px 0 0;color:#819bb5;font-size:12px">Campus Tour UniNorte 2026 • SI & ADS</p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
