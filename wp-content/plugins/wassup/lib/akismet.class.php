<?php
/**
 * For Akismet spam check on wassup visitor records
 *
 * @package WassUp Real-time Analytics
 * @subpackage akismet.class.php module
 */
//no direct request for this plugin module
$wfile=preg_replace('/\\\\/','/',__FILE__); //for windows
if(!defined('ABSPATH')|| empty($GLOBALS['wp_version'])|| preg_match('#'.preg_quote(basename($wfile)).'#',$_SERVER['PHP_SELF'])|| !empty($_SERVER['SCRIPT_FILENAME'])&& realpath($wfile)===realpath($_SERVER['SCRIPT_FILENAME'])){
	if(!headers_sent()){header('Location: /?p=404page&err=wassup403');exit;
	}elseif(function_exists('wp_die')){wp_die("Bad Request: ".esc_attr(wp_kses(preg_replace('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/i','',$_SERVER['REQUEST_URI']),array())));exit;
	}else{die("Bad Request: ".htmlspecialchars(preg_replace('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/i','',$_SERVER['REQUEST_URI'])));exit;}
	exit;
}
unset($wfile);
//New in Wassup v1.9: Classes and constants renamed for compatibility with Akismet v3.0.0 -Helene D. 2014-05-01
/**
 * 08.11.2010 22:25:17est
 * 
 * Akismet PHP4 class
 * 
 * <b>Usage</b>
 * <code>
 *    $comment = array(
 *           'author'    => 'viagra-test-123',
 *           'email'     => 'test@example.com',
 *           'website'   => 'http://www.example.com/',
 *           'body'      => 'This is a test comment',
 *           'permalink' => 'http://yourdomain.com/yourblogpost.url',
 *        );
 *
 *    $akismet = new Akismet('http://www.yourdomain.com/', 'YOUR_WORDPRESS_API_KEY', $comment);
 *
 *    if($akismet->errorsExist()) {
 *        echo"Couldn't connected to Akismet server!";
 *    } else {
 *        if($akismet->isSpam()) {
 *            echo"Spam detected";
 *        } else {
 *            echo"yay, no spam!";
 *        }
 *    }
 * </code>
 * 
 * @author Bret Kuhns {@link www.bretkuhns.com}
 * @link http://code.google.com/p/akismet-php4
 * @version 0.3.5
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
// Error constants
define("WASSUP_AKISMET_SERVER_NOT_FOUND",	0);
define("WASSUP_AKISMET_RESPONSE_FAILED",	1);
define("WASSUP_AKISMET_INVALID_KEY",		2);

// Base class to assist in error handling between Akismet classes
class wassup_AkismetObject {
	var $errors = array();
	
	// Set an error in the object
	function setError($name,$message){$this->errors[$name]=$message;}
	function getError($name){
		if($this->isError($name)){return $this->errors[$name];}
		else {return false;}
	}
	
	//Return all errors in the object
	function getErrors(){return (array)$this->errors;}
	
	// Check if a certain error exists
	function isError($name){return isset($this->errors[$name]);}
	
	// Check if any errors exist
	function errorsExist(){return (count($this->errors)>0);}

	//New in Wassup v1.9: Remove timeout error
	function removeError($name,$message){
		if(!empty($this->errors[$name])&& $this->errors[$name]==$message)unset($this->errors[$name]);
	}
}

// Used by the wassup_Akismet class to communicate with the Akismet service
class wassup_AkismetHttpClient extends wassup_AkismetObject {
	var $akismetVersion='1.1';
	var $con;
	var $host;
	var $port;
	var $apiKey;
	var $blogUrl;
	var $errors=array();
	
	// Constructor
	function wassup_AkismetHttpClient($host,$blogUrl,$apiKey,$port=80){
		$this->host=$host;
		$this->port=$port;
		$this->blogUrl=$blogUrl;
		$this->apiKey=$apiKey;
	}
	
	// Use the connection active in $con to get a response from the server and return that response
	function getResponse($request,$path,$type="post",$responseLength=1160){
		$this->_connect();
		if($this->con && !$this->isError(WASSUP_AKISMET_SERVER_NOT_FOUND)){
			$request=strToUpper($type)." /{$this->akismetVersion}/$path HTTP/1.0\r\n" .
				"Host: ".((!empty($this->apiKey)) ? $this->apiKey."." : null)."{$this->host}\r\n" .
				"Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n" .
				"Content-Length: ".strlen($request)."\r\n" .
				"User-Agent: Wassup ".WASSUPVERSION." Akismet PHP4 Class\r\n" .
				"\r\n".$request;
			$response="";
			@fwrite($this->con,$request);
			while(!feof($this->con)){
				$response .= @fgets($this->con,$responseLength);
			}
			$response=explode("\r\n\r\n",$response,2);
			return $response[1];
		}else{
			$this->setError(WASSUP_AKISMET_RESPONSE_FAILED, __("The response could not be retrieved.","wassup"));
		}
		$this->_disconnect();
	}
	
	// Connect to the Akismet server and store that connection in the instance variable $con
	function _connect(){
		if(!($this->con=@fsockopen($this->host,$this->port))){
			$this->setError(WASSUP_AKISMET_SERVER_NOT_FOUND,__("Could not connect to akismet server.","wassup"));
		}
	}
	
	// Close the connection to the Akismet server
	function _disconnect(){@fclose($this->con);}
} //end Class

// The controlling class. This is the ONLY class the user should instantiate in order to use the Akismet service!
class wassup_Akismet extends wassup_AkismetObject {
	var $apiPort=80;
	var $akismetServer='rest.akismet.com';
	var $akismetVersion='1.1';
	var $http;
	var $ignore=array(
			'HTTP_COOKIE',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED_HOST',
			'HTTP_MAX_FORWARDS',
			'HTTP_X_FORWARDED_SERVER',
			'REDIRECT_STATUS',
			'SERVER_PORT',
			'PATH',
			'DOCUMENT_ROOT',
			'SERVER_ADMIN',
			'QUERY_STRING',
			'PHP_SELF',
			'argv');
	var $blogUrl="";
	var $apiKey ="";
	var $comment=array();
	
	/**
	 * Constructor
	 * Set instance variables, connect to Akismet, check API key
	 * 
	 * @param String $blogUrl	- The URL to your own blog
	 * @param  String $apiKey	- Your wordpress API key
	 * @param  String[] $comment	- A formatted comment array to be examined by the Akismet service
	 * @return Akismet
	 */
	function wassup_Akismet($blogUrl,$apiKey,$comment=array()) {
		$this->blogUrl=$blogUrl;
		$this->apiKey =$apiKey;
		$this->setComment($comment);
		
		// Connect to the Akismet server and populate errors if they exist
		$this->http=new wassup_AkismetHttpClient($this->akismetServer,$blogUrl,$apiKey);
		if($this->http->errorsExist()) {
			$this->errors = array_merge($this->errors, $this->http->getErrors());
		}
		
		// Check if the API key is valid
		if(!$this->_isValidApiKey($apiKey)){
			$this->setError(WASSUP_AKISMET_INVALID_KEY,__("Your Akismet API key is not valid.","wassup"));
		}
	}
	
	//Query the Akismet and determine if the comment is spam or not
	function isSpam() {
		//New in Wassup v1.9: shorten script timeout to prevent slowdowns due to slow server response
		//lets not wait for slow server response //TODO - test
		$stimeout=0;
		if(!ini_get('safe_mode')){
			$stimeout=ini_get("max_execution_time");
			//set error in case of timeout
			$this->setError(WASSUP_AKISMET_RESPONSE_FAILED,__("Timed out waiting for server response.","wassup"));
			if((int)$stimeout>7)set_time_limit(7);
		}
		$response=$this->http->getResponse($this->_getQueryString(),'comment-check');
		if(!empty($stimeout)){
			set_time_limit($stimeout);
			$this->removeError(WASSUP_AKISMET_RESPONSE_FAILED,__("Timed out waiting for server response.","wassup"));
		}
		return ($response=="true");
	}
	
	// Submit this comment as an unchecked spam to the Akismet server
	function submitSpam(){
		$this->http->getResponse($this->_getQueryString(),'submit-spam');
	}
	
	// Submit a false-positive comment as "ham" to the Akismet server
	function submitHam(){
		$this->http->getResponse($this->_getQueryString(),'submit-ham');
	}
	
	// Manually set the comment value of the instantiated object.
	function setComment($comment){
		$this->comment = $comment;
		if(!empty($comment)){
			$this->_formatCommentArray();
			$this->_fillCommentValues();
		}
	}
	
	// Returns the current value of the object's comment array.
	function getComment(){return $this->comment;}
	
	// Check with the Akismet server to determine if the API key is valid
	function _isValidApiKey($key){
		$keyCheck=$this->http->getResponse("key=".$this->apiKey."&blog=".$this->blogUrl,'verify-key');
		return ($keyCheck=="valid");
	}
	
	// Format the comment array in accordance to the Akismet API
	function _formatCommentArray(){
		$format=array(	'type'  =>'comment_type',
				'author'=>'comment_author',
				'email' =>'comment_author_email',
				'website'=>'comment_author_url',
				'body'  =>'comment_content');
		foreach($format as $short=>$long){
			if(isset($this->comment[$short])){
				$this->comment[$long]=$this->comment[$short];
				unset($this->comment[$short]);
			}
		}
	}
	
	// Fill any values not provided by the developer with available values.
	function _fillCommentValues(){
		if(!isset($this->comment['user_ip'])){
			$this->comment['user_ip']=($_SERVER['REMOTE_ADDR']!=getenv('SERVER_ADDR')) ?$_SERVER['REMOTE_ADDR'] :getenv('HTTP_X_FORWARDED_FOR');
		}
		if(!isset($this->comment['user_agent'])){
			$this->comment['user_agent']=$_SERVER['HTTP_USER_AGENT'];
		}
		if(!isset($this->comment['referrer'])){
			$this->comment['referrer']=$_SERVER['HTTP_REFERER'];
		}
		if(!isset($this->comment['blog'])){
			$this->comment['blog']=$this->blogUrl;
		}
	}
	
	// Build a query string for use with HTTP requests
	function _getQueryString(){
		foreach($_SERVER as $key=>$value){
			if(!in_array($key,$this->ignore)){
				if($key=='REMOTE_ADDR'){
					$this->comment[$key]=$this->comment['user_ip'];
				}else{
					$this->comment[$key]=$value;
				}
			}
		}
		$query_string='';
		foreach($this->comment as $key=>$data){
			$query_string .=$key.'='.urlencode(stripslashes($data)).'&';
		}
		return $query_string;
	}
} //end Class
?>
