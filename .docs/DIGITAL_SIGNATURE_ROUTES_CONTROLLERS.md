# Routes & Controllers Reference - Digital Signature System

## 📍 Complete Routes Documentation

**File**: `routes/web.php` (Lines 65-270)

---

## 🌐 PUBLIC ROUTES (No Authentication)

### Group: `signature.*`

#### 1. Verification Page

```php
GET /signature/verify
```

**Controller**: `VerificationController@verificationPage`

**Purpose**: Display public verification form

**Access**: Public (anyone)

**Returns**: Blade view with verification form

**Usage**:

-   User opens this page to manually verify a signature
-   Input: verification code or token
-   Submit ke `POST /signature/verify`

---

#### 2. Verify by Token (QR Scan)

```php
GET /signature/verify/{token}
```

**Controller**: `VerificationController@verifyByToken`

**Purpose**: Verify signature via QR code scan

**Parameters**:

-   `token` (string): Short code (e.g., "A1B2-C3D4-E5F6") atau full encrypted token

**Access**: Public

**Process**:

1. Decrypt token via `QRCodeService::decryptVerificationData()`
2. Verify via `VerificationService::verifyByToken()`
3. Log attempt ke `signature_verification_logs`
4. Return verification result view

**Returns**: Verification result page (valid/invalid)

**Example URLs**:

```
https://domain.com/signature/verify/A1B2-C3D4-E5F6
https://domain.com/signature/verify/eyJpdiI6ImlubGl...
```

---

#### 3. Public Verification Form Submit

```php
POST /signature/verify
```

**Controller**: `VerificationController@verifyPublic`

**Purpose**: Manual verification form submission

**Request Body**:

```json
{
    "verification_code": "A1B2-C3D4-E5F6"
}
```

**Access**: Public

**Process**: Same as `verifyByToken()`

**Returns**: JSON response atau redirect ke result page

---

### API Endpoints (Public)

#### 4. Get Verification Details (API)

```php
GET /signature/api/verify/{token}
```

**Controller**: `VerificationController@getVerificationDetails`

**Purpose**: Get verification details via API (JSON response)

**Parameters**:

-   `token` (string): Verification token

**Access**: Public API

**Response**:

```json
{
    "success": true,
    "data": {
        "is_valid": true,
        "document_name": "Surat Permohonan PKL",
        "document_number": "001/PKL/2025",
        "signed_by": "Dr. John Doe",
        "signed_at": "2025-10-30 12:30:00",
        "verification_checks": {
            "document_exists": true,
            "digital_signature_valid": true,
            "document_integrity": true,
            "cms_signature_valid": true,
            "timestamp_valid": true
        },
        "verification_summary": {
            "overall_status": "VALID",
            "checks_passed": 7,
            "checks_failed": 0,
            "success_rate": 100
        }
    }
}
```

**Usage**: For mobile apps atau third-party integrations

---

#### 5. Bulk Verify (API)

```php
POST /signature/api/bulk-verify
```

**Controller**: `VerificationController@bulkVerify`

**Purpose**: Verify multiple signatures at once

**Request Body**:

```json
{
    "tokens": ["A1B2-C3D4-E5F6", "G7H8-I9J0-K1L2", "M3N4-O5P6-Q7R8"]
}
```

**Access**: Public API

**Response**:

```json
{
    "success": true,
    "data": {
        "total": 3,
        "valid": 2,
        "invalid": 1,
        "results": [
            {
                "token": "A1B2-C3D4-E5F6",
                "is_valid": true,
                "document_name": "Doc A"
            },
            {
                "token": "G7H8-I9J0-K1L2",
                "is_valid": true,
                "document_name": "Doc B"
            },
            {
                "token": "M3N4-O5P6-Q7R8",
                "is_valid": false,
                "error": "Document has been modified"
            }
        ]
    }
}
```

---

#### 6. Public Statistics (API)

```php
GET /signature/api/statistics
```

**Controller**: `VerificationController@getPublicStatistics`

**Purpose**: Get public verification statistics

**Access**: Public API

**Response**:

```json
{
    "success": true,
    "data": {
        "total_signatures": 1234,
        "total_verifications": 5678,
        "verification_rate": 4.6,
        "period_days": 30
    }
}
```

---

#### 7. Download Verification Certificate

```php
GET /signature/certificate/{token}
```

**Controller**: `VerificationController@downloadCertificate`

**Purpose**: Download verification certificate (PDF report)

**Parameters**:

-   `token` (string): Verification token

**Access**: Public

**Process**:

1. Verify signature
2. Generate PDF certificate with verification details
3. Return PDF download

**Returns**: PDF file download

---

## 🔐 KAPRODI ROUTES (Authentication Required)

