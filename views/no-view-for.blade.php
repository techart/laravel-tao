@php
    unset($_views['tao1']);
    unset($_views['tao2']);
@endphp
<div style="border: 1px solid red;padding: 10px;margin: 10px 0;">
  <h5>{{ $item->field('title') }}</h5>
  <p>No views found for datatype <b>{{ $item->getDatatype() }}</b></p>
  <ul>
    @foreach($_views as $_view)
        <li>{{ $_view }}</li>
    @endforeach
  </ul>
</div>