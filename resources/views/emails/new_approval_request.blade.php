<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> Document</title>
</head>

<body>
    <p>Dear Kaprodi,</p>

    <p>A new approval request has been submitted by {{ $approvalRequest->user->name }}.</p>

    <p>Document Name: {{ $approvalRequest->document_name }}</p>
    <p>Notes: {{ $approvalRequest->notes }}</p>

    <p>Please review the request at your earliest convenience.</p>

    <p>Thank you.</p>
</body>

</html>
