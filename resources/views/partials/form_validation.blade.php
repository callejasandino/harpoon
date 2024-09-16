<div class="container">
    <div class="title">Form Validation</div>
    @if(isset($data['description']) && isset($data['improvement']))
        <div class="description">{{ $data['description'] }}</div>
        <div class="improve">{{ $data['improvement'] }}</div>
    @else
        <ul>
            @foreach ($data as $formValidation)
                <li class="description">{{ $formValidation }}</li>
            @endforeach
        </ul>
    @endif
</div>