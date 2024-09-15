<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Box Layout</title>
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
                <div class="url">
                    URL Tested: {{ $improvement['url'] }}
                </div>
            </div>

            @if($improvement['https']['description'] && $improvement['https']['improvement'])
                <div class="container">
                    <div class="title">HTTPS</div>
                    <div class="description">{{ $improvement['https']['description'] }}</div>
                    <div class="improve">{{ $improvement['https']['improvement'] }}</div>
                </div>
            @endif

            @if($improvement['hsts']['description'] && $improvement['hsts']['improvement'])
                <div class="container">
                    <div class="title">HSTS</div>
                    <div class="description">{{ $improvement['hsts']['description'] }}</div>
                    <div class="improve">{{ $improvement['hsts']['improvement'] }}</div>
                </div>
            @endif

            @if($improvement['csrf']['description'] && $improvement['csrf']['improvement'])
                <div class="container">
                    <div class="title">CSRF</div>
                    <div class="description">{{ $improvement['csrf']['description'] }}</div>
                    <div class="improve">{{ $improvement['csrf']['improvement'] }}</div>
                </div>
            @endif

            @if($improvement['cors']['description'] && $improvement['cors']['improvement'])
                <div class="container">
                    <div class="title">CORS</div>
                    <div class="description">{{ $improvement['cors']['description'] }}</div>
                    <div class="improve">{{ $improvement['cors']['improvement'] }}</div>
                </div>
            @endif

            @if(isset($improvement['form_validation']['description']) && isset($improvement['form_validation']['improvement']))
                <div class="container">
                    
                    <div class="description">{{ $improvement['form_validation']['description'] }}</div>
                    <div class="improve">{{ $improvement['form_validation']['improvement'] }}</div>
                </div>
            @else
                <div class="container">
                    <div class="title">Form Validation</div>
                    <ul>
                        @foreach ($improvement['form_validation'] as $formValidation)
                            <li class="description">{{$formValidation}}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(isset($improvement['security_headers']))
                <div class="container">
                    <div class="title">Security Headers</div>
                    <ul>
                        <li class="description">'Content-Security-Policy: <strong>{{$improvement['security_headers']['Content-Security-Policy']}}</strong></li>
                        <li class="description">'X-Frame-Options: <strong>{{$improvement['security_headers']['X-Frame-Options']}}</strong></li>
                        <li class="description">'X-Content-Type-Options: <strong>{{$improvement['security_headers']['X-Content-Type-Options']}}</strong></li>
                        <li class="description">'Strict-Transport-Security: <strong>{{$improvement['security_headers']['Strict-Transport-Security']}}</strong></li>
                        <li class="description">'Referrer-Policy: <strong>{{$improvement['security_headers']['Referrer-Policy']}}</strong></li>
                        <li class="description">'Permissions-Policy: <strong>{{$improvement['security_headers']['Permissions-Policy']}}</strong></li>
                        <li class="description">'X-XSS-Protection: <strong>{{$improvement['security_headers']['X-XSS-Protection']}}</strong></li>
                    </ul>
                </div>
            @endif

            @if(isset($improvement['xss']['X-XSS-Protection']))
                <div class="container">
                    <div class="title">XSS</div>
                    <div class="description">X-XSS-Protection: <strong>{{ $improvement['xss']['X-XSS-Protection'] }}</strong></div>
                    <div class="description">Content-Security-Policy: <strong>{{ $improvement['xss']['Content-Security-Policy'] }}</strong></div>
                    <div class="improve">Improvement: <strong>{{ $improvement['xss']['Improvement'] }}</strong></div>
                </div>
            @endif

            @if(isset($improvement['ssl']['ssl_tls']))
                <div class="container">
                    <div class="title">SSL / TLS</div>
                    <div class="description">SSL / TLS: <strong>{{ $improvement['ssl']['ssl_tls'] }}</strong></div>
                    <div class="description">Description: <strong>{{ $improvement['ssl']['description'] }}</strong></div>
                    <div class="improve">Improvemnet: <strong>{{ $improvement['ssl']['improvement'] }}</strong></div>
                </div>
            @endif
        @endforeach    
    @endif
    
    <a href="http://127.0.0.1:8000/" class="return-btn">Return</a>

</body>
</html>
