# User Flow - Sistem Digital Signature

## 🎭 Actors dalam Sistem

1. **User (Mahasiswa/Staff)** - Submit dokumen untuk approval
2. **Kaprodi (Ketua Program Studi)** - Approve dan sign dokumen
3. **Public/Verifier** - Verifikasi tanda tangan digital

---

## 📋 Complete User Flow

### Phase 1: Initialization (Kaprodi Setup)

**Actor**: Kaprodi

```
┌──────────────────────────────────────────────────────────┐
│  PHASE 1: DIGITAL SIGNATURE KEY GENERATION               │
│  (One-time setup atau periodic renewal)                  │
└──────────────────────────────────────────────────────────┘

1. Kaprodi Login
   URL: /kaprodi/login
   Controller: Auth\KaprodiController@login

2. Navigate to Key Management
   URL: /admin/signature/keys
   Controller: DigitalSignatureController@listKeys

3. Generate New Key Pair
   Click: "Generate New Key"
   URL: POST /admin/signature/keys/generate
   Controller: DigitalSignatureController@generateKey

   ┌─────────────────────────────────────────┐
   │ Backend Process (DigitalSignatureService)│
   ├─────────────────────────────────────────┤
   │ 1. Generate RSA-2048 key pair          │
   │    - openssl_pkey_new()                 │
   │                                         │
   │ 2. Generate self-signed certificate     │
   │    - openssl_csr_new()                  │
   │    - openssl_csr_sign()                 │
   │                                         │
   │ 3. Extract public key                   │
   │    - openssl_pkey_get_details()         │
   │                                         │
   │ 4. Generate fingerprint                 │
   │    - SHA-256 hash of public key         │
   │                                         │
   │ 5. Store to database                    │
   │    - private_key: encrypted             │
   │    - public_key: plain text             │
   │    - certificate: PEM format            │
   │    - valid_from: now()                  │
   │    - valid_until: now() + 1 year        │
   │    - status: 'active'                   │
   │                                         │
   │ 6. Create audit log                     │
   │    - action: KEY_GENERATED              │
   │    - metadata: key details              │
   └─────────────────────────────────────────┘

4. View Key Details
   URL: /admin/signature/keys/{id}
   Display:
   - Signature ID
   - Public Key (truncated)
   - Algorithm: RSA-SHA256
   - Key Length: 2048 bits
   - Valid From/Until
   - Status
   - Fingerprint
   - Certificate info
```

**Output**: DigitalSignature record created with status 'active'

---

### Phase 2: Document Submission (User)

**Actor**: User (Mahasiswa/Staff)

```
┌──────────────────────────────────────────────────────────┐
│  PHASE 2: DOCUMENT APPROVAL REQUEST                       │
│  (User submits document for approval)                     │
└──────────────────────────────────────────────────────────┘

1. User Login
   URL: /login
   Controller: Auth\LoginController@login

2. Navigate to Submission Form
   URL: /approval-requests/create
   Controller: ApprovalRequestController@create

3. Fill Form & Upload PDF
   Fields:
   - document_name: "Surat Permohonan PKL"
   - document_type: "Permohonan"
   - document_number: "001/PKL/2025"
   - document_file: Upload PDF (stored via Storage)
   - notes: "Untuk semester 5"

4. Submit Request
   URL: POST /approval-requests
   Controller: ApprovalRequestController@store

   ┌─────────────────────────────────────────┐
   │ Backend Process                          │
   ├─────────────────────────────────────────┤
   │ 1. Validate input                       │
   │    - PDF format check                   │
   │    - File size check                    │
   │                                         │
   │ 2. Store PDF file                       │
   │    - Storage::put('documents/', $file)  │
   │                                         │
   │ 3. Create ApprovalRequest record        │
   │    - user_id: Auth::id()                │
   │    - document_path: storage path        │
   │    - status: 'pending'                  │
   │                                         │
   │ 4. Send notification to Kaprodi         │
   │    - Email notification                 │
   └─────────────────────────────────────────┘

5. View Status
   URL: /approval-requests/{id}
   Status: "Pending Approval"
```

**Output**: ApprovalRequest record with status 'pending'

---

### Phase 3: Document Approval (Kaprodi)

**Actor**: Kaprodi

