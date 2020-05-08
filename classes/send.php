<?php

class message {

	public function send($app_access_token, $payload)
	{
		// Prepare new cURL resource
		$ch = curl_init('https://open.larksuite.com/open-apis/message/v4/send/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

		// Set HTTP Header for POST request
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    "Authorization: Bearer ".$app_access_token,
		    'Content-Length: ' . strlen($payload))
		);

		// Submit the POST request
		$result = curl_exec($ch);

		// Close cURL session handle
		curl_close($ch);
	}
}
