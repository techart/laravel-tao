<div class="tao-fields-upload">
    <input type="hidden" name="{{ $field->name }}" value="{{ $field->getTempId() }}" id="tao_upload_hidden_{{ $field->name }}">
    <span id="tao_upload_informer_{{ $field->name}}">
        @if ($field->url())
            @if (isset($image))
                <a class="preview"><img src="{{ $field->adminPreviewUrl() }}"></a>
            @endif
            <a href="{!! url($field->url()) !!}">Скачать</a> ({{ $field->humanSize() }})
            @if ($field->param('can_delete', true))
                <a href="javascript:void();" id="tao_upload_delete_{{ $field->name }}">Удалить</a>
            @endif
        @endif
    </span>
    <a href="javascript:void();" id="tao_upload_button_{{ $field->name}}">
        @if ($field->url())
            Заменить
        @else
            Загрузить
        @endif
    </a>
    @include('fields ~ upload._ajax-upload-script')
</div>