**Middleware**: `auth:kaprodi`

**Base Path**: `/admin/signature`

### Dashboard

#### 8. Admin Dashboard

```php
GET /admin/signature/dashboard
```

**Controller**: `DigitalSignatureController@adminDashboard`

**Purpose**: Main dashboard untuk Kaprodi

**Access**: Authenticated Kaprodi only

**Returns**: Dashboard view with:

-   Total keys generated
-   Total documents signed
-   Recent signature activities
-   Key expiration warnings
-   Verification statistics

---

## 🔑 KEY MANAGEMENT ROUTES

### Group: `admin.signature.keys.*`

#### 9. List Keys

```php
GET /admin/signature/keys
```

**Controller**: `DigitalSignatureController@listKeys`

**Purpose**: List all digital signature keys untuk current Kaprodi

**Access**: Authenticated Kaprodi

**Query Parameters**:

-   `status` (optional): 'active', 'expired', 'revoked'
-   `sort` (optional): 'created_at', 'valid_until'

**Returns**: List view dengan pagination

---

#### 10. Generate New Key

```php
POST /admin/signature/keys/generate
```

**Controller**: `DigitalSignatureController@generateKey`

**Purpose**: Generate new RSA key pair

**Request Body**:

```json
{
    "key_length": 2048,
    "validity_years": 1,
    "purpose": "Document Signing"
}
```

**Access**: Authenticated Kaprodi

**Process**:

1. Call `DigitalSignatureService::createDigitalSignature()`
2. Generate RSA-2048 key pair
3. Create self-signed certificate
4. Store encrypted private key
5. Create audit log

**Response**:

```json
{
    "success": true,
    "message": "Digital signature key generated successfully",
    "data": {
        "signature_id": "SIG-ABC123",
        "algorithm": "RSA-SHA256",
        "key_length": 2048,
        "valid_from": "2025-10-30",
        "valid_until": "2026-10-30"
    }
}
```

---

#### 11. View Key Details

```php
GET /admin/signature/keys/{id}
```

**Controller**: `DigitalSignatureController@showKey`

**Purpose**: View detailed info tentang specific key

**Parameters**:

-   `id` (int): digital_signatures.id

**Access**: Authenticated Kaprodi (own keys only)

**Returns**: Key details view with:

-   Public key (truncated)
-   Certificate info
-   Usage statistics
-   List of documents signed with this key
-   Expiration countdown

---

#### 12. Revoke Key

```php
POST /admin/signature/keys/{id}/revoke
```

**Controller**: `DigitalSignatureController@revokeKey`

**Purpose**: Revoke a digital signature key

**Parameters**:

-   `id` (int): digital_signatures.id

**Request Body**:

```json
{
    "reason": "Security breach detected"
}
```

**Access**: Authenticated Kaprodi (own keys only)

**Process**:

1. Call `DigitalSignatureService::revokeSignature()`
2. Update status to 'revoked'
3. Invalidate all associated document signatures
4. Create audit log

**Response**:

```json
{
    "success": true,
    "message": "Key revoked successfully",
    "data": {
        "signature_id": "SIG-ABC123",
        "revoked_at": "2025-10-30 15:00:00",
        "reason": "Security breach detected",
        "affected_documents": 45
    }
}
```

---

#### 13. Download Public Key

```php
GET /admin/signature/keys/{id}/public-key
```

**Controller**: `DigitalSignatureController@downloadPublicKey`

**Purpose**: Download public key (PEM format)

**Parameters**:

-   `id` (int): digital_signatures.id

**Access**: Authenticated Kaprodi

**Returns**: Text file dengan public key PEM

---

#### 14. Key Statistics

```php
GET /admin/signature/keys/{id}/statistics
```

**Controller**: `DigitalSignatureController@keyStatistics`

**Purpose**: Get usage statistics untuk specific key

**Parameters**:

-   `id` (int): digital_signatures.id

**Access**: Authenticated Kaprodi

**Response**:

```json
{
    "success": true,
    "data": {
        "signature_id": "SIG-ABC123",
        "total_documents_signed": 156,
        "successful_signatures": 156,
        "pending_signatures": 0,
        "last_used": "2025-10-30 14:00:00",
        "days_until_expiry": 245,
        "usage_stats": {
            "avg_per_day": 5.2,
            "peak_day": "2025-10-15",
            "peak_count": 23
        }
    }
}
```

---

## 📝 DOCUMENT SIGNING ROUTES

### Group: `admin.signature.documents.*`

#### 15. List Pending Documents

```php
GET /admin/signature/documents/pending
```

**Controller**: `DigitalSignatureController@listPendingDocuments`

**Purpose**: List approval requests yang ready untuk signing

