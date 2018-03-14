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