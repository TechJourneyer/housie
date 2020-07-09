<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script src='/assets/js/user/register.js'></script>
<style>
    .loginbox{
        box-shadow: 4px 7px 14px -4px #8AB7C6;
    }
</style>
<div ng-controller='user_register' class='p-3'>
    <div class="p-3">
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6 p-4 loginbox bg-light rounded border">
                <div class="bd-highlight">
                    <form method='post' action='/Auth/register'>
                        <div class="form-group row">
                            <label for="name" class="col-sm-3 col-form-label">Name</label>
                            <div class="col-sm-9">
                                <input type="name" class="form-control" name="name" id="name">
                                <p id='name_error' ><?php echo form_error('name'); ?></p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email_id" class="col-sm-3 col-form-label">Email id</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control" name="email_id" id="email_id">
                                <p id='email_error'><?php echo form_error('email_id'); ?></p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="username" class="col-sm-3 col-form-label">Username</label>
                            <div class="col-sm-9">
                                <input type="username" class="form-control" name="username" id="username">
                                <p id='username_error'><?php echo form_error('username'); ?></p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password" class="col-sm-3 col-form-label">Password</label>
                            <div class="col-sm-9">
                                <input type="password" class="form-control" name="password" id="password">
                                <p id='password_error'><?php echo form_error('password'); ?></p>
                            </div>
                        </div>
                        <div class="form-group row p-3">
                            <div class="col-sm text-center">
                                <button type="submit" class="btn btn-primary ">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-sm-3"></div>
        </div>
    </div>
</div>