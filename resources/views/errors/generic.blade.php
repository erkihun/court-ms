<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Error') }}</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }
        .wrap {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 560px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(2, 6, 23, 0.06);
            text-align: center;
        }
        h1 {
            margin: 0 0 10px;
            font-size: 1.2rem;
        }
        p {
            margin: 0;
            color: #334155;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1>{{ __('An error occurred') }}</h1>
            <p>{{ $message ?? 'Something went wrong. Please try again later.' }}</p>
        </div>
    </div>
</body>
</html>
