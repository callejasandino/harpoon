<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Analysis Results</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #2e2e2e; /* Slightly lighter than jet black */
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            width: 80%;
            margin: 20px 0;
            padding: 20px;
            background-color: #424242; /* Slightly lighter than the page background */
            color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px; /* 10px rounded corners */
            display: flex;
            flex-direction: column;
        }

        .title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .url {
            display: flex;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .description, .improve {
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .return-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #555;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
        }

        .return-btn:hover {
            background-color: #777;
        }
    </style>
</head>
<body>
    @if (!empty($improvements))
        @foreach ($improvements as $improvement)
            <div class="container">
                <div class="url">URL Tested: {{ $improvement['url'] }}</div>
            </div>

            @php
                $sections = [
                    'https' => 'HTTPS',
                    'hsts' => 'HSTS',
                    'csrf' => 'CSRF',
                    'cors' => 'CORS'
                ];
            @endphp

            @foreach ($sections as $key => $title)
                @include('partials.security_section', ['data' => $improvement[$key], 'title' => $title])
            @endforeach

            @include('partials.form_validation', ['data' => $improvement['form_validation']])
            @include('partials.security_headers', ['headers' => $improvement['security_headers'] ?? null])
            @include('partials.xss', ['xss' => $improvement['xss'] ?? null])
            @include('partials.ssl_tls', ['ssl' => $improvement['ssl'] ?? null])
        @endforeach    
    @endif
    
    <a href="{{ url('/') }}" class="return-btn">Return</a>

</body>
</html>

