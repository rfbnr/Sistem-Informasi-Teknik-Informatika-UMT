# Log Usage Mapping & Update Plan

## 📊 SignatureAuditLog Usage Map

### 1. **DigitalSignatureService.php**

#### Location 1: `createDigitalSignature()` - Line 100

**Current Implementation:**

```php
SignatureAuditLog::create([
    'kaprodi_id' => $createdBy ?? Auth::id(),
    'action' => 'create_digital_signature',
    'status_to' => $signature->status,
    'description' => 'Digital signature created',
    'metadata' => [
        'signature_id' => $signature->signature_id,
    ],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'performed_at' => now()
]);
```

**✅ UPDATE NEEDED:** Use `SignatureAuditLog::createMetadata()` helper

---

#### Location 2: `revokeDigitalSignature()` - Line 445

**Current Implementation:**

```php
SignatureAuditLog::create([
    'kaprodi_id' => Auth::id(),
    'action' => 'revoke_digital_signature',
    'status_to' => $digitalSignature->status,
    'description' => 'Digital signature revoked: ' . ($reason ?? 'No reason provided'),
    'performed_at' => now()
]);
```

**⚠️ ISSUE:** Missing `ip_address`, `user_agent`, and `metadata`
**✅ UPDATE NEEDED:** Add missing fields + use metadata helper

---

### 2. **DigitalSignatureController.php**

#### Location 1: `signDocument()` - Line 501

**Current Implementation:**

```php
SignatureAuditLog::create([
    'document_signature_id' => $documentSignature->id,
    'approval_request_id' => $approvalRequestId,
    'user_id' => Auth::id(),
    'action' => 'document_signed',
    'status_to' => DocumentSignature::STATUS_SIGNED,
    'description' => "Document '{$approvalRequest->document_name}' signed successfully",
    'metadata' => [
        'template_id' => $request->template_id ?? null,
        'signature_id' => $digitalSignature->signature_id,
        'pdf_merged' => $signedPdfPath !== null
    ],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'performed_at' => now()
]);
```

**✅ UPDATE NEEDED:** Merge with `SignatureAuditLog::createMetadata()` + add duration tracking

---

#### Location 2: `signDocument()` catch block - Line 537

**Current Implementation:**

```php
SignatureAuditLog::create([
    'document_signature_id' => $documentSignature->id ?? null,
    'approval_request_id' => $approvalRequestId,
    'user_id' => Auth::id(),
    'action' => 'signing_failed',
    'description' => "Document signing failed: {$e->getMessage()}",
    'metadata' => [
        'error_message' => $e->getMessage(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine()
    ],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'performed_at' => now()
]);
```

**✅ UPDATE NEEDED:** Add `error_code` to metadata + use metadata helper

---

### 3. **SignatureTemplateController.php**

#### Location 1: `update()` - Line 279

**Current Implementation:**

```php
SignatureAuditLog::create([
    'kaprodi_id' => Auth::id(),
    'action' => SignatureAuditLog::ACTION_TEMPLATE_UPDATED,
    'description' => "Template '{$template->name}' has been updated",
    'metadata' => [
        'template_id' => $template->id,
        'changes' => array_keys($updateData)
    ],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'performed_at' => now()
]);
```

**✅ UPDATE NEEDED:** Use metadata helper + add `status_from` & `status_to`

---

#### Location 2: `destroy()` - Line 346

**Current Implementation:**

```php
SignatureAuditLog::create([
    'kaprodi_id' => Auth::id(),
    'action' => 'template_deleted',
    'description' => "Template '{$templateName}' has been deleted",
    'metadata' => [
        'template_id' => $id,
        'template_name' => $templateName
    ],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'performed_at' => now()
]);
```

**✅ UPDATE NEEDED:** Use metadata helper

---

### 4. **Model Files (Observer Patterns)**

#### ApprovalRequest.php - Line 95, 516

#### DocumentSignature.php - Line 78, 468

#### DigitalSignature.php - Line 117