```
┌──────────────────────────────────────────────────────────┐
│  PHASE 3: DOCUMENT REVIEW & APPROVAL                      │
│  (Kaprodi reviews and approves document)                  │
└──────────────────────────────────────────────────────────┘

1. Kaprodi Login & View Pending Requests
   URL: /admin/approval-requests?status=pending
   Controller: ApprovalRequestController@index

2. Review Document
   URL: /admin/approval-requests/{id}
   Actions:
   - View PDF preview
   - Read document details
   - Check user info

3. Approve Document
   Click: "Approve"
   URL: POST /admin/approval-requests/{id}/approve
   Controller: ApprovalRequestController@approve

   ┌─────────────────────────────────────────┐
   │ Backend Process                          │
   ├─────────────────────────────────────────┤
   │ 1. Update ApprovalRequest                │
   │    - status: 'approved'                 │
   │    - approved_by: Kaprodi ID            │
   │    - approved_at: now()                 │
   │                                         │
   │ 2. Send notification to User            │
   │    - Email: "Document Approved"         │
   │                                         │
   │ 3. Document now ready for signing       │
   └─────────────────────────────────────────┘
```

**Output**: ApprovalRequest status updated to 'approved'

---

### Phase 4: Digital Signing (Kaprodi)

**Actor**: Kaprodi

