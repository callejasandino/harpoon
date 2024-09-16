@if(isset($data['description']) && isset($data['improvement']))
    <div class="container">
        <div class="title">{{ $title }}</div>
        <div class="description">{{ $data['description'] }}</div>
        <div class="improve">{{ $data['improvement'] }}</div>
    </div>
@endif