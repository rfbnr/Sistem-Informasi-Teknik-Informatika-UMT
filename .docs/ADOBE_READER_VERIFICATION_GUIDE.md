# Adobe Reader Signature Verification Guide

**Document Version**: 1.0
**Last Updated**: November 22, 2025
**Target Users**: System Administrators, End Users

---

## Table of Contents

1. [Overview](#overview)
2. [Understanding Self-Signed Certificates](#understanding-self-signed-certificates)
3. [Opening Signed PDF in Adobe Reader](#opening-signed-pdf-in-adobe-reader)
4. [Verifying Signature Integrity](#verifying-signature-integrity)
5. [Trusting the Certificate](#trusting-the-certificate)
6. [Troubleshooting](#troubleshooting)
7. [FAQs](#faqs)

---

## Overview

### What is Digital Signature Verification?

Digital signature verification confirms:
- **Who signed the document** (Authentication)
- **Document has not been modified** (Integrity)
- **Signer cannot deny signing** (Non-repudiation)

### What to Expect

For documents signed by Universitas Muhammadiyah Tangerang's internal system:

| Status | Meaning | Action Required |
|--------|---------|-----------------|
| ⚠️ "Validity Unknown" | Normal for self-signed certificates | Trust the certificate (one-time) |
| ✅ "Valid Signature" | Certificate is trusted | None - signature is valid |
| ❌ "Invalid Signature" | Document modified after signing | Do not trust the document |

---

## Understanding Self-Signed Certificates

### What is a Self-Signed Certificate?

A self-signed certificate is a digital certificate **signed by the organization itself** (UMT) rather than a third-party Certificate Authority (CA) like DigiCert or Let's Encrypt.

### Why Does Adobe Show "Validity Unknown"?

Adobe Reader shows this message because:

1. ✅ The certificate is **valid and correctly formatted**
2. ✅ The signature is **cryptographically correct**
3. ⚠️ The certificate is **not in Adobe's trusted list** (AATL)
4. ⚠️ The certificate is **not in your local trust store**

**This is expected and normal for internal university documents.**

### Is It Safe?

**YES** - for internal UMT documents:

- ✅ Document integrity is still verified
- ✅ Modifications can still be detected
- ✅ Signer identity is still authenticated
- ⚠️ You just need to manually trust the certificate once

**NO** - for external documents from unknown sources:
- ⚠️ Only trust certificates from known organizations
- ⚠️ Verify certificate details match expected signer
- ⚠️ Contact the organization if unsure

---

## Opening Signed PDF in Adobe Reader

### Step 1: Download and Open

1. Download Adobe Acrobat Reader DC (free)
   - Windows/Mac: https://get.adobe.com/reader/
   - Ensure latest version for best compatibility

2. Open the signed PDF file
   - Double-click the PDF file
   - Or File → Open in Adobe Reader

### Step 2: View Signature Panel

When you open a signed PDF, Adobe Reader will show:

```
┌─────────────────────────────────────────┐
│ ⚠️ At least one signature has problems  │
│                                         │
│ This document contains a signature.     │
│ Click for details.                      │
└─────────────────────────────────────────┘
```

**This is normal!** Click the banner to see details.

### Step 3: Open Signature Panel

**Option A**: Click the warning banner

**Option B**: Manual navigation
1. View → Show/Hide → Navigation Panes → Signatures
2. Or click the signature icon on the left sidebar

You should see:

```
Signatures Panel
├── 📝 Signed by: Dr. Budi Santoso, M.Kom
│   └── ⚠️ Signature validity is UNKNOWN
│       └── Signed on: [Date and Time]
```

---

## Verifying Signature Integrity

### Step 1: Open Signature Properties

1. In the Signature Panel, click the signature
2. Or right-click the visible signature box in the PDF
3. Select "Show Signature Properties"

### Step 2: Review Signature Properties

A dialog will open with multiple tabs:

#### **Verification Summary Tab**

```
┌────────────────────────────────────────┐
│ Signature Validation Status            │
│                                        │
│ ⚠️ Signature validity is UNKNOWN       │
│                                        │
│ Reason:                                │
│ • The signer's identity is unknown     │
│   because it has not been included in  │
│   your list of trusted identities and  │
│   none of its parent certificates are  │
│   trusted identities.                  │
│                                        │
│ ✅ Document has not been modified      │
│    since this signature was applied    │
└────────────────────────────────────────┘
```

**Key Points**:
- ✅ "Document has not been modified" = Signature is **valid**
- ⚠️ "Signer's identity is unknown" = Certificate **not trusted yet**

#### **Signer Tab**

Verify the signer information:

| Field | Expected Value |
|-------|----------------|
| Name | Dr. Budi Santoso, M.Kom |
| Organization | Universitas Muhammadiyah Tangerang |
| Organizational Unit | Fakultas Teknik - Program Studi Teknik Informatika |
| Email | ridwanfebnur88@gmail.com |
| Location | Universitas Muhammadiyah Tangerang |
| Reason | Document Approval |

**Verification**:
- ✅ Does the name match the expected approver?
- ✅ Is the organization correct?
- ✅ Is this the person who should have signed?

#### **Legal Tab**

Shows signature legal information:

| Field | Value |
|-------|-------|
| Signing Time | Date and time of signing |
| Location | Universitas Muhammadiyah Tangerang |
| Reason | Document Approval |

### Step 3: Verify Document Integrity

**Critical Check**: Look for this message:
```
✅ "Document has not been modified since this signature was applied"
```

**Possible Status Messages**:

| Message | Meaning | Action |
|---------|---------|--------|
| ✅ "Document has not been modified" | Safe - integrity verified | Proceed |
| ❌ "Document has been altered or corrupted" | Unsafe - do not trust | Reject document |
| ❌ "Invalid signature" | Signature broken | Contact sender |

---

## Trusting the Certificate

### When to Trust a Certificate

**ONLY** trust certificates when:
- ✅ You verified the signer's identity matches expectation
- ✅ The organization is Universitas Muhammadiyah Tangerang
- ✅ Document integrity check passed ("not been modified")
- ✅ You received the document through official channels

**DO NOT** trust certificates if:
- ❌ Signer identity doesn't match expected person
- ❌ Organization is unknown or suspicious
- ❌ Document shows as modified or corrupted
- ❌ You received the document from untrusted source

### How to Trust the Certificate

#### Method 1: Trust During Verification (Recommended)

1. Open Signature Properties (right-click signature)
2. Click "Show Signer's Certificate" button
3. In the Certificate Viewer, click "Trust" tab
4. Click "Add to Trusted Identities..." button
5. A new dialog appears:

```
┌────────────────────────────────────────┐
│ Import Contact Settings                │
│                                        │
│ ☑ Use this certificate as a trusted    │
│   root                                 │
│                                        │
│ I trust this certificate for:          │
│ ☑ Signatures and certified documents   │
│ ☐ Dynamic content                      │
│ ☐ High privilege operations            │
│                                        │
│        [OK]  [Cancel]                  │
└────────────────────────────────────────┘
```

6. Check the following:
   - ☑ "Use this certificate as a trusted root"
   - ☑ "Signatures and certified documents"

7. Click "OK"
8. Click "Close" on all dialogs
9. **Close and reopen the PDF** to see the updated status

#### Method 2: Trust from Certificate Manager

1. Edit → Preferences (or Ctrl+K / Cmd+K)
2. Select "Signatures" category on the left
3. Click "More..." button in "Identities & Trusted Certificates"
4. Select "Trusted Certificates" on the left
5. Click "Add Contacts" or "Import"
6. Browse to the certificate file (if you have it)
7. Or extract from the PDF:
   - Right-click signature
   - Show Signature Properties
   - Show Signer's Certificate
   - Details tab → "Export Contact"
   - Save as `.cer` file
8. Import the saved certificate
9. Set trust settings as in Method 1

### After Trusting

**Close and reopen the PDF**. You should now see:

```
┌────────────────────────────────────────┐
│ ✅ Signed and all signatures are valid │
│                                        │
│ This document has been signed by:      │
│ Dr. Budi Santoso, M.Kom               │
│                                        │
│ Signed on: [Date and Time]            │
└────────────────────────────────────────┘
```

Signature Panel:
```
Signatures Panel
├── 📝 Signed by: Dr. Budi Santoso, M.Kom
│   └── ✅ Signature is VALID
│       └── Signed on: [Date and Time]
```

---

## Troubleshooting

### Problem 1: "At least one signature has problems"

**Symptoms**:
- Yellow warning banner at top
- "Signature validity is UNKNOWN"

**Diagnosis**:
1. Open Signature Properties
2. Check "Document has not been modified" message
   - ✅ Present → Certificate just needs to be trusted
   - ❌ Absent → Document may be corrupted

**Solution**:
- If integrity is valid → Follow [Trusting the Certificate](#trusting-the-certificate)
- If integrity is invalid → Contact document sender

### Problem 2: "Signature is invalid"

**Symptoms**:
- Red warning banner
- "Invalid signature" or "Signature broken"

**Possible Causes**:
1. Document was modified after signing
2. Signature data is corrupted
3. PDF file is damaged

**Solution**:
1. Do NOT trust this document
2. Request a new signed copy from sender
3. Verify file wasn't corrupted during download/transfer

### Problem 3: Signature appearance not visible

**Symptoms**:
- Signature panel shows signature exists
- No visible signature box in PDF

**Possible Causes**:
1. Signature appearance positioned outside visible area
2. Transparency issues
3. PDF rendering problem

**Solution**:
1. Zoom out to see entire page
2. Check near the QR code (expected position)
3. Update Adobe Reader to latest version
4. Try opening in different PDF reader for comparison

### Problem 4: "This document has been certified by..."

**Symptoms**:
- Blue banner instead of yellow/red
- "Certified" instead of "Signed"

**Explanation**:
This is actually a **good sign**! Certification is a type of signature that:
- ✅ Prevents most modifications
- ✅ Provides higher security
- ✅ Allows specific form filling (if configured)

**Action**: None required - this is normal and expected.

### Problem 5: Trust settings don't persist

**Symptoms**:
- Certificate trusted, but shows as untrusted after reopening
- Trust settings reset

**Possible Causes**:
1. Adobe Reader settings not saved
2. User permissions issue
3. Corporate policy override

**Solution**:
1. Run Adobe Reader as administrator (Windows)
2. Check Edit → Preferences → Security → Advanced Preferences
3. Ensure "Verify signatures when the document is opened" is checked
4. Re-trust the certificate with proper permissions
5. Contact IT if corporate policy prevents trust

### Problem 6: Certificate expired

**Symptoms**:
- "Certificate has expired" warning
- Signature shows as invalid due to expiration

**Explanation**:
Self-signed certificates have expiration dates. Our implementation uses 1-year validity.

**Solution**:
1. **For old documents**: Trust the certificate anyway if:
   - Document was signed BEFORE certificate expired
   - Document integrity is valid
   - Signer identity is verified

2. **For new documents**: Contact system administrator to:
   - Generate new signing certificate
   - Re-sign document with new certificate

---

## FAQs

### Q1: Why doesn't UMT use a "real" certificate authority?

**A**: For internal university documents, self-signed certificates are:
- ✅ More cost-effective (no annual CA fees)
- ✅ Fully controlled by university
- ✅ Equally secure for internal use
- ✅ Compliant with Indonesian digital signature regulations

External/public documents might require CA-signed certificates.

### Q2: Do I need to trust the certificate every time?

**A**: No! Trust the certificate **once** on your computer, and it will remain trusted for all future documents signed by that certificate.

### Q3: Can I verify signatures on mobile?

**A**: Yes, but with limitations:
- **Adobe Acrobat Reader Mobile** (iOS/Android):
  - Can view signatures
  - Can check integrity
  - Limited trust management
- **Browser PDF viewers**:
  - May not show signature details
  - Use Adobe Reader for full verification

### Q4: What if the document shows "modified"?

**A**: This means the PDF content was changed after signing.
- ❌ Do NOT trust the document
- Contact the sender for explanation
- Request a newly signed copy
- Report to system administrator if suspicious

### Q5: Can I print a signed PDF?

**A**: Yes! Printing is allowed. However:
- ✅ Digital signature remains in the file
- ⚠️ Printed copy has no digital signature protection
- ⚠️ Printed signature appearance may look like regular image
- Consider stamping printed copies with "Digital Original Available"

### Q6: How do I verify signature without Adobe Reader?

**A**: Alternative methods:
1. **PDF-XChange Viewer** (Windows) - supports signature verification
2. **Okular** (Linux) - basic signature support
3. **Online verification** - Use university's verification portal:
   - Upload PDF
   - System verifies signature automatically
   - No certificate trust required

### Q7: What happens if signing certificate is lost?

**A**: If the university's signing private key is lost:
- ✅ Old signatures remain valid (can still be verified)
- ⚠️ New documents cannot be signed with that certificate
- ⚠️ University must generate new certificate
- ⚠️ Users must trust the new certificate separately

### Q8: Can signatures be forged?

**A**: Digital signatures using proper cryptography are **extremely difficult** to forge:
- Private key never leaves the server
- Hash functions prevent content modification
- Certificate authenticates the signer
- **HOWEVER**: Always verify signer identity matches expectation

### Q9: How long are signatures valid?

**A**: Digital signatures remain valid as long as:
- ✅ Certificate was valid at signing time (1 year)
- ✅ Private key remains secure
- ✅ Hash algorithms remain cryptographically secure

**Best Practice**: Add trusted timestamps (RFC 3161) for long-term validity.

### Q10: Why two signatures on the same document?

**A**: Some workflows require multiple signatures:
- Document creator signs first
- Approver signs second
- Each signature is independent
- All must be verified separately

---

## Visual Guide

### Signature Appearance in PDF

Expected visual layout:

```
┌─────────────────────────────────────────────────┐
│                                                 │
│  [Document Content]                             │
│                                                 │
│                                                 │
│  ┌─────────┐  ┌──────────────────────────────┐ │
│  │         │  │ Digitally signed by:         │ │
│  │   QR    │  │ Dr. Budi Santoso, M.Kom     │ │
│  │  CODE   │  │ Date: 2025.11.21 16:44:05   │ │
│  │         │  │ Reason: Document Approval    │ │
│  └─────────┘  │ Location: Universitas...     │ │
│               └──────────────────────────────┘ │
│                                                 │
│  [More Document Content]                        │
│                                                 │
└─────────────────────────────────────────────────┘
```

### Certificate Trust Icons

| Icon | Meaning |
|------|---------|
| ⚠️ Yellow Triangle | Validity unknown - certificate not trusted |
| ✅ Green Checkmark | Valid signature - certificate trusted |
| ❌ Red X | Invalid signature - document modified or corrupted |
| 🔒 Blue Lock | Certified document - highest security level |
| ❓ Question Mark | Signature cannot be verified (missing data) |

---

## Technical Details

### Signature Format: PKCS#7 (adbe.pkcs7.detached)

Our implementation uses:
- **Format**: PKCS#7 / CMS (Cryptographic Message Syntax)
- **SubFilter**: `adbe.pkcs7.detached`
- **Hash Algorithm**: SHA-256 (256-bit)
- **Encryption**: RSA 2048-bit
- **Standard**: ISO 32000-1 (PDF 1.7)

### Certificate Details

| Field | Value |
|-------|-------|
| Version | X.509 v3 |
| Key Type | RSA |
| Key Size | 2048 bits |
| Signature Algorithm | SHA-256 with RSA |
| Validity Period | 1 year |
| Issuer | Universitas Muhammadiyah Tangerang |
| Subject | Dr. Budi Santoso, M.Kom |

### Signature Integrity Mechanism

1. **Hash Calculation**: SHA-256 hash of document bytes
2. **Encryption**: Hash encrypted with private key
3. **Embedding**: Encrypted hash embedded in PDF
4. **Verification**: Adobe Reader:
   - Recalculates hash of current document
   - Decrypts embedded hash with public key (from certificate)
   - Compares: If hashes match → Document unchanged ✅

---

## Support Contacts

### Technical Support

**Program Studi Teknik Informatika**
- Email: informatika@umt.ac.id
- Phone: [University IT Support Number]
- Office Hours: Monday-Friday, 08:00-16:00 WIB

### Report Issues

**For signature verification issues**:
1. Take screenshot of error message
2. Note document filename and signature date
3. Email to: ridwanfebnur88@gmail.com
4. Include: Your Adobe Reader version

**For certificate trust issues**:
1. Verify you're using Adobe Reader (not browser viewer)
2. Try updating to latest Adobe Reader version
3. Contact IT support if corporate policy prevents trust

---

## Document Revision History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2025-11-22 | Initial release |

---

**End of Guide**

For the latest version of this document, visit:
`/docs/ADOBE_READER_VERIFICATION_GUIDE.md`
