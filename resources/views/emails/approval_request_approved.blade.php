@extends('emails.layouts.master')

@section('header')
    @include('emails.partials.header', [
        'title' => 'Permintaan Anda Disetujui! ✅',
        'subtitle' => 'Dokumen Anda telah disetujui dan siap untuk ditandatangani'
    ])
@endsection

@section('content')
    {{-- Greeting --}}
    <p class="greeting" style="font-size: 16px; color: #2c3e50; font-weight: 500; margin-bottom: 20px;">
        Halo {{ $approvalRequest->user->name }},
    </p>

    {{-- Success Message --}}
    <div class="alert alert-success" style="padding: 20px; border-radius: 6px; margin: 20px 0; font-size: 15px; background-color: #e8f5e9; border-left: 4px solid #4caf50; color: #2e7d32;">
        <strong>🎉 Selamat!</strong> Permintaan persetujuan dokumen Anda telah <strong>DISETUJUI</strong> oleh Ketua Program Studi.
    </div>

    {{-- Introduction --}}
    <p style="margin: 0 0 16px 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Dokumen Anda dengan nama <strong>{{ $approvalRequest->document_name }}</strong> telah melalui proses review dan mendapatkan persetujuan. Langkah selanjutnya adalah proses <strong>penandatanganan digital</strong>.
    </p>

    {{-- Document Details Card --}}
    @include('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ])

    {{-- IMPORTANT: Next Action Required --}}
    <div class="alert alert-warning" style="padding: 20px; border-radius: 6px; margin: 25px 0; font-size: 15px; background-color: #fff8e1; border-left: 4px solid #ffc107; color: #f57c00;">
        <strong style="font-size: 16px;">⚡ TINDAKAN DIPERLUKAN</strong>
        <p style="margin: 10px 0 0 0; color: #f57c00; font-size: 14px; line-height: 1.6;">
            Dokumen Anda telah disetujui, tetapi Anda perlu <strong>MENANDATANGANI dokumen secara manual</strong> menggunakan template tanda tangan digital Anda sebelum Kaprodi dapat memverifikasinya.
        </p>
    </div>

    {{-- How to Sign Section --}}
    <div class="card" style="background-color: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 6px; padding: 20px; margin: 25px 0;">
        <h3 class="card-title" style="margin: 0 0 15px 0; color: #2c3e50; font-size: 16px; font-weight: 600;">
            ✍️ Cara Menandatangani Dokumen
        </h3>
        <ol style="margin: 0; padding-left: 20px; color: #555555; font-size: 14px; line-height: 1.8;">
            <li style="margin-bottom: 10px;"><strong>Klik tombol "Tandatangani Dokumen"</strong> di bawah ini untuk membuka halaman penandatanganan</li>
            <li style="margin-bottom: 10px;"><strong>Pilih template tanda tangan</strong> Anda atau buat template baru jika belum ada</li>
            <li style="margin-bottom: 10px;"><strong>Letakkan tanda tangan</strong> pada posisi yang sesuai di dokumen (drag & drop)</li>
            <li style="margin-bottom: 10px;"><strong>Review penempatan</strong> tanda tangan Anda sebelum submit</li>
            <li style="margin-bottom: 0;"><strong>Submit untuk review</strong> - Kaprodi akan memverifikasi tanda tangan Anda</li>
        </ol>
    </div>

    {{-- Next Steps After Signing --}}
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 25px 0;">
        <h4 style="margin: 0 0 12px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
            📌 Setelah Anda Menandatangani
        </h4>
        <ul style="margin: 0; padding-left: 20px; color: #666666; font-size: 13px; line-height: 1.8;">
            <li style="margin-bottom: 6px;">Kaprodi akan menerima notifikasi untuk memverifikasi tanda tangan Anda</li>
            <li style="margin-bottom: 6px;">Proses verifikasi biasanya memakan waktu 1-2 hari kerja</li>
            <li style="margin-bottom: 6px;">Anda akan menerima email notifikasi setelah dokumen diverifikasi</li>
            <li style="margin-bottom: 0;">Dokumen final akan dilengkapi dengan <strong>QR Code verifikasi</strong></li>
        </ul>
    </div>

    {{-- Timeline Indicator --}}
    <div style="background-color: #f8f9fa; padding: 25px 20px; border-radius: 6px; margin: 25px 0;">
        <h4 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 14px; font-weight: 600; text-align: center;">
            📊 Progress Dokumen Anda
        </h4>

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
            <tr>
                {{-- Step 1: Submitted --}}
                <td width="25%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: bold; font-size: 18px;">
                        ✓
                    </div>
                    <div style="font-size: 11px; color: #4caf50; font-weight: 600;">DIAJUKAN</div>
                </td>

                {{-- Connector 1 --}}
                <td width="25%" align="center" style="padding: 10px 0;">
                    <div style="height: 3px; background: linear-gradient(to right, #4caf50 0%, #66bb6a 100%); width: 100%;"></div>
                </td>

                {{-- Step 2: Approved --}}
                <td width="25%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: bold; font-size: 18px;">
                        ✓
                    </div>
                    <div style="font-size: 11px; color: #4caf50; font-weight: 600;">DISETUJUI</div>
                </td>

                {{-- Connector 2 --}}
                <td width="25%" align="center" style="padding: 10px 0;">
                    <div style="height: 3px; background: linear-gradient(to right, #ddd 0%, #ddd 100%); width: 100%;"></div>
                </td>

                {{-- Step 3: Signed (Pending) --}}
                <td width="25%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #e0e0e0; color: #999; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: bold; font-size: 18px;">
                        ⏳
                    </div>
                    <div style="font-size: 11px; color: #999; font-weight: 600;">DITANDATANGANI</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Action Buttons --}}
    <div style="margin: 30px 0;">
        <p style="margin: 0 0 15px 0; color: #2c3e50; font-size: 15px; font-weight: 600; text-align: center;">
            Tandatangani Dokumen Anda Sekarang
        </p>

        @include('emails.components.button', [
            'url' => route('user.signature.sign.document', $approvalRequest->id),
            'text' => '✍️ Tandatangani Dokumen',
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

    {{-- Important Notes --}}
    <div style="background-color: #fff3e0; border-left: 3px solid #ff9800; padding: 16px 20px; border-radius: 6px; margin: 20px 0;">
        <h4 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
            💡 Catatan Penting
        </h4>
        <ul style="margin: 0; padding-left: 20px; color: #666666; font-size: 13px; line-height: 1.8;">
            <li>Simpan email ini sebagai bukti persetujuan</li>
            <li>Cek halaman status secara berkala untuk update terbaru</li>
            <li>Setelah ditandatangani, Anda akan menerima dokumen final dengan QR Code</li>
            <li>QR Code dapat digunakan untuk verifikasi keaslian dokumen</li>
        </ul>
    </div>

    {{-- Closing --}}
    <p style="margin: 30px 0 10px 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Terima kasih telah menggunakan sistem Digital Signature UMT Informatika.
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
