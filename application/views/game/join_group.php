<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    let groupId = '<?php echo $group_id; ?>';
</script>
<script src='/assets/js/game/join_group.js'></script>

<?php echo $note; ?>
<!-- show_join_page -->
<?php if($show_join_page){ ?>

<div ng-controller= 'join_group' class="container p-2" ng-cloak>
    <div ng-if='loading'>
        Loading Group details ... {{loading}}
    </div>
    <div ng-hide='loading'>
        <div class="card">
            <h5 class="card-header">Group Name : <strong>{{game_details.group_name}}</strong></h5>
            <div class="card-body">
                
                <h5 class="card-title text-info text-center">Buy ticket at price &#8377; {{game_details.ticket_price}}</h5>

                <h5 class="card-title">Joined Users ({{users.length}})</h5>
                <hr>
                <ul class="list-group">
                    <li ng-repeat='u in users track by $index' class="list-group-item d-flex justify-content-between align-items-center">
                        {{$index+1}}. {{u.user_name}}
                        <span ng-if='u.admin' class="badge badge-primary badge-md ">Admin</span>
                    </li>
                </ul>
            </div>
        </div>
        <br>
        <div class="p-2 text-center">
            <button ng-click="joinGroup()" class='btn btn-primary'>Join Group</button>  
        </div> 
    </div>
</div>

<?php } ?>