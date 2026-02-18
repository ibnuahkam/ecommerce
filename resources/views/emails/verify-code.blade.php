<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Verifikasi Email</title>
</head>

<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
        <tr>
            <td align="center">

                <table width="520" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:12px;padding:40px;box-shadow:0 8px 24px rgba(0,0,0,0.06);">

                    <tr>
                        <td align="center">

                            <h2 style="margin:0;color:#111827;">
                                Verifikasi Email
                            </h2>

                            <p style="margin:12px 0 0;color:#6b7280;font-size:14px;">
                                Gunakan kode di bawah untuk menyelesaikan pendaftaran akun.
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:28px 0;">

                            <div
                                style="
                                    display:inline-block;
                                    padding:18px 28px;
                                    font-size:32px;
                                    letter-spacing:6px;
                                    font-weight:bold;
                                    background:#111827;
                                    color:#ffffff;
                                    border-radius:10px;
                                    ">
                                {{ $code }}
                            </div>

                        </td>
                    </tr>

                    <tr>
                        <td align="center">

                            <p style="margin:0;color:#6b7280;font-size:14px;">
                                Kode berlaku selama <b>10 menit</b>.
                            </p>

                            <p style="margin:10px 0 0;color:#9ca3af;font-size:13px;">
                                Jika kamu tidak merasa mendaftar, abaikan email ini.
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="padding-top:28px;border-top:1px solid #e5e7eb;" align="center">

                            <p style="margin:0;color:#9ca3af;font-size:12px;">
                                © {{ date('Y') }} Ecommerce API<br>
                                Secure Authentication System
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
