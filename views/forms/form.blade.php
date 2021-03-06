{{ \Assets::useFile('/tao/styles/forms.css') }}
@if ($ajax)
  @include($form->templateAjax($__data))
@endif

<form id="{{ $form->htmlId($__data) }}" class="{{ $form->class($__data) }}" method="post" action="{!! $form->action($__data) !!}">
  @if ($ajax)
    <ul class="ajax-errors"></ul>
  @endif
  {{ csrf_field() }}<input type="hidden" name="_session_key" value="{{ $session_key }}">
  @include($form->templateFields($__data))
  @include($form->templateSubmit($__data))
</form>
