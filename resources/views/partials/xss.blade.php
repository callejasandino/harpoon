@if(isset($xss))
    <div class="container">
        <div class="title">XSS</div>
        <div class="description">X-XSS-Protection: <strong>{{ $xss['X-XSS-Protection'] }}</strong></div>
        <div class="description">Content-Security-Policy: <strong>{{ $xss['Content-Security-Policy'] }}</strong></div>
        <div class="improve">Improvement: <strong>{{ $xss['Improvement'] }}</strong></div>
    </div>
@endif