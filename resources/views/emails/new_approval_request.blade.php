@extends('emails.layouts.master')

@section('header')
    @include('emails.partials.header', [
        'title' => 'Permintaan Persetujuan Baru',
        'subtitle' => 'Terdapat dokumen yang memerlukan persetujuan Anda'
    ])
@endsection

@section('content')
    {{-- Greeting --}}
    <p class="greeting">
        Yth. Ketua Program Studi,
    </p>

    {{-- Introduction --}}
    <p>
        Sebuah permintaan persetujuan dokumen baru telah diajukan oleh <strong>{{ $approvalRequest->user->name }}</strong> dan memerlukan tindakan Anda.
    </p>

    {{-- Alert Box --}}
    <div class="alert alert-info">
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
    <div class="alert alert-warning">
        <h3 class="section-title">
            💬 Catatan dari Pemohon
        </h3>
        <p class="mb-0">
            "{{ $approvalRequest->notes }}"
        </p>
    </div>
    @endif

    {{-- Action Buttons --}}
    <div class="mt-30">
        <p class="text-center text-strong mb-15">
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
        <p class="mt-15 mb-0 text-center text-muted text-small">
            Atau kunjungi
            <a href="{{ route('admin.signature.dashboard') }}" class="link-primary">
                Dashboard Kaprodi
            </a>
            untuk melihat semua permintaan
        </p>
    </div>

    {{-- Divider --}}
    <div class="divider"></div>

    {{-- Additional Info --}}
    <div class="section-card">
        <h4 class="section-subtitle">
            ℹ️ Informasi Penting
        </h4>
        <ul class="list-no-margin text-muted text-small">
            <li>Dokumen akan tersedia untuk ditandatangani setelah Anda menyetujui</li>
            <li>Mahasiswa akan menerima notifikasi otomatis setelah persetujuan</li>
            <li>Anda dapat melihat preview dokumen sebelum memberikan persetujuan</li>
            <li>Jika ada pertanyaan, silakan hubungi pemohon melalui email: <a href="mailto:{{ $approvalRequest->user->email }}" class="link-primary">{{ $approvalRequest->user->email }}</a></li>
        </ul>
    </div>

    {{-- Closing --}}
    <p class="mt-30 mb-10">
        Terima kasih atas perhatian dan kerjasamanya.
    </p>

    <p class="mb-0">
        Hormat kami,<br>
        <strong>Sistem Digital Signature</strong><br>
        <span class="text-muted text-small">UMT Informatika</span>
    </p>
@endsection

@section('footer')
    @include('emails.partials.footer')
@endsection
