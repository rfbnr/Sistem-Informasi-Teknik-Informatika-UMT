# System Flow - Digital Signature Internal Processing

## 🔄 Internal System Flow Architecture

---

## 1️⃣ KEY GENERATION FLOW

### Process: Generate RSA Key Pair

```
User Action: Kaprodi clicks "Generate New Key"
     ↓
Controller: DigitalSignatureController@generateKey
     ↓
Service: DigitalSignatureService::createDigitalSignature()
     ↓
┌─────────────────────────────────────────────────────────┐
│              KEY GENERATION PROCESS                      │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Step 1: Generate RSA Key Pair                          │
│  ─────────────────────────────                          │
│    PHP OpenSSL Functions:                               │
│    • openssl_pkey_new($config)                          │
│      - digest_alg: sha256                               │
│      - private_key_bits: 2048                           │
│      - private_key_type: OPENSSL_KEYTYPE_RSA            │
│                                                          │
│    Output:                                               │
│    • $privateKey (resource)                             │
│                                                          │
│  Step 2: Extract Public Key                             │
│  ─────────────────────────────                          │
│    • openssl_pkey_get_details($privateKey)              │
│                                                          │
│    Output:                                               │
│    • $publicKey (PEM string)                            │
│    • $keyLength (2048)                                  │
│                                                          │
│  Step 3: Export Private Key                             │
│  ─────────────────────────────                          │
│    • openssl_pkey_export($privateKey, $privateKeyPem)   │
│                                                          │
│    Output:                                               │
│    • $privateKeyPem (PEM string)                        │
│                                                          │
│  Step 4: Generate Self-Signed Certificate               │
│  ─────────────────────────────                          │
│    • openssl_csr_new($dn, $privateKey)                  │
│    • openssl_csr_sign($csr, null, $privateKey, 365)     │
│    • openssl_x509_export($cert, $certPem)               │
│                                                          │
│    Certificate DN:                                       │
│    • CN: Digital Signature Authority                    │
│    • O: Digital Signature System                        │
│    • C: ID                                              │
│                                                          │
│    Output:                                               │
│    • $certificate (X.509 PEM)                           │
│                                                          │
│  Step 5: Generate Fingerprint                           │
│  ─────────────────────────────                          │
│    • hash('sha256', $publicKey)                         │
│    • Format: AA:BB:CC:DD:...                            │
│                                                          │
│    Output:                                               │
│    • $fingerprint (SHA-256 hash, formatted)             │
│                                                          │
│  Step 6: Store to Database                              │
│  ─────────────────────────────                          │
│    DigitalSignature::create([                            │
│      'signature_id' => Str::random(16),                 │
│      'public_key' => $publicKey,                        │
│      'private_key' => $privateKeyPem, // ← Will encrypt │
│      'algorithm' => 'RSA-SHA256',                       │
│      'key_length' => 2048,                              │
│      'certificate' => $certificate,                     │
│      'valid_from' => now(),                             │
│      'valid_until' => now()->addYear(),                 │
│      'status' => 'active',                              │
│      'created_by' => Auth::id(),                        │
│      'metadata' => [                                    │
│        'fingerprint' => $fingerprint,                   │
│        'created_ip' => request()->ip()                  │
│      ]                                                  │
│    ]);                                                  │
│                                                          │
│    Model Mutator (Automatic):                           │
│    • setPrivateKeyAttribute($value)                     │
│      → encrypt($value) using Laravel Crypt              │
│                                                          │
│  Step 7: Create Audit Log                               │
│  ─────────────────────────────                          │
│    SignatureAuditLog::create([                           │
│      'kaprodi_id' => Auth::id(),                        │
│      'action' => 'SIGNATURE_KEY_GENERATED',             │
│      'status_to' => 'active',                           │
│      'description' => 'Key pair generated',             │
│      'metadata' => [                                    │
│        'signature_id' => $signatureId,                  │
│        'key_length' => 2048,                            │
│        'algorithm' => 'RSA-SHA256'                      │
│      ],                                                 │
│      'ip_address' => request()->ip(),                   │
│      'performed_at' => now()                            │
│    ]);                                                  │
│                                                          │
└─────────────────────────────────────────────────────────┘
     ↓
Response: DigitalSignature model instance
     ↓
View: Success message + key details
```

---

## 2️⃣ DOCUMENT SIGNING FLOW

### Process: Complete Signing Operation

