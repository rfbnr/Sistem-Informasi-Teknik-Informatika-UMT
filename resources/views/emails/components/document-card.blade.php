{{-- Document Card Component --}}
{{--
    Usage:
    @include('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ])
--}}

<div class="info-card-purple">
    <h3 class="section-title">
        📄 Detail Dokumen
    </h3>

    <table class="info-table" width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 6px; overflow: hidden;">
        {{-- Document Name --}}
        <tr class="info-row">
            <td class="info-label">
                Nama Dokumen
            </td>
            <td class="info-value">
                {{ $approvalRequest->document_name }}
            </td>
        </tr>

        {{-- Document Number --}}
        @if($approvalRequest->nomor)
        <tr class="info-row">
            <td class="info-label">
                Nomor Dokumen
            </td>
            <td class="info-value">
                {{ $approvalRequest->full_document_number ?? $approvalRequest->nomor }}
            </td>
        </tr>
        @endif

        {{-- Document Type --}}
        @if($approvalRequest->document_type)
        <tr class="info-row">
            <td class="info-label">
                Jenis Dokumen
            </td>
            <td class="info-value">
                {{ ucfirst(str_replace('_', ' ', $approvalRequest->document_type)) }}
            </td>
        </tr>
        @endif

        {{-- Requester/Student --}}
        @if(isset($showRequester) && $showRequester && $approvalRequest->user)
        <tr class="info-row">
            <td class="info-label">
                Pemohon
            </td>
            <td class="info-value">
                {{ $approvalRequest->user->name }}<br>
                <span class="text-muted text-small">{{ $approvalRequest->user->email }}</span>
            </td>
        </tr>
        @endif

        {{-- Submission Date --}}
        <tr class="info-row">
            <td class="info-label">
                Tanggal Pengajuan
            </td>
            <td class="info-value">
                {{ $approvalRequest->created_at->format('d F Y, H:i') }} WIB
            </td>
        </tr>

        {{-- Priority --}}
        @if(isset($approvalRequest->priority))
        <tr class="info-row">
            <td class="info-label">
                Prioritas
            </td>
            <td class="info-value">
                @php
                    $priorityBadge = [
                        'high' => ['class' => 'badge-danger', 'text' => 'Tinggi'],
                        'normal' => ['class' => 'badge-warning', 'text' => 'Normal'],
                        'low' => ['class' => 'badge-info', 'text' => 'Rendah']
                    ][$approvalRequest->priority] ?? ['class' => 'badge-info', 'text' => ucfirst($approvalRequest->priority)];
                @endphp
                <span class="badge {{ $priorityBadge['class'] }}">
                    {{ $priorityBadge['text'] }}
                </span>
            </td>
        </tr>
        @endif

        {{-- Status --}}
        @if(isset($showStatus) && $showStatus)
        <tr>
            <td class="info-label">
                Status
            </td>
            <td class="info-value">
                @php
                    $statusBadge = [
                        'pending' => ['class' => 'badge-warning', 'text' => 'Menunggu Persetujuan'],
                        'approved' => ['class' => 'badge-success', 'text' => 'Disetujui'],
                        'rejected' => ['class' => 'badge-danger', 'text' => 'Ditolak'],
                        'user_signed' => ['class' => 'badge-info', 'text' => 'Sudah Ditandatangani'],
                        'sign_approved' => ['class' => 'badge-success', 'text' => 'Terverifikasi']
                    ][$approvalRequest->status] ?? ['class' => 'badge-info', 'text' => $approvalRequest->status];
                @endphp
                <span class="badge {{ $statusBadge['class'] }}">
                    {{ $statusBadge['text'] }}
                </span>
            </td>
        </tr>
        @endif

        {{-- Notes --}}
        @if($approvalRequest->notes)
        <tr>
            <td class="info-label">
                Catatan
            </td>
            <td class="info-value">
                {{ $approvalRequest->notes }}
            </td>
        </tr>
        @endif
    </table>
</div>