**Access**: Authenticated Kaprodi

**Query Parameters**:

-   `page` (optional): Pagination
-   `per_page` (optional): Items per page
-   `search` (optional): Search by document name

**Returns**: List of ApprovalRequests dengan status 'approved'

---

#### 16. Sign Document Page

```php
GET /admin/signature/sign/{approvalRequestId}
```

**Controller**: `DigitalSignatureController@signDocumentPage`

**Purpose**: Display signing interface

**Parameters**:

-   `approvalRequestId` (int): approval_requests.id

**Access**: Authenticated Kaprodi

**Returns**: Vue/React component view with:

-   PDF viewer/canvas
-   Signature template selector
-   Key selector dropdown
-   Position controls
-   Sign button

---

#### 17. Sign Document (Submit)

```php
POST /admin/signature/sign
```

**Controller**: `DigitalSignatureController@signDocument`

**Purpose**: Perform digital signing operation

**Request Body**:

```json
{
    "approval_request_id": 123,
    "digital_signature_id": 5,
    "template_id": 2,
    "positioning_data": {
        "page": 1,
        "position": { "x": 450, "y": 650 },
        "size": { "width": 200, "height": 80 },
        "canvas_dimensions": { "width": 595, "height": 842 }
    }
}
```

**Access**: Authenticated Kaprodi

**Process** (Complex):

1. Validate inputs
2. Create CMS signature (`DigitalSignatureService::createCMSSignature()`)
3. Create DocumentSignature record
4. Generate QR code (`QRCodeService::generateVerificationQR()`)
5. Embed signature + QR into PDF (`PDFSignatureService::mergeSignatureIntoPDF()`)
6. Update ApprovalRequest status
7. Create audit log
8. Send notification to user

**Response**:

```json
{
    "success": true,
    "message": "Document signed successfully",
    "data": {
        "document_signature_id": 42,
        "signed_pdf_url": "/storage/signed-documents/signed_xxx.pdf",
        "qr_code_url": "/storage/qrcodes/qr_42.png",
        "verification_url": "https://domain.com/signature/verify/A1B2-C3D4",
        "signed_at": "2025-10-30 12:30:00"
    }
}
```

---

#### 18. List Signed Documents

```php
GET /admin/signature/documents/signed
```

**Controller**: `DigitalSignatureController@listSignedDocuments`

**Purpose**: List all documents signed by current Kaprodi

**Access**: Authenticated Kaprodi

**Returns**: List of DocumentSignatures dengan status 'verified'

---

#### 19. Download Signed Document

```php
GET /admin/signature/download/{documentSignatureId}
```

**Controller**: `DigitalSignatureController@downloadSignedDocument`

**Purpose**: Download signed PDF

**Parameters**:

-   `documentSignatureId` (int): document_signatures.id

**Access**: Authenticated Kaprodi

**Returns**: PDF file download

---

#### 20. View Document Signature Details

```php
GET /admin/signature/documents/{id}
```

**Controller**: `DigitalSignatureController@showDocumentSignature`

**Purpose**: View details tentang specific signed document

**Parameters**:

-   `id` (int): document_signatures.id

**Access**: Authenticated Kaprodi

**Returns**: Detail view with:

-   Document info
-   Signature metadata
-   Verification status
-   QR code
-   Verification logs
-   Download links

---

## 🖼️ TEMPLATE MANAGEMENT ROUTES

### Group: `admin.signature.templates.*`

#### 21. List Templates

```php
GET /admin/signature/templates
```

**Controller**: `DigitalSignatureController@listTemplates`

**Purpose**: List signature visual templates

**Access**: Authenticated Kaprodi

**Returns**: Grid/list view of available templates

---

#### 22. Create Template

```php
POST /admin/signature/templates
```

**Controller**: `DigitalSignatureController@createTemplate`

**Purpose**: Upload dan create new signature template

**Request**: Multipart form data

-   `template_name` (string)
-   `signature_image` (file): PNG/JPG image
-   `layout_config` (JSON)
-   `style_config` (JSON)

**Access**: Authenticated Kaprodi

**Process**:

1. Upload image ke storage
2. Create SignatureTemplate record
3. Return template_id

---

#### 23. Update Template

```php
PUT /admin/signature/templates/{id}
```

**Controller**: `DigitalSignatureController@updateTemplate`

**Purpose**: Update template settings

**Parameters**:

-   `id` (int): signature_templates.id

**Access**: Authenticated Kaprodi (own templates only)

---

#### 24. Delete Template

```php
DELETE /admin/signature/templates/{id}
```

**Controller**: `DigitalSignatureController@deleteTemplate`

**Purpose**: Delete template

**Parameters**:

-   `id` (int): signature_templates.id

**Access**: Authenticated Kaprodi (own templates only)

---

## 📊 AUDIT & LOGS ROUTES

### Group: `admin.signature.logs.*`

#### 25. View Audit Logs

```php
GET /admin/signature/logs/audit
```

**Controller**: `DigitalSignatureController@auditLogs`

**Purpose**: View signature audit logs

**Access**: Authenticated Kaprodi

**Query Parameters**:

-   `action` (optional): Filter by action type
-   `date_from` (optional): Filter by date range
-   `date_to` (optional): Filter by date range

**Returns**: Paginated audit logs table

---

#### 26. View Verification Logs

```php
GET /admin/signature/logs/verification
```

**Controller**: `DigitalSignatureController@verificationLogs`

**Purpose**: View all verification attempts

**Access**: Authenticated Kaprodi

**Returns**: Verification logs with:

-   Timestamp
-   IP address
-   Result (valid/invalid)
-   Document name

---

#### 27. View All Logs (Combined)

```php
GET /admin/signature/logs
```

**Controller**: `DigitalSignatureController@allLogs`

**Purpose**: Combined view of audit + verification logs

**Access**: Authenticated Kaprodi

**Returns**: Unified logs view

---

## 📈 ANALYTICS & REPORTS

#### 28. Signature Analytics

```php
GET /admin/signature/analytics
```

**Controller**: `DigitalSignatureController@analytics`

**Purpose**: View analytics dashboard

**Access**: Authenticated Kaprodi

**Returns**: Charts and statistics:

-   Documents signed over time
-   Verification attempts over time
-   Key usage distribution
-   Success rates

---

#### 29. Export Report

```php
GET /admin/signature/export
```

**Controller**: `DigitalSignatureController@exportReport`

**Purpose**: Export data untuk reporting

**Query Parameters**:

-   `type`: 'signatures', 'verifications', 'audit'
-   `format`: 'csv', 'xlsx', 'pdf'
-   `date_from`: Start date
-   `date_to`: End date

**Access**: Authenticated Kaprodi

**Returns**: File download (CSV/XLSX/PDF)

---

## 🔄 USER-FACING ROUTES (Approval Requests)

### Group: `approval-requests.*`

#### 30. List User's Approval Requests

```php
GET /approval-requests
```

**Controller**: `ApprovalRequestController@index`

**Purpose**: User views their submitted documents

**Access**: Authenticated User

**Returns**: List of user's approval requests dengan status

---

#### 31. Create Approval Request

```php
POST /approval-requests
```

**Controller**: `ApprovalRequestController@store`

**Purpose**: Submit new document untuk approval

**Request**: Multipart form data

-   `document_name`
-   `document_type`
-   `document_number`
-   `document_file` (PDF)
-   `notes`

**Access**: Authenticated User

**Process**:

1. Validate PDF
2. Store file
3. Create ApprovalRequest
4. Notify Kaprodi

---

## 📋 Route Groups Summary

| Group               | Base Path                    | Routes Count | Authentication | Purpose           |
| ------------------- | ---------------------------- | ------------ | -------------- | ----------------- |
| Public Verification | `/signature/verify`          | 6            | None           | Verify signatures |
| Verification API    | `/signature/api`             | 3            | None           | API access        |
| Admin Dashboard     | `/admin/signature`           | 1            | Kaprodi        | Overview          |
| Key Management      | `/admin/signature/keys`      | 6            | Kaprodi        | Manage keys       |
| Document Signing    | `/admin/signature/documents` | 5            | Kaprodi        | Sign documents    |
| Template Management | `/admin/signature/templates` | 4            | Kaprodi        | Manage templates  |
| Logs & Audit        | `/admin/signature/logs`      | 3            | Kaprodi        | View logs         |
| Analytics           | `/admin/signature/analytics` | 2            | Kaprodi        | Reports           |
| User Requests       | `/approval-requests`         | 2+           | User           | Submit docs       |

**Total Routes**: ~32 endpoints

---

## 🔐 Middleware Stack

### Public Routes

```php
// No middleware
Route::get('signature/verify/{token}', ...)
```

### Kaprodi Routes

```php
Route::middleware(['auth:kaprodi'])->group(function () {
    // All admin routes
});
```

### User Routes

```php
Route::middleware(['auth'])->group(function () {
    // User approval request routes
});
```

---

## 🎯 API Response Format (Standardized)

### Success Response

```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        // Response data
    }
}
```

### Error Response

```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

---

**Next**: Read [DIGITAL_SIGNATURE_ANALYSIS_OVERVIEW.md](DIGITAL_SIGNATURE_ANALYSIS_OVERVIEW.md) untuk kembali ke overview.
