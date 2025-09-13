@if (Route::currentRouteName() == 'home')
<link rel="stylesheet" href="{{asset('assets/thems/css/dropzone.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/thems/css/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/thems/css/dataTables.bootstrap5.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/thems/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/thems/css/bootstrap-datepicker.min.css')}}">
    <style>
        .dropzone{
            padding: 0.5rem
        }

        .dropzone .dz-preview{
            margin: 0rem
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b{
            top: 135%;
        }

        .dropzone .dz-preview.dz-image-preview{
            width: 100%;
        }

        .dropzone .dz-preview .dz-image{
            width: 100%;
            height: 100%;
        }

        .dropzone .dz-preview .dz-image img{
            width: 100%;
        }

        .dz-error-message{
            display: none;
        }
    </style>
@endif
