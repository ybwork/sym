<?php

namespace App\Service;

class UrlChecker
{
    public function checkUrl($url)
    {
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		curl_exec($ch);

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close($ch);

		return $httpCode;
    }
}