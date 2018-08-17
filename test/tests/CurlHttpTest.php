<?php

namespace TravisTest\Tests;

use PHPUnit_Framework_TestCase;
use Exception;

class CurlHttpTest extends PHPUnit_Framework_TestCase
{
    public function fetchHttpProvider()
    {
        return array(
            array('http://imagine.readthedocs.org/en/latest/_static/logo.jpg'),
            array('https://imagine.readthedocs.org/en/latest/_static/logo.jpg'),
            array('https://imagine.readthedocs.io/en/latest/_static/logo.jpg'),
        );
    }
    /**
     * @dataProvider fetchHttpProvider
     */
    public function testFetchHttps($path)
    {
        if (!function_exists('curl_init')) {
            $this->markTestSkipped('curl PHP extension is not installed.');
        }
        $curl = @curl_init($path);
        if ($curl === false) {
            throw new Exception('curl_init() failed.');
        }
        if (!@curl_setopt($curl, CURLOPT_RETURNTRANSFER, true)) {
            throw new Exception('curl_setopt(CURLOPT_RETURNTRANSFER) failed.');
        }
        if (!@curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept-Encoding: identity'))) {
            throw new Exception('curl_setopt(CURLOPT_HTTPHEADER) failed.');
        }
        if (!@curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true)) {
            throw new Exception('curl_setopt(CURLOPT_FOLLOWLOCATION) failed.');
        }
        $response = @curl_exec($curl);
        if ($response === false) {
            $errorMessage = curl_error($curl);
            if ($errorMessage === '') {
                $errorMessage = 'curl_exec() failed.';
            }
            $errorCode = curl_errno($curl);
            curl_close($curl);
            throw new Exception($errorMessage, $errorCode);
        }
        $responseInfo = curl_getinfo($curl);
        curl_close($curl);
        if ($responseInfo['http_code'] == 404) {
            throw new Exception(sprintf('The file "%s" does not exist.', $path));
        }
        if ($responseInfo['http_code'] < 200 || $responseInfo['http_code'] >= 300) {
            throw new Exception(sprintf('Failed to download "%s": %s', $path, $responseInfo['http_code']));
        }
        return $response;
    }
}
