<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php";

class OdysseaMailer
{
	const APPLICATION_NAME = "Odyssea mailer";
	const SCOPES = Google_Service_Gmail::GMAIL_SEND;
	const CLIENT_SECRET_PATH = __DIR__ . "/OdysseaMailer_client_secret.json"; // Do not forget to add this to the .gitignore!
	const CREDENTIALS_PATH = __DIR__ . "/OdysseaMailer_credentials.json"; // Do not forget to add this to the .gitignore!
	const USER_ID = "odysseus.ithacky@gmail.com";
	const SENDER_NAME = "Odysseus Ithacký";
	const SENDER_EMAIL = "odysseus.ithacky@gmail.com";

	private $client;
	private $service;

	function __construct()
	{
		$this->client = new Google_Client();
		$this->client->setApplicationName(self::APPLICATION_NAME);
		$this->client->setScopes(self::SCOPES);
		if(!file_exists(self::CLIENT_SECRET_PATH))
		{
			throw new Exception("OdysseaMailer_client_secret.json file missing\n");
		}
		$this->client->setAuthConfig(self::CLIENT_SECRET_PATH);
		$this->client->setAccessType("offline");
	}

	public function getCredentials()
	{
		if(php_sapi_name() != "cli")
		{
			throw new Exception("Method OdysseaMailer::getCredentials() must be called from a CLI!\n");
		}
		$authURI = $this->client->createAuthUrl();
		printf("Open the following link in your browser to proceed with authentication: \n%s\n", $authURI);
		print("Enter the verification code: ");
		$authCode = trim(fgets(STDIN));
		$accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
		if(isset($accessToken["error_description"]))
		{
			throw new Exception("The server returned with an error! Error description: " . $accessToken["error_description"] . "\n");
		}
		if(!isset($accessToken["access_token"]))
		{
			throw new Exception("No access token present in server response! No error description provided by server.\n");
		}
		if(!isset($accessToken["refresh_token"]))
		{
			throw new Exception("No refresh token present in server response! Remove the " . self::APPLICATION_NAME . " application from your account(https://security.google.com/settings/security/permissions) and try again.\n");
		}
		if(!file_exists(dirname(self::CREDENTIALS_PATH)))
		{
			mkdir(dirname(self::CREDENTIALS_PATH), 0700, true);
		}
		file_put_contents(self::CREDENTIALS_PATH, json_encode($accessToken));
		printf("Credentials saved.\n");
	}

	public function authenticate()
	{
		if(!file_exists(self::CREDENTIALS_PATH))
		{
			throw new Exception("No credentials available! Call OdysseaMailer::getCredentials() first.\n");
		}
		$accessToken = json_decode(file_get_contents(self::CREDENTIALS_PATH), true);
		$this->client->setAccessToken($accessToken);
		if($this->client->isAccessTokenExpired())
		{
			$this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
			file_put_contents(self::CREDENTIALS_PATH, json_encode($this->client->getAccessToken()));
		}

		$this->service = new Google_Service_Gmail($this->client);
	}

	private function utf8base64($plain)
	{
		$buffer = "";
		$first = true;
		$len = mb_strlen($plain);
		for($i = 0; $i < $len; $i ++)
		{
			$limit = $first ? 42 : 48;
			$char = mb_substr($plain, $i, 1);
			if(strlen($buffer . $char) > $limit)
			{
				$arr[] = $buffer;
				$buffer = $char;
				$first = false;
			}
			else
			{
				$buffer .= $char;
			}
		}
		$arr[] = $buffer;
		$encoded = "";
		foreach($arr as $i)
		{
			$encoded .= "\r\n =?UTF-8?B?" . base64_encode($i) . "?=";
		}
		return $encoded;
	}

	public function send($recipient, $subject, $body)
	{
		if(!isset($this->service))
		{
			throw new Exception("OdysseaMailer not authenticated! Call OdysseaMailer::authenticate() before attempting to send mail.\n");
		}
		$message = "From: " . $this->utf8base64(self::SENDER_NAME) . " <" . self::SENDER_EMAIL . ">" . "\r\n";
		$message .= "To: " . $recipient . "\r\n";
		$message .= "Subject: " . $this->utf8base64($subject) . "\r\n";
		$message .= "\r\n" . $body;
		$messageBase64 = rtrim(strtr(base64_encode($message), '+/', '-_'), '=');
		$gMessage = new Google_Service_Gmail_Message();
		$gMessage->setRaw($messageBase64);
		$this->service->users_messages->send(self::USER_ID, $gMessage);
	}
}
?>