```
┌──────────────────────────────────────────────────────────┐
│  PHASE 4: DIGITAL SIGNATURE SIGNING PROCESS              │
│  (The most complex phase)                                 │
└──────────────────────────────────────────────────────────┘

Step 4.1: Navigate to Sign Page
────────────────────────────────
URL: /admin/signature/sign/{approvalRequestId}
Controller: DigitalSignatureController@signDocumentPage

Display:
- Document preview (PDF viewer)
- Signature template selector
- Canvas for positioning
- Available digital signature keys dropdown

Step 4.2: Select Signature Key
────────────────────────────────
Kaprodi chooses which DigitalSignature (key pair) to use:
- Key options shown from digital_signatures where:
  - created_by = current kaprodi
  - status = 'active'
  - valid_until > now()

Step 4.3: Select Signature Template
────────────────────────────────────
URL: GET /admin/signature/templates
Controller: DigitalSignatureController@listTemplates

Kaprodi selects visual template:
- Template with Kaprodi's signature image
- Pre-configured layout (position, size)

Step 4.4: Position Signature on Canvas
────────────────────────────────────────
Frontend (JavaScript):
- Load PDF into canvas
- Show signature template preview
- Drag & resize signature on PDF
- Choose target page
- Capture positioning data:
  {
    "page": 1,
    "position": {"x": 450, "y": 650},
    "size": {"width": 200, "height": 80},
    "canvas_dimensions": {"width": 595, "height": 842}
  }

Step 4.5: Submit Signing Request
────────────────────────────────────
Click: "Sign Document"
URL: POST /admin/signature/sign
Controller: DigitalSignatureController@signDocument

Request Body:
{
  "approval_request_id": 123,
  "digital_signature_id": 5,
  "template_id": 2,
  "positioning_data": {...}
}

┌─────────────────────────────────────────────────────────┐
│ Backend Signing Process (DigitalSignatureService)        │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ ▼ STEP 1: Create CMS Signature (Cryptographic)          │
│ ───────────────────────────────────────────────────────  │
│   Service: DigitalSignatureService::createCMSSignature   │
│                                                          │
│   1.1 Read original PDF content                          │
│       - From: approval_request.document_path            │
│       - Storage::get($path)                             │
│                                                          │
│   1.2 Calculate document hash                            │
│       - hash('sha256', $pdfContent)                     │
│       → Result: document_hash                           │
│                                                          │
│   1.3 Sign hash with private key                         │
│       - Get private key from DigitalSignature           │
│       - Decrypt private key (model accessor)            │
│       - openssl_sign($hash, $signature, $privateKey)    │
│       → Result: binary signature                        │
│                                                          │
│   1.4 Encode to CMS format                               │
│       - base64_encode($signature)                       │
│       → Result: cms_signature                           │
│                                                          │
│   1.5 Create signature value hash                        │
│       - hash('sha256', $signature)                      │
│       → Result: signature_value                         │
│                                                          │
│   Returns:                                               │
│   {                                                      │
│     "document_hash": "abc123...",                       │
│     "cms_signature": "base64string...",                 │
│     "signature_value": "def456...",                     │
│     "algorithm": "RSA-SHA256",                          │
│     "signed_at": "2025-10-30 12:00:00",                 │
│     "metadata": {...}                                   │
│   }                                                      │
│                                                          │
│ ▼ STEP 2: Create DocumentSignature Record               │
│ ───────────────────────────────────────────────────────  │
│   Service: DigitalSignatureService::signApprovalRequest  │
│                                                          │
│   2.1 Create/Update DocumentSignature                    │
│       DocumentSignature::updateOrCreate([                │
│         'approval_request_id' => $id                    │
│       ], [                                              │
│         'digital_signature_id' => $keyId,               │
│         'document_hash' => $hash,                       │
│         'cms_signature' => $cmsSignature,               │
│         'signature_value' => $sigValue,                 │
│         'signed_at' => now(),                           │
│         'signature_status' => 'signed',                 │
│         'signature_metadata' => $metadata,              │
│         'positioning_data' => $positionData             │
│       ]);                                               │
│                                                          │
│   2.2 Generate verification token                        │
│       - Str::random(64)                                 │
│       - Store in document_signature.verification_token  │
│                                                          │
│ ▼ STEP 3: Generate QR Code                              │
│ ───────────────────────────────────────────────────────  │
│   Service: QRCodeService::generateVerificationQR         │
│                                                          │
│   3.1 Create encrypted verification data                 │
│       $data = [                                         │
│         'document_signature_id' => $id,                 │
│         'approval_request_id' => $approvalId,           │
│         'verification_token' => $token,                 │
│         'created_at' => now()->timestamp,               │
│         'expires_at' => now()->addYears(5)->timestamp   │
│       ];                                                │
│       $encrypted = Crypt::encryptString(json_encode()); │
│                                                          │
│   3.2 Create short code mapping                          │
│       VerificationCodeMapping::createMapping(            │
│         $encrypted, $docSigId, $expiresAt              │
│       );                                                │
│       → Result: short_code (e.g., "A1B2-C3D4-E5F6")    │
│                                                          │
│   3.3 Generate verification URL                          │
│       $url = route('signature.verify', [                │
│         'token' => $shortCode                           │
│       ]);                                               │
│       → "https://domain.com/signature/verify/A1B2..."  │
│                                                          │
│   3.4 Generate QR code image                             │
│       - Use Endroid QR Code library                     │
│       - Add logo (optional)                             │
│       - Add label                                       │
│       - Save to storage: qrcodes/document-signatures/   │
│       → Result: qr_code_path                            │
│                                                          │
│   3.5 Update DocumentSignature                           │
│       - qr_code_path                                    │
│       - verification_url                                │
│                                                          │
│ ▼ STEP 4: Embed Signature into PDF                      │
│ ───────────────────────────────────────────────────────  │
│   Service: PDFSignatureService::mergeSignatureIntoPDF    │
│                                                          │
│   4.1 Load original PDF                                  │
│       - Get absolute path                               │
│       - Check PDF version (via detectPdfVersion)        │
│                                                          │
│   4.2 Convert PDF if needed                              │
│       - If PDF version > 1.4:                           │
│         → convertPdfTo14() using Ghostscript            │
│       - FPDI requires PDF 1.4                           │
│                                                          │
│   4.3 Initialize FPDI (PDF manipulator)                  │
│       - $pdf = new Fpdi();                              │
│       - setSourceFile($originalPdf)                     │
│                                                          │
│   4.4 Process each page                                  │
│       FOR each page in PDF:                             │
│         - Import page                                   │
│         - Add new page with same dimensions             │
│         - Use imported page as template                 │
│                                                          │
│         IF page == target signature page:               │
│           - addSignatureToPage()                        │
│             * Convert pixel coords to mm                │
│             * Scale based on canvas dimensions          │
│             * Add signature image at position           │
│                                                          │
│           - addQRCodeToPage()                           │
│             * Position: bottom-right corner             │
│             * Size: 16mm x 16mm                         │
│             * Margin: 10mm from edges                   │
│                                                          │
│   4.5 Save signed PDF                                    │
│       - Filename: "signed_" + original_name             │
│       - Path: storage/signed-documents/                 │
│       - Output to file                                  │
│       → Result: final_pdf_path                          │
│                                                          │
│   4.6 Update DocumentSignature                           │
│       - final_pdf_path                                  │
│       - signature_status: 'verified'                    │
│                                                          │
│   4.7 Cleanup temp files                                 │
│       - Delete QR temp file                             │
│       - Delete converted PDF (if any)                   │
│                                                          │
│ ▼ STEP 5: Update Approval Request Status                │
│ ───────────────────────────────────────────────────────  │
│   ApprovalRequest::markUserSigned()                      │
│   - status: 'sign_approved'                             │
│                                                          │
│ ▼ STEP 6: Create Audit Log                              │
│ ───────────────────────────────────────────────────────  │
│   SignatureAuditLog::create([                            │
│     'kaprodi_id' => Auth::id(),                         │
│     'action' => 'DOCUMENT_SIGNED',                      │
│     'status_to' => 'verified',                          │
│     'description' => 'Document signed successfully',     │
│     'metadata' => [standardized metadata],              │
│     'ip_address' => request()->ip(),                    │
│     'performed_at' => now()                             │
│   ]);                                                   │
│                                                          │
│ ▼ STEP 7: Send Notifications                            │
│ ───────────────────────────────────────────────────────  │
│   - Email to User: "Document has been signed"           │
│   - Attachment: Signed PDF                              │
│   - QR code for verification                            │
│                                                          │
└─────────────────────────────────────────────────────────┘

Response:
{
  "success": true,
  "message": "Document signed successfully",
  "data": {
    "document_signature_id": 42,
    "approval_request_id": 123,
    "signed_pdf_url": "/storage/signed-documents/signed_xxx.pdf",
    "qr_code_url": "/storage/qrcodes/qr_42_xxx.png",
    "verification_url": "https://domain.com/signature/verify/A1B2-C3D4",
    "signed_at": "2025-10-30 12:30:00"
  }
}
```

