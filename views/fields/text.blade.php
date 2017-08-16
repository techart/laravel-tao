<textarea
  type="text"
  name="{{ $field->name }}"
  class="input text {{ $field->classForInput() }}"
  style="{!! $field->styleForInput() !!}"
>{{ $item[$field->name] }}
</textarea>