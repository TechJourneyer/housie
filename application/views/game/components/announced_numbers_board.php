<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .number-box:hover {
        background-color:black;
        color:white;
    }
</style>

<?php 
    $arr = [5,13,12,78];
    
    for($i=1;$i<=10;$i++){ 
        echo '<div class="d-flex bd-highlight ">';
        for($j=1;$j<=10;$j++){ 
            $num = (($i - 1) * 10) + $j;
            if($num<100){
                $class = (in_array($num,$arr)) ? "text-white bg-info" : "";
                echo "<div class='p-1 text-center   $class flex-fill number-box  border'>".numPrefix($num). '</div>';
            }
            else{
                echo '<div class="p-1 text-center text-muted flex-fill   border">xx</div>';
            }
        } 
        echo '</div>';
    }
?>
