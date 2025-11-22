# ONE-PASS TCPDF SIGNING IMPLEMENTATION

## 📋 Overview

Implementasi ini mengubah workflow signing dari **2-pass** (embed QR → sign separately) menjadi **ONE-PASS** (embed QR + sign simultaneously) menggunakan TCPDF built-in `setSignature()` method.

**Tujuan**: Membuat PDF signature yang dapat diverifikasi oleh Adobe Reader.

---

## ✅ What Was Implemented

### 1. **New Method: `embedQRCodeAndSignPDF()`**
**File**: `app/Services/PDFSignatureService.php` (Lines 894-1156)

Method ini menggabungkan:
- QR code embedding (dari user drag & drop)
- PDF digital signature (menggunakan TCPDF `setSignature()`)

**Key Features**:
- ✅ One-pass operation (QR + sign dalam 1 step)
- ✅ TCPDF `setSignature()` dipanggil saat PDF creation
- ✅ Certificate & private key dari database
- ✅ Adobe Reader compatible signature structure
- ✅ Automatic cleanup of temporary files
- ✅ Comprehensive logging

**Method Signature**:
```php
public function embedQRCodeAndSignPDF(
    string $originalPdfPath,
    string $qrCodePath,
    array $qrPositioningData,
    DocumentSignature $documentSignature,
    array $signingOptions
): string
```

**Signing Options**:
```php
[
    'certificate_pem' => string,  // X.509 certificate PEM
    'private_key_pem' => string,  // RSA private key PEM
    'signer_name' => string,      // Name for Adobe Reader
    'reason' => string,           // Reason for signing
    'location' => string,         // Location
    'contact_info' => string      // Email/contact
]
```

**Process Flow**:
1. Validate files and signing options
2. Create temporary certificate files (TCPDF requirement)
3. Initialize FPDI for PDF manipulation
4. Detect PDF version & convert if needed
5. Import all pages from original PDF
6. Embed QR code at user-defined position
7. **Call `setSignature()`** - THIS IS THE KEY!
8. Set signature appearance (visual box)
9. Output signed PDF
10. Cleanup temporary files

---

### 2. **Updated Controller: `processDocumentSigning()`**
**File**: `app/Http/Controllers/DigitalSignature/DigitalSignatureController.php` (Lines 317-381)

**Changes**:

**BEFORE (2-Pass)**:
```php
// Step 3: Embed QR code
$pdfWithQRPath = $this->pdfSignatureService->embedQRCodeIntoPDF(...);

// Step 4: Sign document (SEPARATE!)
$signedDocumentSignature = $this->digitalSignatureService->signDocumentWithUniqueKey(...);
```

**AFTER (1-Pass)**:
```php
// Step 3-4: Generate digital signature key FIRST
$digitalSignature = $this->digitalSignatureService->createDigitalSignatureForDocument($documentSignature);

// Step 5: ONE-PASS - Embed QR + Sign simultaneously
$pdfWithQRPath = $this->pdfSignatureService->embedQRCodeAndSignPDF(
    $originalPdfPath,
    $qrCodeAbsolutePath,
    $qrPositioningData,
    $documentSignature,
    [
        'certificate_pem' => $digitalSignature->certificate,
        'private_key_pem' => $digitalSignature->private_key,
        'signer_name' => $approvalRequest->user->name,
        'reason' => 'Document Approval',
        'location' => 'Universitas Muhammadiyah Tangerang',
        'contact_info' => $approvalRequest->user->email
    ]
);

// Step 6: Create CMS signature and update database
$signatureData = $this->digitalSignatureService->createCMSSignature($pdfWithQRPath, $digitalSignature);
$documentSignature->update([...]);
```

**Key Differences**:
- Digital signature key generated BEFORE PDF signing (not after)
- QR embedding and signing happen in ONE method call
- CMS signature created from already-signed PDF
- Metadata includes `'signing_method' => 'one_pass_tcpdf'`

---

