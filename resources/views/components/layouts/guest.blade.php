<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Статус ремонта' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-900 bg-gray-50 flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 text-white shadow-xl mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
        <h1 class="text-2xl font-black uppercase tracking-tighter italic text-gray-900">Новые Решения</h1>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Сервисный центр</p>
    </div>
    {{ $slot }}
    <div class="text-center mt-8 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
        © {{ date('Y') }} ERP System. Все права защищены.
    </div>
</div>

</body>
</html>
