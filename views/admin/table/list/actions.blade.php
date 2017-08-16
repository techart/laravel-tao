@if ($with_row_actions)
    <td class="actions">
    
        @if ($can_edit)
            <a class="btn btn-primary" href="{{ url($controller->actionUrl('edit', ['id' => $row->getKey() ])) }}"><i class="icon-pencil icon-white"></i></a>
        @endif
        
        @if ($can_delete)
            <a onClick="return confirm('Вы уверены?')" class="btn btn-danger" href="{{ url($controller->actionUrl('delete', ['id' => $row->getKey() ])) }}"><i class="icon-remove icon-white"></i></a>
        @endif
    </td>
@endif
