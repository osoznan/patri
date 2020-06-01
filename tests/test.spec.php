<?php
// php vendor\kahlan\kahlan\bin\kahlan

define('IS_RELEASE', false);
define('IS_TEST', true);

// require(__DIR__ . '/../functions.php');

function sendQuery($url, $postVars = [], $headers = []){
    //Transform our POST array into a URL-encoded query string.
    $postStr = http_build_query($postVars);
    //Create an $options array that can be passed into stream_context_create.
    $options = array('http' => [
        'method'  => 'POST', //We are using the POST HTTP method.
        'header'  => $headers,
        'content' => $postStr //Our URL-encoded query string.
    ]);
    //Pass our $options array into stream_context_create.
    //This will return a stream context resource.
    $streamContext  = stream_context_create($options);
    //Use PHP's file_get_contents function to carry out the request.
    //We pass the $streamContext variable in as a third parameter.
    $result = file_get_contents($url, false, $streamContext);
    //If $result is FALSE, then the request has failed.
    if($result === false){
        //If the request failed, throw an Exception containing
        //the error.
        $error = error_get_last();
        throw new Exception('POST request failed: ' . $error['message']);
    }
    //If everything went OK, return the response.
    return $result;
}

function post($url, $postVars = []) {
    return sendQuery($url, $postVars, [
        'Content-type: application/x-www-form-urlencoded'
    ]);
}

function postAjax($url, $postVars = []) {
    return sendQuery($url . '?test=1', $postVars, [
        'Content-type: application/x-www-form-urlencoded',
        'X-Requested-With: XMLHttpRequest'
    ]);
}

function registerAsAdmin() {
    return post('http://fruit/admin/site/login', [
        'ajax' => 1,
        'login' => 'admin',
        'password' => '123'
    ]);
}

//TODO make autotesting

require(__DIR__ . '/ModelTest.php');
// require(__DIR__ . '/AppTest.php');
