<?php


    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: X-Accept-Charset,X-Accept,Content-Type,Authorization,Accept,Origin,Access-Control-Request-Method,Access-Control-Request-Headers");
    header("Content-Type: application/json");
    
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of index
 *
 * @author mac01
 */
               
include './TaskManagerBase.php';

class API extends TaskManagerBase {
    
    
    function createAuthToken($email){
        
        $login_time = time();
        $token = array("login_time"=> $login_time,"user_email"=>$email, "app_key"=> "PROJECT_WORK_MCA15002"); // App ID to verify authorized api calls
        $token_auth = API::encode($token, "university_of_mysore"); // secreat key to encript token
        
        return $token_auth;
        
    }
    
     //Token Create
     
        public static function decode($t_auth, $key = null, $verify = true)
	{
                
		$tks = explode('.', $t_auth);
		if (count($tks) != 3) {
 			throw new UnexpectedValueException('Wrong number of segments');
                } 
		list($headb64, $bodyb64, $cryptob64) = $tks;
		if (null === ($header = API::jsonDecode(API::urlsafeB64Decode($headb64)))) {
                        
 			throw new UnexpectedValueException('Invalid segment encoding');
                } 
		if (null === $payload = API::jsonDecode(API::urlsafeB64Decode($bodyb64))) {
 			throw new UnexpectedValueException('Invalid segment encoding');
		}
		$sig = API::urlsafeB64Decode($cryptob64);
		if ($verify) {
			if (empty($header->alg)) {
				throw new DomainException('Empty algorithm');
			}
			if ($sig != API::sign("$headb64.$bodyb64", $key, $header->alg)) {
				throw new UnexpectedValueException('Signature verification failed');
			}
		}
		return $payload;
	}
        
	/**
	 * Converts and signs a PHP object or array into a ApplicationController string.
	 *
	 * @param object|array $payload PHP object or array
	 * @param string       $key     The secret key
	 * @param string       $algo    The signing algorithm. Supported
	 *                              algorithms are 'HS256', 'HS384' and 'HS512'
	 *
	 * @return string      A signed ApplicationController
	 * @uses jsonEncode
	 * @uses urlsafeB64Encode
	 */
        
