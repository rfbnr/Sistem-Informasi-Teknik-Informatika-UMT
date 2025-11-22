# Analisis Arsitektur Sistem Digital Signature

## 📐 Arsitektur Keseluruhan

### Layer Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   Routes     │  │  Controllers │  │    Views     │     │
│  │   web.php    │  │   (HTTP)     │  │   (Blade)    │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                      BUSINESS LAYER                          │
│  ┌─────────────────────────────────────────────────────┐   │
│  │                    SERVICES                          │   │
│  │  • DigitalSignatureService  (Key & Signing)         │   │
│  │  • PDFSignatureService      (PDF Manipulation)      │   │
│  │  • QRCodeService            (QR Generation)         │   │
│  │  • VerificationService      (Verification Logic)    │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                       DATA LAYER                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │    Models    │  │  Eloquent    │  │  Database    │     │
│  │   (Entities) │  │    ORM       │  │   (MySQL)    │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                    EXTERNAL SERVICES                         │
│  • OpenSSL (RSA Signing)   • Laravel Storage (Files)        │
│  • TCPDF/FPDI (PDF)        • Laravel Cache (Performance)    │
│  • Endroid QR Code         • Laravel Crypt (Encryption)     │
└─────────────────────────────────────────────────────────────┘
```

---

## 🗄️ Database Schema & Relationships

### ERD (Entity Relationship Diagram)

```
                    ┌─────────────────┐
                    │    kaprodis     │
                    │  (Guard Table)  │
                    └────────┬────────┘
                             │ 1
                             │
                             │ created_by
                             │
              ┌──────────────┴──────────────┐
              │                             │
              │                             │
         Many │                             │ Many
    ┌─────────▼────────────┐       ┌───────▼──────────┐
    │ digital_signatures   │       │ approval_requests│
    │                      │       │                  │
    │ • signature_id (UK)  │       │ • document_name  │
    │ • public_key         │       │ • document_path  │
    │ • private_key (enc)  │       │ • status         │
    │ • algorithm          │       │ • user_id (FK)   │
    │ • key_length         │       │                  │
    │ • certificate        │       └────────┬─────────┘
    │ • valid_from         │                │ 1
    │ • valid_until        │                │
    │ • status             │                │
    │ • created_by (FK)    │                │
    └──────────┬───────────┘                │
               │ 1                           │
               │                             │
               │                             │
               │         Many                │ Many
               │   ┌─────────────────────────▼───────┐
               │   │    document_signatures         │
               │   │                                 │
               └───►  • approval_request_id (FK)    │
                   │  • digital_signature_id (FK)   │
                   │  • document_hash               │
                   │  • signature_value             │
                   │  • cms_signature               │
                   │  • signed_at                   │
                   │  • signed_by (FK)              │
                   │  • signature_status            │
                   │  • qr_code_path                │
                   │  • verification_token          │
                   │  • final_pdf_path              │
                   │  • positioning_data            │
                   └────────┬───────────────────────┘
                            │ 1
                            │
                ┌───────────┴──────────────┐
                │                          │
           Many │                     Many │
    ┌───────────▼───────────┐   ┌─────────▼───────────────┐
    │ signature_audit_logs  │   │ signature_verification  │
    │                       │   │        _logs            │
    │ • kaprodi_id (FK)     │   │                         │
    │ • action              │   │ • document_signature_id │
    │ • status_from/to      │   │ • verification_method   │
    │ • metadata            │   │ • is_valid              │
    │ • ip_address          │   │ • result_status         │
    │ • performed_at        │   │ • ip_address            │
    └───────────────────────┘   └─────────────────────────┘


         ┌────────────────────────┐
         │ signature_templates    │
         │                        │
         │ • template_name        │
         │ • signature_image_path │
         │ • layout_config (JSON) │
         │ • style_config (JSON)  │
         │ • created_by (FK)      │
         └────────────────────────┘


    ┌─────────────────────────────┐
    │ verification_code_mappings  │
    │                             │
    │ • short_code (UK)           │
    │ • encrypted_payload         │
    │ • document_signature_id(FK) │
    │ • access_count              │
    │ • last_accessed_at          │
    │ • expires_at                │
    └─────────────────────────────┘
