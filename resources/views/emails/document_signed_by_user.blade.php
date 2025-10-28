@extends('emails.layouts.master')

@section('header')
    @include('emails.partials.header', [
        'title' => 'Dokumen Telah Ditandatangani ✍️',
        'subtitle' => 'Mahasiswa telah menandatangani dokumen dan menunggu verifikasi Anda'
    ])
@endsection

@section('content')
    {{-- Greeting --}}
    <p class="greeting" style="font-size: 16px; color: #2c3e50; font-weight: 500; margin-bottom: 20px;">
        Yth. Ketua Program Studi,
    </p>

    {{-- Alert: Action Required --}}
    <div class="alert alert-warning" style="padding: 20px; border-radius: 6px; margin: 20px 0; font-size: 15px; background-color: #fff8e1; border-left: 4px solid #ffc107; color: #f57c00;">
        <strong style="font-size: 16px;">⏰ VERIFIKASI DIPERLUKAN</strong>
        <p style="margin: 10px 0 0 0; color: #f57c00; font-size: 14px; line-height: 1.6;">
            Mahasiswa <strong>{{ $approvalRequest->user->name }}</strong> telah menyelesaikan proses penandatanganan dokumen. Dokumen ini memerlukan <strong>verifikasi Anda</strong> sebelum dapat difinalisasi.
        </p>
    </div>

    {{-- Introduction --}}
    <p style="margin: 0 0 16px 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Dokumen dengan nama <strong>{{ $approvalRequest->document_name }}</strong> telah ditandatangani secara digital oleh mahasiswa menggunakan template tanda tangan yang telah dibuat. Mohon untuk melakukan verifikasi terhadap penempatan dan kualitas tanda tangan.
    </p>

    {{-- Document Details Card --}}
    @include('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ])

    {{-- Signature Information --}}
    <div class="card" style="background-color: #f8f9fa; border-left: 4px solid #667eea; border-radius: 6px; padding: 20px; margin: 20px 0;">
        <h3 class="card-title" style="margin: 0 0 15px 0; color: #2c3e50; font-size: 16px; font-weight: 600;">
            ✍️ Informasi Penandatanganan
        </h3>

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #e9ecef;">
                <td style="padding: 12px 0; font-weight: 600; color: #2c3e50; font-size: 14px; width: 45%;">
                    Mahasiswa
                </td>
                <td style="padding: 12px 0; color: #555555; font-size: 14px;">
                    {{ $approvalRequest->user->name }}
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #e9ecef;">
                <td style="padding: 12px 0; font-weight: 600; color: #2c3e50; font-size: 14px;">
                    Waktu Tanda Tangan
                </td>
                <td style="padding: 12px 0; color: #555555; font-size: 14px;">
                    {{ $documentSignature->signed_at ? $documentSignature->signed_at->format('d F Y, H:i') : '-' }} WIB
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #e9ecef;">
                <td style="padding: 12px 0; font-weight: 600; color: #2c3e50; font-size: 14px;">
                    Template Tanda Tangan
                </td>
                <td style="padding: 12px 0; color: #555555; font-size: 14px;">
                    {{ $documentSignature->signatureTemplate->template_name ?? 'Template Default' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 12px 0; font-weight: 600; color: #2c3e50; font-size: 14px;">
                    Status Dokumen
                </td>
                <td style="padding: 12px 0;">
                    <span class="badge badge-warning" style="display: inline-block; padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #fff3cd; color: #856404;">
                        Menunggu Verifikasi
                    </span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Verification Guide --}}
    <div style="background-color: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 6px; padding: 20px; margin: 25px 0;">
        <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 16px; font-weight: 600;">
            📋 Panduan Verifikasi
        </h3>
        <p style="margin: 0 0 12px 0; color: #555555; font-size: 14px; line-height: 1.6;">
            Saat melakukan verifikasi, mohon periksa hal-hal berikut:
        </p>
        <ul style="margin: 0; padding-left: 20px; color: #555555; font-size: 14px; line-height: 1.8;">
            <li style="margin-bottom: 8px;"><strong>Penempatan tanda tangan:</strong> Pastikan tanda tangan berada pada posisi yang tepat</li>
            <li style="margin-bottom: 8px;"><strong>Kualitas visual:</strong> Tanda tangan terlihat jelas dan tidak buram</li>
            <li style="margin-bottom: 8px;"><strong>Ukuran proporsional:</strong> Ukuran tanda tangan sesuai dengan area dokumen</li>
            <li style="margin-bottom: 8px;"><strong>Tidak overlap:</strong> Tanda tangan tidak menutupi konten penting dokumen</li>
            <li style="margin-bottom: 0;"><strong>Kesesuaian template:</strong> Tanda tangan sesuai dengan template yang disetujui</li>
        </ul>
    </div>

    {{-- Timeline Progress --}}
    <div style="background-color: #f8f9fa; padding: 25px 20px; border-radius: 6px; margin: 25px 0;">
        <h4 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 14px; font-weight: 600; text-align: center;">
            📊 Progress Dokumen
        </h4>

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
            <tr>
                {{-- Step 1: Submitted --}}
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: bold; font-size: 18px;">
                        ✓
                    </div>
                    <div style="font-size: 11px; color: #4caf50; font-weight: 600;">DIAJUKAN</div>
                </td>

                {{-- Connector 1 --}}
                <td width="20%" align="center" style="padding: 10px 0;">
                    <div style="height: 3px; background: linear-gradient(to right, #4caf50 0%, #66bb6a 100%); width: 100%;"></div>
                </td>

                {{-- Step 2: Approved --}}
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: bold; font-size: 18px;">
                        ✓
                    </div>
                    <div style="font-size: 11px; color: #4caf50; font-weight: 600;">DISETUJUI</div>
                </td>

                {{-- Connector 2 --}}
                <td width="20%" align="center" style="padding: 10px 0;">
                    <div style="height: 3px; background: linear-gradient(to right, #4caf50 0%, #66bb6a 100%); width: 100%;"></div>
                </td>

                {{-- Step 3: Signed --}}
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: bold; font-size: 18px;">
                        ✓
                    </div>
                    <div style="font-size: 11px; color: #4caf50; font-weight: 600;">DITANDATANGANI</div>
                </td>

                {{-- Connector 3 --}}
                <td width="20%" align="center" style="padding: 10px 0;">
                    <div style="height: 3px; background: linear-gradient(to right, #ffc107 0%, #ffc107 100%); width: 100%;"></div>
                </td>

                {{-- Step 4: Verification (Current) --}}
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: bold; font-size: 18px;">
                        ⏳
                    </div>
                    <div style="font-size: 11px; color: #ffc107; font-weight: 600;">VERIFIKASI</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Action Buttons --}}
    <div style="margin: 30px 0;">
        <p style="margin: 0 0 15px 0; color: #2c3e50; font-size: 15px; font-weight: 600; text-align: center;">
            Verifikasi Tanda Tangan Dokumen
        </p>

        @include('emails.components.button', [
            'url' => route('admin.signature.dashboard'),
            'text' => '✅ Verifikasi & Review Dokumen',
            'type' => 'primary',
            'block' => true
        ])

        <p style="margin: 15px 0 0 0; text-align: center;">
            <a href="{{ route('admin.signature.dashboard') }}" style="color: #667eea; text-decoration: none; font-size: 14px;">
                atau lihat semua dokumen yang menunggu verifikasi
            </a>
        </p>
    </div>

    {{-- Divider --}}
    <div class="divider" style="height: 1px; background-color: #e9ecef; margin: 30px 0;"></div>

    {{-- Important Notes --}}
    <div style="background-color: #fff3e0; border-left: 3px solid #ff9800; padding: 16px 20px; border-radius: 6px; margin: 20px 0;">
        <h4 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
            💡 Catatan Penting
        </h4>
        <ul style="margin: 0; padding-left: 20px; color: #666666; font-size: 13px; line-height: 1.8;">
            <li>Verifikasi dapat dilakukan kapan saja melalui dashboard Kaprodi</li>
            <li>Jika tanda tangan tidak sesuai, Anda dapat menolak dan meminta mahasiswa untuk menandatangani ulang</li>
            <li>Setelah diverifikasi, dokumen final akan otomatis dikirim ke mahasiswa dengan QR Code</li>
            <li>Proses verifikasi sebaiknya dilakukan dalam 1-2 hari kerja</li>
        </ul>
    </div>

    {{-- Closing --}}
    <p style="margin: 30px 0 10px 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Terima kasih atas perhatian dan kerjasama Bapak/Ibu.
    </p>

    <p style="margin: 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Salam hormat,<br>
        <strong>Sistem Digital Signature</strong><br>
        <span style="color: #999999; font-size: 13px;">UMT Informatika</span>
    </p>
@endsection

@section('footer')
    @include('emails.partials.footer')
@endsection
