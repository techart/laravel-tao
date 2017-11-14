<script>
    $(function() {
        var files = {!! $field->renderFilelistJSON() !!};
        var $button = $("#tao_attaches_button_{{ $field->name }}");
        var $informer = $("#tao_attaches_informer_{{ $field->name }}");
        var $filelist = $("#tao_attaches_filelist_{{ $field->name }}");
        var $hidden = $("#tao_attaches_hidden_{{ $field->name }}");

        function renderFileList() {
            $filelist.empty();
            var count = 0;
            $.each(files, function (key, data) {
                var $name;
                $del = $('<a>').addClass('delete').attr('href', 'javascript:void(0)').html('&nbsp;').click(function() {
                    if (confirm('Вы уверены?')) {
                        deleteFromFileList(key);
                    }
                });
                if (data.new) {
                    $name = $('<span>').addClass('file-name').text(data.name);
                } else {
                    $name = $('<a>').addClass('file-name').attr('href', data.url).text(data.name);
                }
                var $entry = $('<div>').addClass('entry').addClass('entry-'+key).append($name).append($del);
                $filelist.append($entry);
                count++;
            });
            if (count==0) {
              $filelist.append('<div class="message-empty">Нет файлов</div>');
            }
            $hidden.val(JSON.stringify(files));
        }

        function deleteFromFileList(key) {
            delete files[key];
            renderFileList();
        }

        renderFileList();

        $adminForm[0].elements['{{ $field->name }}-files'].onchange = function() {
            var formData = new FormData();
            var fileList = $adminForm[0].elements['{{ $field->name }}-files'].files;
            for (var i = 0; i < fileList.length; i++) {
                formData.append("uploadfile[]", fileList[i]);
            }

            $.ajax({
                url:  '{!! $field->uploadUrl() !!}',
                data: formData,
                type: 'POST',
                contentType: false,
                processData: false,
                dataType: 'json'

            })
            .done(function(response) {
                $informer.removeClass('upload-progress').empty();
                if (response.error) {
                    $informer.addClass('upload-error').html(response.error);
                } else {
                    if (typeof response.files !== 'undefined' && response.files.length)
                    {
                        for (i in response.files) {
                            var key = response.files[i].key;
                            files[key] = response.files[i];
                        }
                    }
                    renderFileList();
                }
            })
            .fail(function(response) {
                $informer.addClass('upload-error').html(response.error);
            });
        };
    });
</script>
