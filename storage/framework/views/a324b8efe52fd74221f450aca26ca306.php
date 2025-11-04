<!DOCTYPE html>
<html>

<head>
    <title>Create Event</title>
</head>

<body>
    <h1>Create Event</h1>
    <form action="<?php echo e(route('events.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br><br>
        <label for="title">description:</label>
        <input type="text" id="description" name="description" required><br><br>
        <label for="start_time">Start Time:</label>
        <input type="datetime-local" id="start_time" name="start_time" required><br><br>
        <label for="end_time">End Time:</label>
        <input type="datetime-local" id="end_time" name="end_time" required><br><br>
        <button type="submit">Save</button>
    </form>
</body>

</html>
<?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/events/create.blade.php ENDPATH**/ ?>