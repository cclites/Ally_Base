@if(Auth::user()->role_type == 'admin')
    @include('menu.admin')
@elseif(Auth::user()->role_type == 'office_user')
    @include('menu.office_user')
@elseif(Auth::user()->role_type == 'caregiver')
    @include('menu.caregiver')
@elseif(Auth::user()->role_type == 'client')
    @include('menu.client')
@endif
