{{-- Button Component --}}
{{--
    Usage:
    @include('emails.components.button', [
        'url' => 'https://example.com',
        'text' => 'Click Me',
        'type' => 'primary', // primary, secondary
        'block' => false // true for full width
    ])
--}}

@php
    $type = $type ?? 'primary';
    $block = $block ?? false;
    $buttonClass = 'button';

    if ($type === 'secondary') {
        $buttonClass .= ' button-secondary';
    }

    if ($block) {
        $buttonClass .= ' button-block';
    }
@endphp

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 20px 0;">
    <tr>
        <td align="{{ $block ? 'left' : 'center' }}">
            <a href="{{ $url }}" class="{{ $buttonClass }}" style="display: {{ $block ? 'block' : 'inline-block' }}; padding: 14px 32px; margin: 0; background: {{ $type === 'secondary' ? '#ffffff' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }}; color: {{ $type === 'secondary' ? '#667eea' : '#ffffff' }} !important; font-size: 15px; font-weight: 600; text-decoration: none; border-radius: 6px; text-align: center; {{ $type === 'secondary' ? 'border: 2px solid #667eea;' : '' }}">
                {{ $text }}
            </a>
        </td>
    </tr>
</table>
