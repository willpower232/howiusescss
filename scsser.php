<?php
/*
this is part of PHP 5.4 but not anything older so here is a fallback function
https://gist.github.com/3952113
*/
if (!function_exists('http_response_code')) {
	function http_response_code($code = NULL) {
		if ($code !== NULL) {

			switch ($code) {
				case 100: $text = 'Continue'; break;
				case 101: $text = 'Switching Protocols'; break;
				case 200: $text = 'OK'; break;
				case 201: $text = 'Created'; break;
				case 202: $text = 'Accepted'; break;
				case 203: $text = 'Non-Authoritative Information'; break;
				case 204: $text = 'No Content'; break;
				case 205: $text = 'Reset Content'; break;
				case 206: $text = 'Partial Content'; break;
				case 300: $text = 'Multiple Choices'; break;
				case 301: $text = 'Moved Permanently'; break;
				case 302: $text = 'Moved Temporarily'; break;
				case 303: $text = 'See Other'; break;
				case 304: $text = 'Not Modified'; break;
				case 305: $text = 'Use Proxy'; break;
				case 400: $text = 'Bad Request'; break;
				case 401: $text = 'Unauthorized'; break;
				case 402: $text = 'Payment Required'; break;
				case 403: $text = 'Forbidden'; break;
				case 404: $text = 'Not Found'; break;
				case 405: $text = 'Method Not Allowed'; break;
				case 406: $text = 'Not Acceptable'; break;
				case 407: $text = 'Proxy Authentication Required'; break;
				case 408: $text = 'Request Time-out'; break;
				case 409: $text = 'Conflict'; break;
				case 410: $text = 'Gone'; break;
				case 411: $text = 'Length Required'; break;
				case 412: $text = 'Precondition Failed'; break;
				case 413: $text = 'Request Entity Too Large'; break;
				case 414: $text = 'Request-URI Too Large'; break;
				case 415: $text = 'Unsupported Media Type'; break;
				case 500: $text = 'Internal Server Error'; break;
				case 501: $text = 'Not Implemented'; break;
				case 502: $text = 'Bad Gateway'; break;
				case 503: $text = 'Service Unavailable'; break;
				case 504: $text = 'Gateway Time-out'; break;
				case 505: $text = 'HTTP Version not supported'; break;
				default: exit('Unknown http status code "' . htmlentities($code) . '"'); break;
			}

			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

			header($protocol . ' ' . $code . ' ' . $text);

			$GLOBALS['http_response_code'] = $code;

		} else {
			$code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
		}

		return $code;
	}
}

//can be determined programmatically
$development = false;

//if you do have external dependencies
$importpaths = array();

//re calculate the path to the source scss file
$path = preg_replace('/\.css$/', ".scss", "./assets/".$_GET['p']);

//generate the path to a locally accessible temporary file
$cachefile = "./../tmp/compiled_scss_".md5($path).filemtime($path).".css";

if (file_exists($path)) {
	//this check allows the browser to cache the file
	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime($path)) {
		http_response_code(304);
	} else {

		if ($development !== false) {
			$crunch = true;
			$cache = false;
		} else {
			if (file_exists($cachefile)) {
				$crunch = false;
				$cache = false;
			} else {
				$crunch = true;
				$cache = true;
			}
		}

		if ($crunch === true) {
			require "./scssphp/scss.inc.php"; //or wherever you have put it
			$scss = new scssc();
			foreach ($import_paths as $ip) {
				if (is_dir('./'.$ip)) {
					$scss->addImportPath($ip);
				} else {
					throw new Exception("Cannae find ".$ip);
				}
			}

			$scss->setFormatter('scss_formatter_compressed');
			$compiled = $scss->compile('@import "'.$path.'"');
			$compiled = preg_replace('#\/\*.+?\*/#sm', "", $compiled); //strip comments out of the compiled code
		} else {
			$compiled = implode("\n", file($cachefile));
		}

		if ($cache === true) {
			if (!$fp = @fopen($cachefile, 'wb')) {
				throw new Exception("Unable to write scsser cache file: ".$cachefile);
			}
			flock($fp, LOCK_EX);
			fwrite($fp, $compiled);
			flock($fp, LOCK_UN);
			fclose($fp);
		}

		$offset = 5183999; //1 second shy of two months

		header("Content-Type: text/css");
		header("X-Content-Type-Options: nosniff");
		header('Expires: '. gmdate('D, d M Y H:i:s', date("U") + $offset) .' GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)) .' GMT');

		//if you haven't already enabled gzip in your server settings
		/*if (!in_array('ob_gzhandler', ob_list_handlers())) {
			ob_start('ob_gzhandler'); //prepare an optionally GZIPed response
		}
		else { */
			ob_start();
		//}

		echo $compiled;

		ob_end_flush();
	}
} else {
	http_response_code(404);
}