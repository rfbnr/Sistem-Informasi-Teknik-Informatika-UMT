# 🚀 PDF Merging Service - Quick Start Guide

**Status:** ✅ COMPLETED & READY TO USE
**Date:** October 19, 2025

---

## ⚡ Quick Setup (5 Minutes)

### Step 1: Install Dependencies (Already Done ✅)
```bash
composer install
# TCPDF v6.10.0 ✅ Installed
# FPDI v2.6.4 ✅ Installed
```

### Step 2: Run Database Migrations & Seeders
```bash
# Fresh database with all seeders
php artisan migrate:fresh --seed

# Output should show:
# ✓ Digital Signature created successfully!
# ✓ Signature Template created successfully!
# ⚠ IMPORTANT: You need to manually upload signature image
```

### Step 3: Upload Signature Template Image

**Option A: Via File System (Quick)**
1. Create sample signature image (400×200 pixels, PNG format)
2. Save to: `storage/app/signature-templates/default-signature.png`

```bash
# Create directory if not exists
mkdir -p storage/app/signature-templates

# Copy your signature image
cp /path/to/your/signature.png storage/app/signature-templates/default-signature.png
```

**Option B: Via Admin Panel (Recommended)**
1. Login as Kaprodi: `kaprodi.informatika@umt.ac.id` / `password`
2. Go to: Admin > Signature Templates
3. Click on "Default Kaprodi Signature"
4. Upload signature image
5. Click "Save"

### Step 4: Test the Workflow

#### A. Upload Document (as Mahasiswa)
```
1. Login: user@umt.ac.id / password
2. Menu: User > Approval Request
3. Upload PDF document
4. Submit request
```

#### B. Approve Document (as Kaprodi)
```
1. Login: kaprodi.informatika@umt.ac.id / password
2. Menu: Admin > Approval Requests
3. Find pending request
4. Click "Approve"
```

#### C. Sign Document (as Mahasiswa)
```
1. Login: user@umt.ac.id / password
2. Menu: User > Approval Status
3. Find approved document
4. Click "Sign Document"
5. Drag signature template onto PDF
6. Adjust position and size
7. Click "Sign Document" button
8. ✅ Success!
```

#### D. Verify Signed PDF Created
```bash
# Check database
php artisan tinker
>>> DB::table('document_signatures')->latest()->first()->final_pdf_path

# Check file system
ls -lh storage/app/signed-documents/

# Should show: signed_YYYYMMDDHHMMSS_{id}.pdf
```

#### E. Download and Open PDF
1. Download signed PDF from UI
2. Open with PDF reader
3. ✅ Verify signature visible at correct position
4. ✅ Verify QR code at bottom-right corner

---

## 🎯 What Changed?

### Before
```
User signs → Only database updated → PDF unchanged
❌ No visible signature on PDF
```

### After
```
User signs → Database updated → PDF physically modified
✅ Signature template embedded
✅ QR code added
✅ New signed PDF created
```

---

## 📁 File Structure

```
storage/app/
├── signature-templates/        ← Template images
│   └── default-signature.png   (Manual upload required)
│
├── signed-documents/           ← Generated signed PDFs
│   ├── signed_20251019120530_1.pdf
│   ├── signed_20251019120645_2.pdf
│   └── ...
│
└── temp/                       ← Temporary QR codes (auto-deleted)
    └── qr_*.png
```

---

## 🔍 Verification

### Quick Checks

**1. Service Registered:**
```bash
php artisan tinker
>>> app(App\Services\PDFSignatureService::class)
# Should return: App\Services\PDFSignatureService object
```

**2. Signature Template Exists:**
```bash
php artisan tinker
>>> App\Models\SignatureTemplate::first()
# Should return: SignatureTemplate object with default template
```

**3. Digital Signature Exists:**
```bash
php artisan tinker
>>> App\Models\DigitalSignature::first()
# Should return: DigitalSignature object for system
```

**4. Libraries Installed:**
```bash
composer show | grep -E "tcpdf|fpdi"
# Should show:
# setasign/fpdi        v2.6.4
# tecnickcom/tcpdf     6.10.0
```

---

## 🧪 Testing Commands

### Test 1: Sign Document via Artisan Tinker
```php
php artisan tinker

// Get approval request
$approval = App\Models\ApprovalRequest::where('status', 'approved')->first();

// Get template
$template = App\Models\SignatureTemplate::first();

// Get service
$service = app(App\Services\PDFSignatureService::class);

// Test positioning data
$positioningData = [
    'template_id' => $template->id,
    'page' => 1,
    'position' => ['x' => 100, 'y' => 600],
    'size' => ['width' => 200, 'height' => 100],
    'canvas_dimensions' => ['width' => 794, 'height' => 1123]
];

// Get document signature
$docSig = $approval->documentSignature;

// Test PDF merging
$originalPdfPath = Storage::path($approval->document_path);
$signedPdfPath = $service->mergeSignatureIntoPDF(
    $originalPdfPath,
    $template->id,
    $positioningData,
    $docSig,
    null
);

echo "Signed PDF created: " . $signedPdfPath;
```

