<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? h($pageTitle) . ' / ' . APP_NAME : APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (function_exists('current_user') && current_user()): ?>
    <meta name="csrf-token" content="<?php echo h(csrf_token()); ?>">
    <?php endif; ?>
</head>
<body>