```
User Action: Kaprodi submits signing form
     ↓
Controller: DigitalSignatureController@signDocument
     ↓
Multiple Services: Orchestrated signing process
     ↓
┌─────────────────────────────────────────────────────────┐
│           DOCUMENT SIGNING PROCESS                       │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  PHASE 1: CREATE CMS SIGNATURE (Cryptographic)          │
│  ════════════════════════════════════════════════════    │
│                                                          │
│  Service: DigitalSignatureService::createCMSSignature()  │
│                                                          │
│  Input:                                                  │
│    • $documentPath (approval_request.document_path)     │
│    • $digitalSignatureId (selected key ID)              │
│                                                          │
│  Process Flow:                                           │
│                                                          │
│    [1] Read Original PDF                                │
│        ├─ Check if absolute path exists                 │
│        │    → file_exists($documentPath)                │
│        ├─ OR read from storage                          │
│        │    → Storage::disk('public')->get($path)       │
│        └─ Result: $documentContent (binary)             │
│                                                          │
│    [2] Calculate Document Hash                          │
│        ├─ hash('sha256', $documentContent)              │
│        └─ Result: $documentHash (64 chars hex)          │
│           Example: "a3b2c1d4e5f6..."                    │
│                                                          │
│    [3] Load Digital Signature Key                       │
│        ├─ DigitalSignature::findOrFail($id)             │
│        ├─ Check isValid()                               │
│        │    • status === 'active'                       │
│        │    • valid_until > now()                       │
│        │    • NOT revoked                               │
│        └─ Get private key (auto-decrypted by accessor)  │
│                                                          │
│    [4] Sign Hash with Private Key                       │
│        ├─ openssl_sign(                                 │
│        │     $documentHash,                             │
│        │     $signature,         // output              │
│        │     $privateKey,                               │
│        │     OPENSSL_ALGO_SHA256                        │
│        │   )                                            │
│        │                                                 │
│        ├─ Input: Hash string (64 chars)                 │
│        ├─ Output: Binary signature (~256 bytes)         │
│        └─ Result: $signature (binary)                   │
│                                                          │
│    [5] Encode to CMS Format                             │
│        ├─ base64_encode($signature)                     │
│        └─ Result: $cmsSignature (base64 string)         │
│           Example: "SGVsbG8gV29ybGQ..."                 │
│                                                          │
│    [6] Create Signature Value Hash                      │
│        ├─ hash('sha256', $signature)                    │
│        └─ Result: $signatureValue (verification hash)   │
│                                                          │
│    [7] Collect Metadata                                 │
│        └─ Result: [                                     │
│             'document_size' => strlen($content),        │
│             'signature_id' => $digitalSignatureId,      │
│             'signing_ip' => request()->ip(),            │
│             'signing_user_agent' => request()->ua()     │
│           ]                                             │
│                                                          │
│  Output:                                                 │
│    {                                                     │
│      "document_hash": "a3b2c1...",                      │
│      "cms_signature": "SGVsbG8...",                     │
│      "signature_value": "b4c3d2...",                    │
│      "algorithm": "RSA-SHA256",                         │
│      "signed_at": "2025-10-30 12:30:00",                │
│      "metadata": {...}                                  │
│    }                                                     │
│                                                          │
│  ════════════════════════════════════════════════════    │
│                                                          │
│  PHASE 2: CREATE DOCUMENT SIGNATURE RECORD              │
│  ════════════════════════════════════════════════════    │
│                                                          │
│  Service: DigitalSignatureService::signApprovalRequest() │
│                                                          │
│  Process:                                                │
│                                                          │
│    [1] Generate Verification Token                      │
│        ├─ Str::random(64)                               │
│        └─ Result: $verificationToken                    │
│                                                          │
│    [2] Create/Update DocumentSignature                  │
│        DocumentSignature::updateOrCreate([               │
│          'approval_request_id' => $approvalId           │
│        ], [                                             │
│          'digital_signature_id' => $keyId,              │
│          'document_hash' => $documentHash,              │
│          'signature_value' => $signatureValue,          │
│          'cms_signature' => $cmsSignature,              │
│          'signed_at' => now(),                          │
│          'signed_by' => Auth::id(),                     │
│          'signature_status' => 'signed',                │
│          'signature_metadata' => $metadata,             │
│          'verification_token' => $verificationToken,    │
│          'positioning_data' => $positioningData         │
│        ]);                                              │
│                                                          │
│  ════════════════════════════════════════════════════    │
│                                                          │
│  PHASE 3: GENERATE QR CODE                              │
│  ════════════════════════════════════════════════════    │
│                                                          │
│  Service: QRCodeService::generateVerificationQR()        │
│                                                          │
│  Process Flow:                                           │
│                                                          │
│    [1] Create Verification Payload                      │
│        $payload = [                                     │
│          'document_signature_id' => $id,                │
│          'approval_request_id' => $approvalId,          │
│          'verification_token' => $token,                │
│          'created_at' => now()->timestamp,              │
│          'expires_at' => now()->addYears(5)->timestamp  │
│        ];                                               │
│                                                          │
│    [2] Encrypt Payload                                  │
│        ├─ json_encode($payload)                         │
│        ├─ Crypt::encryptString($json)                   │
│        └─ Result: $encryptedPayload                     │
│           (~400-600 chars base64)                       │
│                                                          │
│    [3] Create Short Code Mapping                        │
│        VerificationCodeMapping::createMapping([          │
│          'short_code' => generateShortCode(),           │
│          'encrypted_payload' => $encryptedPayload,      │
│          'document_signature_id' => $id,                │
│          'expires_at' => $expiresAt                     │
│        ]);                                              │
│                                                          │
│        Short Code Generation:                           │
│        ├─ Generate 12 random chars (A-Z, 0-9)          │
│        ├─ Format: XXXX-XXXX-XXXX                        │
│        ├─ Check uniqueness                              │
│        └─ Example: "A1B2-C3D4-E5F6"                     │
│                                                          │
│    [4] Build Verification URL                           │
│        route('signature.verify', ['token' => $shortCode])│
│        → "https://domain.com/signature/verify/A1B2..."  │
│                                                          │
│    [5] Generate QR Code Image                           │
│        ├─ Use Endroid\QrCode library                    │
│        ├─ QrCode::create($verificationUrl)              │
│        ├─ Set size: 300x300                             │
│        ├─ Add logo (optional): UMT logo                 │
│        ├─ Add label: "Scan untuk verifikasi"           │
│        ├─ Write to PNG                                  │
│        └─ Save to: storage/qrcodes/qr_{id}_{time}.png  │
│                                                          │
│    [6] Update DocumentSignature                         │
│        DocumentSignature::find($id)->update([            │
│          'qr_code_path' => $qrPath,                     │
│          'verification_url' => $verificationUrl         │
│        ]);                                              │
│                                                          │
│  Output:                                                 │
│    {                                                     │
│      "qr_code_path": "qrcodes/qr_42_xxx.png",          │
│      "qr_code_url": "/storage/qrcodes/...",            │
│      "verification_url": "https://.../verify/A1B2...", │
│      "size": 300,                                       │
│      "format": "png"                                    │
│    }                                                     │
│                                                          │
│  ════════════════════════════════════════════════════    │
│                                                          │
│  PHASE 4: EMBED SIGNATURE INTO PDF                      │
│  ════════════════════════════════════════════════════    │
│                                                          │
│  Service: PDFSignatureService::mergeSignatureIntoPDF()   │
│                                                          │
│  Input:                                                  │
│    • $originalPdfPath (absolute path)                   │
│    • $templateId (signature template)                   │
│    • $positioningData (from frontend)                   │
│    • $documentSignature (for metadata)                  │
│    • $qrCodePath (generated QR)                         │
│                                                          │
│  Process Flow:                                           │
│                                                          │
│    [1] Load Signature Template                          │
│        ├─ SignatureTemplate::findOrFail($templateId)    │
│        ├─ Get signature_image_path                      │
│        └─ Storage::disk('public')->path($imagePath)     │
│                                                          │
│    [2] Check PDF Version                                │
│        ├─ detectPdfVersion($originalPdfPath)            │
│        ├─ Read first 1024 bytes                         │
│        ├─ Regex: /%PDF-(\d\.\d)/                        │
│        └─ Result: "1.4", "1.5", "1.7", etc.            │
│                                                          │
│    [3] Convert PDF if Needed (FPDI requires 1.4)        │
│        IF version > 1.4:                                │
│          ├─ Use Ghostscript: convertPdfTo14()           │
│          ├─ Command:                                    │
│          │   gs -sDEVICE=pdfwrite                       │
│          │      -dCompatibilityLevel=1.4                │
│          │      -dPDFSETTINGS=/prepress                 │
│          │      -dNOPAUSE -dQUIET -dBATCH                │
│          │      -sOutputFile=converted.pdf input.pdf    │
│          ├─ Save temp file                              │
│          └─ Use converted PDF for next steps            │
│                                                          │
│    [4] Initialize FPDI (PDF Manipulator)                │
│        ├─ $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();      │
│        ├─ Set creator, author, title                    │
│        ├─ Disable header/footer                         │
│        ├─ Disable auto page break                       │
│        └─ $pageCount = $pdf->setSourceFile($pdfPath)    │
│                                                          │
│    [5] Process Each Page                                │
│        FOR $i = 1 TO $pageCount:                        │
│          ├─ Import page: $tplIdx = importPage($i)       │
│          ├─ Get page size: $size = getTemplateSize()    │
│          ├─ Determine orientation: P or L               │
│          ├─ Add new page: AddPage($orientation, $size)  │
│          ├─ Use template: useTemplate($tplIdx)          │
│          │                                              │
│          IF $i == $targetPage:                          │
│            ├─ [A] Add Signature Image                   │
│            │    ├─ Convert pixel → mm coordinates       │
│            │    │   scaleX = pageWidth / canvasWidth    │
│            │    │   scaleY = pageHeight / canvasHeight  │
│            │    ├─ Calculate: x, y, width, height (mm)  │
│            │    └─ $pdf->Image(                         │
│            │          $signatureImagePath,              │
│            │          $x, $y, $width, $height,          │
│            │          '', '', '', false, 300            │
│            │        )                                   │
│            │                                            │
│            └─ [B] Add QR Code                           │
│                 ├─ Position: bottom-right corner        │
│                 ├─ Size: 16mm x 16mm                    │
│                 ├─ Margin: 10mm from edges              │
│                 ├─ Calculate position:                  │
│                 │   x = pageWidth - 16 - 10             │
│                 │   y = pageHeight - 16 - 10            │
│                 └─ $pdf->Image(                         │
│                       $qrCodePath,                      │
│                       $x, $y, 16, 16,                   │
│                       '', '', '', false, 300            │
│                     )                                   │
│                                                          │
│    [6] Save Signed PDF                                  │
│        ├─ Generate filename:                            │
│        │   "signed_" + originalName                     │
│        ├─ Path: storage/signed-documents/               │
│        ├─ $pdf->Output($absolutePath, 'F')              │
│        └─ Result: $signedPdfStoragePath                 │
│                                                          │
│    [7] Update DocumentSignature                         │
│        DocumentSignature::find($id)->update([            │
│          'final_pdf_path' => $signedPdfStoragePath,    │
│          'signature_status' => 'verified'               │
│        ]);                                              │
│                                                          │
│    [8] Cleanup Temp Files                               │
│        ├─ Delete QR temp file (if generated here)       │
│        └─ Delete converted PDF (if created)             │
│                                                          │
│  Output:                                                 │
│    "signed-documents/signed_xxx.pdf"                    │
│                                                          │
│  ════════════════════════════════════════════════════    │
│                                                          │
│  PHASE 5: UPDATE APPROVAL REQUEST & AUDIT               │
│  ════════════════════════════════════════════════════    │
│                                                          │
│  Process:                                                │
│                                                          │
│    [1] Update ApprovalRequest Status                    │
│        ApprovalRequest::find($id)->update([              │
│          'status' => 'sign_approved'                    │
│        ]);                                              │
│                                                          │
│    [2] Create Audit Log                                 │
│        SignatureAuditLog::create([                       │
│          'kaprodi_id' => Auth::id(),                    │
│          'action' => 'DOCUMENT_SIGNED',                 │
│          'status_from' => 'approved',                   │
│          'status_to' => 'sign_approved',                │
│          'description' => 'Document signed',            │
│          'metadata' => [                                │
│            'document_signature_id' => $docSigId,        │
│            'approval_request_id' => $approvalId,        │
│            'digital_signature_id' => $keyId,            │
│            'document_name' => $docName                  │
│          ],                                             │
│          'ip_address' => request()->ip(),               │
│          'user_agent' => request()->userAgent(),        │
│          'performed_at' => now()                        │
│        ]);                                              │
│                                                          │
│  ════════════════════════════════════════════════════    │
│                                                          │
│  PHASE 6: SEND NOTIFICATIONS                            │
│  ════════════════════════════════════════════════════    │
│                                                          │
│  Process:                                                │
│                                                          │
│    [1] Send Email to User                               │
│        Mail::to($user->email)->send(                     │
│          new DocumentSignedNotification([              │
│            'document_name' => $docName,                 │
│            'signed_at' => now(),                        │
│            'signed_by' => Kaprodi name,                 │
│            'verification_url' => $verificationUrl,      │
│            'attachments' => [                           │
│              $signedPdfPath,                            │
│              $qrCodePath                                │
│            ]                                            │
│          ])                                             │
│        );                                               │
│                                                          │
└─────────────────────────────────────────────────────────┘
     ↓
Response: {
  "success": true,
  "data": {
    "document_signature_id": 42,
    "signed_pdf_url": "...",
    "qr_code_url": "...",
    "verification_url": "..."
  }
}
     ↓
View: Success notification + download links
```

