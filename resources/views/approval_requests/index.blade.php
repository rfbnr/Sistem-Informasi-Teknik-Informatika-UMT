<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Approval Requests</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Approval Requests</h1>
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Nama Mahasiswa</th>
                    <th>Nama Surat</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($approvalRequests as $request)
                    <tr>
                        <td>{{ $request->user->name }}</td>
                        <td>{{ $request->document_name }}</td>
                        <td>{{ $request->notes }}</td>
                        <td>{{ $request->status }}</td>
                        <td class="actions">
                            @if ($request->status == 'pending')
                            <button class="btn btn-info" onclick="viewDocument('{{ $request->id }}', '{{ asset('storage/' . $request->document_path) }}')">View Document</button>
                                <a href="{{ route('approval-request.approve', $request->id) }}"
                                    class="btn btn-success">Approve</a>
                                <a href="{{ route('approval-request.reject', $request->id) }}"
                                    class="btn btn-danger">Reject</a>

                            @elseif ($request->status == 'rejected')
                                Ditolak
                            @elseif ($request->signed_document_path)
                                <a href="{{ route('approval-request.downloadSignedDocument', $request->id) }}"
                                    class="btn btn-primary">Download Signed Document</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal for Document View and Barcode Placement -->
    <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Document Viewer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="document-view" id="document-view">
                        <iframe src="" id="document-iframe" style="width:100%; height:500px;" frameborder="0"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function viewDocument(id, documentUrl) {
            // Set the iframe src to the document URL
            document.getElementById('document-iframe').src = documentUrl;
            // You can set the barcode src here if you have a URL for it
            // document.getElementById('barcode').src = 'barcode_url_here';
            // Show the modal
            $('#documentModal').modal('show');
        }
    </script>
</body>

</html>
