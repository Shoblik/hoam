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


$query = "SELECT `phone`
          FROM `users`
          WHERE `phone` = $phoneNumber";
$result = mysqli_query($conn, $query);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        $output['newUser'] = false;
    } else {
        $output['newUser'] = true;
    }
} else {
    $output['errors'][] = 'Error in query';
}