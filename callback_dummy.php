<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

//Receive the RAW post data via the php://input IO stream.
$content = file_get_contents("php://input");

$json_input = json_decode($content);

ob_start();
var_dump($json_input);
$result = ob_get_clean();

$myfile = fopen("/tmp/callback_log.txt", "a") or die("Unable to open file!");
fwrite($myfile, $content);
fwrite($myfile, $result);
fwrite($myfile, "\n\n------\n\n");
fclose($myfile);

$callback_files = ['java.json', 'java-exam.json', 'python.json'];
$file_path = "tester_callbacks/" . $callback_files[rand(0, 2)];
$json_result = file_get_contents($file_path);

$callback_url = $json_input->callback_url;
$callback_secret = $json_input->secret_token;
$callback_uniid = $json_input->user;
$callback_project = 'hello';

if (@$json_input->project) {
    $callback_project = $json_input->project;
}
//API Url
$url = $callback_url;

//Initiate cURL.
$ch = curl_init($url);

$json_data = json_decode($json_result);
$last_error = json_last_error();
if ($last_error > 0) {
    var_dump($last_error);
    var_dump(json_last_error_msg());
}

$json_data->token = $callback_secret;
$json_data->uniid = $callback_uniid;
$json_data->project = $callback_project;

//Encode the array into JSON.
$json_data_encoded = json_encode($json_data);

//Tell cURL that we want to send a POST request.
curl_setopt($ch, CURLOPT_POST, 1);

//Attach our encoded JSON string to the POST fields.
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data_encoded);

//Set the content type to application/json
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

//Execute the request
$result = curl_exec($ch);

echo $result;
