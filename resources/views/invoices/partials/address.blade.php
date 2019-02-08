@if(isset($address))
    <div>{{ $address->address1 }}</div>
    @if($address->address2)
        <div>{{ $address->address2 }}</div>
    @endif
    @if($address->city && $address->state)
        <span>{{ $address->city }}</span>,
        <span>{{ $address->state }}</span>
    @elseif($address->city)
        {{ $address->city }}
    @elseif($address->state)
        {{ $address->state }}
    @endif
    <span>{{ $address->zip }}</span>
@endif
@if(isset($phone))
    <div>{{ $phone->number() }}</div>
@endif