**Output**:
- DocumentSignature record dengan status 'verified'
- Signed PDF dengan embedded signature + QR code
- QR code image untuk verifikasi
- Audit log entry

---

### Phase 5: Document Distribution (Kaprodi)

**Actor**: Kaprodi

```
┌──────────────────────────────────────────────────────────┐
│  PHASE 5: SIGNED DOCUMENT DELIVERY                        │
└──────────────────────────────────────────────────────────┘

1. Download Signed PDF
   URL: /admin/signature/download/{documentSignatureId}
   Controller: DigitalSignatureController@downloadSignedDocument

   Returns:
   - PDF dengan signature visual
   - QR code di pojok kanan bawah
   - Metadata dalam PDF

2. Automatic Email Sent to User
   Email contains:
   - Signed PDF attachment
   - Verification URL link
   - QR code image
   - Instructions untuk verifikasi

3. User receives signed document
```

---

### Phase 6: Public Verification

**Actor**: Public/Verifier (Anyone)

```
┌──────────────────────────────────────────────────────────┐
│  PHASE 6: SIGNATURE VERIFICATION                          │
│  (Public can verify authenticity)                         │
└──────────────────────────────────────────────────────────┘

Method 1: QR Code Scan
──────────────────────
1. Scan QR code on signed PDF using smartphone

2. QR contains URL:
   https://domain.com/signature/verify/A1B2-C3D4-E5F6

3. Browser opens verification page
   URL: /signature/verify/{token}
   Controller: VerificationController@verifyByToken

Method 2: Manual Verification
──────────────────────────────
1. Navigate to public verification page
   URL: /signature/verify
   Controller: VerificationController@verificationPage

2. Enter verification code manually
   Input: "A1B2-C3D4-E5F6"

3. Submit form
   URL: POST /signature/verify
   Controller: VerificationController@verifyPublic

┌─────────────────────────────────────────────────────────┐
│ Backend Verification Process (VerificationService)       │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ ▼ STEP 1: Decrypt Verification Token                    │
│ ───────────────────────────────────────────────────────  │
│   Service: QRCodeService::decryptVerificationData        │
│                                                          │
│   1.1 Detect token type                                  │
│       IF length <= 30 && contains '-':                   │
│         → Short code format                             │
│       ELSE:                                             │
│         → Full encrypted token (legacy)                 │
│                                                          │
│   1.2 Lookup short code mapping (if short code)          │
│       $mapping = VerificationCodeMapping::findByShortCode│
│       - Check if expired                                │
│       - Track access (access_count++)                   │
│       - Rate limit check (max 10 per window)            │
│       - Get encrypted payload from mapping              │
│                                                          │
│   1.3 Decrypt payload                                    │
│       $data = json_decode(                              │
│         Crypt::decryptString($encryptedPayload)         │
│       );                                                │
│                                                          │
│   1.4 Check expiration                                   │
│       IF $data['expires_at'] < now():                   │
│         → throw "QR Code has expired"                   │
│                                                          │
│   Returns:                                               │
│   {                                                      │
│     "document_signature_id": 42,                        │
│     "approval_request_id": 123,                         │
│     "verification_token": "abc123...",                  │
│     "created_at": timestamp,                            │
│     "expires_at": timestamp                             │
│   }                                                      │
│                                                          │
│ ▼ STEP 2: Perform Comprehensive Verification            │
│ ───────────────────────────────────────────────────────  │
│   Service: VerificationService::performComprehensive     │
│                                                          │
│   CHECK 1: Document Signature Exists                     │
│   ────────────────────────────────                      │
│   - Find DocumentSignature by ID                        │
│   - Verify signature_status = 'verified'                │
│   ✓ Pass: Record found and verified                     │
│   ✗ Fail: Record not found or invalid status            │
│                                                          │
│   CHECK 2: Digital Signature Key Validity                │
│   ────────────────────────────────                      │
│   - Get associated DigitalSignature                     │
│   - Check status = 'active'                             │
│   - Check valid_until > now()                           │
│   - Check NOT revoked                                   │
│   ✓ Pass: Key is active and valid                       │
│   ✗ Fail: Key expired, revoked, or invalid              │
│   ⚠ Warning: Key expiring soon (< 30 days)              │
│                                                          │
│   CHECK 3: Approval Request Status                       │
│   ────────────────────────────────                      │
│   - Get associated ApprovalRequest                      │
│   - Verify exists and accessible                        │
│   - Check status = 'sign_approved'                      │
│   ✓ Pass: Approval request valid                        │
│   ✗ Fail: Not found or wrong status                     │
│                                                          │
│   CHECK 4: Document Integrity (Hash Verification)        │
│   ────────────────────────────────────                  │
│   4.1 Read signed PDF file                               │
│       - Priority: final_pdf_path (signed PDF)           │
│       - Fallback: document_path (original PDF)          │
│       - Storage::get($path)                             │
│                                                          │
│   4.2 Calculate current hash                             │
│       $currentHash = hash('sha256', $pdfContent);       │
│                                                          │
│   4.3 Compare with stored hash                           │
│       $storedHash = document_signature.document_hash    │
│       IF hash_equals($storedHash, $currentHash):        │
│         ✓ Pass: Document unchanged                      │
│       ELSE:                                             │
│         ✗ Fail: Document has been modified              │
│                                                          │
│   CHECK 5: CMS Signature Verification                    │
│   ────────────────────────────────────                  │
│   Service: DigitalSignatureService::verifyCMSSignature   │
│                                                          │
│   5.1 Read document content (same as check 4)            │
│                                                          │
│   5.2 Calculate document hash                            │
│       $documentHash = hash('sha256', $content);         │
│                                                          │
│   5.3 Decode CMS signature                               │
│       $signature = base64_decode($cmsSignature);        │
│                                                          │
│   5.4 Verify with public key                             │
│       $result = openssl_verify(                         │
│         $documentHash,                                  │
│         $signature,                                     │
│         $publicKey,                                     │
│         OPENSSL_ALGO_SHA256                             │
│       );                                                │
│                                                          │
│       IF $result === 1:                                 │
│         ✓ Pass: Signature cryptographically valid       │
│       ELSE:                                             │
│         ✗ Fail: Signature verification failed           │
│                                                          │
│   CHECK 6: Timestamp Validation                          │
│   ────────────────────────────────────                  │
│   - Check signed_at not in future                       │
│   - Check signed_at not too old (> 10 years)            │
│   ✓ Pass: Timestamp reasonable                          │
│   ✗ Fail: Timestamp suspicious                          │
│                                                          │
│   CHECK 7: Certificate Validation                        │
│   ────────────────────────────────────                  │
│   - Parse X.509 certificate                             │
│   - Check certificate validity period                   │
│   - Check not expired                                   │
│   ✓ Pass: Certificate valid                             │
│   ✗ Fail: Certificate invalid or expired                │
│   ⚠ Warning: Self-signed certificate                    │
│                                                          │
│ ▼ STEP 3: Create Verification Summary                   │
│ ───────────────────────────────────────────────────────  │
│   Calculate:                                             │
│   - Total checks: 7                                     │
│   - Checks passed: X                                    │
│   - Checks failed: Y                                    │
│   - Success rate: (X/7) * 100%                          │
│   - Overall status: VALID/INVALID                       │
│                                                          │
│ ▼ STEP 4: Log Verification Attempt                      │
│ ───────────────────────────────────────────────────────  │
│   SignatureVerificationLog::create([                     │
│     'document_signature_id' => $id,                     │
│     'verification_method' => 'token',                   │
│     'verification_token_hash' => hash('sha256',$token), │
│     'is_valid' => $overallValid,                        │
│     'result_status' => 'success/failed/expired',        │
│     'ip_address' => request()->ip(),                    │
│     'user_agent' => request()->userAgent(),             │
│     'metadata' => [verification details],               │
│     'verified_at' => now()                              │
│   ]);                                                   │
│                                                          │
└─────────────────────────────────────────────────────────┘

Verification Response/View:
───────────────────────────

IF VALID:
┌────────────────────────────────────────┐
│ ✅ SIGNATURE VERIFIED                  │
├────────────────────────────────────────┤
│ Document: Surat Permohonan PKL         │
│ Document No: 001/PKL/2025              │
│ Signed By: Dr. John Doe                │
│ Signed At: 30 Oktober 2025, 12:30 WIB  │
│ Algorithm: RSA-SHA256 (2048-bit)       │
│                                        │
│ Verification Checks:                   │
│ ✓ Document signature exists            │
│ ✓ Digital signature key valid          │
│ ✓ Approval request valid               │
│ ✓ Document integrity verified          │
│ ✓ CMS signature valid                  │
│ ✓ Timestamp valid                      │
│ ✓ Certificate valid                    │
│                                        │
│ Success Rate: 100% (7/7 checks passed) │
│                                        │
│ [Download Signed PDF] [Print Report]  │
└────────────────────────────────────────┘

IF INVALID:
┌────────────────────────────────────────┐
│ ❌ SIGNATURE INVALID                   │
├────────────────────────────────────────┤
│ The digital signature could not be     │
│ verified. This document may have been  │
│ modified or the signature is invalid.  │
│                                        │
│ Failed Checks:                         │
│ ✗ Document integrity check failed      │
│ ✗ CMS signature verification failed    │
│                                        │
│ Verification ID: verify_abc123         │
│ Verified At: 30 Oktober 2025, 14:00    │
│                                        │
│ [Contact Support]                      │
└────────────────────────────────────────┘
```

