<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New website enquiry</title>
</head>
<body style="margin:0;padding:24px;background:#f7f7f5;font-family:Arial,Helvetica,sans-serif;color:#242424;">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #e7e5e4;border-radius:12px;overflow:hidden;">
        <div style="background:#3d0b13;padding:20px 24px;">
            <p style="margin:0;font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#f2c75c;">
                Bandgar Construction &amp; Developers
            </p>
            <h1 style="margin:6px 0 0;font-size:20px;color:#ffffff;">New website enquiry</h1>
        </div>

        <div style="padding:24px;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <tr>
                    <td style="padding:6px 0;color:#78716c;width:110px;">Name</td>
                    <td style="padding:6px 0;font-weight:bold;">{{ $inquiry->name }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#78716c;">Phone</td>
                    <td style="padding:6px 0;font-weight:bold;">
                        <a href="tel:{{ $inquiry->phone }}" style="color:#64151f;">{{ $inquiry->phone }}</a>
                    </td>
                </tr>
                @if ($inquiry->email)
                    <tr>
                        <td style="padding:6px 0;color:#78716c;">Email</td>
                        <td style="padding:6px 0;">
                            <a href="mailto:{{ $inquiry->email }}" style="color:#64151f;">{{ $inquiry->email }}</a>
                        </td>
                    </tr>
                @endif
                @if ($service)
                    <tr>
                        <td style="padding:6px 0;color:#78716c;">Service</td>
                        <td style="padding:6px 0;">{{ $service }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="padding:6px 0;color:#78716c;">Language</td>
                    <td style="padding:6px 0;">{{ strtoupper($inquiry->locale) }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#78716c;">Received</td>
                    <td style="padding:6px 0;">{{ $inquiry->created_at?->format('d M Y, H:i') }}</td>
                </tr>
            </table>

            <div style="margin-top:20px;padding-top:16px;border-top:1px solid #e7e5e4;">
                <p style="margin:0 0 8px;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#78716c;">Message</p>
                <p style="margin:0;font-size:14px;line-height:1.6;">{!! nl2br(e($inquiry->message)) !!}</p>
            </div>
        </div>

        <div style="padding:14px 24px;background:#f7f7f5;border-top:1px solid #e7e5e4;font-size:12px;color:#78716c;">
            This enquiry was submitted through the website contact form.
        </div>
    </div>
</body>
</html>
