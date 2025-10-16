@extends('user.layouts.app')

@section('title', 'Download')

@section('content')
    <!-- Section Header -->
    <section id="header-section">
        <h1>Download</h1>
    </section>
    {{-- Section Download --}}
    <section id="jurnal">
        <div class="container-fluid mt-5">
            {{-- <div class="text-center mb-4">
                <h2 class="fs-3 fw-bolder">Download</h3>
            </div> --}}
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <iframe src="https://docs.google.com/spreadsheets/d/1ai5xqpBpX05zX8YyZFWRsiH4RZrTgh2V-Z40djHkCn8/preview?gid=417893748&single=true"
                        frameborder="0"
                        width="100%"
                        height="800"
                        allowfullscreen
                        style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                    </iframe>
                </div>



            </div>
        </div>
    </section>

@endsection