**Output**:
- Verification result (valid/invalid)
- Detailed verification report
- SignatureVerificationLog entry
- Updated access_count in mapping table

---

## 📊 User Flow Diagram Summary

```
┌───────┐     ┌─────────┐     ┌──────────┐     ┌─────────┐
│ USER  │────▶│ KAPRODI │────▶│ SYSTEM   │────▶│ PUBLIC  │
│Submit │     │ Approve │     │  Sign    │     │ Verify  │
└───────┘     └─────────┘     └──────────┘     └─────────┘
    │              │                │                 │
    │              │                │                 │
 Submit        Review &         Generate         Scan QR/
Document       Approve          Digital          Enter Code
    │              │            Signature             │
    │              │                │                 │
    ▼              ▼                ▼                 ▼
[Pending]    [Approved]        [Signed &         [Verified]
                               Verified]

Status Flow:
pending → approved → sign_approved
                         ↓
                    [signed] → [verified]
```

---

## ⏱️ Timeline Estimate

| Phase | Actor | Duration |
|-------|-------|----------|
| 1. Key Generation | Kaprodi | 5-10 minutes (one-time) |
| 2. Document Submission | User | 3-5 minutes |
| 3. Document Approval | Kaprodi | 2-5 minutes per doc |
| 4. Digital Signing | Kaprodi | 3-7 minutes per doc |
| 5. Document Distribution | System | Instant (automated) |
| 6. Verification | Public | 10-30 seconds |

**Total Time (per document)**: ~15-30 minutes from submission to signed delivery

---

**Next**: Read [DIGITAL_SIGNATURE_SYSTEM_FLOW.md](DIGITAL_SIGNATURE_SYSTEM_FLOW.md) untuk detail internal system flow.
