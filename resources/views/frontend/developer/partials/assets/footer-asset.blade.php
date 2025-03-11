
<!-- jquery -->
<script src="{{ asset('public/frontend/') }}/js/jquery-3.5.1.js"></script>
<!-- bootstrap js -->
<script src="{{ asset('public/frontend/') }}/js/bootstrap.bundle.js"></script>
<!-- swipper js -->
<script src="{{ asset('public/frontend/') }}/js/swiper.js"></script>
<!-- wow js file -->
{{-- <script src="{{ asset('public/frontend/') }}/js/wow.min.js"></script> --}}

<!-- main -->
<!-- nice select js -->
<script src="{{ asset('public/frontend/js/jquery.nice-select.js') }}"></script>
<script src="{{ asset('public/backend/js/select2.js') }}"></script>

<script src="{{ asset('public/frontend/') }}/js/odometer.js"></script>
<!-- viewport js -->
<script src="{{ asset('public/frontend/') }}/js/viewport.jquery.js"></script>

<script src="{{ asset('public/frontend/') }}/js/prettify.js"></script>
<script src="{{ asset('public/frontend/') }}/js/main.js"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> --}}

<script>
    $(".sidebar-mobile-btn button").click(function(){
        $(".developer-page-container .developer-bar").toggleClass("active");
        $('.body-overlay').addClass('active');
    });
    $(document).on("click","#body-overlay",function(){
        $('.body-overlay').removeClass('active');
        $('.developer-page-container .developer-bar').removeClass('active');
    });
</script>

@include('admin.partials.notify')
