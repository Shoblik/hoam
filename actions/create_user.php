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
//$pin = $_GET['pin'];
$formattedGPS = $lat . ', ' . $lng;
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
        $data = null;
        $jsonData = null;
        getAddress($lat, $lng);
    }
};
    getAddress($lat, $lng);


    //generate HOA list
function getHoaList($zipCode, &$output) {
    $urlArr = ['https://www.allpropertymanagement.com/find/index.php?thisSearchPage=HOME&search=Y&t=71&zip=' . $zipCode . '&submit=', 'https://www.allpropertymanagement.com/find/index.php?thisSearchPage=HOME&search=Y&t=73&zip=' . $zipCode . '&submit=', 'https://www.allpropertymanagement.com/find/index.php?thisSearchPage=HOME&search=Y&t=76&zip=' . $zipCode . '&submit='];
    $count = count($urlArr);
    $bizArray = [];
    $tempBizName = '';
    $findGreaterThan = false;
    $scraping = false;

    for ($urlIndex = 0; $urlIndex < $count; $urlIndex++) {
        $url = $urlArr[$urlIndex];

        $content = @file_get_contents($url);
        $length = strlen($content);

        for ($i=0; $i<$length; $i++) {
            if ($content[$i] === 'b' && $content[$i + 1] === 'i' && $content[$i + 2] === 'z' && $content[$i + 3] === '_' && $content[$i + 4] === 'n' && $content[$i + 5] === 'a' && $content[$i + 6] === 'm' && $content[$i + 7] === 'e') {
                $findGreaterThan = true;
            }
            else if ($content[$i] === '>' && $findGreaterThan === true) {
                $scraping = true;
            }
            else if ($content[$i] === '<' && $findGreaterThan === true) {
                $findGreaterThan = false;
                $scraping = false;
                $bizArray[] = $tempBizName;
                $tempBizName = null;
            }
            else if ($scraping) {
                $tempBizName = $tempBizName . $content[$i];

            }
        }

    }
    $bizArrCount = count($bizArray);
    $cleanedBizArray = array();

    for ($i=0; $i<$bizArrCount; $i++) {
        $cleanedBizArray[$bizArray[$i]] = 1;
    }
    $output['data'] = array_keys($cleanedBizArray);
}

getHoaList($zipCode, $output);

    //create new user in database
    $query = "INSERT INTO `users` (`name`, `phone`, `gps_loc`, `address`, `created`, `updated`, `active`) 
              VALUES ('$name', '$phoneNumber', '$formattedGPS', '$formattedAddress', CURRENT_DATE, CURRENT_DATE, 'active')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        if (mysqli_affected_rows($conn) > 0) {
            $output['createdUser'] = true;
        } else {
            $output['createdUser'] = false;
        }
    } else {
        $output['errors'][] = 'Error inserting new user';
    }
