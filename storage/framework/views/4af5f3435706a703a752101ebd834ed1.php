<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo e($subject ?? 'UMT Informatika - Digital Signature System'); ?></title>
    <style>
        <?php echo file_get_contents(resource_path('views/emails/styles/email-styles.css')); ?>

    </style>
</head>
<body>
    <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="email-container" width="600" cellpadding="0" cellspacing="0" role="presentation">
                    <!-- Header -->
                    <tr>
                        <td class="email-header">
                            <?php echo $__env->yieldContent('header'); ?>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="email-content">
                            <?php echo $__env->yieldContent('content'); ?>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            <?php echo $__env->yieldContent('footer'); ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/emails/layouts/master.blade.php ENDPATH**/ ?>