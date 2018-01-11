<textarea
  type="text"
  name="{{ $field->name }}"
  class="input text {{ $field->classForInput() }}"
  style="{!! $field->styleForInput() !!}"
>{{ htmlspecialchars($item[$field->name]) }}
</textarea>