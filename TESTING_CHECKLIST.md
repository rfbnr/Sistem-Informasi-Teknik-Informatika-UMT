# ✅ TESTING CHECKLIST: Drag & Drop Signature Template

## 📋 **Pre-Testing Setup**

### ☐ **1. Create Digital Signature Key**
```bash
php artisan tinker
```
```php
$service = new \App\Services\DigitalSignatureService();
$admin = \App\Models\User::where('roles', 'admin')->first();
$signature = $service->createDigitalSignature('System Default Signature', $admin->id, 5);
echo "✓ Digital Signature Created: {$signature->signature_id}\n";
exit;
```

**Expected Result:** Should create signature key with 2048-bit RSA

---

### ☐ **2. Upload Kaprodi Signature Image**
```bash
# Create directory
mkdir -p storage/app/public/signature_templates

# Copy your signature image
# cp /path/to/your/ttd_kaprodi.png storage/app/public/signature_templates/

# Create symlink
php artisan storage:link
```

**Expected Result:** 
- Directory created
- Image uploaded (PNG, ~400x200px, transparent background)
- Symlink created: `public/storage → storage/app/public`

---

### ☐ **3. Create Signature Template**
```bash
php artisan tinker
```
```php
$kaprodi = \App\Models\User::where('roles', 'kaprodi')->first();
$template = \App\Models\SignatureTemplate::create([
    'name' => 'Template TTD Kaprodi TI',
    'description' => 'Template resmi Kaprodi Teknik Informatika',
    'signature_image_path' => 'signature_templates/ttd_kaprodi.png',
    'kaprodi_id' => $kaprodi->id,
    'status' => 'active',
    'is_default' => true,
    'canvas_width' => '800',
    'canvas_height' => '600',
    'text_config' => \App\Models\SignatureTemplate::getDefaultTextConfig(),
    'layout_config' => \App\Models\SignatureTemplate::getDefaultLayoutConfig()
]);
echo "✓ Template created: ID {$template->id}\n";
exit;
```

**Expected Result:** Template record created in database

---

## 🧪 **Functional Testing**

### ☐ **TEST 1: Page Load**
**Steps:**
1. Login as user: `user@umt.ac.id` / `password`
2. Navigate to approval status page
3. Click "Sign Document" on approved request

**Expected Result:**
- ✅ New UI loads (not old canvas UI)
- ✅ PDF preview shows on top
- ✅ Template grid shows at bottom
- ✅ No JavaScript errors in console (F12)

**Screenshot Location:** `screenshots/01-page-load.png`

---

### ☐ **TEST 2: PDF Rendering**
**Steps:**
1. Wait for PDF to load
2. Check PDF quality
3. Try page navigation (if multi-page)

**Expected Result:**
- ✅ PDF renders clearly (not blurry)
- ✅ Page number shows: "Page 1 of X"
- ✅ Previous/Next buttons work (if multi-page)
- ✅ No PDF.js errors in console

**Screenshot Location:** `screenshots/02-pdf-render.png`

---

### ☐ **TEST 3: Template Loading**
**Steps:**
1. Check template grid at bottom
2. Verify template info displays

**Expected Result:**
- ✅ Template card shows with image
- ✅ Template name displays
- ✅ Kaprodi name shows
- ✅ Usage count shows
- ✅ "DEFAULT" badge if is_default=true

**Screenshot Location:** `screenshots/03-template-grid.png`

---

### ☐ **TEST 4: Drag & Drop**
**Steps:**
1. Click and hold template card
2. Drag to PDF preview area
3. Drop on PDF

**Expected Result:**
- ✅ Cursor changes to "grabbing"
- ✅ Template becomes semi-transparent while dragging
- ✅ PDF area highlights when dragging over
- ✅ Signature appears at drop position
- ✅ Border shows around placed signature
- ✅ Resize handles appear at corners
- ✅ Delete button (×) appears top-right

**Screenshot Location:** `screenshots/04-drag-drop.png`

---

### ☐ **TEST 5: Signature Manipulation**
**Steps:**
1. **Drag:** Click signature and drag to new position
2. **Resize:** Drag corner handles to resize
3. **Delete:** Click × button to remove

**Expected Result:**
- ✅ Signature moves when dragged
- ✅ Signature resizes proportionally
- ✅ Signature removes when × clicked
- ✅ Can place signature again after delete

**Screenshot Location:** `screenshots/05-manipulation.png`

---

### ☐ **TEST 6: Control Panel**
**Steps:**
1. After placing signature, check control panel shows
2. Adjust width slider
3. Adjust height slider
4. Adjust position sliders

**Expected Result:**
- ✅ Control panel appears after placement
- ✅ Sliders work smoothly
- ✅ Values update in real-time
- ✅ Signature size/position changes accordingly

**Screenshot Location:** `screenshots/06-control-panel.png`

---

### ☐ **TEST 7: Preview Modal**
**Steps:**
1. Place signature on PDF
2. Check confirmation checkbox
3. Click "Preview" button

**Expected Result:**
- ✅ Modal opens
- ✅ Shows final document with signature overlay
- ✅ Document details display correctly
- ✅ Timestamp shows
- ✅ "Confirm & Sign" button enabled

**Screenshot Location:** `screenshots/07-preview-modal.png`

---

### ☐ **TEST 8: Sign Document**
**Steps:**
1. In preview modal, click "Confirm & Sign"
2. Wait for processing
3. Check response

**Expected Result:**
- ✅ Loading overlay appears
- ✅ "Processing Digital Signature..." message shows
- ✅ No errors in console
- ✅ Success message appears
- ✅ Redirects to status page

**Screenshot Location:** `screenshots/08-signing-process.png`

