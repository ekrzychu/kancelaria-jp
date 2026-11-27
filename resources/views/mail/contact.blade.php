<!doctype html>
<html lang="pl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;background:#f8f5f0;color:#291d24;font:16px/1.55 Arial,sans-serif">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8f5f0">
    <tr><td align="center" style="padding:24px 12px">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#fffdf9;border:1px solid #dfd4ca">
            <tr><td style="background:#1d0613;color:#faf4ed;padding:20px 24px;font:22px Georgia,serif">Jaroch-Konwent Pakos</td></tr>
            <tr><td style="padding:24px">
                <h1 style="margin:0 0 16px;font:28px Georgia,serif">Nowa wiadomość z formularza</h1>
                <p><strong>Imię i nazwisko</strong><br>{{ $record->name }}</p>
                <p><strong>Adres e-mail</strong><br>{{ $record->email }}</p>
                <p><strong>Telefon</strong><br>{{ $record->phone ?? '—' }}</p>
                <h2 style="border-top:1px solid #dfd4ca;padding-top:18px;font:21px Georgia,serif">Treść wiadomości</h2>
                <p style="overflow-wrap:anywhere">{!! nl2br(e($record->message), false) !!}</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
