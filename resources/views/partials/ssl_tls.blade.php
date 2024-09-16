@if(isset($ssl['ssl_tls']))
    <div class="container">
        <div class="title">SSL / TLS</div>
        <div class="description">SSL / TLS: <strong>{{ $ssl['ssl_tls'] }}</strong></div>
        <div class="description">Description: <strong>{{ $ssl['description'] }}</strong></div>
        <div class="improve">Improvement: <strong>{{ $ssl['improvement'] }}</strong></div>
    </div>
@endif