<?php
    // Test to make sure we have cURL installed before proceeding further
    if (!function_exists('curl_version')) {
    	echo json_encode(array('status' => 'error', 'type' => 'curl', 'message' => 'cURL and/or the PHP bindings for cURL are not installed. Please fix and try again.'));
    	return;
    }
    
    if (isset($_POST['login']) && isset($_POST['pass'])) {
        $url = 'https://api.payjunction.com/transactions/1';
        $login = $_POST['login'];
        $pass = $_POST['pass'];
        
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'X-PJ-Application-Key: ' . 'b171e533-ff4d-44cd-aa2a-efc1e6c92125'));
		//639ff34b-d729-48cc-9f99-6e099543bb66
		curl_setopt($ch, CURLOPT_USERPWD, $login . ':' . $pass);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		$raw_content = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		
		if ($curl_errno) {
			echo json_encode(array('status' => 'error', 'type' => 'cURL:'.$curl_errno, 'message' => $curl_error));
		} else {
			$content = json_decode($raw_content, true);
			// We should either have a message stating that the transaction doesn't exist or transaction details if it does for a successfull login
			if (isset($content['errors'])) {
			    foreach ($content['errors'] as $err) {
			        if (strpos($err['message'], "Transaction Id 1 does not exist") !== false) {
			        	// We would only get this message with valid, credentials, send back success message
			            echo json_encode(array('status' => 'success'));
			            return;
			        } elseif (strpos($err['message'], "Authentication failed") !== false) {
			            echo json_encode(array('status' => 'failure', 'type' => 'authentication', 'message' => $err['message']));
			            return;
			        }
			    }
			   echo json_encode(array('status' => 'error', 'type' => 'unknown', 'message' => 'Unknown error(s) from API.'));

			} elseif (isset($content['transactionId'])) {
			    echo json_encode(array('status' => 'success'));
			} else {
			     // if we've reached this point we weren't able to figure out what the error was, send back what we got
			     echo json_encode(array('status' => 'error', 'type' => 'unknown', 'message' => 'Could not parse response from API'));
			}
		}
    } else {
        echo json_encode(array('status' => 'failure', 'type' => 'input', 'message' => 'Please provide a login and password to check'));
    }
    
?>
