<ul class="nav">
@foreach($links as $link)
    @if($link->count()==0)
        <li>@include('navigation ~ link')</li>
    @else
        <li class="dropdown">
            <a href="{{ url($link->url) }}" data-toggle="dropdown" class="dropdown-toggle">{{ $link->title }} <b class="caret"></b></a>
            {!! $link->render('ul', ['args' => 'class="dropdown-menu"']) !!}
        </li>
    @endif
@endforeach
</ul>