@if ($with_datepicker)
  {{ \Assets::useBottomScript('/tao/scripts/jquery-ui.min.js') }}
  {{ \Assets::useFile('/tao/styles/jquery-ui.css') }}
  {{ \Assets::addBottomLine('<script>$(function() {$(".date_input_'.$field->name.'").datepicker({dateFormat: "dd.mm.yy"});});</script>') }}
@endif
<input
  type="text"
  name="{{ $field->name }}"
  class="date_input_{{ $field->name }} input string {{ $field->classForInput() }}"
  style="{!! $field->styleForInput() !!}"
  value="{{ $item[$field->name]==0? '' : date($field->generateFormat(), $item[$field->name]) }}"
>