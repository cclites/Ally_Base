{{-- Bluebird polyfills Promises for IE --}}
{{--<script src="https://cdn.jsdelivr.net/bluebird/latest/bluebird.min.js"></script>--}}
<script src="https://cdn.polyfill.io/v2/polyfill.min.js"></script>

<!-- App JavaScript -->
<script src="{{ asset(mix('js/app.js')) }}"></script>

<!-- App Initial State -->
<script>
    @if(is_office_user())
        window.Store.commit('setBusinesses', @json(
            \App\Http\Resources\BusinessSettingsResource::collection(
                Auth::user()->officeUser->businesses
            )->toArray(request())
        ));
    @elseif(is_admin())
        window.Store.commit('setBusinesses', @json(
            \App\Http\Resources\BusinessSettingsResource::collection(
                \App\Business::all()
            )->toArray(request())
        ));
    @elseif(is_client())
        window.Store.commit('setBusinesses', @json(
            [(new \App\Http\Resources\BusinessSettingsResource(
                auth()->user()->role->business
            ))->toArray(request())]
        ));
    @endif
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery.print/1.5.1/jQuery.print.min.js"></script>
<script src="/vendor/jquery-mask/jquery.mask.min.js"></script>

{{--<!-- slimscrollbar scrollbar JavaScript -->--}}
<script src="/demo/js/jquery.slimscroll.js"></script>

{{--<!--stickey kit -->--}}
<script src="/demo/assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
<script src="/demo/assets/plugins/sparkline/jquery.sparkline.min.js"></script>

<!-- Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
