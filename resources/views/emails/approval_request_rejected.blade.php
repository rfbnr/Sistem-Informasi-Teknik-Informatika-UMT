@extends('emails.layouts.master')

@section('header')
    @include('emails.partials.header', [
        'title' => 'Permintaan Perlu Perbaikan',
        'subtitle' => 'Informasi mengenai permintaan persetujuan dokumen Anda'
    ])
@endsection

@section('content')
    {{-- Greeting --}}
    <p class="greeting" style="font-size: 16px; color: #2c3e50; font-weight: 500; margin-bottom: 20px;">
        Halo {{ $approvalRequest->user->name }},
    </p>

    {{-- Alert Message --}}
    <div class="alert alert-warning" style="padding: 20px; border-radius: 6px; margin: 20px 0; font-size: 15px; background-color: #fff8e1; border-left: 4px solid #ffc107; color: #f57c00;">
        <strong>⚠️ Perhatian:</strong> Permintaan persetujuan dokumen Anda <strong>memerlukan perbaikan</strong>.
    </div>

    {{-- Introduction --}}
    <p style="margin: 0 0 16px 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Setelah melakukan review, Ketua Program Studi memutuskan bahwa dokumen <strong>{{ $approvalRequest->document_name }}</strong> memerlukan beberapa perbaikan sebelum dapat disetujui.
    </p>

    {{-- Document Details Card --}}
    @include('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ])

    {{-- Rejection Reason --}}
    @if($approvalRequest->rejection_reason)
    <div class="card" style="background-color: #ffebee; border-left: 4px solid #f44336; border-radius: 6px; padding: 20px; margin: 25px 0;">
        <h3 class="card-title" style="margin: 0 0 12px 0; color: #c62828; font-size: 16px; font-weight: 600;">
            📝 Alasan Penolakan
        </h3>
        <p class="card-content" style="margin: 0; color: #c62828; font-size: 14px; line-height: 1.6; background-color: #ffffff; padding: 15px; border-radius: 4px;">
            "{{ $approvalRequest->rejection_reason }}"
        </p>
    </div>
    @endif

    {{-- What to Do Next --}}
    <div class="card" style="background-color: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 6px; padding: 20px; margin: 25px 0;">
        <h3 class="card-title" style="margin: 0 0 15px 0; color: #2c3e50; font-size: 16px; font-weight: 600;">
            🔧 Langkah Perbaikan
        </h3>
        <ol style="margin: 0; padding-left: 20px; color: #555555; font-size: 14px; line-height: 1.8;">
            <li style="margin-bottom: 8px;">Baca dengan teliti <strong>alasan penolakan</strong> di atas</li>
            <li style="margin-bottom: 8px;">Lakukan <strong>perbaikan</strong> pada dokumen sesuai catatan yang diberikan</li>
            <li style="margin-bottom: 8px;">Pastikan semua <strong>persyaratan</strong> dokumen sudah terpenuhi</li>
            <li style="margin-bottom: 8px;"><strong>Upload ulang</strong> dokumen yang sudah diperbaiki</li>
            <li style="margin-bottom: 0;">Tunggu proses review berikutnya</li>
        </ol>
    </div>

    {{-- Action Button --}}
    <div style="margin: 30px 0;">
        <p style="margin: 0 0 15px 0; color: #2c3e50; font-size: 15px; font-weight: 600; text-align: center;">
            Siap Mengajukan Ulang?
        </p>

        @include('emails.components.button', [
            'url' => route('user.signature.approval.request'),
            'text' => '📤 Ajukan Dokumen Baru',
            'type' => 'primary',
            'block' => true
        ])

        <p style="text-align: center; margin: 15px 0 0 0; font-size: 13px; color: #999999;">
            atau lihat
            <a href="{{ route('user.signature.approval.status') }}" style="color: #667eea; text-decoration: none;">
                Status Semua Dokumen
            </a>
        </p>
    </div>

    {{-- Divider --}}
    <div class="divider" style="height: 1px; background-color: #e9ecef; margin: 30px 0;"></div>

    {{-- Common Rejection Reasons & Tips --}}
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0;">
        <h4 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
            💡 Tips Agar Dokumen Disetujui
        </h4>
        <ul style="margin: 0; padding-left: 20px; color: #666666; font-size: 13px; line-height: 1.8;">
            <li>Pastikan format dokumen sesuai (PDF, ukuran max 10MB)</li>
            <li>Periksa kelengkapan informasi dalam dokumen</li>
            <li>Gunakan nama file yang jelas dan deskriptif</li>
            <li>Isi catatan tambahan jika ada informasi penting</li>
            <li>Hubungi kaprodi jika ada pertanyaan: <a href="mailto:{{ $approvalRequest->kaprodi->email ?? 'informatika@umt.ac.id' }}" style="color: #667eea; text-decoration: none;">{{ $approvalRequest->kaprodi->email ?? 'informatika@umt.ac.id' }}</a></li>
        </ul>
    </div>

    {{-- Rejection Details (if available) --}}
    @if($approvalRequest->rejected_at && $approvalRequest->rejector)
    <div style="background-color: #ffffff; border: 1px solid #e9ecef; padding: 15px 20px; border-radius: 6px; margin: 20px 0;">
        <table width="100%" cellpadding="5" cellspacing="0" style="font-size: 13px; color: #666666;">
            <tr>
                <td width="40%" style="font-weight: 600; color: #2c3e50;">Ditolak Oleh:</td>
                <td width="60%">{{ $approvalRequest->rejector->name }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600; color: #2c3e50;">Tanggal Penolakan:</td>
                <td>{{ $approvalRequest->rejected_at->format('d F Y, H:i') }} WIB</td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Encouragement Message --}}
    <div class="alert alert-info" style="padding: 16px 20px; border-radius: 6px; margin: 20px 0; font-size: 14px; background-color: #e8f5e9; border-left: 4px solid #4caf50; color: #2e7d32;">
        <strong>💪 Jangan Berkecil Hati!</strong> Penolakan ini adalah bagian dari proses quality control untuk memastikan dokumen Anda sempurna. Lakukan perbaikan dan ajukan kembali!
    </div>

    {{-- Closing --}}
    <p style="margin: 30px 0 10px 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Jika ada pertanyaan atau butuh klarifikasi lebih lanjut, jangan ragu untuk menghubungi kami.
    </p>

    <p style="margin: 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Salam,<br>
        <strong>Tim Digital Signature</strong><br>
        <span style="color: #999999; font-size: 13px;">UMT Informatika</span>
    </p>
@endsection

@section('footer')
    @include('emails.partials.footer')
@endsection