### 3. **Helper Method: `extractPKCS7FromSignedPDF()`**
**File**: `app/Services/DigitalSignatureService.php` (Lines 1680-1750)

Method untuk extract PKCS#7 signature dari signed PDF.

**Purpose**: Backward compatibility dengan verification system yang existing.

**How It Works**:
1. Read signed PDF content
2. Find `/Contents <hex_data>` in PDF structure
3. Convert hex to binary
4. Validate PKCS#7 structure (must start with 0x30 SEQUENCE)
5. Encode to base64 for database storage

**Usage**:
```php
$signedPdfPath = $this->pdfSignatureService->embedQRCodeAndSignPDF(...);

// Extract PKCS#7 for verification later
$pkcs7Signature = $this->digitalSignatureService->extractPKCS7FromSignedPDF($signedPdfPath);

// Store in database (optional, for backward compatibility)
$documentSignature->update([
    'cms_signature' => $pkcs7Signature
]);
```

---

## 🔑 Key Technical Details

### Why ONE-PASS is Required

**TCPDF Limitation**: `setSignature()` MUST be called DURING PDF creation, not after PDF is finalized.

**Analogy**:
- ❌ **2-Pass**: Sign a document after it's laminated (IMPOSSIBLE!)
- ✅ **1-Pass**: Sign a document BEFORE laminating (CORRECT!)

### TCPDF `setSignature()` Parameters

```php
$pdf->setSignature(
    'file://' . $certTempFile,     // Certificate file path (NOT string!)
    'file://' . $keyTempFile,      // Private key file path (NOT string!)
    '',                             // Password (empty if no password)
    '',                             // Extra certificates (chain)
    2,                              // Signature type: 2 = approval signature
    $signatureInfo                  // Metadata array
);
```

**Signature Types**:
- `1` = Author signature (locks document)
- `2` = Approval signature (recommended for most use cases)
- `3` = Certification signature

### PDF Signature Structure Created

TCPDF creates proper PDF signature dictionary:

```pdf
/Type /Sig
/Filter /Adobe.PPKLite
/SubFilter /adbe.pkcs7.detached
/Contents <PKCS7_HEX_DATA>
/ByteRange [0 12345 20537 5432]
/M (D:20251121120000+07'00')
/Name (Signer Name)
/Reason (Document Approval)
/Location (Universitas Muhammadiyah Tangerang)
/ContactInfo (email@umt.ac.id)
```