#### SignatureTemplate.php - Line 66, 177, 288

**Common Pattern:**

-   All need to be updated to use `SignatureAuditLog::createMetadata()`
-   Some missing `ip_address` and `user_agent`
-   Need to standardize metadata structure

---

## 📊 SignatureVerificationLog Usage Map

### 1. **VerificationService.php**

#### Location: `logVerificationAttempt()` - Line 483

**Current Implementation:**

```php
$logData = [
    'document_signature_id' => $documentSignature->id,
    'approval_request_id' => $documentSignature->approval_request_id,
    'user_id' => Auth::id(),
    'verification_method' => $token ? 'token' : 'id',
    'verification_token_hash' => $token ? hash('sha256', $token) : null,
    'is_valid' => $verificationResult['is_valid'],
    'result_status' => $verificationResult['is_valid'] ? 'success' : 'failed',
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'referrer' => request()->headers->get('referer'),
    'metadata' => [
        'verification_id' => $verificationResult['verification_id'],
        'message' => $verificationResult['message']
    ],
    'verified_at' => now()
];

SignatureVerificationLog::create($logData);
```

**✅ UPDATE NEEDED:**

-   Use `SignatureVerificationLog::createMetadata()` helper
-   Add `verification_duration_ms` tracking
-   Add `previous_verification_count`
-   Add `failed_reason` categorization using `categorizeFailedReason()`
-   Optionally add geolocation data

---

## 🔧 Update Strategy

### Phase 1: Create Helper Trait (Optional - for DRY)

```php
// app/Traits/LogsActivity.php
trait LogsActivity
{
    protected function createAuditLog($data)
    {
        $metadata = SignatureAuditLog::createMetadata($data['metadata'] ?? []);

        return SignatureAuditLog::create(array_merge($data, [
            'metadata' => $metadata,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'performed_at' => $data['performed_at'] ?? now()
        ]));
    }

    protected function createVerificationLog($data)
    {
        $metadata = SignatureVerificationLog::createMetadata($data['metadata'] ?? []);

        return SignatureVerificationLog::create(array_merge($data, [
            'metadata' => $metadata,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'referrer' => $data['referrer'] ?? request()->headers->get('referer'),
            'verified_at' => $data['verified_at'] ?? now()
        ]));
    }
}
```

### Phase 2: Update Order

1. ✅ Models enhanced (DONE)
2. 🔄 Update VerificationService first (highest impact)
3. 🔄 Update DigitalSignatureService
4. 🔄 Update Controllers
5. 🔄 Update Model Observers
6. ✅ Test all changes

### Phase 3: Testing Checklist

-   [ ] Test signing document - check audit log
-   [ ] Test failed signing - check error metadata
-   [ ] Test verification - check verification log
-   [ ] Test failed verification - check failed_reason
-   [ ] Test template CRUD - check audit logs
-   [ ] Check computed properties work (device_type, browser_name, etc)
-   [ ] Check scope methods work
-   [ ] Test statistics methods

---

## 📋 Summary of Required Changes

### SignatureAuditLog

-   **Total Locations:** ~16 occurrences
-   **Files to Update:** 9 files
-   **Main Changes:**
    -   Replace manual metadata with `SignatureAuditLog::createMetadata()`
    -   Add `duration_ms` tracking where applicable
    -   Add `error_code` for failed actions
    -   Ensure all logs have `ip_address` and `user_agent`

### SignatureVerificationLog

-   **Total Locations:** 1 main location (VerificationService)
-   **Files to Update:** 1 file
-   **Main Changes:**
    -   Use `SignatureVerificationLog::createMetadata()`
    -   Add `verification_duration_ms` tracking
    -   Add `previous_verification_count`
    -   Use `categorizeFailedReason()` for failed attempts
    -   Optionally add geolocation

---

## 🎯 Next Steps

1. Review this mapping
2. Confirm update strategy
3. Begin Phase 2 updates
4. Test incrementally
5. Deploy
