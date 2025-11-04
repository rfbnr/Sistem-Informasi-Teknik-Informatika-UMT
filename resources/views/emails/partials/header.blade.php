{{-- Email Header Component --}}
<div class="logo">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                {{-- University Logo --}}
                <div style="background-color: rgba(255, 255, 255, 0.95); padding: 12px 24px; border-radius: 8px; display: inline-block;">
                    <span style="font-size: 28px; font-weight: 700; color: #667eea; letter-spacing: -1px;">
                        UMT
                    </span>
                    <span style="font-size: 14px; color: #764ba2; font-weight: 600; margin-left: 4px;">
                        INFORMATIKA
                    </span>
                </div>
            </td>
        </tr>
    </table>
</div>

<h1>{{ $title ?? 'Digital Signature System' }}</h1>

@if(isset($subtitle))
<p class="subtitle">{{ $subtitle }}</p>
@endif
