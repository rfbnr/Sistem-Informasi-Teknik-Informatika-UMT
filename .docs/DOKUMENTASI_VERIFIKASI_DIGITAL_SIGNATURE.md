# Dokumentasi Sistem Verifikasi Digital Signature

## Program Studi Teknik Informatika - Universitas Muhammadiyah Tangerang

---

## 📋 Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Arsitektur Database](#arsitektur-database)
3. [Model dan Relasi](#model-dan-relasi)
4. [Alur User (User Flow)](#alur-user-user-flow)
5. [Alur Sistem (System Flow)](#alur-sistem-system-flow)
6. [Keamanan dan Privasi](#keamanan-dan-privasi)
7. [API Endpoints](#api-endpoints)

---

## 📖 Pendahuluan

### Tujuan Sistem

Sistem Digital Signature ini dirancang untuk memberikan mekanisme tanda tangan digital yang aman dan terverifikasi untuk dokumen-dokumen akademik di Program Studi Teknik Informatika UMT. Sistem ini menggunakan teknologi kriptografi RSA-SHA256 dan standar X.509 certificate untuk memastikan integritas dan autentisitas dokumen.

### Fitur Utama

-   ✅ **Tanda Tangan Digital dengan RSA-SHA256**: Setiap dokumen ditandatangani dengan kunci unik
-   ✅ **Sertifikat X.509 v3**: Setiap tanda tangan memiliki sertifikat digital yang valid
-   ✅ **Verifikasi Publik**: Siapa saja dapat memverifikasi keaslian dokumen melalui QR Code atau Token
-   ✅ **Audit Trail Lengkap**: Semua aktivitas tercatat dalam log audit
-   ✅ **Keamanan Multi-Layer**: Rate limiting, encryption, dan masking data sensitif
-   ✅ **QR Code Dynamic Positioning**: Kaprodi dapat menempatkan QR code di posisi yang diinginkan

### Teknologi yang Digunakan

-   **Backend**: Laravel 10+, PHP 8.1+
-   **Kriptografi**: OpenSSL (RSA 2048-bit, SHA-256)
-   **Database**: MySQL/MariaDB
-   **QR Code**: Endroid QR Code Library
-   **PDF Processing**: TCPDF/DomPDF

---

## 🗄️ Arsitektur Database

### 1. Tabel: `approval_requests`

**Deskripsi**: Menyimpan data permintaan persetujuan dokumen dari mahasiswa/user.

**Kolom Utama**:

```sql
- id                      (Primary Key)
- user_id                 (FK -> users)
- document_name           (Nama dokumen)
- document_path           (Path file PDF original)
- signed_document_path    (Path file PDF yang sudah ditandatangani)
- notes                   (Catatan dari user)
- status                  (pending, approved, user_signed, sign_approved, rejected)
- approved_at             (Timestamp approval oleh kaprodi)
- approved_by             (FK -> kaprodis)
- rejected_at             (Timestamp penolakan)
- rejected_by             (FK -> kaprodis)
- user_signed_at          (Timestamp user menyelesaikan signing)
- sign_approved_at        (Timestamp verifikasi final)
- sign_approved_by        (FK -> kaprodis)
- approval_notes          (Catatan approval dari kaprodi)
- rejection_reason        (Alasan penolakan)
- document_type           (Jenis dokumen)
- workflow_metadata       (JSON: metadata workflow)
- created_at, updated_at
```

**Status Flow**:

```
pending → approved → sign_approved
   ↓
rejected
```

**Indexes**:

-   `user_id` (untuk query dokumen per user)
-   `status` (untuk filter status)
-   `approved_by` (untuk query per kaprodi)

---

### 2. Tabel: `document_signatures`

**Deskripsi**: Menyimpan data tanda tangan digital untuk setiap dokumen.

**Kolom Utama**:

```sql
- id                       (Primary Key)
- approval_request_id      (FK -> approval_requests, UNIQUE 1-to-1)
- document_hash            (SHA-256 hash dari dokumen final)
- signature_value          (SHA-256 hash dari signature binary)
- signature_metadata       (JSON: metadata lengkap signing)
- temporary_qr_code_path   (Path QR code sementara untuk drag & drop)
- qr_code_path             (Path QR code final)
- verification_url         (URL verifikasi publik)
- cms_signature            (Base64 encoded CMS signature)
- signed_at                (Timestamp penandatanganan)
- signed_by                (FK -> kaprodis)
- signature_status         (pending, signed, verified, invalid)
- qr_positioning_data      (JSON: posisi QR code pada PDF)
- final_pdf_path           (Path PDF final dengan QR embedded)
- verification_token       (Token 64 char untuk verifikasi publik)
- created_at, updated_at
```

**Status Flow**:

```
pending → signed → verified
   ↓
invalid (jika key dicabut)
```

**Indexes**:

-   `approval_request_id` (foreign key)
-   `document_hash, signature_status` (composite untuk query verifikasi)
-   `signed_at, signature_status` (untuk reporting)

---

### 3. Tabel: `digital_signatures`

**Deskripsi**: Menyimpan pasangan kunci RSA (public/private key) dan sertifikat X.509 untuk setiap dokumen.

**Konsep Penting**:

-   **1-to-1 Relationship dengan `document_signatures`**
-   **Setiap dokumen memiliki kunci unik** (tidak sharing key antar dokumen)
-   **Auto-generated** saat proses signing

**Kolom Utama**:

```sql
- id                       (Primary Key)
- signature_id             (String unik: SIG-XXXXXXXXXXXX)
- document_signature_id    (FK -> document_signatures, UNIQUE 1-to-1)
- public_key               (Text: RSA public key PEM format)
- private_key              (Text: RSA private key PEM format, ENCRYPTED)
- algorithm                (RSA-SHA256)
- key_length               (2048 bit)
- certificate              (Text: X.509 certificate PEM format)
- valid_from               (Timestamp mulai berlaku)
- valid_until              (Timestamp berakhir, default 3 tahun)
- status                   (active, expired, revoked)
- revocation_reason        (Alasan pencabutan jika revoked)
- revoked_at               (Timestamp pencabutan)
- metadata                 (JSON: fingerprint, created_ip, dll)
- created_at, updated_at
```

**Status Flow**:

```
active → expired (otomatis setelah valid_until)
   ↓
revoked (manual oleh kaprodi)
```

**Security Features**:

-   Private key di-encrypt menggunakan Laravel encryption (APP_KEY)
-   Certificate menggunakan standar X.509 v3 dengan DN (Distinguished Name) yang personalized

**Indexes**:

-   `signature_id` (untuk quick lookup)
-   `document_signature_id` (foreign key, unique)
-   `status, valid_from, valid_until` (composite untuk query validity check)

---

### 4. Tabel: `signature_verification_logs`

**Deskripsi**: Mencatat semua aktivitas verifikasi publik terhadap dokumen yang ditandatangani.

**Kolom Utama**:

```sql
- id                          (Primary Key)
- document_signature_id       (FK -> document_signatures, nullable)
- approval_request_id         (FK -> approval_requests, nullable)
- user_id                     (FK -> users, nullable untuk anonymous)
- verification_method         (token, url, qr, id)
- verification_token_hash     (SHA-256 hash token untuk privacy)
- is_valid                    (Boolean: hasil verifikasi)
- result_status               (success, failed, expired, invalid, not_found)
- ip_address                  (IP address verifier)
- user_agent                  (Browser/device info)
- referrer                    (URL referrer)
- metadata                    (JSON: device_type, browser, error details)
- verified_at                 (Timestamp verifikasi)
- created_at, updated_at
```

**Tujuan Logging**:

-   **Audit Trail**: Siapa, kapan, dari mana melakukan verifikasi
-   **Security Monitoring**: Deteksi brute force atau suspicious activity
-   **Analytics**: Statistik penggunaan sistem verifikasi
-   **Compliance**: Bukti audit untuk akreditasi

**Indexes**:

-   `document_signature_id, verified_at` (untuk query verifikasi per dokumen)
-   `is_valid, verified_at` (untuk statistik success/failed)
-   `ip_address, verified_at` (untuk deteksi abuse)
-   `verification_method, verified_at` (untuk analytics metode verifikasi)

---

### 5. Tabel: `signature_audit_logs`

**Deskripsi**: Mencatat semua aktivitas internal sistem terkait signing dan management.

**Kolom Utama**:

```sql
- id                          (Primary Key)
- document_signature_id       (FK -> document_signatures, nullable)
- approval_request_id         (FK -> approval_requests, nullable)
- user_id                     (FK -> users, nullable)
- kaprodi_id                  (FK -> kaprodis, nullable)
- action                      (signature_initiated, document_signed, dll)
- status_from                 (Status sebelumnya)
- status_to                   (Status sesudahnya)
- description                 (Deskripsi lengkap aktivitas)
- metadata                    (JSON: detail lengkap)
- ip_address                  (IP address actor)
- user_agent                  (Browser/device info)
- performed_at                (Timestamp aktivitas)
- created_at, updated_at
```

**Action Constants**:

```php
- signature_initiated              // Proses signing dimulai
- document_signed                  // Dokumen berhasil ditandatangani
- signature_verified               // Signature diverifikasi
- signature_invalidated            // Signature dibatalkan
- verification_token_regenerated   // Token verifikasi di-regenerate
- signature_key_generated          // Kunci RSA dibuat
- signature_key_revoked            // Kunci RSA dicabut
- signing_failed                   // Proses signing gagal
```

**Indexes**:

-   `user_id, performed_at` (query log per user)
-   `action, performed_at` (query per jenis aktivitas)
-   `document_signature_id, action` (query log per dokumen)

---

## 🔗 Model dan Relasi

### 1. Model: `ApprovalRequest`

**Lokasi**: `app/Models/ApprovalRequest.php`

**Relasi**:

```php
// 1. User yang mengajukan (Many-to-One)
public function user()
{
    return $this->belongsTo(User::class);
}

// 2. Kaprodi yang approve (Many-to-One)
public function approver()
{
    return $this->belongsTo(Kaprodi::class, 'approved_by');
}

// 3. Kaprodi yang approve signature (Many-to-One)
public function signApprover()
{
    return $this->belongsTo(Kaprodi::class, 'sign_approved_by');
}

// 4. Kaprodi yang reject (Many-to-One)
public function rejector()
{
    return $this->belongsTo(Kaprodi::class, 'rejected_by');
}

// 5. Document Signature (One-to-One)
public function documentSignature()
{
    return $this->hasOne(DocumentSignature::class);
}

// 6. Digital Signature (Has-One-Through)
public function digitalSignature()
{
    return $this->hasOneThrough(
        DigitalSignature::class,
        DocumentSignature::class,
        'approval_request_id',
        'id',
        'id',
        'digital_signature_id'
    );
}

// 7. Audit Logs (One-to-Many)
public function auditLogs()
{
    return $this->hasMany(SignatureAuditLog::class);
}
```

**Method Penting**:

```php
// Check apakah dokumen bisa ditandatangani user
public function canBeSignedByUser(): bool

// Approve dokumen oleh kaprodi
public function approveApprovalRequest($approverId, $notes = null): DocumentSignature

// Reject dokumen
public function reject($reason = null, $rejectedBy = null): void

// Mark dokumen sudah ditandatangani (auto-approve)
public function markUserSigned($signPath): void
```

**Status Constants**:

```php
const STATUS_PENDING = 'pending';           // Menunggu approval kaprodi
const STATUS_APPROVED = 'approved';         // Disetujui, siap ditandatangani
const STATUS_USER_SIGNED = 'user_signed';   // (Deprecated) Sudah ditandatangani
const STATUS_SIGN_APPROVED = 'sign_approved'; // Selesai & terverifikasi
const STATUS_REJECTED = 'rejected';         // Ditolak
```

---

### 2. Model: `DocumentSignature`

**Lokasi**: `app/Models/DocumentSignature.php`

**Relasi**:

```php
// 1. Approval Request (Many-to-One)
public function approvalRequest()
{
    return $this->belongsTo(ApprovalRequest::class);
}

// 2. Digital Signature (One-to-One)
public function digitalSignature()
{
    return $this->hasOne(DigitalSignature::class, 'document_signature_id');
}

// 3. Signer (Kaprodi yang menandatangani) (Many-to-One)
public function signer()
{
    return $this->belongsTo(Kaprodi::class, 'signed_by');
}

// 4. Audit Logs (One-to-Many)
public function auditLogs()
{
    return $this->hasMany(SignatureAuditLog::class);
}

// 5. Verification Logs (One-to-Many)
public function verificationLogs()
{
    return $this->hasMany(SignatureVerificationLog::class);
}
```

**Method Penting**:

```php
// Generate hash SHA-256 dari file
public static function generateDocumentHash($filePath): string

// Check apakah signature valid
public function isValid(): bool

// Invalidate signature (karena key revoked, dll)
public function invalidate($reason = null): void

// Regenerate verification token
public function regenerateVerificationToken(): string

// Get signature info untuk display
public function getSignatureInfo(): array

// Generate temporary QR code untuk drag & drop
public function generateTemporaryQRCode(): string

// Clear temporary QR code setelah finalisasi
public function clearTemporaryQRCode(): void

// Save posisi QR code dari drag & drop
public function saveQRPositioning($positioningData): bool
```

**Status Constants**:

```php
const STATUS_PENDING = 'pending';   // Belum ditandatangani
const STATUS_SIGNED = 'signed';     // Sudah ditandatangani
const STATUS_VERIFIED = 'verified'; // Terverifikasi
const STATUS_INVALID = 'invalid';   // Tidak valid (key revoked)
```

**Auto-Generated Fields** (via Model Events):

-   `verification_token`: 64 karakter random string (saat create)
-   Audit log otomatis tercatat saat create

---

### 3. Model: `DigitalSignature`

**Lokasi**: `app/Models/DigitalSignature.php`

**Relasi**:

```php
// 1. Document Signature (Many-to-One / Belongs-to-One)
public function documentSignature()
{
    return $this->belongsTo(DocumentSignature::class);
}
```

**Method Penting**:

```php
// Check apakah signature key masih valid
public function isValid(): bool

// Check apakah akan expired dalam X hari
public function isExpiringSoon($days = 30): bool

// Revoke signature key
public function revoke($reason = null): void

// Get fingerprint dari public key
public function getPublicKeyFingerprint(): string
```

**Accessor/Mutator (Encryption)**:

```php
// Private key di-encrypt saat disimpan
public function setPrivateKeyAttribute($value)
{
    $this->attributes['private_key'] = encrypt($value);
}

// Private key di-decrypt saat diambil
public function getPrivateKeyAttribute($value)
{
    return decrypt($value);
}
```

**Auto-Generated Fields** (via Model Events):

-   `signature_id`: Format `SIG-XXXXXXXXXXXX` (12 karakter random uppercase)
-   `valid_from`: Default `now()`
-   `valid_until`: Default `now()->addYears(3)` (3 tahun)

**Hidden Fields** (tidak di-expose dalam JSON response):

-   `private_key`: Untuk keamanan, tidak pernah di-expose via API

---

### 4. Model: `SignatureVerificationLog`

**Lokasi**: `app/Models/SignatureVerificationLog.php`

**Relasi**:

```php
// 1. Document Signature (Many-to-One)
public function documentSignature()
{
    return $this->belongsTo(DocumentSignature::class);
}

// 2. Approval Request (Many-to-One)
public function approvalRequest()
{
    return $this->belongsTo(ApprovalRequest::class);
}

// 3. User (Many-to-One, nullable untuk anonymous)
public function user()
{
    return $this->belongsTo(User::class);
}
```

**Computed Properties** (Accessor):

```php
// Device type dari user agent
public function getDeviceTypeAttribute(): string // desktop, mobile, tablet, bot

// Browser name dari user agent
public function getBrowserNameAttribute(): string // Chrome, Firefox, Safari, dll

// Check apakah verifikasi anonymous
public function getIsAnonymousAttribute(): bool

// Get alasan gagal jika verification failed
public function getFailedReasonAttribute(): ?string

// Duration verifikasi dalam milliseconds
public function getVerificationDurationMsAttribute(): ?int

// Label hasil untuk UI
public function getResultLabelAttribute(): string

// Icon untuk UI
public function getResultIconAttribute(): string

// Color untuk UI badge
public function getResultColorAttribute(): string
```

**Scopes**:

```php
public function scopeSuccessful($query)      // Filter verifikasi sukses
public function scopeFailed($query)          // Filter verifikasi gagal
public function scopeByMethod($query, $method)
public function scopeByDocument($query, $docId)
public function scopeToday($query)
public function scopeInPeriod($query, $start, $end)
public function scopeAnonymous($query)       // Filter verifikasi tanpa login
public function scopeAuthenticated($query)   // Filter verifikasi dengan login
public function scopeSuspiciousActivity($query) // Deteksi abuse (multiple failed)
```

**Static Helper Methods**:

```php
// Get statistics verifikasi
public static function getStatistics($startDate = null, $endDate = null): array

// Create standardized metadata
public static function createMetadata($customData = []): array

// Categorize failed reason dari error message
public static function categorizeFailedReason($errorMessage): string
```

---

### 5. Model: `SignatureAuditLog`

**Lokasi**: `app/Models/SignatureAuditLog.php`

**Relasi**:

```php
// 1. Document Signature (Many-to-One)
public function documentSignature()
{
    return $this->belongsTo(DocumentSignature::class);
}

// 2. Approval Request (Many-to-One)
public function approvalRequest()
{
    return $this->belongsTo(ApprovalRequest::class);
}

// 3. User (Many-to-One)
public function user()
{
    return $this->belongsTo(User::class);
}

// 4. Kaprodi (Many-to-One)
public function kaprodi()
{
    return $this->belongsTo(Kaprodi::class);
}
```

**Computed Properties** (Accessor):

```php
// Label action untuk display
public function getActionLabelAttribute(): string

// Icon untuk UI
public function getActionIconAttribute(): string

// Color untuk UI
public function getActionColorAttribute(): string

// Device type
public function getDeviceTypeAttribute(): string

// Browser name
public function getBrowserNameAttribute(): string

// Duration dalam ms
public function getDurationMsAttribute(): ?int

// Session ID
public function getSessionIdAttribute(): ?string

// Error code jika ada
public function getErrorCodeAttribute(): ?string

// Check apakah action sukses
public function getIsSuccessAttribute(): bool
```

**Scopes**:

```php
public function scopeByAction($query, $action)
public function scopeInPeriod($query, $start, $end)
public function scopeByUser($query, $userId)
public function scopeByDocument($query, $approvalRequestId)
public function scopeByKaprodi($query, $kaprodiId)
public function scopeFailedActions($query)
public function scopeSuccessfulActions($query)
public function scopeToday($query)
public function scopeLastNDays($query, $days = 7)
```

**Static Helper Methods**:

```php
// Get statistics audit logs
public static function getStatistics($startDate = null, $endDate = null): array

// Create standardized metadata
public static function createMetadata($customData = []): array
```

---

## 👤 Alur User (User Flow)

### Flow 1: Mahasiswa/User Mengajukan Dokumen untuk Ditandatangani

**Aktor**: Mahasiswa/User yang sudah login

**Langkah-langkah**:

1. **Login ke Sistem**

    - User login menggunakan kredensial (auth:web)
    - Route: `POST /login`

2. **Navigate ke Halaman Approval Request**

    - User membuka halaman form pengajuan approval
    - Route: `GET /user/signature/approval/request`
    - Controller: `ApprovalRequestController@showUploadForm`

3. **Upload Dokumen**

    - User memilih file PDF yang akan ditandatangani
    - User mengisi informasi:
        - Nama dokumen
        - Jenis dokumen (opsional)
        - Catatan (opsional)
    - Route: `POST /user/signature/approval/upload`
    - Controller: `ApprovalRequestController@upload`

4. **Sistem Memproses Upload**

    - Validasi file (harus PDF, max 10MB)
    - Simpan file ke `storage/public/documents/`
    - Buat record di tabel `approval_requests`:
        ```php
        ApprovalRequest::create([
            'user_id' => auth()->id(),
            'document_name' => $request->document_name,
            'document_path' => $filePath,
            'notes' => $request->notes,
            'status' => 'pending',
            'document_type' => $request->document_type
        ]);
        ```
    - Auto-create audit log (via model event)

5. **User Melihat Status**

    - User dapat melihat status dokumen di halaman "Status Pengajuan"
    - Route: `GET /user/signature/approval/status`
    - Controller: `ApprovalRequestController@status`
    - Status awal: `pending` (Menunggu Persetujuan)

6. **Notifikasi ke Kaprodi**
    - Sistem mengirim email notifikasi ke Kaprodi
    - Email berisi: nama dokumen, nama user, link approval

**Database Changes**:

-   Insert 1 row ke `approval_requests` (status: pending)
-   Insert 1 row ke `signature_audit_logs` (action: signature_initiated)

---

### Flow 2: Kaprodi Menyetujui Dokumen

**Aktor**: Kaprodi yang sudah login (auth:kaprodi)

**Langkah-langkah**:

1. **Login sebagai Kaprodi**

    - Kaprodi login menggunakan kredensial kaprodi
    - Route: `POST /login`

2. **Melihat Daftar Approval Request**

    - Kaprodi membuka dashboard approval requests
    - Route: `GET /admin/signature/approval-requests`
    - Controller: `ApprovalRequestController@index`
    - Melihat list dokumen yang status `pending`

3. **Melihat Detail Dokumen**

    - Kaprodi klik salah satu dokumen untuk detail
    - Route: `GET /admin/signature/approval-requests/{id}`
    - Controller: `ApprovalRequestController@show`
    - Kaprodi bisa preview PDF dokumen
    - Route preview: `GET /admin/signature/approval-requests/{id}/download`

4. **Approve Dokumen**

    - Kaprodi klik tombol "Approve"
    - Kaprodi bisa menambahkan catatan approval (opsional)
    - Route: `POST /admin/signature/approval-requests/{id}/approve`
    - Controller: `ApprovalRequestController@approve`

5. **Sistem Memproses Approval**

    ```php
    // Update ApprovalRequest
    $approvalRequest->update([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => auth('kaprodi')->id(),
        'approval_notes' => $notes
    ]);

    // Auto-create DocumentSignature record
    $documentSignature = DocumentSignature::create([
        'approval_request_id' => $approvalRequest->id,
        'signature_status' => 'pending'
    ]);

    // Auto-generate temporary QR code untuk drag & drop
    $documentSignature->generateTemporaryQRCode();
    ```

6. **Auto-Generate Temporary QR Code**

    - Sistem membuat QR code sementara untuk preview
    - QR code disimpan di `storage/public/temp-qrcodes/`
    - Path disimpan di field `temporary_qr_code_path`

7. **Notifikasi ke User**
    - Sistem mengirim email notifikasi ke user
    - Email berisi: dokumen disetujui, menunggu proses signing

**Database Changes**:

-   Update 1 row di `approval_requests` (status: pending → approved)
-   Insert 1 row ke `document_signatures` (status: pending)
-   Insert 1 row ke `signature_audit_logs` (action: approved)
-   File temporary QR code dibuat di storage

---

### Flow 3: User Menandatangani Dokumen (Drag & Drop QR Code)

**Aktor**: User yang dokumennya sudah di-approve

**Langkah-langkah**:

1. **User Melihat Notifikasi/Status**

    - User menerima email bahwa dokumen sudah disetujui
    - User membuka halaman status
    - Route: `GET /user/signature/approval/status`
    - User melihat tombol "Tanda Tangani Dokumen"

2. **User Membuka Halaman Signing**

    - User klik tombol "Tanda Tangani Dokumen"
    - Route: `GET /user/signature/sign/{approvalRequestId}`
    - Controller: `DigitalSignatureController@signDocument`

3. **Halaman Signing di-Load**

    - Sistem menampilkan:
        - PDF viewer (canvas) dengan dokumen original
        - QR code sementara (draggable)
        - Zoom controls
        - Page navigation (untuk multi-page PDF)
    - User dapat drag-drop QR code ke posisi yang diinginkan

4. **User Menempatkan QR Code**

    - User drag QR code ke posisi yang sesuai pada PDF
    - User bisa zoom in/out untuk akurasi
    - Sistem mencatat posisi (x, y, page, scale)

5. **User Submit Positioning**

    - User klik tombol "Proses Tanda Tangan"
    - Konfirmasi dialog: "Apakah posisi QR code sudah sesuai?"
    - Route: `POST /user/signature/sign/{approvalRequestId}/process`
    - Controller: `DigitalSignatureController@processDocumentSigning`

6. **Sistem Memproses Signing** (Backend Heavy Process)

    **Step 1: Save QR Positioning Data**

    ```php
    $documentSignature->saveQRPositioning([
        'x' => $request->qr_x,
        'y' => $request->qr_y,
        'page' => $request->qr_page,
        'scale' => $request->qr_scale
    ]);
    ```

    **Step 2: Generate Verification URL & Token**

    ```php
    $token = $documentSignature->verification_token; // Already generated
    $verificationUrl = route('signature.verify', ['token' => $token]);
    ```

    **Step 3: Generate Final QR Code dengan Verification URL**

    ```php
    $qrCodeService->generateQRCode($verificationUrl, [
        'size' => 300,
        'margin' => 10,
        'format' => 'png'
    ]);
    // Save to storage/public/qrcodes/
    ```

    **Step 4: Embed QR Code ke PDF pada Posisi yang Dipilih**

    ```php
    $pdfService->embedQRCodeToPDF(
        $originalPdfPath,
        $qrCodeImagePath,
        $positioningData,
        $outputPath
    );
    ```

    **Step 5: Generate Unique Digital Signature Key**

    ```php
    $digitalSignature = $digitalSignatureService
        ->createDigitalSignatureForDocument($documentSignature);

    // Di dalam method ini:
    // - Generate RSA key pair (2048-bit)
    // - Generate X.509 certificate (self-signed, 3 years validity)
    // - Personalize certificate dengan info signer
    // - Save to digital_signatures table
    ```

    **Step 6: Create CMS Signature**

    ```php
    $signatureData = $digitalSignatureService
        ->createCMSSignature($finalPdfPath, $digitalSignature);

    // Di dalam method ini:
    // - Read final PDF content
    // - Generate SHA-256 hash dari dokumen
    // - Sign hash dengan private key (RSA-SHA256)
    // - Encode signature ke Base64 (CMS format)
    // - Extract certificate fingerprint
    // - Build comprehensive metadata
    ```

    **Step 7: Update DocumentSignature**

    ```php
    $documentSignature->update([
        'document_hash' => $signatureData['document_hash'],
        'signature_value' => $signatureData['signature_value'],
        'cms_signature' => $signatureData['cms_signature'],
        'signed_at' => now(),
        'signed_by' => $approvalRequest->approved_by,
        'signature_status' => 'verified',
        'signature_metadata' => $signatureData['metadata'],
        'final_pdf_path' => $finalPdfPath,
        'qr_code_path' => $qrCodeImagePath,
        'verification_url' => $verificationUrl
    ]);
    ```

    **Step 8: Update ApprovalRequest Status**

    ```php
    $approvalRequest->update([
        'status' => 'sign_approved',
        'user_signed_at' => now(),
        'sign_approved_at' => now(),
        'sign_approved_by' => $approvalRequest->approved_by,
        'signed_document_path' => $finalPdfPath
    ]);
    ```

    **Step 9: Clear Temporary QR Code**

    ```php
    $documentSignature->clearTemporaryQRCode();
    ```

    **Step 10: Create Audit Log**

    ```php
    SignatureAuditLog::create([
        'action' => 'document_signed',
        'status_from' => 'pending',
        'status_to' => 'verified',
        'description' => 'Document signed with unique key',
        // ... metadata lengkap
    ]);
    ```

7. **User Melihat Hasil**
    - User diarahkan ke halaman sukses
    - User dapat download dokumen yang sudah ditandatangani
    - User dapat melihat QR code untuk verifikasi

**Database Changes**:

-   Insert 1 row ke `digital_signatures` (kunci RSA + certificate)
-   Update 1 row di `document_signatures` (status: pending → verified)
-   Update 1 row di `approval_requests` (status: approved → sign_approved)
-   Insert 1 row ke `signature_audit_logs` (action: document_signed)
-   Delete temporary QR code file
-   Create final QR code file
-   Create final signed PDF file

**File Changes**:

-   `storage/public/temp-qrcodes/temp_qr_X.png` → DELETED
-   `storage/public/qrcodes/qr_X.png` → CREATED
-   `storage/public/signed_documents/signed_X.pdf` → CREATED

---

### Flow 4: Publik Memverifikasi Dokumen

**Aktor**: Siapa saja (tidak perlu login)

**Metode Verifikasi**:

1. **Scan QR Code** (paling umum)
2. **Input Token Manual**
3. **Input URL Verifikasi**
4. **Input Document ID** (jika tahu)

**Langkah-langkah**:

#### Metode 1: Scan QR Code

1. **User Scan QR Code**

    - User menggunakan smartphone atau QR scanner
    - QR code berisi URL verifikasi lengkap
    - Format: `https://domain.com/signature/verify/{token}`

2. **Browser Membuka URL**

    - URL otomatis terbuka di browser
    - Route: `GET /signature/verify/{token}`
    - Controller: `VerificationController@verifyByToken`

3. **Sistem Memproses Verifikasi**

    **Step 1: Rate Limiting Check**

    ```php
    // Max 10 attempts per 5 minutes per IP
    if (RateLimiter::tooManyAttempts($key, 10)) {
        return view('rate-limited');
    }
    ```

    **Step 2: Verify Token**

    ```php
    $verificationService->verifyByToken($token);

    // Di dalam method ini:
    // 1. Find DocumentSignature by verification_token
    // 2. Check signature_status (harus 'verified')
    // 3. Check DigitalSignature exists
    // 4. Check key status (harus 'active', tidak 'revoked')
    // 5. Check key validity (belum expired)
    // 6. Verify CMS signature (re-hash dokumen & verify dengan public key)
    // 7. Build verification result array
    ```

    **Step 3: Create Verification Log**

    ```php
    SignatureVerificationLog::create([
        'document_signature_id' => $docSig->id,
        'approval_request_id' => $docSig->approval_request_id,
        'user_id' => auth()->id(), // null jika anonymous
        'verification_method' => 'token',
        'verification_token_hash' => hash('sha256', $token),
        'is_valid' => $isValid,
        'result_status' => $isValid ? 'success' : 'failed',
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'referrer' => request()->header('Referer'),
        'metadata' => [
            'device_type' => 'mobile/desktop',
            'browser' => 'Chrome',
            'message' => $verificationMessage
        ],
        'verified_at' => now()
    ]);
    ```

4. **User Melihat Hasil Verifikasi**

    - View: `resources/views/digital-signature/verification/result.blade.php`
    - Informasi yang ditampilkan:

    **Jika Valid** ✅:

    ```
    ✅ Tanda Tangan Digital Valid

    Informasi Dokumen:
    - Nama Dokumen: [nama]
    - Nomor Dokumen: [nomor]
    - Ditandatangani Oleh: [nama kaprodi]
    - Tanggal Tanda Tangan: [tanggal]
    - Hash Dokumen: [hash] ✅ Cocok

    Informasi Signature:
    - Algorithm: RSA-SHA256
    - Key Length: 2048 bit
    - Status: Active ✅
    - Berlaku Hingga: [tanggal] (Sisa X hari)

    Tombol Aksi:
    - [Lihat Sertifikat] → Modal sertifikat X.509
    - [Lihat Dokumen] → Preview PDF
    - [Download Dokumen]
    ```

    **Jika Invalid** ❌:

    ```
    ❌ Tanda Tangan Digital Tidak Valid

    Alasan:
    - [Dokumen telah dimodifikasi]
    - [Kunci digital telah dicabut]
    - [Token verifikasi tidak valid]
    - [Signature sudah expired]

    Peringatan Keamanan:
    ⚠️ Dokumen ini mungkin telah diubah atau dipalsukan.
    Jangan percaya keaslian dokumen ini.
    ```

5. **User Melihat Sertifikat Digital** (Opsional)

    - User klik tombol "Lihat Sertifikat"
    - JavaScript fetch AJAX request
    - Route: `GET /signature/certificate/view/{token}`
    - Controller: `VerificationController@viewPublicCertificate`

6. **Sistem Menampilkan Sertifikat (Public Safe Info)**

    ```php
    // Parse certificate with security masking
    $certInfo = $this->parsePublicCertificateInfo($certificate, $digitalSignature);

    // Return JSON dengan informasi SAFE:
    return json([
        'version' => 'v3',
        'serial_number' => '****12345678', // Masked
        'subject' => [
            'CN' => 'Dr. Ahmad Wijaya',
            'OU' => 'Program Studi Teknik Informatika',
            'O' => 'Universitas Muhammadiyah Tangerang',
            'C' => 'ID'
            // NO EMAIL - Privacy
        ],
        'issuer' => [...],
        'valid_from' => '01 January 2025',
        'valid_until' => '01 January 2028',
        'days_remaining' => 1095,
        'fingerprint_sha256' => 'AA:BB:CC:DD:**:**:**:...:WW:XX:YY:ZZ', // Masked
        'is_self_signed' => true,
        'status' => 'active'
        // NO private key
        // NO full serial number
        // NO IP addresses
        // NO metadata
    ]);
    ```

7. **Modal Sertifikat Ditampilkan**
    - JavaScript menampilkan modal Bootstrap
    - Informasi sertifikat ditampilkan dalam format yang mudah dibaca
    - User dapat melihat:
        - Subject (pemilik sertifikat)
        - Issuer (penerbit sertifikat)
        - Validity period
        - Fingerprint (masked)
        - Status

#### Metode 2: Input Manual

1. **User Membuka Halaman Verifikasi**

    - Route: `GET /signature/verify`
    - Controller: `VerificationController@verificationPage`

2. **User Input Token/URL/ID**

    - Form input dengan dropdown:
        - Token (64 karakter)
        - URL (full verification URL)
        - QR Code Data
        - Document ID (numeric)

3. **User Submit Form**

    - Route: `POST /signature/verify`
    - Controller: `VerificationController@verifyPublic`
    - Rate limit: 5 attempts per 5 minutes per IP

4. **Sistem Extract Token dari Input**

    ```php
    switch ($type) {
        case 'token':
            $token = $input;
            break;
        case 'url':
            $token = extractTokenFromUrl($input);
            break;
        case 'id':
            $token = findTokenById($input);
            break;
    }
    ```

5. **Proses Verifikasi Sama dengan Metode 1**
    - Verify token → Create log → Show result

**Database Changes per Verification**:

-   Insert 1 row ke `signature_verification_logs`

**Performance**:

-   Average response time: 200-500ms
-   Rate limiting mencegah abuse
-   Caching dapat diimplementasikan untuk token yang sering diakses

---

## 🔧 Alur Sistem (System Flow)

### System Flow 1: Key Generation dan Certificate Creation

**Trigger**: Saat user submit QR positioning untuk signing

**Service**: `DigitalSignatureService::generateKeyPair()`

**Detail Proses**:

```php
public function generateKeyPair($keyLength = 2048, $algorithm = 'RSA-SHA256',
                                $validityYears = 3, $signerInfo = null)
{
    // STEP 1: Generate RSA Private Key (2048-bit)
    $config = [
        "digest_alg" => "sha256",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
        "encrypt_key" => false
    ];

    $privateKey = openssl_pkey_new($config);
    // Resource: OpenSSLAsymmetricKey object

    // STEP 2: Extract Public Key dari Private Key
    $publicKeyDetails = openssl_pkey_get_details($privateKey);
    /*
    $publicKeyDetails = [
        'bits' => 2048,
        'key' => '-----BEGIN PUBLIC KEY-----
                  MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A...
                  -----END PUBLIC KEY-----',
        'rsa' => [...],
        'type' => OPENSSL_KEYTYPE_RSA
    ]
    */

    // STEP 3: Export Private Key ke PEM format
    openssl_pkey_export($privateKey, $privateKeyPem);
    /*
    $privateKeyPem = '-----BEGIN PRIVATE KEY-----
                       MIIEvQIBADANBgkqhkiG9w0BAQEFA...
                       -----END PRIVATE KEY-----'
    */

    // STEP 4: Generate X.509 Certificate (Self-Signed)
    $certificate = $this->generateSelfSignedCertificate(
        $privateKey,
        $publicKeyDetails,
        $validityYears = 3,
        $signerInfo = [
            'name' => 'Dr. Ahmad Wijaya',
            'email' => 'ahmad@umt.ac.id',
            'role' => 'Kepala Program Studi',
            'document_name' => 'Surat Keterangan Mahasiswa Aktif'
        ]
    );

    // STEP 5: Generate Public Key Fingerprint (untuk identification)
    $fingerprint = hash('sha256', $publicKeyDetails['key']);

    // STEP 6: Return Key Pair + Certificate
    return [
        'private_key' => $privateKeyPem,
        'public_key' => $publicKeyDetails['key'],
        'key_length' => 2048,
        'algorithm' => 'RSA-SHA256',
        'certificate' => $certificate, // X.509 PEM format
        'fingerprint' => $fingerprint  // SHA-256 hash
    ];
}
```

**Certificate Generation Detail**:

```php
private function generateSelfSignedCertificate(OpenSSLAsymmetricKey $privateKey,
                                               $publicKeyDetails,
                                               $validityYears = 3,
                                               $signerInfo = null)
{
    // STEP 1: Build Distinguished Name (DN) dengan Personalisasi
    $dn = [
        "countryName" => "ID",
        "stateOrProvinceName" => "Banten",
        "localityName" => "Tangerang",
        "organizationName" => "Universitas Muhammadiyah Tangerang",
        "organizationalUnitName" => "Fakultas Teknik - Program Studi Teknik Informatika",
        "commonName" => $signerInfo['name'] ?? "Digital Signature System UMT",
        "emailAddress" => $signerInfo['email'] ?? "informatika@umt.ac.id"
    ];

    // STEP 2: Create Certificate Signing Request (CSR)
    $configArgs = [
        "digest_alg" => "sha256",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA
    ];

    $csr = openssl_csr_new($dn, $privateKey, $configArgs);
    /*
    CSR (Certificate Signing Request) = permintaan pembuatan sertifikat
    Berisi: DN (Distinguished Name) + Public Key
    */

    // STEP 3: Self-Sign CSR (subject = issuer)
    $validityDays = $validityYears * 365; // 3 years = 1095 days

    $cert = openssl_csr_sign(
        $csr,              // CSR yang dibuat
        null,              // NULL = self-signed (tidak ada CA)
        $privateKey,       // Private key untuk signing
        $validityDays,     // 1095 days (3 years)
        $configArgs,       // Config
        time()             // Serial number (timestamp)
    );

    // STEP 4: Validate Certificate
    $certResource = openssl_x509_read($cert);
    if (!$certResource) {
        throw new Exception('Certificate generation failed');
    }

    // STEP 5: Export Certificate ke PEM format
    openssl_x509_export($certResource, $certPem);
    /*
    $certPem = '-----BEGIN CERTIFICATE-----
                MIIDXTCCAkWgAwIBAgIJAKL0UG+mRzQhMA0GCS...
                -----END CERTIFICATE-----'
    */

    // STEP 6: Validate Exported Certificate
    $parsedCert = openssl_x509_parse($certPem);
    /*
    $parsedCert = [
        'name' => '/C=ID/ST=Banten/L=Tangerang/O=UMT/...',
        'subject' => [
            'C' => 'ID',
            'ST' => 'Banten',
            'L' => 'Tangerang',
            'O' => 'Universitas Muhammadiyah Tangerang',
            'OU' => 'Fakultas Teknik - Program Studi Teknik Informatika',
            'CN' => 'Dr. Ahmad Wijaya',
            'emailAddress' => 'ahmad@umt.ac.id'
        ],
        'issuer' => [...], // Same as subject (self-signed)
        'version' => 2, // X.509 v3
        'serialNumber' => '1234567890',
        'validFrom' => 'Jan  1 00:00:00 2025 GMT',
        'validTo' => 'Jan  1 00:00:00 2028 GMT',
        'validFrom_time_t' => 1704067200,
        'validTo_time_t' => 1861920000,
        'purposes' => [...],
        'extensions' => [...],
        'signatureTypeSN' => 'RSA-SHA256',
        'signatureTypeLN' => 'sha256WithRSAEncryption'
    ]
    */

    return $certPem; // Return X.509 certificate dalam PEM format
}
```

**Karakteristik Certificate**:

1. **Self-Signed**: Subject = Issuer (internal PKI)
2. **Validity**: 3 tahun (1095 hari)
3. **Algorithm**: RSA-SHA256
4. **Key Length**: 2048 bit
5. **Version**: X.509 v3
6. **Personalized**: DN menggunakan nama signer sebenarnya
7. **Serial Number**: Unique timestamp-based

**Security Notes**:

-   Private key TIDAK PERNAH dikirim ke client
-   Private key di-encrypt menggunakan Laravel encryption (APP_KEY) saat disimpan
-   Certificate adalah public information (aman untuk ditampilkan)
-   Fingerprint SHA-256 untuk quick identification

---

### System Flow 2: Document Signing dengan CMS Signature

**Trigger**: Setelah key generation selesai

**Service**: `DigitalSignatureService::createCMSSignature()`

**Detail Proses**:

```php
public function createCMSSignature($documentPath, $digitalSignature)
{
    // STEP 1: Read Document Content
    $documentContent = file_get_contents($documentPath);
    // $documentContent = binary data dari PDF (contoh: 250KB)

    // STEP 2: Generate Document Hash (SHA-256)
    $documentHash = hash('sha256', $documentContent);
    /*
    $documentHash = 'a3f5e8c9b2d4f6e8a1c3b5d7e9f1a3b5c7d9e1f3a5b7c9d1e3f5a7b9c1d3e5f7'
    64 karakter hexadecimal
    */

    // STEP 3: Get Private Key (Decrypted)
    $privateKey = $digitalSignature->private_key;
    // Auto-decrypted oleh model accessor

    // STEP 4: Sign Document Hash dengan Private Key
    $signature = '';
    $signResult = openssl_sign(
        $documentHash,           // Data yang akan di-sign (hash)
        $signature,              // Output signature (by reference)
        $privateKey,             // Private key untuk signing
        OPENSSL_ALGO_SHA256      // Algorithm
    );

    /*
    Proses signing:
    1. Hash document content → SHA-256 (32 bytes)
    2. Encrypt hash dengan private key (RSA 2048-bit)
    3. Result: binary signature (~256 bytes)

    Signature ini HANYA bisa di-decrypt dengan public key yang sesuai!
    Ini adalah bukti bahwa dokumen di-sign dengan private key tertentu.
    */

    if (!$signResult) {
        throw new Exception('Signing failed: ' . openssl_error_string());
    }

    // STEP 5: Encode Signature ke Base64 (CMS format)
    $cmsSignature = base64_encode($signature);
    /*
    $cmsSignature = 'VGhpcyBpcyBhIGJhc2U2NCBlbmNvZGVkIHNpZ25hdHVyZQ=='
    Base64 string (~344 karakter untuk 256 bytes)
    */

    // STEP 6: Generate Certificate Fingerprint
    $certificateFingerprint = null;
    if ($digitalSignature->certificate) {
        $certificateFingerprint = openssl_x509_fingerprint(
            $digitalSignature->certificate,
            'sha256'
        );
        /*
        $certificateFingerprint =
            'A1B2C3D4E5F6A7B8C9D0E1F2A3B4C5D6E7F8A9B0C1D2E3F4A5B6C7D8E9F0A1B2'
        64 karakter hexadecimal (SHA-256 hash dari certificate)
        */
    }

    // STEP 7: Parse Certificate untuk Metadata
    $certInfo = null;
    if ($digitalSignature->certificate) {
        $certData = openssl_x509_parse($digitalSignature->certificate);
        $certInfo = [
            'subject_cn' => $certData['subject']['CN'] ?? 'N/A',
            'subject_email' => $certData['subject']['emailAddress'] ?? 'N/A',
            'issuer_cn' => $certData['issuer']['CN'] ?? 'N/A',
            'valid_from' => date('Y-m-d H:i:s', $certData['validFrom_time_t']),
            'valid_until' => date('Y-m-d H:i:s', $certData['validTo_time_t']),
            'serial_number' => $certData['serialNumber'] ?? 'N/A'
        ];
    }

    // STEP 8: Build Comprehensive Metadata
    return [
        'document_hash' => $documentHash,
        'cms_signature' => $cmsSignature,
        'signature_value' => hash('sha256', $signature), // Hash dari signature
        'algorithm' => 'RSA-SHA256',
        'signed_at' => now(),
        'metadata' => [
            // Document info
            'document_size' => strlen($documentContent),
            'document_size_mb' => round(strlen($documentContent) / 1024 / 1024, 2),
            'document_hash_algorithm' => 'SHA-256',

            // Signature info
            'signature_id' => $digitalSignature->signature_id,
            'signature_algorithm' => 'RSA-SHA256',
            'key_length' => 2048,

            // Certificate info
            'certificate_fingerprint' => $certificateFingerprint,
            'certificate_info' => $certInfo,
            'certificate_status' => 'valid',

            // Signing context
            'signing_ip' => request()->ip(),
            'signing_user_agent' => request()->userAgent(),
            'signing_location' => 'Tangerang, Banten, Indonesia',
            'signing_reason' => 'Document Approval and Authentication',
            'signing_timestamp' => now()->toIso8601String(),

            // System info
            'platform' => 'DiSign - Digital Signature System UMT',
            'version' => '2.0',
            'compliance' => 'X.509 v3, CMS Signature'
        ]
    ];
}
```

**CMS (Cryptographic Message Syntax) Explained**:

CMS adalah standar RFC 5652 untuk signing dan encrypting data. Dalam sistem ini:

1. **Document Hash**: SHA-256 hash dari file PDF (32 bytes)
2. **Signature**: Document hash di-encrypt dengan private key RSA (256 bytes)
3. **Base64 Encoding**: Signature binary di-encode ke base64 untuk storage (string)

**Verification Process** (akan dibahas di flow berikutnya):

1. Re-hash dokumen yang akan diverifikasi
2. Decrypt signature dengan public key
3. Bandingkan hasil decrypt dengan hash dokumen
4. Jika cocok → Valid ✅, jika tidak → Invalid ❌

**Security Properties**:

-   **Integrity**: Dokumen tidak bisa diubah tanpa terdeteksi
-   **Authenticity**: Signature hanya bisa dibuat dengan private key tertentu
-   **Non-repudiation**: Signer tidak bisa menyangkal telah menandatangani
-   **Confidentiality**: Private key tidak pernah terekspos

---

### System Flow 3: Document Verification (Public)

**Trigger**: User scan QR code atau input token manual

**Service**: `VerificationService::verifyByToken()`

**Detail Proses**:

```php
public function verifyByToken($token)
{
    // STEP 1: Find DocumentSignature by Verification Token
    $documentSignature = DocumentSignature::where('verification_token', $token)
        ->with(['digitalSignature', 'approvalRequest.user', 'signer'])
        ->first();

    if (!$documentSignature) {
        return [
            'is_valid' => false,
            'message' => 'Token verifikasi tidak ditemukan',
            'reason' => 'not_found'
        ];
    }

    // STEP 2: Check Signature Status
    if ($documentSignature->signature_status !== 'verified') {
        return [
            'is_valid' => false,
            'message' => 'Dokumen belum ditandatangani atau sudah dibatalkan',
            'reason' => 'invalid_status',
            'details' => [
                'current_status' => $documentSignature->signature_status
            ]
        ];
    }

    // STEP 3: Check Digital Signature Exists
    $digitalSignature = $documentSignature->digitalSignature;

    if (!$digitalSignature) {
        return [
            'is_valid' => false,
            'message' => 'Kunci digital tidak ditemukan',
            'reason' => 'key_not_found'
        ];
    }

    // STEP 4: Check Key Status (Revoked?)
    if ($digitalSignature->status === 'revoked') {
        return [
            'is_valid' => false,
            'message' => 'Kunci digital telah dicabut',
            'reason' => 'key_revoked',
            'details' => [
                'revoked_at' => $digitalSignature->revoked_at,
                'revocation_reason' => $digitalSignature->revocation_reason
            ]
        ];
    }

    // STEP 5: Check Key Validity (Expired?)
    if (!$digitalSignature->isValid()) {
        $isExpired = $digitalSignature->valid_until < now();

        return [
            'is_valid' => false,
            'message' => $isExpired ?
                'Kunci digital sudah expired' :
                'Kunci digital belum berlaku',
            'reason' => $isExpired ? 'key_expired' : 'key_not_yet_valid',
            'details' => [
                'valid_from' => $digitalSignature->valid_from,
                'valid_until' => $digitalSignature->valid_until
            ]
        ];
    }

    // STEP 6: Verify CMS Signature (Re-hash & Verify)
    $verificationResult = $this->verifyCMSSignature(
        $documentSignature,
        $digitalSignature
    );

    if (!$verificationResult['is_valid']) {
        return [
            'is_valid' => false,
            'message' => 'Signature verification failed: Dokumen mungkin telah dimodifikasi',
            'reason' => 'signature_mismatch',
            'details' => $verificationResult
        ];
    }

    // STEP 7: All Checks Passed ✅
    return [
        'is_valid' => true,
        'message' => 'Tanda tangan digital valid dan dokumen belum dimodifikasi',
        'verification_id' => $documentSignature->id,
        'verified_at' => now()->toISOString(),
        'details' => [
            'document_signature' => $documentSignature,
            'digital_signature' => $digitalSignature,
            'approval_request' => $documentSignature->approvalRequest,
            'signer' => $documentSignature->signer,
            'checks' => [
                'token_valid' => true,
                'signature_status' => 'verified',
                'key_status' => 'active',
                'key_validity' => 'valid',
                'cms_signature' => 'valid',
                'document_integrity' => 'intact'
            ]
        ]
    ];
}
```

**CMS Signature Verification Detail**:

```php
private function verifyCMSSignature($documentSignature, $digitalSignature)
{
    // STEP 1: Get Document Path
    $documentPath = $documentSignature->final_pdf_path;

    // STEP 2: Read Document Content
    if (file_exists($documentPath)) {
        // Absolute path
        $documentContent = file_get_contents($documentPath);
    } else {
        // Relative path dari storage
        $documentContent = Storage::disk('public')->get($documentPath);
    }

    if (!$documentContent) {
        throw new Exception('Cannot read document for verification');
    }

    // STEP 3: Re-Generate Document Hash
    $currentDocumentHash = hash('sha256', $documentContent);
    /*
    Hash dokumen SAAT INI (yang akan diverifikasi)
    */

    // STEP 4: Get Stored Document Hash
    $storedDocumentHash = $documentSignature->document_hash;
    /*
    Hash dokumen SAAT SIGNING (tersimpan di database)
    */

    // STEP 5: Compare Hashes (Document Integrity Check)
    if ($currentDocumentHash !== $storedDocumentHash) {
        // Dokumen telah dimodifikasi! Hash tidak cocok!
        return [
            'is_valid' => false,
            'reason' => 'document_modified',
            'message' => 'Dokumen telah dimodifikasi setelah ditandatangani',
            'details' => [
                'stored_hash' => $storedDocumentHash,
                'current_hash' => $currentDocumentHash,
                'match' => false
            ]
        ];
    }

    // STEP 6: Decode CMS Signature dari Base64
    $cmsSignature = $documentSignature->cms_signature;
    $signature = base64_decode($cmsSignature);

    if (!$signature) {
        return [
            'is_valid' => false,
            'reason' => 'invalid_signature_format',
            'message' => 'Format signature tidak valid'
        ];
    }

    // STEP 7: Verify Signature dengan Public Key
    $publicKey = $digitalSignature->public_key;

    $verifyResult = openssl_verify(
        $storedDocumentHash,    // Data yang di-verify (hash dokumen)
        $signature,             // Signature yang akan di-verify
        $publicKey,             // Public key untuk verification
        OPENSSL_ALGO_SHA256     // Algorithm
    );

    /*
    openssl_verify() Process:
    1. Decrypt signature dengan public key
       → Hasil: decrypted hash (32 bytes)

    2. Bandingkan decrypted hash dengan $storedDocumentHash

    3. Return:
       - 1 (integer) → Signature VALID ✅
       - 0 (integer) → Signature INVALID ❌
       - -1 (integer) → Error

    Kenapa ini secure?
    - Hanya private key yang sesuai yang bisa membuat signature
      yang bisa di-decrypt dengan public key tertentu
    - Jika signature di-tamper, verification akan gagal
    - Jika dokumen di-tamper, hash tidak akan cocok
    */

    // STEP 8: Evaluate Verification Result
    if ($verifyResult === 1) {
        // Signature VALID ✅
        return [
            'is_valid' => true,
            'message' => 'Signature verified successfully',
            'document_hash' => $storedDocumentHash,
            'hash_match' => true,
            'signature_algorithm' => 'RSA-SHA256',
            'verified_at' => now(),
            'signature_status' => $digitalSignature->status,
            'certificate_valid' => $digitalSignature->isValid()
        ];
    }
    elseif ($verifyResult === 0) {
        // Signature INVALID ❌
        return [
            'is_valid' => false,
            'reason' => 'signature_verification_failed',
            'message' => 'Signature tidak cocok dengan public key',
            'details' => [
                'openssl_error' => openssl_error_string()
            ]
        ];
    }
    else {
        // Error
        return [
            'is_valid' => false,
            'reason' => 'verification_error',
            'message' => 'Terjadi error saat verifikasi',
            'details' => [
                'openssl_error' => openssl_error_string()
            ]
        ];
    }
}
```

**Verification Checks Summary**:

| Check                  | Apa yang Dicek                          | Tujuan                                      |
| ---------------------- | --------------------------------------- | ------------------------------------------- |
| 1. Token Valid         | Token ada di database                   | Mencegah token palsu                        |
| 2. Signature Status    | Status = 'verified'                     | Mencegah verifikasi dokumen pending/invalid |
| 3. Key Exists          | Digital signature ada                   | Mencegah orphaned document signature        |
| 4. Key Not Revoked     | Status ≠ 'revoked'                      | Mencegah verifikasi setelah key dicabut     |
| 5. Key Valid Period    | now() antara valid_from dan valid_until | Mencegah verifikasi expired key             |
| 6. Document Hash Match | Current hash = Stored hash              | Deteksi modifikasi dokumen                  |
| 7. Signature Verify    | openssl_verify() = 1                    | Verifikasi signature dengan public key      |

**Jika SEMUA check passed** → ✅ Valid
**Jika SALAH SATU gagal** → ❌ Invalid

---

### System Flow 4: QR Code Embedding ke PDF

**Trigger**: Setelah user submit QR positioning

**Service**: `PDFService::embedQRCodeToPDF()` (custom implementation)

**Detail Proses**:

```php
public function embedQRCodeToPDF($originalPdfPath, $qrCodeImagePath,
                                  $positioningData, $outputPath)
{
    // STEP 1: Load PDF menggunakan TCPDF
    $pdf = new TCPDF();
    $pdf->setSourceFile($originalPdfPath);

    // STEP 2: Get Total Pages
    $totalPages = $pdf->setSourceFile($originalPdfPath);

    // STEP 3: Loop Through Each Page
    for ($pageNo = 1; $pageNo <= $totalPages; $pageNo++) {
        // Import page dari original PDF
        $tplIdx = $pdf->importPage($pageNo);

        // Add page ke new PDF
        $pdf->AddPage();

        // Use imported page as template
        $pdf->useTemplate($tplIdx);

        // STEP 4: Check jika QR Code harus di-embed di page ini
        if ($positioningData['page'] == $pageNo) {
            // STEP 5: Calculate Position dalam unit PDF (mm)
            $x = $positioningData['x'] * $positioningData['scale'];
            $y = $positioningData['y'] * $positioningData['scale'];
            $width = $positioningData['width'] ?? 30; // mm
            $height = $positioningData['height'] ?? 30; // mm

            // STEP 6: Embed QR Code Image
            $pdf->Image(
                $qrCodeImagePath,    // Path ke QR code PNG
                $x,                   // X position (mm dari kiri)
                $y,                   // Y position (mm dari atas)
                $width,               // Width (mm)
                $height,              // Height (mm)
                'PNG',                // Format
                '',                   // Link (kosong)
                '',                   // Align
                false,                // Resize
                300,                  // DPI
                '',                   // Palign
                false,                // Ismask
                false,                // Imgmask
                0,                    // Border
                false,                // Fitbox
                false,                // Hidden
                false                 // Fitonpage
            );
        }
    }

    // STEP 7: Save New PDF
    $pdf->Output($outputPath, 'F'); // 'F' = save to file

    return $outputPath;
}
```

**Positioning Data Structure**:

```json
{
    "x": 150, // Pixels dari kiri (dalam canvas koordinat)
    "y": 200, // Pixels dari atas (dalam canvas koordinat)
    "page": 1, // Nomor halaman (1-based)
    "scale": 0.264583, // Scale factor (pixels to mm: 1px ≈ 0.264583mm untuk 96 DPI)
    "width": 30, // Width dalam mm
    "height": 30 // Height dalam mm
}
```

**Conversion Formula**:

```
PDF coordinates (mm) = Canvas coordinates (pixels) × Scale factor
Scale factor = 25.4 mm/inch ÷ DPI
             = 25.4 ÷ 96
             ≈ 0.264583

Contoh:
Canvas: x=150px, y=200px
PDF: x=39.69mm, y=52.92mm
```

---

## 🔒 Keamanan dan Privasi

### 1. Private Key Protection

**Encryption**:

-   Private key di-encrypt menggunakan Laravel encryption (AES-256-CBC)
-   Key encryption: `APP_KEY` dari `.env` (32 karakter random)
-   Mutator otomatis encrypt saat save, decrypt saat load

```php
// Model: DigitalSignature
public function setPrivateKeyAttribute($value)
{
    $this->attributes['private_key'] = encrypt($value);
}

public function getPrivateKeyAttribute($value)
{
    return decrypt($value);
}
```

**Never Exposed**:

-   Hidden dari JSON response
-   Tidak pernah dikirim ke client
-   Hanya diakses di server-side untuk signing

---

### 2. Public Certificate Viewer Security

**Untuk Verifikasi Publik** (VerificationController@viewPublicCertificate):

**Data yang Ditampilkan** ✅:

-   Subject CN, OU, O, C (tanpa email)
-   Issuer CN, OU, O, C
-   Validity period
-   Public key algorithm
-   Signature algorithm
-   Fingerprint (masked)
-   Status (active/revoked)

**Data yang Di-HIDE** ❌:

-   ❌ Email addresses (privacy)
-   ❌ Full serial number (hanya show last 8 digits)
-   ❌ Full fingerprint (masked middle part)
-   ❌ IP addresses
-   ❌ Metadata (signing context, user agent, dll)
-   ❌ Private key (obviously)

**Masking Implementation**:

```php
// Serial number masking
'serial_number' => '****' . substr($certData['serialNumber'], -8)

// Fingerprint masking (show first 4 pairs + last 4 pairs)
// Input:  A1:B2:C3:D4:E5:F6:A7:B8:C9:D0:E1:F2:A3:B4:C5:D6:E7:F8:A9:B0:C1:D2:E3:F4:A5:B6:C7:D8:E9:F0:A1:B2
// Output: A1:B2:C3:D4:**:**:**:**:**:**:**:**:**:**:**:**:**:**:**:**:**:**:**:**:**:**:**:**:A1:B2:E9:F0
private function maskFingerprint($fingerprint)
{
    $parts = explode(':', $formatted);
    $totalParts = count($parts);

    $visible = [];
    for ($i = 0; $i < $totalParts; $i++) {
        if ($i < 4 || $i >= $totalParts - 4) {
            $visible[] = $parts[$i]; // Show
        } else {
            $visible[] = '**'; // Hide
        }
    }

    return implode(':', $visible);
}
```

---

### 3. Rate Limiting

**Public Verification Endpoints**:

```php
// Per IP address rate limiting
Route::get('signature/verify/{token}', ...)
    ->middleware('throttle:verification');

// Custom rate limiter config
RateLimiter::for('verification', function (Request $request) {
    return Limit::perMinute(10)->by($request->ip());
});
```

**Rate Limit Details**:

| Endpoint                          | Max Attempts | Time Window | Decay      |
| --------------------------------- | ------------ | ----------- | ---------- |
| `/signature/verify/{token}` (GET) | 10           | per IP      | 5 minutes  |
| `/signature/verify` (POST form)   | 5            | per IP      | 5 minutes  |
| `/api/signature/verify`           | 20           | per IP      | 5 minutes  |
| `/api/signature/bulk-verify`      | 3            | per IP      | 10 minutes |

**Implementation**:

```php
// In controller
$key = 'verify_token:' . request()->ip();

if (RateLimiter::tooManyAttempts($key, 10)) {
    $seconds = RateLimiter::availableIn($key);
    return view('rate-limited', compact('seconds'));
}

RateLimiter::hit($key, 300); // 300 seconds = 5 minutes
```

**Response saat Rate Limit Exceeded**:

```
HTTP 429 Too Many Requests

{
    "error": "Rate limit exceeded",
    "retry_after": 180, // seconds
    "message": "Too many verification attempts. Please try again in 3 minutes."
}
```

---

### 4. Token Hashing dalam Logs

**Privacy Protection**:

-   Verification token (64 char) tidak disimpan plain dalam logs
-   Token di-hash menggunakan SHA-256 sebelum log

```php
SignatureVerificationLog::create([
    'verification_token_hash' => hash('sha256', $token), // Hashed
    // ... other fields
]);
```

**Tujuan**:

-   Mencegah token leakage jika database compromised
-   Token tetap bisa di-track untuk audit (via hash)
-   Impossible untuk reverse-engineer token dari hash

---

### 5. Database Transaction untuk Atomicity

**Signing Process** menggunakan DB transaction:

```php
DB::beginTransaction();

try {
    // Step 1: Generate key
    $digitalSignature = $this->createDigitalSignatureForDocument(...);

    // Step 2: Create CMS signature
    $signatureData = $this->createCMSSignature(...);

    // Step 3: Update DocumentSignature
    $documentSignature->update([...]);

    // Step 4: Update ApprovalRequest
    $approvalRequest->update([...]);

    // Step 5: Create audit log
    SignatureAuditLog::create([...]);

    // All success → Commit
    DB::commit();

} catch (\Exception $e) {
    // Any error → Rollback ALL changes
    DB::rollBack();
    throw $e;
}
```

**Benefit**:

-   Jika ada error di tengah proses, semua perubahan di-rollback
-   Tidak ada orphaned records
-   Data consistency terjaga

---

### 6. Audit Trail Lengkap

**Semua Aktivitas Tercatat**:

1. **SignatureAuditLog** (Internal Activities):

    - Signature key generated
    - Document signed
    - Key revoked
    - Template created/updated
    - Approval/rejection

2. **SignatureVerificationLog** (Public Verifications):
    - Verification attempts (success/failed)
    - IP address
    - User agent
    - Device type
    - Browser
    - Referrer
    - Timestamp

**Metadata Standardized**:

```php
SignatureAuditLog::createMetadata([
    'timestamp' => now()->timestamp,
    'session_id' => session()->getId(),
    'device_type' => 'desktop/mobile/tablet',
    'browser' => 'Chrome/Firefox/Safari',
    'platform' => 'Windows/macOS/Linux',
    // ... custom data
]);
```

---

## 📡 API Endpoints

### Public Endpoints (No Authentication)

#### 1. Verify by Token (GET)

**URL**: `GET /signature/verify/{token}`

**Deskripsi**: Verifikasi dokumen melalui token (dari QR code)

**Parameters**:

-   `token` (path): Verification token (64 characters)

**Rate Limit**: 10 requests per 5 minutes per IP

**Response Success** (200):

```json
{
    "is_valid": true,
    "message": "Tanda tangan digital valid",
    "verification_id": 123,
    "verified_at": "2025-01-01T10:00:00Z",
    "details": {
        "document": {
            "name": "Surat Keterangan Mahasiswa Aktif",
            "number": "001/III.3.AU/KEP-FT/I/2025",
            "hash": "a3f5e8c9...",
            "hash_match": true
        },
        "signature": {
            "signed_by": "Dr. Ahmad Wijaya",
            "signed_at": "2025-01-01T09:00:00Z",
            "algorithm": "RSA-SHA256",
            "key_length": 2048,
            "status": "verified"
        },
        "certificate": {
            "status": "active",
            "valid_from": "2025-01-01",
            "valid_until": "2028-01-01",
            "days_remaining": 1095
        },
        "checks": {
            "token_valid": true,
            "signature_status": "verified",
            "key_status": "active",
            "key_validity": "valid",
            "cms_signature": "valid",
            "document_integrity": "intact"
        }
    }
}
```

**Response Invalid** (200):

```json
{
    "is_valid": false,
    "message": "Dokumen telah dimodifikasi",
    "reason": "document_modified",
    "details": {
        "stored_hash": "a3f5e8c9...",
        "current_hash": "b4g6f9d0...",
        "match": false
    }
}
```

**Response Not Found** (404):

```json
{
    "is_valid": false,
    "message": "Token verifikasi tidak ditemukan",
    "reason": "not_found"
}
```

**Response Rate Limited** (429):

```json
{
    "error": "Rate limit exceeded",
    "retry_after": 180,
    "message": "Too many attempts"
}
```

---

#### 2. Verify Public (POST Form)

**URL**: `POST /signature/verify`

**Deskripsi**: Verifikasi dokumen melalui form input

**Parameters** (Form Data):

-   `verification_input` (required, string): Token/URL/ID
-   `verification_type` (required, enum): token|url|qr|id

**Rate Limit**: 5 requests per 5 minutes per IP

**Request Example**:

```json
{
    "verification_input": "a1b2c3d4e5f6...",
    "verification_type": "token"
}
```

**Response**: Same as GET /signature/verify/{token}

---

#### 3. View Public Certificate (AJAX)

**URL**: `GET /signature/certificate/view/{token}`

**Deskripsi**: Lihat informasi sertifikat X.509 (public safe info only)

**Parameters**:

-   `token` (path): Verification token

**Headers**:

-   `Accept: application/json`
-   `X-Requested-With: XMLHttpRequest`

**Response Success** (200):

```json
{
    "success": true,
    "certificate": {
        "version": 3,
        "serial_number": "****12345678",
        "subject": {
            "CN": "Dr. Ahmad Wijaya",
            "OU": "Program Studi Teknik Informatika",
            "O": "Universitas Muhammadiyah Tangerang",
            "L": "Tangerang",
            "ST": "Banten",
            "C": "ID"
        },
        "issuer": {
            "CN": "Dr. Ahmad Wijaya",
            "OU": "Program Studi Teknik Informatika",
            "O": "Universitas Muhammadiyah Tangerang",
            "C": "ID"
        },
        "valid_from": "01 January 2025 00:00:00",
        "valid_until": "01 January 2028 00:00:00",
        "days_remaining": 1095,
        "is_expired": false,
        "is_expiring_soon": false,
        "public_key_algorithm": "RSA (2048 bit)",
        "signature_algorithm": "sha256WithRSAEncryption",
        "fingerprint_sha256": "A1:B2:C3:D4:**:**:**:**:**:**:X5:Y6:Z7:A8",
        "fingerprint_partial": "A1B2C3D4E5F6A7B8...XY Z7A8B9C0D1E2F3A4",
        "is_self_signed": true,
        "status": "active",
        "is_revoked": false
    }
}
```

**Response Failed** (404):

```json
{
    "success": false,
    "message": "Sertifikat digital tidak ditemukan"
}
```

---

#### 4. API Verify (JSON)

**URL**: `GET /api/signature/verify/{token}`

**Deskripsi**: API endpoint untuk verifikasi (untuk integrasi external)

**Rate Limit**: 20 requests per 5 minutes per IP

**Response** (200):

```json
{
    "success": true,
    "verification": {
        "is_valid": true,
        "message": "Signature valid",
        "verified_at": "2025-01-01T10:00:00Z",
        "verification_id": 123,
        "document_info": {
            "name": "Surat Keterangan",
            "number": "001/...",
            "submitted_by": "John Doe",
            "submitted_at": "2025-01-01T08:00:00Z",
            "status": "sign_approved"
        },
        "signature_info": {
            "algorithm": "RSA-SHA256",
            "key_length": 2048,
            "signed_at": "2025-01-01T09:00:00Z",
            "signed_by": "Dr. Ahmad Wijaya",
            "status": "verified",
            "verification_checks": {
                "document_integrity": true,
                "signature_validity": true,
                "key_status": "active"
            }
        }
    }
}
```

---

#### 5. Bulk Verify API

**URL**: `POST /api/signature/bulk-verify`

**Deskripsi**: Bulk verification untuk external systems

**Authentication**: Requires API Key

**Rate Limit**: 3 requests per 10 minutes per IP

**Request**:

```json
{
    "tokens": ["token1_64chars", "token2_64chars", "token3_64chars"],
    "api_key": "your-api-key-here"
}
```

**Validation**:

-   `tokens`: max 10 items
-   `api_key`: required, must match `config('app.verification_api_key')`

**Response** (200):

```json
{
    "success": true,
    "summary": {
        "total": 3,
        "verified": 2,
        "failed": 1
    },
    "results": [
        {
            "token_hash": "sha256_hash",
            "is_valid": true,
            "message": "Valid",
            "verified_at": "2025-01-01T10:00:00Z",
            "document_info": {...}
        },
        {
            "token_hash": "sha256_hash",
            "is_valid": false,
            "message": "Invalid",
            "error": "Key revoked"
        }
    ]
}
```

---

### Protected Endpoints (Require Authentication)

#### 6. Kaprodi: Approval Requests List

**URL**: `GET /admin/signature/approval-requests`

**Auth**: `auth:kaprodi`

**Response**:

```json
{
    "data": [
        {
            "id": 1,
            "document_name": "Surat Keterangan",
            "user": {
                "id": 10,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "status": "pending",
            "created_at": "2025-01-01T08:00:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "total": 50
    }
}
```

---

#### 7. Kaprodi: Approve Document

**URL**: `POST /admin/signature/approval-requests/{id}/approve`

**Auth**: `auth:kaprodi`

**Request**:

```json
{
    "notes": "Disetujui untuk ditandatangani"
}
```

**Response** (200):

```json
{
    "success": true,
    "message": "Document approved successfully",
    "data": {
        "approval_request": {...},
        "document_signature": {
            "id": 123,
            "status": "pending",
            "temporary_qr_code_path": "temp-qrcodes/temp_qr_123.png"
        }
    }
}
```

---

#### 8. User: Document Signing

**URL**: `POST /user/signature/sign/{approvalRequestId}/process`

**Auth**: `auth:web`

**Request**:

```json
{
    "qr_x": 150,
    "qr_y": 200,
    "qr_page": 1,
    "qr_scale": 0.264583,
    "qr_width": 30,
    "qr_height": 30
}
```

**Response** (200):

```json
{
    "success": true,
    "message": "Document signed successfully",
    "data": {
        "document_signature_id": 123,
        "signature_id": "SIG-A1B2C3D4E5F6",
        "verification_token": "token_64chars",
        "verification_url": "https://domain.com/signature/verify/token_64chars",
        "final_pdf_path": "signed_documents/signed_123.pdf",
        "qr_code_path": "qrcodes/qr_123.png"
    }
}
```

---

## 📊 Database Relationship Diagram

```
┌─────────────────┐
│     Users       │
│  (Mahasiswa)    │
└────────┬────────┘
         │
         │ 1:N
         │
         ▼
┌─────────────────────┐
│ ApprovalRequests    │ ◄──────────┐
│                     │            │
│ - user_id          │            │
│ - document_name     │            │
│ - document_path     │            │
│ - status            │            │
│ - approved_by       │            │
└──────────┬──────────┘            │
           │                       │
           │ 1:1                   │ N:1
           │                       │
           ▼                       │
┌─────────────────────────────────┴┐
│   DocumentSignatures             │
│                                   │
│ - approval_request_id (UNIQUE)   │
│ - document_hash                   │
│ - cms_signature                   │
│ - signature_status                │
│ - verification_token              │
│ - qr_code_path                    │
│ - final_pdf_path                  │
└──────────┬────────────────────────┘
           │
           │ 1:1
           │
           ▼
┌─────────────────────────────────┐
│   DigitalSignatures             │
│                                  │
│ - document_signature_id (UNIQUE)│
│ - signature_id                   │
│ - public_key                     │
│ - private_key (encrypted)        │
│ - certificate (X.509 PEM)        │
│ - valid_from                     │
│ - valid_until                    │
│ - status (active/revoked/expired)│
└──────────┬──────────────────────┘
           │
           │ 1:N
           │
           ▼
┌─────────────────────────────────┐
│ SignatureVerificationLogs       │
│                                  │
│ - document_signature_id          │
│ - user_id (nullable)             │
│ - verification_method            │
│ - is_valid                       │
│ - ip_address                     │
│ - verified_at                    │
└──────────────────────────────────┘

┌─────────────────────────────────┐
│   SignatureAuditLogs            │
│                                  │
│ - document_signature_id          │
│ - approval_request_id            │
│ - user_id                        │
│ - kaprodi_id                     │
│ - action                         │
│ - performed_at                   │
└──────────────────────────────────┘

┌─────────────────┐
│    Kaprodis     │
│   (Admin)       │
└─────────────────┘
         │
         │ 1:N (sebagai approver)
         │
         └──────► ApprovalRequests
                  DocumentSignatures
                  SignatureAuditLogs
```

---

## 🔍 Query Examples

### 1. Get All Pending Approvals untuk Kaprodi

```php
$pendingApprovals = ApprovalRequest::with(['user', 'documentSignature'])
    ->where('status', ApprovalRequest::STATUS_PENDING)
    ->orderBy('created_at', 'desc')
    ->paginate(20);
```

### 2. Get Dokumen yang Siap Ditandatangani User

```php
$readyToSign = ApprovalRequest::with(['documentSignature'])
    ->where('user_id', auth()->id())
    ->where('status', ApprovalRequest::STATUS_APPROVED)
    ->get();
```

### 3. Get All Verifications untuk Dokumen Tertentu

```php
$verifications = SignatureVerificationLog::with(['user'])
    ->where('document_signature_id', $docSigId)
    ->orderBy('verified_at', 'desc')
    ->get();
```

### 4. Get Signature Keys yang Akan Expired dalam 30 Hari

```php
$expiringKeys = DigitalSignature::expiringSoon(30)
    ->with(['documentSignature.approvalRequest'])
    ->get();
```

### 5. Get Audit Logs untuk Dokumen Tertentu

```php
$auditLogs = SignatureAuditLog::with(['user', 'kaprodi'])
    ->where('approval_request_id', $approvalRequestId)
    ->orderBy('performed_at', 'desc')
    ->get();
```

### 6. Get Statistik Verifikasi Hari Ini

```php
$todayStats = SignatureVerificationLog::today()
    ->selectRaw('
        COUNT(*) as total_verifications,
        SUM(CASE WHEN is_valid = 1 THEN 1 ELSE 0 END) as successful,
        SUM(CASE WHEN is_valid = 0 THEN 1 ELSE 0 END) as failed
    ')
    ->first();
```

### 7. Detect Suspicious Activity (Multiple Failed Attempts)

```php
$suspicious = SignatureVerificationLog::suspiciousActivity(5, 24)
    ->get();
```

---

## 📝 Catatan Penting

### 1. Keamanan

-   **JANGAN PERNAH** expose private key ke client
-   **SELALU** encrypt private key saat simpan di database
-   **GUNAKAN** rate limiting untuk semua public endpoints
-   **HASH** token di logs (jangan simpan plain text)
-   **MASK** data sensitif di public certificate viewer
-   **VALIDATE** semua input dari user (XSS, SQL Injection, dll)

### 2. Performance

-   **INDEX** semua foreign keys dan frequently queried columns
-   **CACHE** verification results untuk token yang sering diakses
-   **LAZY LOAD** relations untuk menghindari N+1 query problem
-   **QUEUE** heavy processes (PDF generation, email sending)
-   **PAGINATE** large result sets

### 3. Maintenance

-   **BACKUP** database secara berkala
-   **ROTATE** APP_KEY dengan hati-hati (private keys akan invalid!)
-   **MONITOR** logs untuk detect abuse/attacks
-   **UPDATE** dependencies secara berkala (security patches)
-   **AUDIT** access logs secara periodik

### 4. Compliance

-   **GDPR/Privacy**: Jangan simpan data pribadi unnecessary
-   **Audit Trail**: Keep logs minimal 1 year untuk compliance
-   **Data Retention**: Clear old verification logs setelah retention period
-   **Access Control**: Implement proper RBAC (Role-Based Access Control)

---

## 🎯 Kesimpulan

Sistem Digital Signature ini menyediakan solusi end-to-end untuk penandatanganan dan verifikasi dokumen digital yang aman, menggunakan standar industri (RSA-2048, SHA-256, X.509 v3, CMS Signature).

**Key Features**:

-   ✅ Unique key per document (tidak sharing key)
-   ✅ Self-signed certificate dengan DN personalized
-   ✅ CMS signature untuk document integrity
-   ✅ Public verification dengan QR code
-   ✅ Comprehensive audit trail
-   ✅ Security-first design (encryption, rate limiting, masking)
-   ✅ User-friendly QR positioning (drag & drop)

**Use Cases**:

-   Surat keterangan mahasiswa
-   Transkrip nilai
-   Sertifikat
-   Surat keputusan
-   Dokumen akademik lainnya

**Scalability**:

-   Dapat handle ribuan dokumen per hari
-   Rate limiting mencegah abuse
-   Database indexes untuk performa query
-   Cacheable verification results

---

**Dokumentasi ini dibuat pada**: 2025-01-03
**Versi**: 2.0
**Maintainer**: Program Studi Teknik Informatika UMT
