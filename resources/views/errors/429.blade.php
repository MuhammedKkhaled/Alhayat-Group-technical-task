<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Too many attempts</title>
</head>
<body>
    <main>
        <h1>Too many attempts</h1>
        <p>Please wait a minute before trying to sign in again.</p>
        <a href="{{ route('login') }}">Return to sign in</a>
    </main>
</body>
</html>