```

---

## 📊 Database Tables Detail

### 1. **digital_signatures** (Master Key Table)

**Purpose**: Menyimpan RSA key pairs untuk signing operations

| Column              | Type        | Description                             |
| ------------------- | ----------- | --------------------------------------- |
| `id`                | bigint      | Primary key                             |
| `signature_id`      | string (UK) | Unique identifier untuk signature       |
| `public_key`        | text        | RSA public key (PEM format)             |
| `private_key`       | text        | RSA private key (encrypted, PEM format) |
| `algorithm`         | string      | Algoritma: 'RSA-SHA256'                 |
| `key_length`        | integer     | Panjang kunci: 2048                     |
| `certificate`       | text        | Self-signed X.509 certificate           |
| `valid_from`        | timestamp   | Tanggal mulai berlaku                   |
| `valid_until`       | timestamp   | Tanggal kadaluarsa                      |
| `status`            | enum        | 'active', 'expired', 'revoked'          |
| `revocation_reason` | text        | Alasan pencabutan jika revoked          |
| `revoked_at`        | timestamp   | Waktu pencabutan                        |
| `created_by`        | FK          | Kaprodi yang generate key               |
| `signature_purpose` | text        | Tujuan penggunaan                       |
| `metadata`          | JSON        | Additional data                         |

**Indexes**:

-   `signature_id` (unique)
-   `(status, valid_from, valid_until)`

**Relationships**:

-   `belongsTo(Kaprodi)` via `created_by`
-   `hasMany(DocumentSignature)`

---

### 2. **document_signatures** (Signed Documents)

**Purpose**: Record setiap dokumen yang telah ditandatangani

| Column                 | Type      | Description                                            |
| ---------------------- | --------- | ------------------------------------------------------ |
| `id`                   | bigint    | Primary key                                            |
| `approval_request_id`  | FK        | Dokumen yang ditandatangani                            |
| `digital_signature_id` | FK        | Key yang digunakan untuk sign                          |
| `document_hash`        | string    | SHA-256 hash dokumen                                   |
| `signature_value`      | text      | Hash dari signature binary                             |
| `cms_signature`        | text      | CMS signature (base64)                                 |
| `signed_at`            | timestamp | Waktu penandatanganan                                  |
| `signed_by`            | FK        | Kaprodi yang menandatangani                            |
| `signature_status`     | enum      | 'pending', 'signed', 'verified', 'invalid', 'rejected' |
| `qr_code_path`         | string    | Path ke QR code image                                  |
| `verification_token`   | text      | Token untuk verifikasi                                 |
| `verification_url`     | text      | URL verifikasi                                         |
| `final_pdf_path`       | string    | Path PDF yang sudah signed                             |
| `positioning_data`     | JSON      | Posisi signature dan QR di PDF                         |
| `canvas_data_path`     | string    | Canvas positioning data                                |
| `signature_metadata`   | JSON      | Metadata tambahan                                      |
| `verified_at`          | timestamp | Waktu verifikasi                                       |
| `verified_by`          | FK        | Yang melakukan verifikasi                              |

**Indexes**:

-   `(document_hash, signature_status)`
-   `(signed_at, signature_status)`

**Relationships**:

-   `belongsTo(ApprovalRequest)`
-   `belongsTo(DigitalSignature)`
-   `belongsTo(Kaprodi, 'signed_by')`
-   `belongsTo(Kaprodi, 'verified_by')`

---

### 3. **approval_requests** (Documents)

**Purpose**: Dokumen yang perlu approval dan signature

| Column            | Type      | Description                                             |
| ----------------- | --------- | ------------------------------------------------------- |
| `id`              | bigint    | Primary key                                             |
| `user_id`         | FK        | User yang submit dokumen                                |
| `document_name`   | string    | Nama dokumen                                            |
| `document_type`   | string    | Jenis dokumen                                           |
| `document_number` | string    | Nomor dokumen                                           |
| `document_path`   | string    | Path PDF original                                       |
| `status`          | enum      | 'pending', 'approved', 'rejected', 'sign_approved', dsb |
| `notes`           | text      | Catatan                                                 |
| `approved_by`     | FK        | Kaprodi yang approve                                    |
| `approved_at`     | timestamp | Waktu approval                                          |

**Relationships**:

-   `belongsTo(User)`
-   `belongsTo(Kaprodi, 'approved_by')`
-   `hasOne(DocumentSignature)`

---

### 4. **signature_templates** (Visual Templates)

**Purpose**: Template visual untuk signature yang di-embed ke PDF

| Column                 | Type   | Description                         |
| ---------------------- | ------ | ----------------------------------- |
| `id`                   | bigint | Primary key                         |
| `template_name`        | string | Nama template                       |
| `signature_image_path` | string | Path gambar signature               |
| `layout_config`        | JSON   | Konfigurasi layout (posisi, ukuran) |
| `style_config`         | JSON   | Konfigurasi style (warna, font)     |
| `created_by`           | FK     | Kaprodi pembuat template            |

**Relationships**:

-   `belongsTo(Kaprodi, 'created_by')`

---

### 5. **signature_audit_logs** (Audit Trail)

**Purpose**: Logging semua operasi signature

| Column         | Type      | Description                   |
| -------------- | --------- | ----------------------------- |
| `id`           | bigint    | Primary key                   |
| `kaprodi_id`   | FK        | Kaprodi yang melakukan action |
| `action`       | string    | Action type (constants)       |
| `status_from`  | string    | Status sebelum                |
| `status_to`    | string    | Status sesudah                |
| `description`  | text      | Deskripsi action              |
| `metadata`     | JSON      | Data tambahan (standardized)  |
| `ip_address`   | string    | IP address                    |
| `user_agent`   | text      | Browser/device info           |
| `performed_at` | timestamp | Waktu action                  |

**Action Constants**:

-   `ACTION_SIGNATURE_KEY_GENERATED`
-   `ACTION_SIGNATURE_KEY_REVOKED`
-   `ACTION_DOCUMENT_SIGNED`
-   `ACTION_SIGNATURE_VERIFIED`
-   dll

---

### 6. **signature_verification_logs** (Verification Tracking)

**Purpose**: Tracking semua verification attempts

| Column                    | Type      | Description                                            |
| ------------------------- | --------- | ------------------------------------------------------ |
| `id`                      | bigint    | Primary key                                            |
| `document_signature_id`   | FK        | Dokumen yang diverifikasi                              |
| `approval_request_id`     | FK        | Approval request terkait                               |
| `user_id`                 | FK        | User yang verify (nullable)                            |
| `verification_method`     | string    | 'token' atau 'id'                                      |
| `verification_token_hash` | string    | Hash dari token (privacy)                              |
| `is_valid`                | boolean   | Hasil verifikasi                                       |
| `result_status`           | enum      | 'success', 'failed', 'expired', 'invalid', 'not_found' |
| `ip_address`              | string    | IP verifier                                            |
| `user_agent`              | text      | Browser info                                           |
| `referrer`                | string    | HTTP referrer                                          |
| `metadata`                | JSON      | Verification details                                   |
| `verified_at`             | timestamp | Waktu verifikasi                                       |

---

### 7. **verification_code_mappings** (Short Code System)

**Purpose**: Mapping short code ke encrypted payload untuk QR codes

| Column                  | Type        | Description                         |
| ----------------------- | ----------- | ----------------------------------- |
| `id`                    | bigint      | Primary key                         |
| `short_code`            | string (UK) | Short code (e.g., 'A1B2-C3D4-E5F6') |
| `encrypted_payload`     | text        | Full encrypted verification data    |
| `document_signature_id` | FK          | Reference ke document signature     |
| `access_count`          | integer     | Berapa kali diakses                 |
| `last_accessed_at`      | timestamp   | Akses terakhir                      |
| `expires_at`            | timestamp   | Waktu kadaluarsa                    |

**Indexes**:

-   `short_code` (unique)
-   `expires_at`

---

## 🔗 Relationship Summary

### One-to-Many Relationships

1. **Kaprodi → DigitalSignatures** (1:N)

    - Satu Kaprodi bisa punya multiple key pairs

2. **DigitalSignature → DocumentSignatures** (1:N)

    - **Satu key digunakan untuk sign BANYAK dokumen**

3. **ApprovalRequest → DocumentSignature** (1:1)

    - Satu dokumen hanya punya 1 signature record

4. **DocumentSignature → VerificationLogs** (1:N)

    - Satu signed document bisa diverifikasi berkali-kali

5. **DocumentSignature → VerificationCodeMapping** (1:1)
    - Satu signed document punya 1 short code

### Foreign Key Cascades

-   **digital_signatures.created_by** → ON DELETE CASCADE
-   **document_signatures.approval_request_id** → ON DELETE CASCADE
-   **document_signatures.digital_signature_id** → ON DELETE CASCADE
-   **document_signatures.verified_by** → ON DELETE SET NULL

---

## 📦 Models & Their Key Methods

### DigitalSignature Model

```php
// Location: app/Models/DigitalSignature.php

