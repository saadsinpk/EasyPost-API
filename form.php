<?php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: token, Content-Type');
    header('Access-Control-Max-Age: 1728000');
    header('Content-Length: 0');
    header('Content-Type: text/plain');
    die();
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
// header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X- 
// Request-With');
// if(isset($_POST)) {
// 	echo json_encode($_POST);
// }
// if(isset($_FILES)) {
// 	echo json_encode($_FILES);
// }
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("easypost/lib/easypost.php");
\EasyPost\EasyPost::setApiKey("###APIKEY###");

try {
	$parcel = \EasyPost\Parcel::create(array(
	  "length" => $_POST['length'],
	  "width" => $_POST['width'],
	  "height" => $_POST['height'],
	  "weight" => $_POST['weight']
	));
}
catch(Exception $e) {
	echo json_encode(array('status' => 'Error','message'=>$e->getMessage() ));
	exit();
}
$from_add = array(
	// "verify_strict"  => true,
    "street1" => $_POST['street1'],
    "street2" => $_POST['street2'],
    "city"    => $_POST['City'],
    "state"   => $_POST['state'],
    "zip"     => $_POST['zip'],
    "country" => 'US',
    "company" => '',
    "phone"   => $_POST['phone']
);
$to_add = array(
    // "verify_strict"  => true,
    "street1"        => "444",
    "street2"        => "",
    "city"           => "233",
    "state"          => "222",
    "zip"            => "1111",
    "country"        => "US",
    "company"        => "11",
    "phone"          => "123123123"
);
$parcels = \EasyPost\Parcel::retrieve($parcel);

$customs_info = \EasyPost\CustomsInfo::create(array(
  "eel_pfc" => '',
  "customs_certify" => false,
  "customs_signer" => '',
  "contents_type" => 'other',
  "contents_explanation" => '',
  "restriction_type" => 'none',
  "non_delivery_option" => 'return'
));

try {
	$shipment = \EasyPost\Shipment::create(array(
	  "to_address" => $to_add,
	  "from_address" => $from_add,
	  "parcel" => $parcels,
	  "customs_info" => $customs_info
	));

	// $shipment = \EasyPost\Shipment::retrieve("###ID###");
	$shipment->buy(array(
	  'rate'      => $shipment->lowest_rate(),
	));
	$shipment_rate = json_decode($shipment->lowest_rate());
	$retrun_array['status'] = 'Success';
	$retrun_array['price'] = $shipment->lowest_rate()->rate;
	$retrun_array['carrier'] = $shipment->lowest_rate()->carrier;
	$retrun_array['label'] = $shipment->postage_label->label_url;
	echo json_encode($retrun_array);
	exit();
}
catch(Exception $e) {
	echo json_encode(array('status' => 'Error','message'=>$e->getMessage() ));
	exit();
}

?>
