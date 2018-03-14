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
$zipCode = null;
$formattedAddress = null;

//get the users address
function getAddress($lat, $lng) {
    $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lng."&sensor=true";
    $data = @file_get_contents($url);
    $jsonData = json_decode($data,true);
    global $formattedAddress;
    global $zipCode;
    if(is_array($jsonData) && $jsonData['status'] == "OK") {
        $formattedAddress = $jsonData['results'][0]['formatted_address'];
        $zipCode = $jsonData['results'][0]['address_components'][6]['long_name'];
    }
    if ($zipCode == null || $formattedAddress == null) {
        getAddress($lat, $lng);
    }
};
getAddress($lat, $lng);

$output['zipCode'] = $zipCode;
$output['address'] = $formattedAddress;

$query = "SELECT * FROM `auth` WHERE `phone` = '$phoneNumber' AND `pin` = $pin";
$result = mysqli_query($conn, $query);

if ($result) {
    $output['count'] = mysqli_num_rows($result);
    if (mysqli_num_rows($result) > 0) {
        $output['validCreds'] = true;
    } else {
        $output['validCreds'] = false;
    }
} else {
    $output['errors'][] = 'Error in query';
}

if ($output['validCreds']) {

}