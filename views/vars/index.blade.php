@extends('~layout')

{{ Assets::setMeta('title', $title) }}
@section('h1'){{ $title }}@endsection

@section('content')
    @foreach($varGroups as $group)
        @include('vars ~ group')
    @endforeach
@endsection