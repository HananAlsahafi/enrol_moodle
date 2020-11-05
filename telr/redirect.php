<?php
/**
 * iSpace Technology | Telr enrolment plugin.
 * info@ispce.net   *** +201149444844
 * This plugin allows you to set up paid courses with Telr Gateway.
 *
 */
//Requirements

require "../../config.php";
require_once "$CFG->dirroot/enrol/telr/lib.php";
require_once $CFG->libdir . '/enrollib.php';

require_login();

if (!enrol_is_enabled('telr')) {
    http_response_code(503);
    throw new moodle_exception('errdisabled', 'enrol_telr');
}
//Assign Variables
$plugin = enrol_get_plugin('telr');
$cart_id=$_GET['crt'];
$ivp_store = $plugin->get_config('telr_ivp_store');
$ivp_authkey = $plugin->get_config('telr_ivp_authkey');

 //Get Information from telr table
$telr_table = $DB->get_record("enrol_telr", array("cartid" => $cart_id));
$bill_id=$telr_table->bill_id;

//Check Payment information
$params = array(
    'ivp_method' => 'check',
    'ivp_store' => trim($ivp_store),
    'ivp_authkey' => trim($ivp_authkey),
    'order_ref' => $bill_id
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://secure.telr.com/gateway/order.json");
    curl_setopt($ch, CURLOPT_POST, count($params));
    curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
    $results = curl_exec($ch);
    curl_close($ch);
    $results = json_decode($results,true);

    //Get Status Code and Amount Paid and Card Type
    $cod= trim($results['order']['status']['code']);
    $amt= trim($results['order']['amount']);
    $typ= trim($results['order']['card']['type']);

    //if payment success and paid amount equal course cost then enrol user
    if ($cod == 3 && $amt >= $telr_table->amount)
    {
    $user = $DB->get_record('user', array('id' => $USER->id, 'deleted' => 0), '*', MUST_EXIST);
    $context = context_course::instance($telr_table->course_id);
    //if user not already enrolled
    if (!is_enrolled($context, $user)) {
        $enrol = enrol_get_plugin('telr');
        if ($enrol === null) {
            echo false;
        }
        //Update Database
        $telr_table = $DB->get_record("enrol_telr", array("bill_id" => $bill_id));
        $telr_table->payment_status=3;
        $telr_table->amount= $amt;
        $telr_table->paymenttype= $typ;
        $telr_table->time_updated= time();
        $DB->update_record("enrol_telr", $telr_table, false);
        //Enrol User
        $instance = $DB->get_record('enrol', array('id' => $telr_table->instance_id));
        $plugin->enrol_user($instance,$USER->id, 5);
    }
    //Set Context     
    $context = context_course::instance($telr_table->course_id, MUST_EXIST);
    $PAGE->set_context($context);
    //Pass $view=true to filter hidden caps if the user cannot see them
    if ($users = get_users_by_capability(
    $context,
    'moodle/course:update',
    'u.*',
    'u.id ASC',
    '',
    '',
    '',
    '',
    false,
    true
    )) {
    $users = sort_by_roleassignment_authority($users, $context);
    $teacher = array_shift($users);} 
    else {
    $teacher = false;}
    //Redirect to Course with Success msg
    redirect(new moodle_url('/course/view.php', array('id' => $telr_table->course_id)), get_string('telr:accepted', 'enrol_telr'));
    exit;
    }
    //Redirect to Course with Failed msg
    else
    {
    redirect(new moodle_url('/course/view.php', array('id' => $telr_table->course_id)), get_string('telr:rejected', 'enrol_telr'));
    exit;}
?>