---

## 3️⃣ VERIFICATION FLOW

### Process: Public Signature Verification

```
User Action: Scan QR code OR enter verification code
     ↓
Route: GET /signature/verify/{token}
     ↓
Controller: VerificationController@verifyByToken
     ↓
Service: VerificationService::verifyByToken()
     ↓
┌─────────────────────────────────────────────────────────┐
│          VERIFICATION PROCESS                            │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  STAGE 1: TOKEN DECRYPTION                              │
│  ═══════════════════════════                            │
│                                                          │
│  Service: QRCodeService::decryptVerificationData()       │
│                                                          │
│  Input: $token (short code or full encrypted)           │
│                                                          │
│  Process:                                                │
│                                                          │
│    [1] Detect Token Type                                │
│        IF (strlen($token) <= 30 && contains('-')):      │
│          → Type: SHORT CODE                             │
│        ELSE:                                            │
│          → Type: FULL ENCRYPTED TOKEN                   │
│                                                          │
│    [2A] Process Short Code                              │
│         ├─ Find in verification_code_mappings:          │
│         │   WHERE short_code = $token                   │
│         │   AND expires_at > now()                      │
│         │                                               │
│         ├─ If not found: throw "Invalid QR Code"        │
│         ├─ If expired: throw "QR Code expired"          │
│         │                                               │
│         ├─ Get encrypted_payload from mapping           │
│         │                                               │
│         ├─ Track access:                                │
│         │   UPDATE access_count++                       │
│         │   UPDATE last_accessed_at = now()             │
│         │                                               │
│         ├─ Check rate limit:                            │
│         │   IF access_count > 10 in last hour:          │
│         │     → throw "Too many attempts"               │
│         │                                               │
│         └─ Set: $encryptedPayload = mapping->payload    │
│                                                          │
│    [2B] Process Full Token (Legacy)                     │
│         └─ Set: $encryptedPayload = $token              │
│                                                          │
│    [3] Decrypt Payload                                  │
│        ├─ Crypt::decryptString($encryptedPayload)       │
│        ├─ json_decode($decrypted, true)                 │
│        └─ Result: [                                     │
│             'document_signature_id' => 42,              │
│             'approval_request_id' => 123,               │
│             'verification_token' => 'abc...',           │
│             'created_at' => timestamp,                  │
│             'expires_at' => timestamp                   │
│           ]                                             │
│                                                          │
│    [4] Validate Expiration                              │
│        IF $data['expires_at'] < now()->timestamp:       │
│          → throw "QR Code has expired"                  │
│                                                          │
│  Output: $verificationData (array)                      │
│                                                          │
│  ═══════════════════════════════════════════════════════ │
│                                                          │
│  STAGE 2: COMPREHENSIVE VERIFICATION                    │
│  ═══════════════════════════════════════════════════    │
│                                                          │
│  Service: VerificationService::performComprehensive()    │
│                                                          │
│  Input: $documentSignature (loaded from ID)             │
│                                                          │
│  Verification Checks (7 checks total):                  │
│                                                          │
│  ┌────────────────────────────────────────────┐        │
│  │ CHECK #1: Document Signature Exists         │        │
│  ├────────────────────────────────────────────┤        │
│  │ Find: DocumentSignature by ID               │        │
│  │ Verify: Record exists                       │        │
│  │ Verify: signature_status = 'verified'       │        │
│  │                                             │        │
│  │ ✓ Pass: Found and verified                  │        │
│  │ ✗ Fail: Not found or invalid status         │        │
│  └────────────────────────────────────────────┘        │
│                                                          │
│  ┌────────────────────────────────────────────┐        │
│  │ CHECK #2: Digital Signature Key Valid       │        │
│  ├────────────────────────────────────────────┤        │
│  │ Get: DigitalSignature by FK                 │        │
│  │                                             │        │
│  │ Verify:                                     │        │
│  │   • status === 'active'                     │        │
│  │   • valid_until > now()                     │        │
│  │   • revoked_at === null                     │        │
│  │                                             │        │
│  │ Warning if:                                 │        │
│  │   • valid_until < now()->addDays(30)        │        │
│  │     → "Key expiring soon"                   │        │
│  │                                             │        │
│  │ ✓ Pass: Key active and valid                │        │
│  │ ✗ Fail: Expired, revoked, or invalid        │        │
│  └────────────────────────────────────────────┘        │
│                                                          │
│  ┌────────────────────────────────────────────┐        │
│  │ CHECK #3: Approval Request Status           │        │
│  ├────────────────────────────────────────────┤        │
│  │ Get: ApprovalRequest by FK                  │        │
│  │                                             │        │
│  │ Verify:                                     │        │
│  │   • Record exists                           │        │
│  │   • status = 'sign_approved'                │        │
│  │                                             │        │
│  │ ✓ Pass: Found and approved                  │        │
│  │ ✗ Fail: Not found or wrong status           │        │
│  └────────────────────────────────────────────┘        │
│                                                          │
│  ┌────────────────────────────────────────────┐        │
│  │ CHECK #4: Document Integrity (Hash)         │        │
│  ├────────────────────────────────────────────┤        │
│  │ Priority: Verify final_pdf_path             │        │
│  │ Fallback: Verify document_path              │        │
│  │                                             │        │
│  │ Process:                                    │        │
│  │   1. Read PDF file content                  │        │
│  │   2. Calculate: hash('sha256', $content)    │        │
│  │   3. Compare with stored document_hash      │        │
│  │   4. Use hash_equals() for timing safety    │        │
│  │                                             │        │
│  │ ✓ Pass: Hashes match (file unchanged)       │        │
│  │ ✗ Fail: Hashes differ (file modified)       │        │
│  └────────────────────────────────────────────┘        │
│                                                          │
│  ┌────────────────────────────────────────────┐        │
│  │ CHECK #5: CMS Signature Verification        │        │
│  ├────────────────────────────────────────────┤        │
│  │ Service: DigitalSignatureService::           │        │
│  │          verifyCMSSignature()               │        │
│  │                                             │        │
│  │ Process:                                    │        │
│  │   1. Read document content (same as #4)     │        │
│  │   2. Calculate document hash                │        │
│  │   3. Decode CMS signature:                  │        │
│  │      base64_decode($cmsSignature)           │        │
│  │   4. Verify with public key:                │        │
│  │      openssl_verify(                        │        │
│  │        $documentHash,                       │        │
│  │        $signature,                          │        │
│  │        $publicKey,                          │        │
│  │        OPENSSL_ALGO_SHA256                  │        │
│  │      )                                      │        │
│  │   5. Result: 1 (valid), 0 (invalid), -1 (error)     │
│  │                                             │        │
│  │ ✓ Pass: openssl_verify === 1                │        │
│  │ ✗ Fail: openssl_verify !== 1                │        │
│  └────────────────────────────────────────────┘        │
│                                                          │
│  ┌────────────────────────────────────────────┐        │
│  │ CHECK #6: Timestamp Validation              │        │
│  ├────────────────────────────────────────────┤        │
│  │ Get: signed_at from DocumentSignature       │        │
│  │                                             │        │
│  │ Verify:                                     │        │
│  │   • signed_at <= now() (not in future)      │        │
│  │   • signed_at >= now()->subYears(10)        │        │
│  │     (not too old)                           │        │
│  │                                             │        │
│  │ ✓ Pass: Timestamp reasonable                │        │
│  │ ✗ Fail: Future date or too old              │        │
│  └────────────────────────────────────────────┘        │
│                                                          │
│  ┌────────────────────────────────────────────┐        │
│  │ CHECK #7: Certificate Validation            │        │
│  ├────────────────────────────────────────────┤        │
│  │ Get: certificate from DigitalSignature      │        │
│  │                                             │        │
│  │ Process:                                    │        │
│  │   1. Parse: openssl_x509_parse($cert)       │        │
│  │   2. Extract validFrom_time_t               │        │
│  │   3. Extract validTo_time_t                 │        │
│  │   4. Check: now() in [validFrom, validTo]   │        │
│  │                                             │        │
│  │ ✓ Pass: Certificate currently valid         │        │
│  │ ✗ Fail: Expired or not yet valid            │        │
│  │ ⚠ Warning: Self-signed certificate          │        │
│  └────────────────────────────────────────────┘        │
│                                                          │
│  Calculate Summary:                                     │
│    • checks_passed: count(status === true)              │
│    • checks_failed: count(status === false)             │
│    • success_rate: (passed / total) * 100%              │
│    • overall_status: ALL pass ? 'VALID' : 'INVALID'     │
│                                                          │
│  ═══════════════════════════════════════════════════════ │
│                                                          │
│  STAGE 3: LOG VERIFICATION ATTEMPT                      │
│  ═══════════════════════════════════════════════════    │
│                                                          │
│  Process:                                                │
│                                                          │
│    [1] Calculate Duration                               │
│        $durationMs = (microtime(true) - $startTime) * 1000│
│                                                          │
│    [2] Categorize Result Status                         │
│        IF overall_valid:                                │
│          → 'success'                                    │
│        ELSE IF contains 'expired':                      │
│          → 'expired'                                    │
│        ELSE IF contains 'not found':                    │
│          → 'not_found'                                  │
│        ELSE IF contains 'invalid':                      │
│          → 'invalid'                                    │
│        ELSE:                                            │
│          → 'failed'                                     │
│                                                          │
│    [3] Create Log Entry                                 │
│        SignatureVerificationLog::create([                │
│          'document_signature_id' => $id,                │
│          'approval_request_id' => $approvalId,          │
│          'user_id' => Auth::id() ?? null,               │
│          'verification_method' => 'token',              │
│          'verification_token_hash' =>                   │
│            hash('sha256', $token),                      │
│          'is_valid' => $isValid,                        │
│          'result_status' => $resultStatus,              │
│          'ip_address' => request()->ip(),               │
│          'user_agent' => request()->userAgent(),        │
│          'referrer' => request()->header('referer'),    │
│          'metadata' => [                                │
│            'verification_id' => $verificationId,        │
│            'message' => $message,                       │
│            'verification_duration_ms' => $durationMs,   │
│            'checks_summary' => $summary,                │
│            'failed_reason' => $failedReason             │
│          ],                                             │
│          'verified_at' => now()                         │
│        ]);                                              │
│                                                          │
└─────────────────────────────────────────────────────────┘
     ↓
Response: {
  "is_valid": true/false,
  "message": "...",
  "details": {
    "document_signature": {...},
    "approval_request": {...},
    "checks": [...],
    "verification_summary": {...}
  }
}
     ↓
View: Verification result page (success/failure)
```