// Key Methods:
- isValid(): bool                    // Check if key is active and not expired
- isExpiringSoon($days): bool        // Check if expiring within X days
- revoke($reason): void              // Revoke the key
- getUsageStats(): array             // Get usage statistics
- setPrivateKeyAttribute($value)     // Encrypt private key (mutator)
- getPrivateKeyAttribute($value)     // Decrypt private key (accessor)

// Relationships:
- creator(): BelongsTo               // Kaprodi yang create
- documentSignatures(): HasMany      // Dokumen yang ditandatangani

// Constants:
- STATUS_ACTIVE = 'active'
- STATUS_EXPIRED = 'expired'
- STATUS_REVOKED = 'revoked'
```

### DocumentSignature Model

```php
// Location: app/Models/DocumentSignature.php

// Key Methods:
- isValid(): bool                    // Check if signature is valid
- verify(): array                    // Verify signature
- getQRCodeUrl(): string             // Get QR code public URL

// Relationships:
- approvalRequest(): BelongsTo
- digitalSignature(): BelongsTo
- signer(): BelongsTo                // Kaprodi yang sign
- verifier(): BelongsTo              // Kaprodi yang verify
- verificationLogs(): HasMany
- codeMapping(): HasOne

// Constants:
- STATUS_PENDING = 'pending'
- STATUS_SIGNED = 'signed'
- STATUS_VERIFIED = 'verified'
- STATUS_INVALID = 'invalid'
- STATUS_REJECTED = 'rejected'
```

### ApprovalRequest Model

```php
// Location: app/Models/ApprovalRequest.php

