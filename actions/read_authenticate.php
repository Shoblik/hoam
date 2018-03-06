<?php
/**
 * Created by PhpStorm.
 * User: shobl
 * Date: 2/20/2018
 * Time: 4:49 PM
 */

if(!isset($PAGEACCESS) || $PAGEACCESS===false){
    die('NO DIRECT ACCESS ALLOWED');
}
$output['success'] = true;
$output['newUser'] = true;
$phoneNumber = $_POST['phoneNumber'];
$lat = $_POST['lat'];
$lng = $_POST['lng'];
$phoneNumber = '001' . $phoneNumber;

//get the users address
$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lng."&sensor=true";
$data = @file_get_contents($url);
$jsonData = json_decode($data,true);
if(is_array($jsonData) && $jsonData['status'] == "OK")
{
    $formattedAddress = $jsonData['results'][0]['formatted_address'];
    $zipCode = $jsonData['results'][0]['address_components'][6]['long_name'];
    $output['address'] = $formattedAddress;
}

//generate HOA list
//$urlArr = ['https://www.allpropertymanagement.com/find/index.php?thisSearchPage=HOME&search=Y&t=71&zip=' . $zipCode . '&submit=', 'https://www.allpropertymanagement.com/find/index.php?thisSearchPage=HOME&search=Y&t=73&zip=' . $zipCode . '&submit=', 'https://www.allpropertymanagement.com/find/index.php?thisSearchPage=HOME&search=Y&t=76&zip=' . $zipCode . '&submit='];
$urlArr = ['https://www.allpropertymanagement.com/find/index.php?thisSearchPage=HOME&search=Y&t=71&zip='];
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
//$output['data'] = $bizArray;

$bizArrCount = count($bizArray);
$cleanedBizArray = array();

for ($i=0; $i<$bizArrCount; $i++) {
    $cleanedBizArray[$bizArray[$i]] = 1;
}

$output['data'] = array_keys($cleanedBizArray);

//query the database checking if the users phone number already exists
$query = "SELECT `phone`
          FROM `users`
          WHERE `phone` = $phoneNumber";
$result = mysqli_query($conn, $query);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        $output['newUser'] = false;
    }
} else {
    $output['errors'][] = 'Error in query';
}

if ($output['newUser']) {
    $rand = rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
    $output['pin'] = $rand;
    require_once ('./php_mailer/mail_handler.php');

    //insert the users authentication into the auth table
    $query = "INSERT INTO `auth` (phone, pin)
              VALUES ('$phoneNumber', '$rand')";

    $res = mysqli_query($conn, $query);
    if ($res) {
        if (mysqli_affected_rows($conn) > 0) {
            $output['success'] = true;
        } else {
            $output['success'] = false;
        }
    } else {
        $output['errors'] = 'Error in query';
        $output['success'] = false;
    }
}

?>