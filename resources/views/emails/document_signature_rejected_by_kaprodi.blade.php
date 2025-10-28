@extends('emails.layouts.master')

@section('header')
    @include('emails.partials.header', [
        'title' => 'Tanda Tangan Perlu Diperbaiki ⚠️',
        'subtitle' => 'Kaprodi meminta Anda untuk menandatangani ulang dokumen'
    ])
@endsection

@section('content')
    {{-- Greeting --}}
    <p class="greeting" style="font-size: 16px; color: #2c3e50; font-weight: 500; margin-bottom: 20px;">
        Halo {{ $approvalRequest->user->name }},
    </p>

    {{-- Warning Message --}}
    <div class="alert alert-warning" style="padding: 20px; border-radius: 6px; margin: 20px 0; font-size: 15px; background-color: #fff8e1; border-left: 4px solid #ffc107; color: #f57c00;">
        <strong style="font-size: 16px;">⚠️ Tanda Tangan Ditolak</strong>
        <p style="margin: 10px 0 0 0; color: #f57c00; font-size: 14px; line-height: 1.6;">
            Kaprodi telah meninjau tanda tangan Anda pada dokumen <strong>{{ $approvalRequest->document_name }}</strong> dan meminta Anda untuk <strong>menandatangani ulang</strong> dokumen tersebut.
        </p>
    </div>

    {{-- Introduction --}}
    <p style="margin: 0 0 16px 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Jangan khawatir, ini adalah hal yang wajar dalam proses quality control. Kaprodi ingin memastikan bahwa tanda tangan Anda terlihat sempurna pada dokumen final.
    </p>

    {{-- Document Details Card --}}
    @include('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ])

    {{-- Rejection Reason --}}
    <div style="background-color: #ffebee; border-left: 4px solid #f44336; border-radius: 6px; padding: 20px; margin: 25px 0;">
        <h3 style="margin: 0 0 12px 0; color: #c62828; font-size: 16px; font-weight: 600;">
            📝 Alasan Penolakan
        </h3>
        <p style="margin: 0; color: #555555; font-size: 14px; line-height: 1.6; background-color: white; padding: 15px; border-radius: 4px;">
            {{ $rejectionReason }}
        </p>
    </div>

    {{-- Common Issues & Tips --}}
    <div class="card" style="background-color: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 6px; padding: 20px; margin: 25px 0;">
        <h3 class="card-title" style="margin: 0 0 15px 0; color: #2c3e50; font-size: 16px; font-weight: 600;">
            💡 Tips untuk Penandatanganan Ulang
        </h3>
        <p style="margin: 0 0 12px 0; color: #555555; font-size: 14px; line-height: 1.6;">
            Berikut adalah hal-hal yang perlu diperhatikan saat menandatangani ulang:
        </p>
        <ul style="margin: 0; padding-left: 20px; color: #555555; font-size: 14px; line-height: 1.8;">
            <li style="margin-bottom: 8px;"><strong>Posisi yang tepat:</strong> Letakkan tanda tangan di area yang ditentukan, tidak terlalu ke pinggir</li>
            <li style="margin-bottom: 8px;"><strong>Ukuran proporsional:</strong> Pastikan ukuran tanda tangan tidak terlalu besar atau terlalu kecil</li>
            <li style="margin-bottom: 8px;"><strong>Tidak menutupi teks:</strong> Pastikan tanda tangan tidak menutupi informasi penting di dokumen</li>
            <li style="margin-bottom: 8px;"><strong>Kualitas visual:</strong> Gunakan template tanda tangan dengan kualitas gambar yang baik</li>
            <li style="margin-bottom: 0;"><strong>Preview sebelum submit:</strong> Selalu review penempatan sebelum mengirimkan</li>
        </ul>
    </div>

    {{-- Step by Step Guide --}}
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 25px 0;">
        <h4 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 16px; font-weight: 600;">
            📋 Langkah-Langkah Menandatangani Ulang
        </h4>
        <ol style="margin: 0; padding-left: 20px; color: #555555; font-size: 14px; line-height: 1.8;">
            <li style="margin-bottom: 10px;"><strong>Klik tombol "Tandatangani Ulang"</strong> di bawah ini</li>
            <li style="margin-bottom: 10px;"><strong>Buka halaman penandatanganan</strong> dokumen</li>
            <li style="margin-bottom: 10px;"><strong>Perhatikan feedback</strong> dari Kaprodi (alasan penolakan di atas)</li>
            <li style="margin-bottom: 10px;"><strong>Pilih atau edit template</strong> tanda tangan Anda jika diperlukan</li>
            <li style="margin-bottom: 10px;"><strong>Letakkan tanda tangan</strong> dengan lebih hati-hati, ikuti tips di atas</li>
            <li style="margin-bottom: 10px;"><strong>Review penempatan</strong> dengan teliti sebelum submit</li>
            <li style="margin-bottom: 0;"><strong>Submit ulang</strong> untuk review Kaprodi</li>
        </ol>
    </div>

    {{-- Timeline Progress --}}
    <div style="background-color: #f8f9fa; padding: 25px 20px; border-radius: 6px; margin: 25px 0;">
        <h4 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 14px; font-weight: 600; text-align: center;">
            📊 Progress Dokumen Anda
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
                    <div style="height: 3px; background: linear-gradient(to right, #ff9800 0%, #ff9800 100%); width: 100%;"></div>
                </td>

                {{-- Step 3: Sign (Need Retry) --}}
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #ff9800 0%, #fb8c00 100%); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: bold; font-size: 18px;">
                        ↻
                    </div>
                    <div style="font-size: 11px; color: #ff9800; font-weight: 600;">TANDA TANGAN ULANG</div>
                </td>

                {{-- Connector 3 --}}
                <td width="20%" align="center" style="padding: 10px 0;">
                    <div style="height: 3px; background: linear-gradient(to right, #ddd 0%, #ddd 100%); width: 100%;"></div>
                </td>

                {{-- Step 4: Verification --}}
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #e0e0e0; color: #999; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: bold; font-size: 18px;">
                        ⏳
                    </div>
                    <div style="font-size: 11px; color: #999; font-weight: 600;">VERIFIKASI</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Action Button --}}
    <div style="margin: 30px 0;">
        <p style="margin: 0 0 15px 0; color: #2c3e50; font-size: 15px; font-weight: 600; text-align: center;">
            Tandatangani Ulang Dokumen Anda
        </p>

        @include('emails.components.button', [
            'url' => route('user.signature.sign.document', $approvalRequest->id),
            'text' => '✍️ Tandatangani Ulang Dokumen',
            'type' => 'primary',
            'block' => true
        ])

        <p style="margin: 15px 0 0 0; text-align: center;">
            <a href="{{ route('user.signature.approval.status') }}" style="color: #667eea; text-decoration: none; font-size: 14px;">
                atau lihat status dokumen Anda
            </a>
        </p>
    </div>

    {{-- Divider --}}
    <div class="divider" style="height: 1px; background-color: #e9ecef; margin: 30px 0;"></div>

    {{-- Encouragement Message --}}
    <div style="background-color: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 6px; padding: 20px; margin: 25px 0;">
        <h4 style="margin: 0 0 10px 0; color: #2e7d32; font-size: 14px; font-weight: 600;">
            💪 Jangan Berkecil Hati!
        </h4>
        <p style="margin: 0; color: #555555; font-size: 14px; line-height: 1.6;">
            Proses quality control ini bertujuan untuk memastikan dokumen Anda terlihat sempurna dan profesional. Dengan mengikuti tips di atas, Anda akan berhasil di percobaan berikutnya!
        </p>
    </div>

    {{-- Support Info --}}
    <div style="background-color: #fff3e0; border-left: 3px solid #ff9800; padding: 16px 20px; border-radius: 6px; margin: 20px 0;">
        <h4 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
            💡 Butuh Bantuan?
        </h4>
        <p style="margin: 0; color: #666666; font-size: 13px; line-height: 1.6;">
            Jika Anda mengalami kesulitan atau memiliki pertanyaan tentang proses penandatanganan, jangan ragu untuk menghubungi:
        </p>
        <ul style="margin: 8px 0 0 0; padding-left: 20px; color: #666666; font-size: 13px; line-height: 1.8;">
            <li>Email: support@informatika.umt.ac.id</li>
            <li>WhatsApp: +62 812-3456-7890</li>
            <li>Jam kerja: Senin - Jumat, 08:00 - 16:00 WIB</li>
        </ul>
    </div>

    {{-- Closing --}}
    <p style="margin: 30px 0 10px 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Semangat untuk menandatangani ulang! Kami yakin Anda bisa melakukannya dengan baik.
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
