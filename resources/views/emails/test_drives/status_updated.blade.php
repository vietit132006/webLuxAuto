<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Cập nhật lịch lái thử</title>
</head>
<body style="margin:0;background:#f6f7fb;color:#172033;font-family:Arial,Helvetica,sans-serif;">
    <div style="max-width:640px;margin:0 auto;padding:28px 16px;">
        <div style="background:#10141c;color:#f8fafc;border-radius:12px;padding:24px;border:1px solid #252d3a;">
            <h1 style="margin:0 0 12px;font-size:22px;">Lux Auto</h1>
            <p style="margin:0;color:#cbd5e1;">Lịch lái thử {{ $ticket->display_code }} đã được cập nhật.</p>
        </div>

        <div style="background:#ffffff;border-radius:12px;padding:24px;margin-top:16px;border:1px solid #e5e7eb;">
            <p style="margin-top:0;">Xin chào <strong>{{ $customerName }}</strong>,</p>
            <p>Thông tin lịch lái thử của bạn:</p>

            <table style="width:100%;border-collapse:collapse;margin:18px 0;">
                <tr>
                    <td style="padding:10px 0;color:#64748b;width:160px;">Xe</td>
                    <td style="padding:10px 0;font-weight:700;">{{ $carName }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 0;color:#64748b;">Ngày hẹn</td>
                    <td style="padding:10px 0;font-weight:700;">{{ $appointmentText }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 0;color:#64748b;">Trạng thái</td>
                    <td style="padding:10px 0;font-weight:700;">{{ $statusText }}</td>
                </tr>
            </table>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <strong>Thông tin liên hệ showroom</strong>
                <div style="margin-top:8px;color:#334155;">
                    {{ $showroomContact['name'] }}<br>
                    Hotline: {{ $showroomContact['phone'] }}<br>
                    Email: {{ $showroomContact['email'] }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