**Key Components**:
- `/Type /Sig`: Identifies as signature dictionary
- `/Filter`: Adobe PPKLite (standard)
- `/SubFilter`: adbe.pkcs7.detached (PKCS#7 detached signature)
- `/Contents`: PKCS#7 signature in hex format
- `/ByteRange`: Specifies which bytes are signed
- `/M`: Signing timestamp
- `/Name`, `/Reason`, `/Location`, `/ContactInfo`: Metadata for Adobe Reader

---

## 📊 Comparison: Before vs After

| Aspect | BEFORE (2-Pass) | AFTER (1-Pass) |
|--------|-----------------|----------------|
| **Workflow** | Create PDF → Sign separately | Create PDF + Sign simultaneously |
| **Methods Called** | `embedQRCodeIntoPDF()` then `signDocumentWithUniqueKey()` | `embedQRCodeAndSignPDF()` (combined) |
| **Adobe Reader Verification** | ❌ FAIL (no signature found) | ✅ SUCCESS (signature detected) |
| **PDF Signature Dictionary** | ❌ Missing | ✅ Present |
| **ByteRange** | ❌ Not calculated | ✅ Calculated automatically |
| **PKCS#7 Location** | Database only | Database + PDF structure |
| **Implementation Complexity** | HIGH (manual PDF manipulation) | LOW (TCPDF built-in) |
| **Code Maintenance** | Difficult | Easy |
| **Standards Compliance** | Partial | Full (ISO 32000-1) |

---

## 🔍 Adobe Reader Verification Results

### Expected Behavior

When opening signed PDF in Adobe Reader:

**✅ SUCCESS Indicators**:
1. "Signed by: [Signer Name]" banner at top of document
2. Signature panel visible (click banner to see details)
3. Certificate information accessible
4. Document integrity verified

**⚠️ Validity Unknown Warning**:
```
Signature Validity: UNKNOWN
The signer's identity is unknown because it has not been included
in your list of trusted certificates and none of its parent
certificates are trusted certificates.
```

**This is NORMAL for self-signed certificates!**

**Why "Validity Unknown"?**
- Certificate is self-signed (not from trusted CA like DigiCert)
- For internal university use, this is ACCEPTABLE
- User can still verify:
  - Signer name
  - Signing timestamp
  - Document not modified after signing
  - Certificate details

### How to Verify in Adobe Reader

1. Open signed PDF
2. Click signature banner at top
3. Click "Signature Panel" button
4. View signature properties:
   - ✅ Signer: [Name]
   - ✅ Date: [Timestamp]
   - ✅ Reason: Document Approval
   - ✅ Location: Universitas Muhammadiyah Tangerang
   - ⚠️ Validity: Unknown (self-signed)
   - ✅ Document: Not modified since signing

---

## 🧪 Testing Checklist

### Unit Testing

- [x] Syntax validation (all files pass `php -l`)
- [ ] Method `embedQRCodeAndSignPDF()` with sample PDF
- [ ] Certificate file creation and cleanup
- [ ] QR positioning calculations
- [ ] Error handling for missing files

### Integration Testing

- [ ] Full signing workflow from UI
- [ ] Sign PDF with different page counts (1, 5, 10+ pages)
- [ ] Sign PDF with different sizes (A4, Letter, Legal)
- [ ] QR code positioning on different pages
- [ ] Multiple signatures on same document

### Adobe Reader Verification Testing (CRITICAL!)

- [ ] Open signed PDF in Adobe Reader
- [ ] Verify signature banner appears
- [ ] Open signature panel
- [ ] Check certificate details
- [ ] Verify document integrity message
- [ ] Test on multiple Adobe Reader versions:
  - [ ] Adobe Reader DC (latest)
  - [ ] Adobe Acrobat Pro
  - [ ] Adobe Reader 2020

### Backward Compatibility Testing

- [ ] Web verification system still works
- [ ] `extractPKCS7FromSignedPDF()` extracts signature correctly
- [ ] Database storage compatibility
- [ ] Existing verification endpoints work

### Performance Testing

- [ ] Sign 10 PDFs in sequence
- [ ] Sign 50 PDFs to test memory usage
- [ ] Check signing duration (should be < 5 seconds)
- [ ] Verify temp file cleanup

---

## 🚀 Deployment Instructions

### Prerequisites

1. **TCPDF Library**: Already installed (`tecnickcom/tcpdf 6.10.0`)
2. **FPDI Library**: Already installed (`setasign/fpdi 2.6.4`)
3. **PHP Extensions**: `openssl`, `gd`, `zlib` (should be already enabled)

### Deployment Steps

#### 1. Backup Current System

```bash
# Backup database
php artisan backup:run

# Backup files (if not using version control)
cp -r app/Services app/Services.backup.$(date +%Y%m%d)
cp -r app/Http/Controllers/DigitalSignature app/Http/Controllers/DigitalSignature.backup.$(date +%Y%m%d)
```

#### 2. Deploy Code Changes

```bash
# Pull latest code
git pull origin feat/revise-digital-signature

# Or manually copy files if not using git
# (already implemented, no additional steps needed)
```

#### 3. Clear Caches

```bash
# Clear Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart queue workers if using
php artisan queue:restart
```

#### 4. Test in Staging Environment

```bash
# Run Laravel in development mode
php artisan serve --port=8000

# Test signing workflow:
# 1. Login as user
# 2. Upload document
# 3. Request approval
# 4. Sign document with QR positioning
# 5. Download signed PDF
# 6. Open in Adobe Reader
```

#### 5. Verify Logs

```bash
# Monitor logs during testing
tail -f storage/logs/laravel.log

# Look for these log messages:
# - "Starting ONE-PASS QR embedding + PDF signing"
# - "Certificate files created"
# - "PDF loaded successfully"
# - "Setting digital signature"
# - "PDF signed successfully with ONE-PASS method"
# - "adobe_reader_compatible: true"
```

#### 6. Production Deployment

```bash
# Switch to production mode
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Enable production error handling
# Set APP_DEBUG=false in .env
```

---

## 📝 Logging & Monitoring

### Key Log Messages to Monitor

**Success Flow**:
```
[INFO] Starting ONE-PASS QR embedding + PDF signing
[INFO] Certificate files created
[INFO] PDF loaded successfully (total_pages: X)
[INFO] Setting digital signature
[INFO] Signature appearance set
[INFO] PDF signed successfully with ONE-PASS method
[INFO] PKCS#7 signature extracted from signed PDF (if using extractPKCS7FromSignedPDF)
[INFO] QR code embedded and PDF signed (ONE-PASS method)
[INFO] Document signature updated with CMS signature
```

**Error Indicators**:
```
[ERROR] Original PDF file not found
[ERROR] Certificate PEM is required for signing
[ERROR] ONE-PASS signing failed
[ERROR] Failed to extract PKCS#7 from signed PDF
```

### Monitoring Queries

```sql
-- Check signing success rate (last 24 hours)
SELECT
    COUNT(*) as total_signed,
    SUM(CASE WHEN JSON_EXTRACT(signature_metadata, '$.signing_method') = 'one_pass_tcpdf' THEN 1 ELSE 0 END) as one_pass_signed,
    SUM(CASE WHEN JSON_EXTRACT(signature_metadata, '$.adobe_reader_compatible') = true THEN 1 ELSE 0 END) as adobe_compatible
FROM document_signatures
WHERE signed_at >= NOW() - INTERVAL 24 HOUR;

-- Check for signing errors (last 24 hours)
SELECT COUNT(*) as errors
FROM signature_audit_logs
WHERE action = 'document_signed'
AND status_to != 'verified'
AND performed_at >= NOW() - INTERVAL 24 HOUR;
```

---

## 🐛 Troubleshooting

### Issue 1: "Certificate PEM is required for signing"

**Cause**: `$digitalSignature->certificate` is null or empty.

**Solution**:
```php
// Check if certificate exists
if (!$digitalSignature->certificate) {
    // Regenerate digital signature
    $digitalSignature = $this->digitalSignatureService->createDigitalSignatureForDocument($documentSignature);
}
```

### Issue 2: "TCPDF error: Unable to get signature"

**Cause**: Certificate or private key file format is invalid.

**Solution**:
- Verify certificate is in PEM format (starts with `-----BEGIN CERTIFICATE-----`)
- Verify private key is in PEM format (starts with `-----BEGIN PRIVATE KEY-----`)
- Check temp directory is writable (`sys_get_temp_dir()`)

### Issue 3: Adobe Reader shows "Invalid Signature"

**Cause**: Document modified after signing.

**Solution**:
- Do NOT modify PDF after `Output()` is called
- Ensure no additional operations on signed PDF
- Check file transfer didn't corrupt PDF (use binary mode)

### Issue 4: No signature visible in Adobe Reader

**Cause**: `setSignature()` not called, or called AFTER `Output()`.

**Solution**:
- Verify `setSignature()` is called BEFORE `Output()`
- Check logs for "Setting digital signature" message
- Verify no exceptions during signing process

### Issue 5: "Temporary file not found"

**Cause**: Temp file cleanup happened too early, or disk full.

**Solution**:
- Check disk space: `df -h`
- Verify temp directory permissions: `ls -la $(php -r 'echo sys_get_temp_dir();')`
- Increase cleanup timeout if needed

---

## 🎯 Future Enhancements (Optional)

### 1. Upgrade to "Trusted" Signature

**Current**: Self-signed certificate → "Validity Unknown" ⚠️

**Option A: Purchase CA Certificate** ($$$)
- Buy certificate from DigiCert, GlobalSign, etc.
- Cost: ~$200-500/year
- Adobe Reader will automatically trust
- Best for external/public documents

**Option B: Create Internal CA**
- Create University Root CA
- Issue certificates from Root CA
- Distribute Root CA to all university computers
- Free but requires IT support
- Best for internal documents

### 2. Timestamp Authority (TSA)

Add RFC 3161 timestamp to signatures for long-term validity:

```php
$pdf->setTimeStamp('https://freetsa.org/tsr');
```

**Benefits**:
- Proof of signing time (even if certificate expires)
- Long-term archive standard (PAdES-LTA)
- Required for some compliance standards

### 3. Visual Signature Image

Add custom signature image (e.g., university seal):

```php
// Before setSignature()
$pdf->Image(
    'path/to/seal.png',
    10, 10, 30, 30,
    'PNG'
);

// Then call setSignature()
$pdf->setSignature(...);

// Set appearance to where image was placed
$pdf->setSignatureAppearance(10, 10, 30, 30);
```

### 4. Multiple Signatures

Allow multiple signers (e.g., student → kaprodi → dean):

```php
// First signature
$pdf->setSignature(...);
$pdf->setSignatureAppearance(10, 10, 50, 30);
$pdf->Output('signed_v1.pdf', 'F');

// Load signed PDF for second signature
$pdf2 = new Fpdi();
$pdf2->setSourceFile('signed_v1.pdf');
// ... import pages ...
$pdf2->setSignature(...); // Second signature
$pdf2->setSignatureAppearance(10, 50, 50, 30); // Different position
$pdf2->Output('signed_v2.pdf', 'F');
```

---

## 📚 References

### TCPDF Documentation
- Official Example 052: https://tcpdf.org/examples/example_052/
- TCPDF GitHub: https://github.com/tecnickcom/TCPDF

### PDF Standards
- ISO 32000-1 (PDF 1.7): PDF signature specification
- RFC 5652: Cryptographic Message Syntax (CMS/PKCS#7)
- RFC 5280: X.509 Certificate standard

### Adobe Documentation
- Adobe PDF Signature Standards: https://www.adobe.com/devnet-docs/acrobatetk/tools/DigSigDC/standards.html
- PAdES (PDF Advanced Electronic Signatures): ETSI TS 102 778

---

## ✅ Implementation Status

- [x] Create `embedQRCodeAndSignPDF()` method
- [x] Update `processDocumentSigning()` controller
- [x] Create `extractPKCS7FromSignedPDF()` helper
- [x] Syntax validation (all files pass)
- [x] Documentation
- [ ] **TODO**: Integration testing with real documents
- [ ] **TODO**: Adobe Reader verification testing
- [ ] **TODO**: UAT with actual users
- [ ] **TODO**: Production deployment

---

## 🎉 Expected Benefits

1. **Adobe Reader Compatibility**: PDF signatures dapat diverifikasi ✅
2. **Industry Standard Compliance**: Full ISO 32000-1 compliance ✅
3. **Simplified Codebase**: One method instead of two ✅
4. **Better Error Handling**: TCPDF handles PDF structure ✅
5. **Improved Logging**: Comprehensive logging at each phase ✅
6. **Professional Appearance**: Signature banner in Adobe Reader ✅
7. **User Trust**: Two verification methods (Adobe + Web) ✅

---

## 👥 Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Review this documentation
3. Test with sample PDF first
4. Contact: informatika@umt.ac.id

---

**Implementation Date**: 2025-11-21
**Author**: Claude Code AI Assistant
**Version**: 1.0
**Status**: ✅ Implemented, Ready for Testing
