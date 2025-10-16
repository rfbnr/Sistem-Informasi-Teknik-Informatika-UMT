@extends('user.layouts.app')

@section('title', 'Welcome')

@section('content')

    <div class="container mt-5">
        <h1 class="text-center mb-4">Status Approval Requests</h1>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Nama Surat</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($approvalRequests as $request)
                    <tr>
                        <td>{{ $request->document_name }}</td>
                        <td>{{ $request->notes }}</td>
                        <td>{{ $request->status }}</td>
                        <td>
                            @if ($request->status == 'approved' && $request->signed_document_path)
                                <a href="{{ route('approval-request.downloadSignedDocument', $request->id) }}"
                                    class="btn btn-primary">Download Signed Document</a>
                            @else
                                @if ($request->status == 'pending')
                                    <span class="badge badge-warning text-black">Menunggu Persetujuan</span>
                                @elseif ($request->status == 'rejected')
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