	public static function encode($payload, $key, $algo = 'HS256')
	{
		$header = array('typ' => 'ApplicationController', 'alg' => $algo);
		$segments = array();
		$segments[] = API::urlsafeB64Encode(API::jsonEncode($header));
		$segments[] = API::urlsafeB64Encode(API::jsonEncode($payload));
		$signing_input = implode('.', $segments);
		$signature = API::sign($signing_input, $key, $algo);
		$segments[] = API::urlsafeB64Encode($signature);
		return implode('.', $segments);
	}
	/**
	 * Sign a string with a given key and algorithm.
	 *
	 * @param string $msg    The message to sign
	 * @param string $key    The secret key
	 * @param string $method The signing algorithm. Supported
	 *                       algorithms are 'HS256', 'HS384' and 'HS512'
	 *
	 * @return string          An encrypted message
	 * @throws DomainException Unsupported algorithm was specified
	 */
	public static function sign($msg, $key, $method = 'HS256')
	{
		$methods = array(
			'HS256' => 'sha256',
			'HS384' => 'sha384',
			'HS512' => 'sha512',
		);
		if (empty($methods[$method])) {
			throw new DomainException('Algorithm not supported');
		}
		return hash_hmac($methods[$method], $msg, $key, true);
	}
	/**
	 * Decode a JSON string into a PHP object.
	 *
	 * @param string $input JSON string
	 *
	 * @return object          Object representation of JSON string
	 * @throws DomainException Provided string was invalid JSON
	 */
	public static function jsonDecode($input)
	{
		$obj = json_decode($input);
		if (function_exists('json_last_error') && $errno = json_last_error()) {
			API::_handleJsonError($errno);
		} else if ($obj === null && $input !== 'null') {
			throw new DomainException('Null result with non-null input');
		}
		return $obj;
	}
	/**
	 * Encode a PHP object into a JSON string.
	 *
	 * @param object|array $input A PHP object or array
	 *
	 * @return string          JSON representation of the PHP object or array
	 * @throws DomainException Provided object could not be encoded to valid JSON
	 */
	public static function jsonEncode($input)
	{
		$json = json_encode($input);
		if (function_exists('json_last_error') && $errno = json_last_error()) {
			API::_handleJsonError($errno);
		} else if ($json === 'null' && $input !== null) {
			throw new DomainException('Null result with non-null input');
		}
		return $json;
	}
	/**
	 * Decode a string with URL-safe Base64.
	 *
	 * @param string $input A Base64 encoded string
	 *
	 * @return string A decoded string
	 */
	public static function urlsafeB64Decode($input)
	{
		$remainder = strlen($input) % 4;
		if ($remainder) {
			$padlen = 4 - $remainder;
			$input .= str_repeat('=', $padlen);
		}
		return base64_decode(strtr($input, '-_', '+/'));
	}
	/**
	 * Encode a string with URL-safe Base64.
	 *
	 * @param string $input The string you want encoded
	 *
	 * @return string The base64 encode of what you passed in
	 */
	public static function urlsafeB64Encode($input)
	{
		return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
	}
	/**
	 * Helper method to create a JSON error.
	 *
	 * @param int $errno An error number from json_last_error()
	 *
	 * @return void
	 */
	private static function _handleJsonError($errno)
	{
		$messages = array(
			JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
			JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
			JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
		);
		throw new DomainException(
			isset($messages[$errno])
			? $messages[$errno]
			: 'Unknown JSON error: ' . $errno
		);
	}
        
        
        function isAuthCheck($auth){
        
        $isAuthenticated = FALSE;

        $tks = explode('.', $auth);

        if (count($tks) != 3) {

            return FALSE;
        }
        
        try {
            
            $token = API::decode($auth, 'university_of_mysore');

        }catch(Exception $e) {
          //echo 'Message: ' .$e->getMessage();
           return FALSE;
        }

        
        if(!empty($token->user_email) && !empty($token->login_time) && !empty($token->app_key)){
            
                $log_time = $token->login_time;
                $app_key = $token->app_key;
                $curtime = time();    
                $timeDif = $curtime - $log_time;
                 if($timeDif > 86400) {  // Expire login time after 24 Hrs
                    
                    $isAuthenticated = FALSE;
                    
                }else{
                    if ($app_key == "PROJECT_WORK_MCA15002"){ // compare app ID
                       $isAuthenticated = TRUE;
                    }

                }
            
        }else{
            
           
            $isAuthenticated = FALSE;
            
        } 
        
        return $isAuthenticated; 
        
    }
    
    
    // After authorised accissible API calls for Application
    function captureEndpoints(){
        
       
            $data = json_decode(file_get_contents('php://input'), true);
            
            $cmd = strtolower($data['action']);
                     
            switch($cmd){
                               
                //USERS Operations
               case 'forgot-password':
                   $this->getPassword($data);
                   break;
               case 'register': // Attendees
                  $this->registerNewEmployee($data);
                   break;
               case 'view-users':
                   $this->viewUsers($data);
                  break;
               case 'remove-users':
                   $this->removeUser($data);
                  break;
              case 'edit-user':
                   $this->updateUser($data);
                  break;
              
              //ATTTENDANCE
               case 'capture-attendance': // Attendees
                  $this->captureAttendance($data);
                   break;
              case 'attendance':
                   $this->attendance($data);
                  break;
              case 'leaves':
                   $this->leaves($data);
                  break;
              case 'apply-leaves':
                  $this->applyLeaves($data);
                  break;
                case 'approve-leaves':
                   $this->approveLeaves($data);
                   break;
              
               //ACTIVITY
              case 'timeline':
                  $this->timelineMessages();
                  break;
              case 'update-status': // Attendees
                  $this->updateStatus($data);
                  break;
              
              
              //MESSAGES
              case 'inbox':
                  $this->userInbox($data);
                  break;
              case 'send-message': // Attendees
                  $this->sendMessage($data);
                  break;
              case 'replay-message': // Attendees
                  $this->replayMessage($data);
                  break;
              
              //Control Panel
              case 'add-bonus':
                   $this->addNewBonus($data);
                  break;
              case 'remove-bonus':
                   $this->removeBonus($data);
                  break;
              case 'bonus-category':
                   $this->bonusCategory();
                  break;
               case 'add-announcement':
                   $this->addAnnouncement($data);
                  break;
               case 'remove-announcement':
                   $this->removeAnnouncement($data);
                  break;
              case 'announcements':
                   $this->announcement();
                  break;
               case 'fetch-settings':
                   $this->settings();
                  break;
               case 'save-settings':
                   $this->saveSettings($data);
                   break;
              
              
                //TASK Operations
              case 'add-task':
                   $this->addNewTask($data);
                  break;
              case 'assign-task':
                   $this->assignTaskTo($data);
                  break;
              case 'edit-task':
                   $this->editTask($data);
                  break;
               case 'remove-task':
                   $this->removeTask($data);
                  break;
               case 'update-task':
                   $this->updateTaskStatus($data);
                  break;
              case 'update-task-remarks':
                   $this->updateRemarks($data);
                  break;
              case 'available-task-adm':
                   $this->fetchAvailableTask();
                  break;
              case 'available-task':
                   $this->fetchTaskWithType('available', $data);
                  break;
              case 'assigned-task':
                   $this->fetchTaskWithType('assigned', $data);
                  break;
              case 'completed-task':
                   $this->fetchTaskWithType('completed',$data);
                  break;
              case 'pending-task':
                   $this->fetchTaskWithType('pending',$data);
                  break;
              case 'task-progress':
                   $this->initCurrentReport($data);
                  break;
              case 'bonus-report':
                   $this->bonusReport($data);
                  break;
                default :
                    $this->response($this->successCode, "Default Call", []);
                    return;
            }
            
   }
   
   function init(){
       
        $headers = apache_request_headers();

        $auth = $headers['Authorization'];

        if($auth == NULL){
            $auth = $headers['authorization'];
        }
        
        if ($this->isAuthCheck($auth)){
            $this->captureEndpoints();
        }else{
            
            $data = json_decode(file_get_contents('php://input'), true);
            $cmd = strtolower($data['action']);
                     
            switch($cmd){
                //Authentication
                case 'login':
                   $user_id =  mysqli_real_escape_string($this->getConn(),$data['email']);
                   $new_token = $this->createAuthToken($user_id);
                   $this->authenticateUser($data, $new_token);
                   return;
                  break;
               case 'forgot-password':
                   $this->getPassword($data);
                   break;
               default :
                   $this->response($this->failureCode, "Un authorised API Call. Bounce", []);
            }
            
        }
       
   }
      
    
}


$self = new API();
$self->init();