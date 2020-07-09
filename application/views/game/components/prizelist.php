<div ng-repeat='p in prizes' class="media pt-3 border-bottom border-gray">
    <span style ='color:#f5cd57 !important' class="material-icons md-36 mr-2  " width="32" height="32" >emoji_events</span>
    <p class="media-body pb-3 mb-0">
        <strong class="d-block text-gray-dark">
            {{p.name}}
            <span ng-if="p.status=='open'" class='text-info'>(Open)</span>
            <span ng-if="p.status=='closed'" class='text-success'>(closed)</span>
            <span ng-if="p.status=='claimed'" class='text-danger'>(claimed)</span>
        </strong>
        ₹ {{p.prize}}
    </p>
</div>