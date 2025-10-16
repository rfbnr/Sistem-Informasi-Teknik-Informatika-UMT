<!DOCTYPE html>
<html lang="en">

<head>
    {{-- Required Meta Tags --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales-all.min.js"></script>
    <style>
        #event-list {
            margin-top: 20px;
        }
    </style>

    {{-- Custom CSS --}}
    <link href="{{ url('assets/style.css') }}" rel="stylesheet">
        <link href="{{ url('assets/logo.JPG') }}" rel="icon">
    <link href="{{ url('assets/logo.JPG') }}" rel="apple-touch-icon">

    <title>UMT | @yield('title')</title>
</head>

<body  style="background-color: #f0f0f0">

    @include('user.layouts.navbar')
    <div>
        @yield('content')
    </div>
    @include('user.layouts.footer')

    {{-- @include('user.layouts.footer') --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>

    <script>
        // Tangkap elemen navbar
        var navbar = document.querySelector('.navbar-transparan');

        // Tangkap tinggi navbar
        var navbarHeight = navbar.offsetHeight;

        // Tambahkan event listener untuk mengubah warna latar belakang saat menggulir
        window.addEventListener('scroll', function() {
            if (window.scrollY >= navbarHeight) {
                navbar.style.backgroundColor = 'rgba(0, 0, 0, 0.9)'; // Ganti dengan warna yang Anda inginkan
            } else {
                navbar.style.backgroundColor = 'rgba(0, 0, 0, 0)'; // Transparan ketika di atas navbar
            }
        });
    </script>


</body>

</html>
