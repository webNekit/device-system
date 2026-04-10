<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Устройство готово к выдаче</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9fafb;
        }

        .container {
            background-color: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .header {
            text-align: center;
            margin-bottom: 32px;
        }

        .header h1 {
            color: #4f46e5;
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .header p {
            color: #6b7280;
            font-size: 14px;
            margin: 8px 0 0;
        }

        .info-box {
            background-color: #f3f4f6;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-weight: 700;
            color: #1f2937;
        }

        .cta-button {
            display: inline-block;
            background-color: #4f46e5;
            color: white;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>✅ Готово к выдаче!</h1>
            <p>Ваше устройство прошло все проверки и готово к получению</p>
        </div>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Номер заявки</span>
                <span class="info-value">{{ $ticket->ulid }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Устройство</span>
                <span class="info-value">{{ $ticket->device_brand }} {{ $ticket->device_model }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Стоимость ремонта</span>
                <span class="info-value">{{ number_format($ticket->estimated_cost, 0, '.', ' ') }} ₽</span>
            </div>
            <div class="info-row">
                <span class="info-label">Филиал</span>
                <span class="info-value">{{ $ticket->branch->name ?? 'Не указан' }}</span>
            </div>
        </div>

        <p style="text-align: center; color: #4b5563; font-size: 14px; margin-bottom: 24px;">
            Пожалуйста, посетите наш сервисный центр для получения устройства.
            Не забудьте взять с собой документ, удостоверяющий личность.
        </p>

        <div class="footer">
            <p>Это письмо отправлено автоматически. Пожалуйста, не отвечайте на него.</p>
            <p>&copy; {{ date('Y') }} Сервисный центр. Все права защищены.</p>
        </div>
    </div>
</body>

</html>