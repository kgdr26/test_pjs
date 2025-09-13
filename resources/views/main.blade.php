<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="base-url" content="{{ url('/') }}">
        <link rel="shortcut icon" type="image/png" href="" />

        <!-- Core Css -->
        <link rel="stylesheet" href="{{asset('assets/thems/css/styles.css')}}" />
        @include('TMP.css')

        <title>Tes Interview PT PJS || {{$title}}</title>
        <script src="{{asset('assets/thems/js/vendor.min.js')}}"></script>
        <script src="{{asset('assets/thems/js/bootstrap.bundle.min.js')}}"></script>
        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        </script>
    </head>

    <body>
        <div class="toast toast-onload align-items-center text-bg-secondary border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-body hstack align-items-start gap-6">
                <i class="ti ti-alert-circle fs-6"></i>
                <div>
                    <h5 class="text-white fs-3 mb-1 text-nowrap">Welcome to Management Tools</h5>
                    <h6 class="text-white fs-2 mb-0">PT. BUKIT BAJA NUSANTARA</h6>
                </div>
                <button type="button" class="btn-close btn-close-white fs-2 m-0 ms-auto shadow-none" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>

        <!-- Preloader -->
        <div class="preloader">
            <img src="https://bootstrapdemos.wrappixel.com/materialpro/dist/assets/images/logos/logo-icon.svg" alt="loader" class="lds-ripple img-fluid" />
        </div>
        <div id="main-wrapper">
            {{-- Sidebar --}}
            @include('TMP.sidebar')

            <div class="page-wrapper">
                {{-- Navbar --}}
                @include('TMP.navbar')

                <div class="body-wrapper">
                    {{-- Content --}}
                    @yield('content')
                </div>
                {{-- Footer --}}
                @include('TMP.footer')
            </div>
        </div>

        <div class="dark-transparent sidebartoggler"></div>
        <!-- Import Js Files -->
        <script src="{{asset('assets/thems/js/simplebar.min.js')}}"></script>
        <script src="{{asset('assets/thems/js/app.init.js')}}"></script>
        <script src="{{asset('assets/thems/js/theme.js')}}"></script>
        <script src="{{asset('assets/thems/js/app.min.js')}}"></script>
        <script src="{{asset('assets/thems/js/sidebarmenu.js')}}"></script>
        <script src="{{asset('assets/thems/js/feather.min.js')}}"></script>

        <!-- solar icons -->
        <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
        <script src="{{asset('assets/thems/js/toastr-init.js')}}"></script>

        @include('TMP.js')

        <script>
            function myError(message){
                toastr.error(
                    message,
                    "Inconceivable!"
                );
            }
        </script>
    </body>
</html>
