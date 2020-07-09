<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container p-2">
    <div class="alert <?php echo $class; ?> alert-dismissible fade show" role="alert">
    <strong><?php echo $status; ?> </strong> <?php echo $message; ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    </div>
</div>