---

### ☐ **TEST 9: Verify Signed Document**
**Steps:**
1. After redirect, check approval status page
2. Find the signed document
3. Check status

**Expected Result:**
- ✅ Status changed to "Sudah Ditandatangani"
- ✅ Badge color changed (blue/info)
- ✅ Signed date shows
- ✅ Can view document (if view button exists)

**Screenshot Location:** `screenshots/09-signed-status.png`

---

### ☐ **TEST 10: Database Verification**
**Steps:**
```bash
php artisan tinker
```
```php
// Check document signature
$ds = \App\Models\DocumentSignature::latest()->first();
echo "Status: {$ds->signature_status}\n";
echo "Signed at: {$ds->signed_at}\n";
echo "Positioning data: " . json_encode($ds->positioning_data) . "\n";

// Check template usage
$template = \App\Models\SignatureTemplate::find(1);
echo "Template usage count: {$template->usage_count}\n";
```

**Expected Result:**
- ✅ signature_status = 'signed'
- ✅ signed_at has timestamp
- ✅ positioning_data contains template_id, page, position, size
- ✅ Template usage_count incremented

---

## 🚨 **Error Scenario Testing**

### ☐ **ERROR TEST 1: No Templates**
**Steps:**
1. Temporarily deactivate all templates
2. Try to load sign page

**Expected Result:**
- ✅ Shows "No templates available" message
- ✅ No JavaScript errors
- ✅ User can still go back

---

### ☐ **ERROR TEST 2: Network Error**
**Steps:**
1. Open DevTools → Network tab
2. Enable "Offline" mode
3. Try to sign document

**Expected Result:**
- ✅ Shows error message
- ✅ Doesn't break UI
- ✅ Loading overlay closes

---

### ☐ **ERROR TEST 3: Unauthorized**
**Steps:**
1. Login as user A
2. Copy sign URL
3. Logout, login as user B
4. Try to access copied URL

**Expected Result:**
- ✅ Shows "Unauthorized" error
- ✅ Redirects to home/status page
- ✅ No data exposed

---

### ☐ **ERROR TEST 4: Missing Confirmation**
**Steps:**
1. Place signature
2. Don't check confirmation checkbox
3. Try to click "Sign Document"

**Expected Result:**
- ✅ Button is disabled
- ✅ Cannot proceed without confirmation

---

## 📱 **Mobile/Responsive Testing**

### ☐ **MOBILE TEST 1: Touch Drag**
**Device:** Smartphone (or Chrome DevTools mobile emulation)

**Steps:**
1. Load sign page on mobile
2. Touch and drag template
3. Drop on PDF

**Expected Result:**
- ✅ Touch drag works
- ✅ Layout responsive
- ✅ All controls accessible
- ✅ Buttons not overlapping

---

### ☐ **MOBILE TEST 2: Modal View**
**Steps:**
1. Open preview modal on mobile
2. Check content visibility

**Expected Result:**
- ✅ Modal fits screen
- ✅ Content scrollable
- ✅ Buttons accessible
- ✅ Can close modal

---

## ⚡ **Performance Testing**

### ☐ **PERF TEST 1: Large PDF**
**Steps:**
1. Test with 10+ page PDF
2. Navigate between pages
3. Place signature

**Expected Result:**
- ✅ Pages load reasonably fast (<2 seconds)
- ✅ No UI freezing
- ✅ Memory usage acceptable

---

### ☐ **PERF TEST 2: Multiple Templates**
**Steps:**
1. Create 10+ templates
2. Load sign page
3. Check grid loading

**Expected Result:**
- ✅ Grid renders quickly
- ✅ Images load progressively
- ✅ Drag & drop still smooth

---

## 🔐 **Security Testing**

### ☐ **SEC TEST 1: CSRF Protection**
**Steps:**
1. Open DevTools → Console
2. Try to submit without CSRF token:
```javascript
fetch('/user/signature/sign/1/process', {method: 'POST', body: {}})
```

**Expected Result:**
- ✅ Request rejected (419 error)
- ✅ "CSRF token mismatch" error

---

### ☐ **SEC TEST 2: Authorization Check**
**Steps:**
1. Try to access another user's sign URL

**Expected Result:**
- ✅ Blocked with 403/Unauthorized
- ✅ Cannot sign other's documents

---

## 📊 **Browser Compatibility**

Test on multiple browsers:

- ☐ **Chrome** (Latest)
- ☐ **Firefox** (Latest)
- ☐ **Safari** (if Mac)
- ☐ **Edge** (Latest)

**Expected:** All features work consistently

---

## ✅ **Final Checklist**

### **Before Deployment:**
- ☐ All tests passed
- ☐ No console errors
- ☐ Performance acceptable
- ☐ Mobile works
- ☐ Security verified
- ☐ Documentation reviewed
- ☐ Backup created
- ☐ Rollback plan ready

### **After Deployment:**
- ☐ Monitor Laravel logs
- ☐ Check browser console on production
- ☐ Test with real users
- ☐ Gather feedback
- ☐ Plan PDF merging implementation (TODO #9)

---

## 📝 **Test Results Template**

```
TEST SESSION REPORT
Date: __________________
Tester: __________________
Environment: [ ] Local [ ] Staging [ ] Production
Browser: __________________

RESULTS:
✅ Passed: _____ / _____
❌ Failed: _____ / _____
⚠️ Issues: _____ / _____

CRITICAL ISSUES:
1. _______________________________________
2. _______________________________________

NOTES:
_________________________________________
_________________________________________

RECOMMENDATION:
[ ] Approve for deployment
[ ] Requires fixes before deployment
[ ] Block deployment
```

---

**Happy Testing! 🧪**
