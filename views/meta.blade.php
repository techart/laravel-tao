@foreach($meta as $name => $value)
    @if($name == 'title')
        <title>{{ $value }}</title>
    @else
    @endif
@endforeach