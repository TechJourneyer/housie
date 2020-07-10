<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
function y($str){
    echo "<pre>";
    print_r($str);
    echo "</pre>";
}

function x($str){
    y($str); exit;
}

##  Validation Methods : start 
function validEmail($email){
    $email = trim($email);

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
       return true;
    }
    return false;
}

function validName($name){
    $name = trim($name);
    if ( empty($name) || !preg_match("/^[a-zA-Z ]*$/",$name)) {
        return false;
    }
    return true;
}

function validPassword($password){
    $password = trim($password);
    $passwordLength = strlen($password);
    if ($passwordLength <=3 ) {
        return false;
    }
    return true;
}
##  Validation Methods : ends

## Encryption Methods : Start
function encrypt($password){
    $ciphering = "AES-128-CTR";  
    $encryption_iv = '1234567891011121'; 
    $encryption_key = "swapmilm"; 
    $options = 0; 
    $encryption = openssl_encrypt($password, $ciphering, 
                $encryption_key, $options, $encryption_iv); 
    return $encryption;
}

function decrypt($encryption){
    $ciphering = "AES-128-CTR";  
    $decryption_iv = '1234567891011121'; 
    $decryption_key = "swapmilm"; 
    $options = 0; 
    $decryption = openssl_decrypt ($encryption, $ciphering, $decryption_key, $options, $decryption_iv); 
    return $decryption ;
}
## Encryption Methods : Ends

## Send Response Methods : start  
function response($result , $status = 'success' , $message = '' ,$errorCode = false ){
    // Clean output buffer
    if (ob_get_level() !== 0 && @ob_end_clean() === FALSE)
    {
        @ob_clean();
    }
    header('Content-Type: application/json');
    $output =  [
        'result' => $result,
        'status' => $status,
        'message' => $message,
        'error_code' => $errorCode
    ];
    echo json_encode($output,JSON_PRETTY_PRINT);
    exit;
}

function flashSuccess($msg){
    $session = getInstance('session'); 
    $session->set_flashdata('success', $msg);
}

function flashError($msg){
    $session = getInstance('session');
    $session->set_flashdata('error', $msg);
}

function flashData($key,$msg){
    $session = getInstance('session');
    $session->set_flashdata($key, $msg);
}

function flashMessage($key){
    $session = getInstance('session');
    return $session->flashdata($key);
}
## Send Response Methods : ends


function sessionExist(){
    if(isset($_SESSION['is_login']) && $_SESSION['is_login'] == true){
        return true;
    }
    return false;
}

function sessionId(){
    return $_SESSION["session_id"];
}
function groupSessionId(){
    return $_SESSION["group_session_id"];
}

function loginAs(){
    return $_SESSION['login_as'];
}

function gameOn(){
    return $_SESSION['game_on'];
}

function userTable(){
    return (loginAs() == 'user') ? 'users' : 'guests';
}

function checkLogin($redirect = true){
    $ci = getInstance();
    if(sessionExist()){
        $userDetails = fetchUserDetails();
        if(!empty($userDetails)){
            return $userDetails;
        }
    }
    if($redirect === false){
        return false;    
    }
    else if($redirect === true){
        redirect(base_url('login')); 
    }
    else{
        set_cookie('join_redirect',$redirect,3600);
        redirect(base_url('login')); 
    }
}

function fetchUserDetails(){
    $ci = getInstance();
    $tableName = loginAs() == 'user' ? 'users' : 'guests' ;
    $userDetails = $ci->auth_model->fetchUserDetails(['u.uid' => getUid()],$tableName);
    if(!empty($userDetails)){
        if($userDetails['session_id'] == sessionId()){
            $gameDetails = $ci->auth_model->fetchGameDetails();
            $ci->auth_model->updateGroupSessionDetails($userDetails,$gameDetails);
            $ci->auth_model->updateOnlineStatus($userDetails['id']);
            return $userDetails;
        }
    }

    return false;
}

function apiAuthentication(){
    if(sessionExist()){
        $userDetails = fetchUserDetails();
        if(!empty($userDetails)){
            return $userDetails;
        }
    }
    response([],"failed","Authentication failed");
    return false;
}

function getInstance($class = false){
    $ciInstance =& get_instance();
    if($class){
        return $ciInstance->{$class};
    }
    return $ciInstance;
}

function numPrefix($num){
    if(strlen($num) == 1){
        return "0".$num;
    }
    return $num;
}

