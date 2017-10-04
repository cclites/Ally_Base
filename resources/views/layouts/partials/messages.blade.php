@if(session('error') || session('status'))
    <div class="row">
        <div class="col-lg-6 col-sm-12">
            @if (session('status'))
                <div class="alert alert-dismissible alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {!! strip_tags(session('status'), '<a><span>') !!}
                </div>
            @else
                <div class="alert alert-dismissible alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {!! strip_tags(session('status'), '<a><span>') !!}
                </div>
            @endif
        </div>
    </div>
@endif