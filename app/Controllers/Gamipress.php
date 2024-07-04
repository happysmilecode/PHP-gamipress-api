<?php
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class Gamipress extends BaseController
{	use ResponseTrait; 
    public $client; //curl Client to make the request to Gamipress API REST
	public $apiURL; //Base URL Gamipress API REST
	public $currentURL; // URL requested by the APP
	public $token; // JWT Token used for Gamipress API REST and WP in General
	
	public function __construct()
    {
		//Setting the Defaults Values
		$this->apiURL = 'https://intoonedweekly.com/wp-json/wp/v2/gamipress/';
		$this->token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL2ludG9vbmVkd2Vla2x5LmNvbSIsImlhdCI6MTY4NTM4NzI1MiwibmJmIjoxNjg1Mzg3MjUyLCJleHAiOjE2ODU5OTIwNTIsImRhdGEiOnsidXNlciI6eyJpZCI6IjMyODUifX19.NzBjnn7yv2UctcS0ARm_JJDWFWCqfRyNBcQZJFhBSug';
		//Init the curl Client to make the request to Gamipress API REST
		$this->client = \Config\Services::curlrequest();
	}
	//If visitors visit https://intoonedweekly.com/gamipress-api/get-points/ will get 403 Error Message
	public function index()
    {
		return $this->failForbidden('You are not allowed to access this content!');
	}
	
	//Getting Points based on the points type and the user ID
    public function get_points($points_type='',$user_id='')
    {	//Getting the current URL(The one requested by the APP)
		$this->currentURL = current_url(true);
		//Getting the segments of the current URL ["gamipress-api","get-points","points_type","diamondsx","user_id","3285"]
        $segments = $this->currentURL->getSegments();
		//Checking the URL is the correct and have all the parameters needed
		if(count($segments)=== 6 && $segments[2]==="points_type" && $segments[4]==="user_id"){
			//Getting the parameters from the URL segment to be used in the code
			$points_type = $segments[3];
			$user_id = $segments[5];
			
			//Assembling the Gamipress API REST URL to get the points
			$get_points_url = $this->apiURL.'get-points?points_type='.$points_type.'&user_id='.$user_id;
			//Curl Request Options
			$curl_options = [
				'headers' => [ 'Authorization' => 'Bearer '.$this->token ],//Passing the Token
				'verify' => false, //Avoid verify SSL Certificate validation
				'http_errors' => false // Keep running if found HTTP ERRORS (400,401,403,500)
			];
			//$response will have the result of the curl request to the GamiPress API REST
			$response =  $this->client->request('get', $get_points_url ,$curl_options);
			//In this case the body will get the data from GamiPress, the json_decode is to prettify the output later
			$body = json_decode($response->getBody());
			//Return $body on Json Format
			return $this->respondCreated($body);
		}else{
			//In case the URL is not the correct one with all the parameters it will show this message
			return $this->failForbidden('You are not allowed to access this content!');
		}
    }
}