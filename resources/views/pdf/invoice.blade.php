<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* DejaVu Sans встроен в dompdf и отлично поддерживает русский язык */
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .details { margin-bottom: 20px; width: 100%; border-collapse: collapse; }
        .details td { padding: 5px; vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f4f4f4; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .total { text-align: right; font-size: 16px; font-weight: bold; margin-top: 20px; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #777; }
        .signatures { margin-top: 50px; width: 100%; }
        .signatures td { width: 50%; text-align: center; padding-top: 40px; }
        .line { border-top: 1px solid #333; width: 80%; margin: 0 auto; margin-top: 5px; }
    </style>
</head>
<body>
<div class="header">
    <div class="title">Акт выполненных работ № {{ $ticket->id }}</div>
    <div>от {{ now()->format('d.m.Y') }}</div>
</div>

<table class="details">
    <tr>
        <td width="50%">
            <strong>Исполнитель:</strong> Сервисный центр "{{ $ticket->branch->name ?? 'Новые Решения' }}"<br>
            <strong>Устройство:</strong> {{ $ticket->device_brand }} {{ $ticket->device_model }}<br>
            <strong>S/N аппарата:</strong> {{ $ticket->serial_number ?? 'Б/Н' }}
        </td>
        <td width="50%">
            <strong>Заказчик:</strong> {{ $ticket->customer->name }}<br>
            <strong>Телефон:</strong> {{ $ticket->customer->phone }}<br>
            <strong>Заявленный дефект:</strong> {{ $ticket->defect_description }}
        </td>
    </tr>
</table>

<table class="table">
    <thead>
    <tr>
        <th>№</th>
        <th>Наименование работ / Запчастей</th>
        <th>Гарантия</th>
        <th>Сумма (₽)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($ticket->usedParts as $index => $part)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $part->inventoryItem->product->name ?? 'Деталь' }} (S/N: {{ $part->inventoryItem->serial_number ?? 'Б/Н' }})</td>
            <td>{{ $part->warranty_days }} дн.</td>
            <td>{{ number_format($part->selling_price, 2, '.', ' ') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="total">
    ИТОГО К ОПЛАТЕ: {{ number_format($ticket->estimated_cost, 2, '.', ' ') }} ₽
</div>

<table class="signatures">
    <tr>
        <td>
            <div>Исполнитель</div>
            <div class="line"></div>
        </td>
        <td>
            <div>Заказчик (Устройство получил, претензий не имею)</div>
            <div class="line"></div>
        </td>
    </tr>
</table>

<div class="footer">
    Сгенерировано в ERP System "Новые Решения" • ID Заявки: {{ $ticket->ulid }}
</div>
</body>
</html>
