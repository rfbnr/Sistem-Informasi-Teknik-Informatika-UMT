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

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" class="my-20">
    <tr>
        <td align="{{ $block ? 'left' : 'center' }}">
            <a href="{{ $url }}" class="{{ $buttonClass }}">
                {{ $text }}
            </a>
        </td>
    </tr>
</table>
