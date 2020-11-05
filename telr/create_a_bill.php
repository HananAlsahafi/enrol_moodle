<?php
/**
 * iSpace Technology | Telr enrolment plugin.
 * info@ispce.net   *** +201149444844
 * This plugin allows you to set up paid courses with Telr Gateway.
 *
 */
//Requirements
require "../../config.php";
require_once($CFG->libdir . '/filelib.php');
require_once("lib.php");

//Page Layout
$PAGE->set_title('Enrollment');
$PAGE->set_heading('Payment Information');

echo $OUTPUT->header();
//Required Login
require_login();

$plugin = enrol_get_plugin('telr');

//If Course or Instance not passed => show InvalidRequest Page
if (!isset($_POST['instance_id']) || !isset($_POST['course_id'])) {
    http_response_code(400);
    throw new moodle_exception('invalidrequest', 'core_error');
}
//Assgin Variables
$crt=time();
$ivp_store = $plugin->get_config('telr_ivp_store');
$ivp_authkey = $plugin->get_config('telr_ivp_authkey');
$plugin_instance = $DB->get_record("enrol", array(
   "id" => $_POST['instance_id'],
   "enrol" => "telr",
   "courseid" => $_POST['course_id'],
   "status" => 0,

), "*", MUST_EXIST);
$course = $DB->get_record('course', array('id' => $plugin_instance->courseid));
//$context = context_course::instance($course->id);

//Get Course Cost
if ((float) $plugin_instance->cost <= 0) {
   $cost = (float) $plugin->get_config('cost');
} else {
   $cost = (float) $plugin_instance->cost;
}
//Assign Request Params
$params = array(
'ivp_method' => 'create',
'ivp_store' => trim($ivp_store),
'ivp_authkey' => trim($ivp_authkey),
'ivp_cart' => $crt,
'ivp_test' => '0',
'ivp_framed' => '2',
'ivp_amount' =>  $cost,
'ivp_currency' => 'SAR',
'ivp_desc' => 'Moodle Course',
'return_auth' => "$CFG->wwwroot/enrol/telr/redirect.php?crt=".$crt,
'return_can' => "$CFG->wwwroot/enrol/telr/redirect.php?crt=".$crt,
'return_decl' => "$CFG->wwwroot/enrol/telr/redirect.php?crt=".$crt
);

//Telr New Order Request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://secure.telr.com/gateway/order.json");
curl_setopt($ch, CURLOPT_POST, count($params));
curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
//Get Response
$results = curl_exec($ch);
curl_close($ch);
$results = json_decode($results,true);
//Get OrderReference and URL
$ref= trim($results['order']['ref']);
$url= trim($results['order']['url']);
//if Request Failed
if (empty($ref) || empty($url)) 
{
  throw new moodle_exception('invalidrequest', 'core_error', '', null, print_r($rbody, true));
  echo '<strong>Payment Failed! Please contact website administrator</strong>';
}
//if Success display the url inside iframe
echo "<iframe src='$url' width='700' height='900' frameBorder='0'>Browser not compatible.</iframe>"
?>
<?php
//Log Request Details to Telr table
$data = new stdClass();
$data->bill_id = $ref;
$data->cartid = $crt;
$data->amount = $cost;
$data->paymenttype = '';
$data->course_id = $course->id;
$data->user_id = $USER->id;
$data->instance_id = $plugin_instance->id;
$data->payment_status = 0;
$data->time_updated = time();
$DB->insert_record("enrol_telr", $data);
?>
