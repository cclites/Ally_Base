@extends( 'layouts.print' )

@section( 'title', 'Deficiency Letters' )

@section( 'content' )

    @foreach( $pages as $caregiver )

        <div class="row">

            <div class="col-lg-12">

                <?php var_dump( $caregiver ); ?>
            </div>
        </div>
    @endforeach
@endsection