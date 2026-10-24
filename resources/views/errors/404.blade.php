<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-xl flex-col items-center justify-center px-6 text-center">
        <p class="text-sm font-bold uppercase tracking-[0.3em] text-teal-700">404</p>
        <h1 class="mt-4 text-4xl font-bold tracking-tight">Page not found</h1>
        <p class="mt-4 text-slate-600">The address may be incorrect, or the page is no longer available.</p>
        <a href="{{ url('/doctors') }}" class="mt-8 rounded-lg bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">Browse doctors</a>
    </main>
</body>
</html>
