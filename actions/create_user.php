<?php
/**
 * Created by PhpStorm.
 * User: shobl
 * Date: 3/14/2018
 * Time: 10:48 AM
 */
if(!isset($PAGEACCESS) || $PAGEACCESS===false){
    die('NO DIRECT ACCESS ALLOWED');
}
$output['success'] = true;
$phoneNumber = '001' . $_GET['phoneNumber'];
$lat = $_GET['lat'];
$lng = $_GET['lng'];
$name = $_GET['nickName'];
$pin = $_GET['pin'];


//get the users address
function getAddress($lat, $lng) {
    $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lng."&sensor=true";
    $data = @file_get_contents($url);
    $jsonData = json_decode($data,true);
    if(is_array($jsonData) && $jsonData['status'] == "OK") {
        $formattedAddress = $jsonData['results'][0]['formatted_address'];
        $zipCode = $jsonData['results'][0]['address_components'][6]['long_name'];

        return array($zipCode, $formattedAddress);
    } else {
        $output['errors'][] = 'Unable to fetch google data';
        getAddress($lat, $lng);
    }
};

$addressArr = getAddress($lat, $lng);
$zipCode = $addressArr[0];
$formattedAddress = $addressArr[1];

$output['zipCode'] = $zipCode;
$output['address'] = $formattedAddress;

if ($zipCode == null || $formattedAddress == null) {
    $output['noAddress'] = true;
    $addressArr = getAddress($lat, $lng);
    $zipCode = $addressArr[0];
    $formattedAddress = $addressArr[1];

    $output['zipCode'] = $zipCode;
    $output['address'] = $formattedAddress;
}


$query = "SELECT * FROM `auth` WHERE `phone` = '$phoneNumber' AND `pin` = $pin";
$result = mysqli_query($conn, $query);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        $output['validCreds'] = true;
    } else {
        $output['validCreds'] = false;
    }
} else {
    $output['errors'][] = 'Error in query';
}