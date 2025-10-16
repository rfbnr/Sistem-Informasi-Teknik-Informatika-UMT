<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <p>Dear {{ $approvalRequest->user->name }},</p>

    <p>Your approval request for {{ $approvalRequest->document_name }} has been rejected.</p>

    <p>Thank you.</p>
</body>

</html>
