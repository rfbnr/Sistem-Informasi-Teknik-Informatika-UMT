# Self-Host QR Scanner Library - Implementation Guide

**Created**: November 22, 2025
**Priority**: HIGH
**Estimated Time**: 15 minutes

---

## Why Self-Host?

Currently, the verification system loads `html5-qrcode` library from CDN:

```html
<script src="https://unpkg.com/html5-qrcode"></script>
```

**Risks:**
- ⚠️ Single Point of Failure (unpkg.com down → QR scanner broken)
- ⚠️ Security Risk (CDN compromise → XSS attack)
- ⚠️ Privacy Concern (External request tracked)
- ⚠️ Performance (Additional DNS lookup + TLS handshake)

---

## Implementation Steps

### Option 1: Using NPM (Recommended)

```bash
# 1. Install library
npm install html5-qrcode

# 2. Copy to public directory
cp node_modules/html5-qrcode/html5-qrcode.min.js public/js/
```

### Option 2: Manual Download

```bash
# 1. Download latest version
cd public/js
wget https://unpkg.com/html5-qrcode@latest/html5-qrcode.min.js

# Or using curl
curl -o html5-qrcode.min.js https://unpkg.com/html5-qrcode@latest/html5-qrcode.min.js
```

### Option 3: Using CDN with Subresource Integrity (Temporary Fix)

```html
<!-- Use versioned CDN with SRI hash for security -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"
        integrity="sha384-..."
        crossorigin="anonymous"></script>
```

---

## Update Blade Template

After downloading the library, update `resources/views/digital-signature/verification/index.blade.php`:

**Find (around line 277):**
```blade
<script src="https://unpkg.com/html5-qrcode"></script>
```

**Replace with:**
```blade
{{-- ✅ Self-hosted QR Scanner Library --}}
<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
```

---

## Verification

Test that QR scanner still works:

1. Navigate to verification page: `/signature/verify`
2. Select "Scan QR Code" method
3. Click "Mulai Scan" button
4. Verify camera permission request appears
5. QR scanner should work without external network request

**Check Browser Console:**
- No 404 errors for `html5-qrcode.min.js`
- No network requests to `unpkg.com`

---

## File Size

- **html5-qrcode.min.js**: ~350KB (minified)
- **Impact on load time**: ~200-300ms (local) vs ~500-800ms (CDN)
- **Benefit**: Reliability + Security + Privacy

---

## Rollback Plan

If issues occur, revert to CDN:

```blade
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
```

---

## Long-term Recommendation

1. ✅ Self-host for production
2. ✅ Version lock (`html5-qrcode@2.3.8` instead of `@latest`)
3. ✅ Implement fallback mechanism
4. ✅ Monitor library updates quarterly

---

**Status**: Ready to implement
**Risk**: Low
**Impact**: High (Reliability)
