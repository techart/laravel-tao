@foreach($meta as $name => $value)
    @if($name == 'title')
        <title>{{ $value }}</title>
    @else
        <meta name="{{ $name }}" content="{{ $value }}">
    @endif
@endforeach