<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .section-welcome{
        background-color: #58b5c3;
        color:white;
    }
    .main{
        background-color: #1a936f;
        color:white;
        min-height: 800px !important;
    }

    .image-container .play-btn {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        -ms-transform: translate(-50%, -50%);
        background-color: #555;
        color: white;
        font-size: 16px;
        padding: 12px 24px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        text-align: center;
    }
    .play-btn{
        opacity : 0.5;
        background-color: black !important;
        color:white !important;
        background-repeat:no-repeat !important;
        cursor:pointer !important;
        overflow: hidden !important;
        box-shadow: 4px 4px 11px -1px #000000;  
        text-decoration: none !important;
    }
    .image-container .play-btn:hover {
        opacity : 1;
    }
</style>
<div >
    <section id='welcome' class=''>
        <div class="image-container">
            <a href='/Game/createGroup' class="play-btn border">
                <strong>
                <?php echo (gameOn()) ? 'Play' : 'Create New group' ; ?>
                </strong>
            </a>
        </div>
    </section>
</div>