---

## 🔄 Data Flow Diagram

```
┌─────────────┐      ┌─────────────┐      ┌─────────────┐
│   Request   │─────▶│ Controller  │─────▶│  Service    │
│   (HTTP)    │      │  (Routing)  │      │  (Logic)    │
└─────────────┘      └─────────────┘      └──────┬──────┘
                                                  │
                                                  ▼
                     ┌────────────────────────────────────┐
                     │         Service Layer              │
                     │                                    │
                     │  DigitalSignatureService           │
                     │  PDFSignatureService               │
                     │  QRCodeService                     │
                     │  VerificationService               │
                     └────────┬───────────────────────────┘
                              │
                              ▼
      ┌───────────────────────┴────────────────────────┐
      │                                                │
      ▼                                                ▼
┌──────────┐                                    ┌──────────┐
│  Models  │                                    │ External │
│          │                                    │ Services │
│ • Digital│                                    │          │
│   Signature                                   │ • OpenSSL│
│ • Document│                                    │ • TCPDF  │
│   Signature                                   │ • Endroid│
│ • Approval│                                    │   QR Code│
│   Request │                                    │ • Storage│
│ • Audit   │                                    │ • Cache  │
│   Log     │                                    └──────────┘
└────┬─────┘
     │
     ▼
┌─────────────┐
│  Database   │
│   (MySQL)   │
└─────────────┘
```

---

**Complete**: Seluruh analisis sistem digital signature selesai!

Kembali ke [DIGITAL_SIGNATURE_ANALYSIS_OVERVIEW.md](DIGITAL_SIGNATURE_ANALYSIS_OVERVIEW.md) untuk overview lengkap.