### Test 2: Check Logs
```bash
# Watch logs during signing
tail -f storage/logs/laravel.log

# Filter for PDF merging logs
grep "PDF signature merge" storage/logs/laravel.log
grep "Signature embedded" storage/logs/laravel.log
```

### Test 3: Verify QR Code Generation
```bash
php artisan tinker

$service = app(App\Services\PDFSignatureService::class);
$qrPath = $service->generateQRCodeImage(
    'https://example.com/verify/abc123',
    'test-doc-1'
);

echo "QR code created: " . $qrPath;
// Output: storage/app/temp/qr_test-doc-1.png
```

---

## 🐛 Troubleshooting

### Issue 1: "Template image not found"
**Error:** `Template image not found: storage/app/signature-templates/...`

**Solution:**
```bash
# Check if file exists
ls -la storage/app/signature-templates/

# If missing, upload via admin panel or:
mkdir -p storage/app/signature-templates
# Then upload your signature image
```

### Issue 2: "Original PDF file not found"
**Error:** `Original PDF file not found: storage/app/approval-documents/...`

**Solution:**
```bash
# Ensure storage link created
php artisan storage:link

# Check if document uploaded correctly
ls -la storage/app/approval-documents/
```

### Issue 3: "Failed to merge signature into PDF"
**Check logs:**
```bash
tail -100 storage/logs/laravel.log | grep -A 10 "Failed to embed signature"
```

**Common causes:**
- Missing signature template image
- Corrupted PDF file
- Insufficient disk space
- PHP memory limit exceeded

**Solutions:**
```bash
# Increase memory limit in php.ini
memory_limit = 256M

# Or in .env
PHP_MEMORY_LIMIT=256M
```

### Issue 4: QR Code Not Generated
**Check if simple-qrcode installed:**
```bash
composer show | grep simple-qrcode
# Should show: simplesoftwareio/simple-qrcode
```

**If missing:**
```bash
composer require simplesoftwareio/simple-qrcode
```

**Note:** System works without QR code (graceful degradation)

### Issue 5: PDF Download Shows Original (Not Signed)
**Check:**
1. Is `final_pdf_path` populated in database?
   ```sql
   SELECT final_pdf_path FROM document_signatures WHERE id = ?;
   ```

2. Is file actually created?
   ```bash
   ls -la storage/app/signed-documents/
   ```

3. Is download route using `final_pdf_path`?
   - Should download from `final_pdf_path`, not `document_path`

---

## 📊 Performance Expectations

| PDF Size | Pages | Expected Time | Memory Used |
|----------|-------|---------------|-------------|
| < 1MB    | 1-2   | 0.3 - 0.8s   | 10-20MB     |
| 1-5MB    | 3-10  | 1-3s         | 30-80MB     |
| 5-10MB   | 10-25 | 3-6s         | 80-150MB    |
| > 10MB   | 25+   | 6-15s        | 150-250MB   |

**Recommendation:** For PDFs > 10MB, consider queue processing

---

## 🎓 Understanding the Code Flow

### Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│  1. USER SIGNS DOCUMENT (Frontend)                         │
│     - Drags template onto PDF canvas                       │
│     - Clicks "Sign Document"                                │
└─────────────────────┬───────────────────────────────────────┘
                      ▼
┌─────────────────────────────────────────────────────────────┐
│  2. AJAX POST to processDocumentSigning()                   │
│     Request data:                                           │
│     - template_id: 1                                        │
│     - positioning_data: {page, position, size, canvas}      │
│     - _token: csrf_token                                    │
└─────────────────────┬───────────────────────────────────────┘
                      ▼
┌─────────────────────────────────────────────────────────────┐
│  3. CONTROLLER: DigitalSignatureController                  │
│     a. Validate request                                     │
│     b. Check authorization                                  │
│     c. Create cryptographic signature (CMS)                 │
│     d. Save to document_signatures table                    │
└─────────────────────┬───────────────────────────────────────┘
                      ▼
┌─────────────────────────────────────────────────────────────┐
│  4. PDF MERGING SERVICE (NEW!)                              │
│     a. Get original PDF path                                │
│     b. Generate QR code image                               │
│     c. Call PDFSignatureService::mergeSignatureIntoPDF()    │
└─────────────────────┬───────────────────────────────────────┘
                      ▼
┌─────────────────────────────────────────────────────────────┐
│  5. MERGE PROCESS (PDFSignatureService)                     │
│     a. Initialize FPDI                                      │
│     b. Import all pages from original PDF                   │
│     c. For target page:                                     │
│        - Convert canvas coordinates to PDF mm               │
│        - Add signature image at position                    │
│        - Add QR code to bottom-right                        │
│     d. Save as new PDF                                      │
└─────────────────────┬───────────────────────────────────────┘
                      ▼
