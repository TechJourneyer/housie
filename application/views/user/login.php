<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script src='/assets/js/user/login.js'></script>
<style>
    .loginbox{
        background-color: Transparent !important;
        box-shadow: 4px 7px 14px -4px #8AB7C6;
    }
    .loginbox:hover{
        background-color: whitesmoke !important;
    }
</style>
<div ng-controller='user_login'  class='p-3' ng-cloak>
    <div class="p-3" ng-if='!loading'  ng-if='!is_login'>
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6 p-4 loginbox bg-light rounded border">
                <div class="bd-highlight" >
                    <div id="firebaseui-auth-container" ></div>
                </div>
            </div>
            <div class="col-sm-3"></div>
        </div>
    </div>
    <div  ng-if='loading' id="loader" ></div>
</div>
