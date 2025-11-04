


<?php
    $type = $type ?? 'primary';
    $block = $block ?? false;
    $buttonClass = 'button';

    if ($type === 'secondary') {
        $buttonClass .= ' button-secondary';
    }

    if ($block) {
        $buttonClass .= ' button-block';
    }
?>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" class="my-20">
    <tr>
        <td align="<?php echo e($block ? 'left' : 'center'); ?>">
            <a href="<?php echo e($url); ?>" class="<?php echo e($buttonClass); ?>">
                <?php echo e($text); ?>

            </a>
        </td>
    </tr>
</table>
<?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/emails/components/button.blade.php ENDPATH**/ ?>