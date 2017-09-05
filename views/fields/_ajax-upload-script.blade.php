{{ Assets::useFile('/tao/scripts/ajaxupload.js') }}
<script>
    $(function() {
        var $button = $("#tao_upload_button_{{ $field->name }}");
        var $informer = $("#tao_upload_informer_{{ $field->name }}");
        new AjaxUpload($button, {
            action: '{!! $field->uploadUrl() !!}',
            responseType: 'json',
            name: 'uploadfile',
            onSubmit: function(file, ext) {
                $informer.removeClass('upload-error').empty().addClass('upload-progress');
            },
            onComplete: function(file, response) {
                $informer.removeClass('upload-progress').empty();
                if (response.error) {
                    $informer.addClass('upload-error').html(response.error);
                } else {
                    var preview = '';
                    if (response.preview!='') {
                        preview = '<a><img src="'+response.preview+'"></a>';
                    }
                    $informer.html(preview+'<span class="filename">'+response.name+'</span> <span class="filesize">('+response.human_size+')</span>');
                }
            }
        });
        $('#tao_upload_delete_{{ $field->name }}').click(function() {
            if (confirm('Вы уверены?')) {
                $('#tao_upload_hidden_{{ $field->name }}').attr('value', 'delete');
                $informer.empty();
            }
            return false;
        });
    });
</script>
