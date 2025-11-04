# 🚀 QUICK START GUIDE: Drag & Drop Signature Template

## ⚡ **5-Minute Setup**

### **Step 1: Create Digital Signature Key** (if not exists)
```bash
php artisan tinker
```

```php
$service = new \App\Services\DigitalSignatureService();
$admin = \App\Models\User::where('roles', 'admin')->first();
$signature = $service->createDigitalSignature('System Default Signature', $admin->id, 5);
echo "Digital Signature Created: {$signature->signature_id}\n";
exit;
```

### **Step 2: Upload Kaprodi Signature Image**

1. Prepare signature image:
   - Format: PNG with transparent background
   - Size: 400x200px (or similar ratio)
   - Content: Kaprodi's signature

2. Place image:
   ```bash
   # Create directory if not exists
   mkdir -p storage/app/public/signature_templates
   
   # Copy your signature image (replace with your actual file)
   cp /path/to/ttd_kaprodi.png storage/app/public/signature_templates/
   
   # Create symlink if not exists
   php artisan storage:link
   ```

### **Step 3: Create Signature Template**
```bash
php artisan tinker
```

```php
$kaprodi = \App\Models\User::where('roles', 'kaprodi')->first();

$template = \App\Models\SignatureTemplate::create([
    'name' => 'Template TTD Kaprodi Teknik Informatika',
    'description' => 'Template resmi untuk penandatanganan dokumen mahasiswa Prodi TI',
    'signature_image_path' => 'signature_templates/ttd_kaprodi.png',
    'kaprodi_id' => $kaprodi->id,
    'status' => 'active',
    'is_default' => true,
    'canvas_width' => '800',
    'canvas_height' => '600',
    'text_config' => [
        'kaprodi_name' => [
            'text' => $kaprodi->name,
            'font_size' => 14,
            'font_weight' => 'bold',
            'color' => '#000000'
        ],
        'nidn' => [
            'text' => 'NIDN: ' . ($kaprodi->NIDN ?? ''),
            'font_size' => 12,
            'color' => '#000000'
        ]
    ],
    'layout_config' => \App\Models\SignatureTemplate::getDefaultLayoutConfig()
]);

echo "Template created successfully!\n";
echo "ID: {$template->id}\n";
echo "Name: {$template->name}\n";
exit;
```

### **Step 4: Test the Feature**

1. **Login as Admin/Kaprodi:**
   ```
   Email: kaprodi.informatika@umt.ac.id
   Password: password
   ```

2. **Approve a Document:**
   - Go to: Admin Panel → Approval Requests
   - Find pending request
   - Click "Approve"

3. **Login as User:**
   ```
   Email: user@umt.ac.id  
   Password: password
   ```

4. **Sign the Document:**
   - Go to: My Documents → Approval Status
   - Find approved document
   - Click "Sign Document"
   - **NEW UI WILL LOAD!** 🎉

5. **Drag & Drop:**
   - Drag template from bottom section
   - Drop on PDF preview
   - Adjust position and size
   - Click "Preview"
   - Click "Confirm & Sign"

---

## 🎯 **Expected Result**

### **Before (Old UI):**
```
User sees canvas → User draws signature manually → Submit
```

### **After (New UI):**
```
User sees PDF preview → User drags template → Drop on PDF → Resize → Submit
```

---

## 🐛 **Troubleshooting**

### **Issue: "No templates available"**
**Solution:**
```bash
php artisan tinker
\App\Models\SignatureTemplate::count(); // Should be > 0
```

### **Issue: "Failed to load templates"**
**Solution:** Check route is registered:
```bash
php artisan route:list | grep templates
# Should show: GET user/signature/sign/{approvalRequestId}/templates
```

### **Issue: "Signature image not showing"**
**Solution:** Verify symlink:
```bash
ls -la public/storage  # Should link to storage/app/public
# If not: php artisan storage:link
```

### **Issue: "PDF not rendering"**
**Solution:** Check PDF.js loaded:
- Open browser console
- Should see: `pdfjsLib` object
- If undefined: CDN might be blocked, use local copy

### **Issue: "Signing fails"**
**Solution:** Check DigitalSignature exists:
```bash
php artisan tinker
\App\Models\DigitalSignature::active()->valid()->first(); // Should return signature
```

---

## 📸 **Screenshot Locations**

If you need screenshots for documentation:

1. **Template Grid:** Bottom section of sign-document page
2. **Drag Action:** While dragging template (opacity changes)
3. **Placed Signature:** After drop (shows resize handles)
4. **Control Panel:** Sliders for fine adjustment
5. **Preview Modal:** Before final signing
6. **Success:** After signing completes

---

## 🔄 **Rollback (if needed)**

If you want to revert to old canvas-based UI:

```bash
cd /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/user

# Backup new version
mv sign-document.blade.php sign-document-dragdrop.blade.php

# Restore old version
mv sign-document-old.blade.php sign-document.blade.php

echo "Reverted to canvas-based UI"
```

Then update route to not load templates.

---

## 📞 **Support**

If you encounter issues:

1. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check browser console:**
   - Press F12 → Console tab
   - Look for JavaScript errors

3. **Check database:**
   ```bash
   php artisan tinker
   \App\Models\SignatureTemplate::all();
   \App\Models\DigitalSignature::all();
   ```

---

## ✅ **Success Indicators**

You'll know it's working when:

- ✅ New UI loads with PDF preview
- ✅ Template grid shows at bottom
- ✅ Drag & drop works smoothly
- ✅ Signature appears on PDF
- ✅ Resize handles visible
- ✅ Preview modal works
- ✅ Signing completes successfully
- ✅ Template usage_count increments

---

**Happy Signing! 🎉**
