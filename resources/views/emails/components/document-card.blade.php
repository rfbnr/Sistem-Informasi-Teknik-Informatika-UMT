{{-- Document Card Component --}}
{{--
    Usage:
    @include('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ])
--}}

<div class="card" style="background-color: #f8f9fa; border-left: 4px solid #667eea; border-radius: 6px; padding: 20px; margin: 20px 0;">
    <h3 class="card-title" style="margin: 0 0 15px 0; color: #2c3e50; font-size: 16px; font-weight: 600;">
        📄 Detail Dokumen
    </h3>

    <table class="info-table" width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 6px; overflow: hidden;">
        {{-- Document Name --}}
        <tr style="border-bottom: 1px solid #e9ecef;">
            <td style="padding: 12px 16px; font-size: 14px; font-weight: 600; color: #2c3e50; width: 40%; background-color: #f8f9fa;">
                Nama Dokumen
            </td>
            <td style="padding: 12px 16px; font-size: 14px; color: #555555; width: 60%;">
                {{ $approvalRequest->document_name }}
            </td>
        </tr>

        {{-- Document Number --}}
        @if($approvalRequest->nomor)
        <tr style="border-bottom: 1px solid #e9ecef;">
            <td style="padding: 12px 16px; font-size: 14px; font-weight: 600; color: #2c3e50; background-color: #f8f9fa;">
                Nomor Dokumen
            </td>
            <td style="padding: 12px 16px; font-size: 14px; color: #555555;">
                {{ $approvalRequest->full_document_number ?? $approvalRequest->nomor }}
            </td>
        </tr>
        @endif

        {{-- Document Type --}}
        @if($approvalRequest->document_type)
        <tr style="border-bottom: 1px solid #e9ecef;">
            <td style="padding: 12px 16px; font-size: 14px; font-weight: 600; color: #2c3e50; background-color: #f8f9fa;">
                Jenis Dokumen
            </td>
            <td style="padding: 12px 16px; font-size: 14px; color: #555555;">
                {{ ucfirst(str_replace('_', ' ', $approvalRequest->document_type)) }}
            </td>
        </tr>
        @endif

        {{-- Requester/Student --}}
        @if(isset($showRequester) && $showRequester && $approvalRequest->user)
        <tr style="border-bottom: 1px solid #e9ecef;">
            <td style="padding: 12px 16px; font-size: 14px; font-weight: 600; color: #2c3e50; background-color: #f8f9fa;">
                Pemohon
            </td>
            <td style="padding: 12px 16px; font-size: 14px; color: #555555;">
                {{ $approvalRequest->user->name }}<br>
                <span style="color: #999999; font-size: 13px;">{{ $approvalRequest->user->email }}</span>
            </td>
        </tr>
        @endif

        {{-- Submission Date --}}
        <tr style="border-bottom: 1px solid #e9ecef;">
            <td style="padding: 12px 16px; font-size: 14px; font-weight: 600; color: #2c3e50; background-color: #f8f9fa;">
                Tanggal Pengajuan
            </td>
            <td style="padding: 12px 16px; font-size: 14px; color: #555555;">
                {{ $approvalRequest->created_at->format('d F Y, H:i') }} WIB
            </td>
        </tr>

        {{-- Priority --}}
        @if(isset($approvalRequest->priority))
        <tr style="border-bottom: 1px solid #e9ecef;">
            <td style="padding: 12px 16px; font-size: 14px; font-weight: 600; color: #2c3e50; background-color: #f8f9fa;">
                Prioritas
            </td>
            <td style="padding: 12px 16px; font-size: 14px; color: #555555;">
                @php
                    $priorityBadge = [
                        'high' => ['class' => 'badge-danger', 'text' => 'Tinggi'],
                        'medium' => ['class' => 'badge-warning', 'text' => 'Sedang'],
                        'low' => ['class' => 'badge-info', 'text' => 'Rendah']
                    ][$approvalRequest->priority] ?? ['class' => 'badge-info', 'text' => ucfirst($approvalRequest->priority)];
                @endphp
                <span class="badge {{ $priorityBadge['class'] }}" style="display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 600; border-radius: 4px; text-transform: uppercase;">
                    {{ $priorityBadge['text'] }}
                </span>
            </td>
        </tr>
        @endif

        {{-- Status --}}
        @if(isset($showStatus) && $showStatus)
        <tr>
            <td style="padding: 12px 16px; font-size: 14px; font-weight: 600; color: #2c3e50; background-color: #f8f9fa;">
                Status
            </td>
            <td style="padding: 12px 16px; font-size: 14px; color: #555555;">
                @php
                    $statusBadge = [
                        'pending' => ['class' => 'badge-warning', 'text' => 'Menunggu Persetujuan'],
                        'approved' => ['class' => 'badge-success', 'text' => 'Disetujui'],
                        'rejected' => ['class' => 'badge-danger', 'text' => 'Ditolak'],
                        'signed' => ['class' => 'badge-info', 'text' => 'Sudah Ditandatangani'],
                        'verified' => ['class' => 'badge-success', 'text' => 'Terverifikasi']
                    ][$approvalRequest->status] ?? ['class' => 'badge-info', 'text' => ucfirst($approvalRequest->status)];
                @endphp
                <span class="badge {{ $statusBadge['class'] }}" style="display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 600; border-radius: 4px; text-transform: uppercase;">
                    {{ $statusBadge['text'] }}
                </span>
            </td>
        </tr>
        @endif

        {{-- Notes --}}
        @if($approvalRequest->notes)
        <tr>
            <td style="padding: 12px 16px; font-size: 14px; font-weight: 600; color: #2c3e50; background-color: #f8f9fa;">
                Catatan
            </td>
            <td style="padding: 12px 16px; font-size: 14px; color: #555555;">
                {{ $approvalRequest->notes }}
            </td>
        </tr>
        @endif
    </table>
</div>
