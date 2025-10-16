<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Mahasiswa Register</title>
</head>

<body>
    <section class="vh-100">
        <div class="container h-80 p-5">
            <div class="row d-flex justify-content-center align-items-center h-80">
                <div class="col-lg-12 col-xl-11">
                    <div class="card text-black" style="border-radius: 20px;">
                        <div class="card-body p-md-5">
                            <div class="row justify-content-center">
                                <div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">
                                    <p class="text-center h1 fw-bold mb-5 mx-1 mx-md-4 mt-4">Sign up</p>
                                    <form method="POST" action="{{ route('do.user.register') }}" class="mx-1 mx-md-4">
                                        @csrf

                                        <div class="d-flex flex-row align-items-center mb-4">

                                            <div class="form-outline flex-fill mb-0">
                                                <label for="name" class="form-label">Nama</label>
                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror"
                                                    name="name" id="name" aria-describedby="nameHelp"
                                                    placeholder="Masukkan nama">
                                                @error('name')
                                                    <div id="nameHelp" class="form-text">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="d-flex flex-row align-items-center mb-4">

                                            <div class="form-outline flex-fill mb-0">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    name="email" id="email" aria-describedby="emailHelp"
                                                    placeholder="Masukkan Email">
                                                @error('email')
                                                    <div id="emailHelp" class="form-text">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="d-flex flex-row align-items-center mb-4">

                                            <div class="form-outline flex-fill mb-0">
                                                <label for="NIM" class="form-label">NIM</label>
                                                <input type="text"
                                                    class="form-control @error('NIM') is-invalid @enderror"
                                                    name="NIM" id="NIM" aria-describedby="NIMHelp"
                                                    placeholder="Masukkan NIM">
                                                @error('NIM')
                                                    <div id="NIMHelp" class="form-text">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="d-flex flex-row align-items-center mb-4">
                                            <div class="form-outline flex-fill mb-0">
                                                <label for="password" class="form-label">Password</label>
                                                <input type="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    name="password" id="password" placeholder="Masukkan password">
                                                @error('password')
                                                    <div id="passwordHelp" class="form-text">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="d-flex flex-row align-items-center mb-4">
                                            <div class="form-outline flex-fill mb-0">
                                                <label for="password_confirmation" class="form-label">Konfirmasi
                                                    Password
                                                </label>
                                                <input type="password"
                                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                                    name="password_confirmation" id="password_confirmation"
                                                    placeholder="Konfirmasi password">
                                                @error('password_confirmation')
                                                    <div id="passwordConfirmationHelp" class="form-text">{{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-centermb-3 mb-lg-4">
                                            <button type="submit"
                                                class="btn btn-primary btn-md w-100">Register</button>
                                        </div>

                                        <p class="text-center">Sudah memiliki akun? <a
                                                href="{{ route('login') }}"><b>Login</b></a>
                                        </p>

                                    </form>

                                </div>
                                <div class="col-md-10 col-lg-6 col-xl-7 d-flex align-items-center order-1 order-lg-2">

                                    <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-registration/draw1.webp"
                                        class="img-fluid" alt="Sample image">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
</body>

</html>
