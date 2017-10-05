<input
  type="text"
  name="{{ $field->name }}"
  class="input string {{ $field->classForInput() }}"
  style="{!! $field->styleForInput() !!}"
  value="{{ date($field->generateFormat(), $item[$field->name]) }}"
>