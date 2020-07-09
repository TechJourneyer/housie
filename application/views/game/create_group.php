<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
.box-style{
    box-shadow: 2px 2px 5px 2px #888888;
    opacity:0.8;
}
</style>
<script src='/assets/js/game/create_group.js'></script>
<div ng-controller='create_group' class='container' ng-cloak>
    <div class="p-4">
        <div class="p-4 text-center bg-white box-shadow-style">
            <div class="container">
                <h3 class="display-5">Create Group</h3>
                <hr>
                <form class='p-4'>
                    <div class="row form-group">
                        <label class=" col-sm-3">Group Name</label>
                        <div class="col-sm-6">
                            <input id='group_name' type="text" minlength="4" required class="form-control">
                        </div>
                    </div>
                    <div class="row form-group ">
                        <label class=" col-sm-3">Ticket Prize (&#8377;)</label>
                        <div class="col-sm-6">
                            <input id='ticket_prize' type="number" min='10' required max='1000' class="form-control">
                        </div>
                    </div>
                    <div class="row form-group ">
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-6">
                            <button type='submit' ng-click="createGroup()" class='btn btn-md btn-primary'>Create</button>
                            &nbsp; &nbsp;
                            <button type='submit' ng-click="clear()" class='btn btn-md btn-light'>clear</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</div>