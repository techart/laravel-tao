@section('right_buttons')

    @if ($can_add)
        <a href="{{ url($controller->actionUrl('add')) }}" class="btn btn-success"><i class="icon-plus icon-white"></i> {{ $add_text }}</a>
    @endif
    
    @if ($with_filter)
        <a href="" class="btn btn-inverse"><i class="icon-search icon-white"></i> Поиск</a>
    @endif
    
@endsection