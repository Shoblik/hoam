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
$phoneNumber = '001' . $phoneNumber;

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
}
?>