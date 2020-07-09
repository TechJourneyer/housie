<?php
use Kreait\Firebase\Factory;
defined('BASEPATH') OR exit('No direct script access allowed');

class Firebase {
	public function __construct(){
        $this->factory = (new Factory)->withServiceAccount( getenv('FIREBASE_ACC_CRED') );
	}

	public function post($path,$data){
		$database = $this->factory->createDatabase();
		$result = $database->getReference($path)->push($data);
		return $result;
	}

    public function verifyToken($idTokenString){
        $auth = $this->factory->createAuth();
		try {
			$verifiedIdToken = $auth->verifyIdToken($idTokenString);
		} catch (\InvalidArgumentException $e) {
            return $this->setResponse("failed" , $e->getMessage());
		} catch (InvalidToken $e) {
            return $this->setResponse("failed" , $e->getMessage());
		}
		$uid = $verifiedIdToken->getClaim('sub');
		$user = $auth->getUser($uid);
        return $this->setResponse("success" , "",$user);
    }

    public function createUser(){
		$auth = $this->factory->createAuth();
		$userProperties = [
			'email' => 'swap.1710@gmail.com',
			'emailVerified' => false,
			'phoneNumber' => '+15555550100',
			'password' => 'password',
			'displayName' => 'John Doe',
			'photoUrl' => 'http://www.example.com/12345678/photo.png',
			'disabled' => false,
		];
        $createdUser = $auth->createUser($userProperties);
        return $createdUser;
	}

    public function signIn(){
		$auth = $this->factory->createAuth();
		$signInResult = $auth->signInWithEmailAndPassword("swapnilm1710@gmail.com", "password");
		$json = json_encode($signInResult);
		$arr = json_decode($json,true);
		return $arr;
    }
	
	public function signOut(){
		$auth = $this->factory->createAuth();
		$res = $auth->signOut();
		return $res;
    }
    
    public function setResponse($status,$msg="",$output=null){
        $output =  [
            'status' => $status,
            'message' => $msg,
            'output' => $output
        ];
        $outputJson = json_encode($output);
        return  json_decode($outputJson,true);
	}
	
	public function check(){
		$auth = $this->factory->createAuth();
		x($auth);
	}
}
