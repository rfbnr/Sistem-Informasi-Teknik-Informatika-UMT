@extends('emails.layouts.master')

@section('header')
    @include('emails.partials.header', [
        'title' => 'Permintaan Persetujuan Baru',
        'subtitle' => 'Terdapat dokumen yang memerlukan persetujuan Anda'
    ])
@endsection

@section('content')
    {{-- Greeting --}}
    <p class="greeting" style="font-size: 16px; color: #2c3e50; font-weight: 500; margin-bottom: 20px;">
        Yth. Ketua Program Studi,
    </p>

    {{-- Introduction --}}
    <p style="margin: 0 0 16px 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Sebuah permintaan persetujuan dokumen baru telah diajukan oleh <strong>{{ $approvalRequest->user->name }}</strong> dan memerlukan tindakan Anda.
    </p>

    {{-- Alert Box --}}
    <div class="alert alert-info" style="padding: 16px 20px; border-radius: 6px; margin: 20px 0; font-size: 14px; background-color: #e3f2fd; border-left: 4px solid #2196f3; color: #0d47a1;">
        <strong>⏰ Perhatian:</strong> Mohon segera review dan berikan persetujuan untuk mempercepat proses tanda tangan digital.
    </div>

    {{-- Document Details Card --}}
    @include('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showRequester' => true,
        'showStatus' => true
    ])

    {{-- Notes Section (if exists) --}}
    @if($approvalRequest->notes)
    <div class="card" style="background-color: #fff8e1; border-left: 4px solid #ffc107; border-radius: 6px; padding: 20px; margin: 20px 0;">
        <h3 class="card-title" style="margin: 0 0 10px 0; color: #2c3e50; font-size: 16px; font-weight: 600;">
            💬 Catatan dari Pemohon
        </h3>
        <p class="card-content" style="margin: 0; color: #555555; font-size: 14px; line-height: 1.6;">
            "{{ $approvalRequest->notes }}"
        </p>
    </div>
    @endif

    {{-- Action Buttons --}}
    <div style="margin: 30px 0;">
        <p style="margin: 0 0 15px 0; color: #2c3e50; font-size: 15px; font-weight: 600;">
            Tindakan yang Diperlukan:
        </p>

        {{-- Review Button --}}
        @include('emails.components.button', [
            'url' => route('admin.signature.approval.show', $approvalRequest->id),
            'text' => '📋 Review & Setujui Dokumen',
            'type' => 'primary',
            'block' => true
        ])

        {{-- Dashboard Link --}}
        <p style="text-align: center; margin: 15px 0 0 0; font-size: 13px; color: #999999;">
            Atau kunjungi
            <a href="{{ route('admin.signature.dashboard') }}" style="color: #667eea; text-decoration: none;">
                Dashboard Kaprodi
            </a>
            untuk melihat semua permintaan
        </p>
    </div>

    {{-- Divider --}}
    <div class="divider" style="height: 1px; background-color: #e9ecef; margin: 30px 0;"></div>

    {{-- Additional Info --}}
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0;">
        <h4 style="margin: 0 0 12px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
            ℹ️ Informasi Penting
        </h4>
        <ul style="margin: 0; padding-left: 20px; color: #666666; font-size: 13px; line-height: 1.8;">
            <li>Dokumen akan tersedia untuk ditandatangani setelah Anda menyetujui</li>
            <li>Mahasiswa akan menerima notifikasi otomatis setelah persetujuan</li>
            <li>Anda dapat melihat preview dokumen sebelum memberikan persetujuan</li>
            <li>Jika ada pertanyaan, silakan hubungi pemohon melalui email: <a href="mailto:{{ $approvalRequest->user->email }}" style="color: #667eea; text-decoration: none;">{{ $approvalRequest->user->email }}</a></li>
        </ul>
    </div>

    {{-- Closing --}}
    <p style="margin: 30px 0 10px 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Terima kasih atas perhatian dan kerjasamanya.
    </p>

    <p style="margin: 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Hormat kami,<br>
        <strong>Sistem Digital Signature</strong><br>
        <span style="color: #999999; font-size: 13px;">UMT Informatika</span>
    </p>
@endsection

@section('footer')
    @include('emails.partials.footer')
@endsection
