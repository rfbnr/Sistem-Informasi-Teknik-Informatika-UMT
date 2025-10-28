{{-- Email Footer Component --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            {{-- Logo/Branding --}}
            <p style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600; color: #ffffff;">
                UMT Informatika
            </p>

            <p style="margin: 0 0 20px 0; color: rgba(255, 255, 255, 0.8); font-size: 13px; line-height: 1.6;">
                Universitas Muhammadiyah Tangerang<br>
                Program Studi Teknik Informatika<br>
                Digital Signature System
            </p>

            {{-- Divider --}}
            <div style="height: 1px; background-color: rgba(255, 255, 255, 0.2); margin: 20px 0;"></div>

            {{-- Contact Info --}}
            <p style="margin: 0 0 15px 0; color: rgba(255, 255, 255, 0.7); font-size: 13px;">
                <strong>Kontak:</strong><br>
                📧 informatika@umt.ac.id<br>
                📱 +62 21 5529 2807<br>
                🌐 <a href="https://informatika.umt.ac.id" style="color: #667eea; text-decoration: none;">informatika.umt.ac.id</a>
            </p>

            {{-- Quick Links --}}
            <div class="footer-links" style="margin: 20px 0;">
                <a href="{{ route('admin.signature.dashboard') }}" style="color: rgba(255, 255, 255, 0.7); text-decoration: none; margin: 0 10px; font-size: 13px;">
                    Dashboard
                </a>
                <span style="color: rgba(255, 255, 255, 0.3);">|</span>
                <a href="{{ route('signature.verify.page') }}" style="color: rgba(255, 255, 255, 0.7); text-decoration: none; margin: 0 10px; font-size: 13px;">
                    Verifikasi Dokumen
                </a>
                <span style="color: rgba(255, 255, 255, 0.3);">|</span>
                <a href="mailto:informatika@umt.ac.id" style="color: rgba(255, 255, 255, 0.7); text-decoration: none; margin: 0 10px; font-size: 13px;">
                    Bantuan
                </a>
            </div>

            {{-- Divider --}}
            <div style="height: 1px; background-color: rgba(255, 255, 255, 0.2); margin: 20px 0;"></div>

            {{-- Privacy Notice --}}
            <p style="margin: 0 0 10px 0; color: rgba(255, 255, 255, 0.6); font-size: 12px; line-height: 1.5;">
                Email ini dikirim secara otomatis oleh sistem Digital Signature UMT Informatika.<br>
                Mohon tidak membalas email ini. Untuk pertanyaan, hubungi informatika@umt.ac.id
            </p>

            {{-- Copyright --}}
            <p style="margin: 15px 0 0 0; color: rgba(255, 255, 255, 0.5); font-size: 12px;">
                &copy; {{ date('Y') }} UMT Informatika. All rights reserved.
            </p>
        </td>
    </tr>
</table>