function checkActiveMenu($url,$exactMatch = false){
    $uri = $_SERVER['REQUEST_URI'];
    if($exactMatch){
        if($url == $uri){
            return true;
        }
        return false;
    }
    if(strpos($uri, $url) !== false){
        return true;
    }
    return false;
}

function checkActiveMenus($urls){
    foreach($urls as $url){
        if(checkActiveMenu($url)){
            return true;
        }
    }
    return false;
}

function inputValidations($inputs,$validationMethod = 'empty'){
    foreach ($inputs as  $value) {
        if($validationMethod == 'empty'){
            if(!isset($_POST[$value]) || empty($_POST[$value])){
                response([] , 'failed' , $message = "input '$value' is required" );
            }
        }
    }
}

function showNote($status,$message,$class = false,$print = true){
    if($class === false){
        $classList = [
            "Success" => "alert-success",
            "Warning" => "alert-warning",
            "Failed" => "alert-danger",
            "Error" => "alert-danger",
        ];
        $class = "alert-info";
        if(isset($classList[$status])){
            $class = $classList[$status];
        }
    }
    return get_instance()->load->view(
        'shared/components/note',
        [
            'status' => $status,
            'message' => $message,
            'class' => $class
        ],
        !$print
    );
}

function curlCall($url,$method,$params){
    $array = [];
    $curl = curl_init();
    $curlOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 0,
    ];
    if($method == 'POST'){
        $curlOptions[CURLOPT_URL] = $url;
        $curlOptions[CURLOPT_POST] = true;
        $curlOptions[CURLOPT_POSTFIELDS] = $params;
    }
    else{
        foreach($params as $key =>  $val){
            $val = urlencode($val);
            $array[] = "$key=$val";
        }
        $requestParameters = implode("&" , $array);
        $curlOptions[CURLOPT_URL] = $url . "?$requestParameters" ;
        $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
    }
    curl_setopt_array($curl, $curlOptions);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $output = [];
    if ($err) {
        $output['status'] = "failed";
        $output['result'] = null;
        $output['message'] = $err;
    } else {
        $output['status'] = "success";
        $output['result'] = $response;
        $output['message'] = "";
    }
    return $output;
}

function groupIdEncode($groupId){
    return base64_encode($groupId);
}

function groupIdDecode($groupIdEncoded){
    return base64_decode($groupIdEncoded);
}

function fetchInputs(){
    if(empty($_POST)){
        $postdata   = file_get_contents("php://input");
        $_POST = json_decode($postdata,true);
    }
}

function amount_format($number){
    return number_format((float)$number, 2, '.', '');
}

function dividendList(){
    return [
        'full_house' => 'Full house'
    ];
}

function prizes($ticketPrice = 100,$ticketCount=false){
    $tickiCountWisePrize = [
        '2' => [
            'full_house' => 100
        ],
        '3' => [
            'full_house' => 70,
            'any_line' => 30
        ],
        '4' => [
            'full_house' => 50,
            'any_line' => 30,
            'early_seven' => 20
        ],
        '5' => [
            'full_house' => 50,
            'any_line' => 20,
            'early_seven' => 15,
            'first_five' => 15
        ], 
    ];
    
    $prizes = [
        'full_house' => [
            'name' => "Full house",
            'prize_value' => amount_format(($ticketPrice * 35) / 100),
            'winners' => [],
            'status' => '',
        ],
        'first_five' => [
            'name' => "First Five",
            'prize_value' => amount_format(($ticketPrice * 20) / 100),
            'winners' => [],
            'status' => '',
        ],
        'top_line' => [
            'name' => "Top line",
            'prize_value' => amount_format(($ticketPrice * 15) / 100),
            'winners' => [],
            'status' => '',
        ],
        'middle_line' => [
            'name' => "Middle line",
            'prize_value' => amount_format(($ticketPrice * 15) / 100),
            'winners' => [],
            'status' => '',
        ],
        'bottom_line' => [
            'name' => "Bottom line",
            'prize_value' => amount_format(($ticketPrice * 15) / 100),
            'winners' => [],
            'status' => '',
        ]
    ];
    return $prizes;
}

function getNumbersArray($string){
    $array = array_map('intval', getArray($string) );
    return $array;
}

function getArray($string){
    $array = explode(",", $string);
    return array_filter($array);
}

function getString($array){
    return implode(",", $array);
}

function objectToArray($object){
    $json = json_encode($object);
    return json_decode($json,true);
} 

function getUid(){
    return $_SESSION['uid'];
}
?>