// Key Methods:
- markUserSigned(): void             // Update status ke sign_approved
- canBeSigned(): bool                // Check if ready for signing

// Relationships:
- user(): BelongsTo                  // User yang submit
- approver(): BelongsTo              // Kaprodi approver
- documentSignature(): HasOne

// Status Flow:
// pending → approved → sign_approved
```

---

## 🛠️ Service Layer Architecture

### Service Dependencies

```
DigitalSignatureController
    ↓ uses
DigitalSignatureService ──┬──> OpenSSL (key generation)
    │                     ├──> DigitalSignature Model
    │                     └──> DocumentSignature Model
    │
    ├──> PDFSignatureService ──┬──> TCPDF/FPDI (PDF manipulation)
    │                          ├──> Storage Facade
    │                          └──> Ghostscript (PDF conversion)
    │
    ├──> QRCodeService ──┬──> Endroid QR Code
    │                    ├──> Laravel Crypt
    │                    └──> VerificationCodeMapping Model
    │
    └──> VerificationService ──┬──> QRCodeService
                                ├──> DigitalSignatureService
                                └──> SignatureVerificationLog Model
```

---

## 🔐 Security Architecture

### Encryption Layers

1. **Private Key Encryption** (at rest)

    ```php
    // Model Mutator
    setPrivateKeyAttribute() → encrypt($value)
    getPrivateKeyAttribute() → decrypt($value)
    ```

2. **Document Hashing**

    ```php
    // SHA-256
    document_hash = hash('sha256', $pdfContent)
    ```

3. **QR Code Payload Encryption**

    ```php
    // Laravel Crypt
    Crypt::encryptString(json_encode($verificationData))
    ```

4. **Verification Token Hashing** (logs)
    ```php
    // SHA-256 for privacy
    verification_token_hash = hash('sha256', $token)
    ```

### Access Control Matrix

| Resource               | Public | User | Kaprodi |
| ---------------------- | ------ | ---- | ------- |
| View verification page | ✅     | ✅   | ✅      |
| Verify signature       | ✅     | ✅   | ✅      |
| Generate key pair      | ❌     | ❌   | ✅      |
| Sign document          | ❌     | ❌   | ✅      |
| Revoke key             | ❌     | ❌   | ✅      |
| View audit logs        | ❌     | ❌   | ✅      |

---

**Next**: Read [DIGITAL_SIGNATURE_USER_FLOW.md](DIGITAL_SIGNATURE_USER_FLOW.md) untuk memahami alur pengguna lengkap.
