<?php
/**
 * Created by PhpStorm.
 * User: shobl
 * Date: 2/20/2018
 * Time: 4:44 PM
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$file = fopen("serverPHPlog.txt", "w");
fwrite($file, 'test');
fwrite($file, $_POST['lat']);
fwrite($file, $_POST['lng']);
fwrite($file, $_POST['phoneNumber']);
fclose($file);

if (empty($_POST)) {
    $_POST['lat'] = '33.7429473';
    $_POST['lng'] = '-117.78252809999998';
    $_POST['phoneNumber'] = '7149483092';
}


$PAGEACCESS = true;
require_once('./credentials.php');

$output = [
    'success' => false,
    'data' => [],
    'errors' => [],
];

switch ($_GET['action']) {
    case 'post':
        switch ($_GET['resource']) {
            case 'authenticate':
                if (!empty($_POST)) {
                    require_once ('./actions/read_authenticate.php');
                }
                break;
        }
}
$json_output = json_encode($output);
print($json_output);
?>