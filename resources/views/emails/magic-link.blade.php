<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
    <h2 style="color: #4f46e5; text-transform: uppercase;">Сервисный центр "Новые Решения"</h2>

    <p>Здравствуйте, <b>{{ $ticket->customer->name }}</b>!</p>

    <p>Ваше устройство <b>{{ $ticket->device_brand }} {{ $ticket->device_model }}</b> успешно прошло диагностику.</p>

    <div style="background-color: #f9fafb; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0; font-size: 12px; color: #6b7280; text-transform: uppercase;">Предварительная стоимость:</p>
        <h3 style="margin: 5px 0 0 0; font-size: 24px; color: #111827;">{{ number_format($ticket->estimated_cost, 0, '.', ' ') }} ₽</h3>
    </div>

    <p>Пожалуйста, перейдите по безопасной ссылке ниже, чтобы ознакомиться с результатами и принять решение о ремонте:</p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('client.portal', $link->token) }}" style="background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; text-transform: uppercase;">
            Согласовать ремонт
        </a>
    </div>

    <p style="font-size: 12px; color: #9ca3af;">Если кнопка не работает, скопируйте эту ссылку в браузер:<br>
        {{ route('client.portal', $link->token) }}</p>
</div>
