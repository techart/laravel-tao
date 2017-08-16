@php
    $classes = array();
    
    if ($number == $text) {
        if ($number == 1) {
            $classes[] = 'first';
        }
    
        if ($number == $numpages) {
            $classes[] = 'last';
        }
    
        if ($number == $page) {
            $classes[] = 'current';
            $classes[] = 'btn-inverse';
        }
    }
@endphp

<a class="btn {{ implode(' ', $classes) }}" href="{{ call_user_func($pager_callback, $number) }}">{{ $text }}</a>