<form id="admin-form" action="" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    
    @if($tabs)
        @include('table ~ form.tabs')
    @else
        @include('table ~ form.fields', [ 'tab' => '#', 'tab_label' => '#'])
    @endif
    
    <button type="submit" class="btn btn-primary"><i class="icon-ok icon-white"></i> {!! $submit_text !!}</button>
</form>