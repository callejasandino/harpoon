@if(isset($headers))
    <div class="container">
        <div class="title">Security Headers</div>
        <ul>
            @foreach ($headers as $header => $value)
                <li class="description">{{ $header }}: <strong>{{ $value }}</strong></li>
            @endforeach
        </ul>
    </div>
@endif