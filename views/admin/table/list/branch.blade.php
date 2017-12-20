<tr>
    @include('table ~ list.branch-title')
    @foreach ($fields as $name => $field)
        @include('table ~ list.td', ['field' => $row->field($name)])
    @endforeach
    @include('table ~ list.actions')
</tr>

@foreach($row->children as $row)
    @include('table ~ list.branch', ['level' => $level+1, 'row' => $row])
@endforeach