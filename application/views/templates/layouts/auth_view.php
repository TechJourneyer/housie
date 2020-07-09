<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en" ng-app="app">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Online Housie | Sign In</title>

    <?php $this->load->view('templates/components/common_plugins_top');?>
  </head>
  <style>
    html {
        position: relative;
        min-height: 100%;
    }
    body {
        margin-bottom: 60px; /* Margin bottom by footer height */
    }
    .footer {
        position: absolute;
        bottom: 0;
        width: 100%;
        background-color: #f5f5f5;
    }
</style> 
  <body >
    <?php $this->load->view('templates/components/header'); ?>
    <div class="bg"></div>
    <div class="bg bg2"></div>
    <div class="bg bg3"></div>
    <div class="pt-3 ">
      <div class=" container">
        <?php $this->load->view($view_page,$data); ?>
      </div>
    </div>
    
    <?php $this->load->view('templates/components/footer'); ?>

    <?php $this->load->view('templates/components/common_plugins_bottom');?>
  </body>
</html>