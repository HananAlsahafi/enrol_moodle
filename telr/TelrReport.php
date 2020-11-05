<?php

require "../../config.php";
 
defined('MOODLE_INTERNAL') || die();

//Page Layout
$PAGE->set_title('Transaction list');
$PAGE->set_heading('Transaction list');

echo $OUTPUT->header();
//Required Login
require_login();

require_once($CFG->libdir . '/filelib.php');
require_once("lib.php");

require_login();
	?>


<style>
	td 
	{
		
		text-align:center !important;
		
	}
    th
	{
		
		text-align:center !important;
	
	}
	table
	{
		direction:ltr
	}
	</style>
  <div class="container">
  
<div class="row">
<div class="col-md-6">
</div>
<div  class="col-md-6" style="margin: 10px 10px;">
<form class="form-inline" action="TelrReport.php" method="POSt">
    <div class="form-group">
	<label>
      <input type="text" class="form-control" id="Search" placeholder="Search: Username, CartID" name="Search">
    </div>

    <button type="submit" class="btn btn-default" onclick="document.getElementById('All').style.visibility = 'hidden'">Search</button>

	    <a href="TelrReport.php" class="btn btn-default" id="All" style="visibility : hidden" >View All </a>
	
  </form>
</div>

<div class="col-md-12">

	<?php
	$telrs = $DB->get_records('enrol_telr' );
	if(isset($_POST["Search"])){
		?>
		<script>
		document.getElementById('All').style.visibility = 'visible';
		</script>
		<?php
		$user = $DB->get_record('user', array('username' => $_POST["Search"]));
		if(!$user){
			$telrs = $DB->get_records('enrol_telr' , array('cartid' => $_POST["Search"]));

		}
		else {
		$telrs = $DB->get_records('enrol_telr' , array('user_id' => $user->id));	
		}
			
	}
	
$table = new html_table();

$table->head = array('Username','Firstname','Course', 'Amount' , 'Status', 'Cart ID', 'TransRef', 'Payment Type',"Time");

foreach ($telrs as $id => $telr) {
	$user = $DB->get_record('user', array('id' => $telr->user_id));	
	$course = $DB->get_record('course', array('id' => $telr->course_id));
    $username = $user->username;
	$firstname=$user->firstname;
    $shortname = $course->shortname;
	$amount = $telr->amount ." SR";
	if ($telr->payment_status ==3)
	{
		$payment_status = "Success";

	}
	else
	{
		$payment_status = "Failed";
	}
	$cartid = $telr->cartid;
	$trans_ref = $telr->trans_ref;
	$paymenttype = $telr->paymenttype;
	$time_updated = date("Y-m-d H:i:s", substr($telr->time_updated, 0, 10));

    $table->data[] = array($username,$firstname,$shortname,$amount, $payment_status, $cartid,$trans_ref,$paymenttype,$time_updated);
}

echo html_writer::table($table);
?>
</tbody>
</table>
</div>
</div>
</div>