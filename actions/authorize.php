<?php
/**
 * Created by PhpStorm.
 * User: shobl
 * Date: 3/14/2018
 * Time: 10:11 AM
 */
if(!isset($PAGEACCESS) || $PAGEACCESS===false){
    die('NO DIRECT ACCESS ALLOWED');
}
$output['success'] = true;
$phoneNumber = $_GET['phoneNumber'];
$phoneNumber = '001' . $phoneNumber;


$query = "SELECT `phone`, `gps_loc`
          FROM `users`
          WHERE `phone` = $phoneNumber";
$result = mysqli_query($conn, $query);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $coordinates = $row['gps_loc'];
        }
        $output['newUser'] = false;
    } else {
        $output['newUser'] = true;
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
} else {
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


    //get lat and lat
    $coordinateLength = strlen($coordinates);
    for ($i = 0; $i < $coordinateLength; $i++) {
        if ($coordinates[$i] === ',') {
            $lat = substr($coordinates, 0, $i);
            $lng = substr($coordinates, $i + 2);

        }
    }

    getAddress($lat, $lng);
    getHoaList($zipCode, $output);
}