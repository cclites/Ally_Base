@if(activeBusiness()->type === \App\Business::TYPE_FRANCHISOR)
    @include('menu.office_users.franchisor')
@else
    @include('menu.office_users.registry')
@endif