┌─────────────────────────────────────────────────────────────┐
│  6. UPDATE DATABASE                                         │
│     - document_signatures.final_pdf_path = signed PDF path  │
│     - approval_requests.status = 'signed'                   │
└─────────────────────┬───────────────────────────────────────┘
                      ▼
┌─────────────────────────────────────────────────────────────┐
│  7. RETURN JSON RESPONSE                                    │
│     {                                                       │
│       "success": true,                                      │
│       "signed_pdf_available": true,                         │
│       "verification_url": "..."                             │
│     }                                                       │
└─────────────────────────────────────────────────────────────┘
```

---

## 📚 Key Files Reference

### 1. Service
- **Path:** `app/Services/PDFSignatureService.php`
- **Methods:**
  - `mergeSignatureIntoPDF()` - Main merging method
  - `addSignatureToPage()` - Add signature image
  - `addQRCodeToPage()` - Add QR code
  - `generateQRCodeImage()` - Generate QR image
  - `cleanupTempFiles()` - Remove temp files

### 2. Controller
- **Path:** `app/Http/Controllers/DigitalSignature/DigitalSignatureController.php`
- **Method:** `processDocumentSigning()`
- **Lines:** 242-430 (PDF merging integrated at 351-396)

### 3. Seeders
- **Digital Signature:** `database/seeders/DigitalSignatureSeeder.php`
- **Template:** `database/seeders/SignatureTemplateSeeder.php`
- **Main:** `database/seeders/DatabaseSeeder.php`

### 4. Migration
- **Document Signatures:** `database/migrations/2025_10_17_142421_create_document_signatures_table.php`
- **Column:** `final_pdf_path` (line 29)

---

## 💡 Pro Tips

### Tip 1: Test with Sample Signature
Create a simple signature image programmatically:
```bash
# Using ImageMagick
convert -size 400x200 xc:white \
  -font Arial -pointsize 40 -fill black \
  -gravity center -annotate +0+0 "Dr. Budi Santoso" \
  storage/app/signature-templates/default-signature.png
```

### Tip 2: Monitor Performance
Add to `.env`:
```env
LOG_LEVEL=debug
```

Then check logs for timing:
```bash
grep "PDF loaded successfully" storage/logs/laravel.log
grep "Signature embedded" storage/logs/laravel.log
```

### Tip 3: Batch Testing
Create multiple approval requests quickly:
```bash
php artisan tinker
>>> factory(App\Models\ApprovalRequest::class, 10)->create()
```

### Tip 4: Debug Coordinates
Add this to `PDFSignatureService.php` after line 210:
```php
Log::debug('Signature placement debug', [
    'canvas_px' => ['x' => $position['x'], 'y' => $position['y']],
    'pdf_mm' => ['x' => $x, 'y' => $y],
    'scale' => ['x' => $scaleX, 'y' => $scaleY]
]);
```

### Tip 5: Clear Cache After Changes
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

## 🎉 Success Indicators

You'll know it's working when:

1. ✅ Seeder runs without errors
2. ✅ Template visible in admin panel
3. ✅ Signing process completes successfully
4. ✅ `signed-documents/` folder contains new PDF
5. ✅ Database `final_pdf_path` is populated
6. ✅ Downloaded PDF shows visible signature
7. ✅ QR code visible on PDF
8. ✅ Scanning QR redirects to verification page
9. ✅ No errors in `storage/logs/laravel.log`

---

## 🔗 Related Documentation

- **Full Implementation Details:** `TODO_9_PDF_MERGING_IMPLEMENTATION.md`
- **Previous Features:** `IMPLEMENTATION_SUMMARY.md`
- **Testing Guide:** `TESTING_CHECKLIST.md`
- **Changelog:** `CHANGELOG_DRAG_DROP.md`

---

## 🆘 Need Help?

**Check logs first:**
```bash
tail -100 storage/logs/laravel.log
```

**Common log search terms:**
- "PDF signature merge"
- "Failed to embed signature"
- "Template image not found"
- "Signature embedded successfully"

**Database queries:**
```sql
-- Check signature templates
SELECT id, name, is_active, is_default, signature_image_path
FROM signature_templates;

-- Check signed documents
SELECT id, final_pdf_path, signature_status, signed_at
FROM document_signatures
ORDER BY id DESC LIMIT 5;

-- Check approval flow
SELECT id, status, user_id, document_path, approved_at
FROM approval_requests
ORDER BY id DESC LIMIT 5;
```

---

**Setup Time:** ~5 minutes
**First Test:** ~2 minutes
**Full Workflow Test:** ~5 minutes

**Total Time to Production:** < 15 minutes ⚡

Good luck! 🚀
