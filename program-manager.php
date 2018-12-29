<?php

/*
	Plugin Name: Program Manager
  	Plugin URI: http://www.encircletechnologies.com/
  	Description: Use to manage all program and their details.
  	Version: 1.0
  	Author: Encircle Technologies
  	Author URI: http://www.encircletechnologies.com/
  	License: GPLv2+
  	Text Domain: Program Manager

*/

if(is_admin())
{
    new WP_Program_Manager();
}
  	

class WP_Program_Manager {

	function __construct()
	{
		add_action( 'admin_menu', array( $this, 'program_add_menu' ));
		register_activation_hook( __FILE__, array( $this, 'program_install' ) );
        register_deactivation_hook( __FILE__, array( $this, 'program_uninstall' ) ); 
    }

    function cssandjs()
	{ ?>
		<style type="text/css">
			.error {
			 		color: #ff0000;
				}	
				.form-field input, .form-field textarea, .form-field select
				{
					width: 25em !important;
				}
		</style>
		
    	<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) .'assests/jquery.min.js'; ?>"></script>
    	<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) .'assests/jquery.blockUI.min.js'; ?>"></script>
    	<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) .'assests/sweetalert.min.js'; ?>"></script>
		<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) .'assests/jquery.validate.min.js'; ?>"></script>
		
		<script type="text/javascript">
			$("body").on("keydown",".onlydigit", function (e) {
		        // Allow: backspace, delete, tab, escape, enter and .
		        var myLength = $(this).val().length;
		        if(myLength == 1) 
			    {
			        if(parseInt($(this).val()) < 1)
			        {
			        	swal("Alert", "0 is not allowed");
			  	    	$(this).val('');
			        }
			    }
		        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
		             // Allow: Ctrl+A, Command+A
		            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
		             // Allow: home, end, left, right, down, up
		            (e.keyCode >= 35 && e.keyCode <= 40)) {
		                 // let it happen, don't do anything
		                 return;
		        }
		        // Ensure that it is a number and stop the keypress
		        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
		            e.preventDefault();
		        }
		    });
		</script>
	<?php }

	/* add into menu */
	function program_add_menu() {
		add_menu_page( 'Price Settings', 'Price Settings', 'manage_options', 'manage-pricesettings', array(__CLASS__,'update_pricesettings'),'dashicons-welcome-learn-more');
		add_submenu_page( 'manage-pricesettings', 'Manage Programs', 'Manage Programs', 'manage_options', 'manage-program', array(__CLASS__,'program_list_table'));
	    add_submenu_page( 'manage-pricesettings', 'Manage Program Weeks', 'Manage Program Weeks', 'manage_options', 'manage-program-weeks', array(__CLASS__,'programweeks_list_table'));
	    add_submenu_page( 'manage-pricesettings', 'Manage Program Dates', 'Manage Program Dates', 'manage_options', 'manage-program-dates', array(__CLASS__,'programdates_list_table'));
	    add_submenu_page( 'manage-pricesettings', 'Manage Bookings', 'Manage Bookings', 'manage_options', 'manage-bookings', array(__CLASS__,'programbookings_list_table'));
	    add_submenu_page( 'manage-pricesettings', 'Manage Payment Account', 'Manage Payment Account', 'manage_options', 'update-paymentaccount', array(__CLASS__,'update_paymentaccount'));
	    add_submenu_page( 'manage-pricesettings', 'Manage Accommodations', 'Manage Accommodations', 'manage_options', 'manage-accommodations', array(__CLASS__,'accommodations_list_table'));
	}

    /* Activation plugin,  create table */
    function program_install() {
    	global $wpdb;
		$programtable_name = $wpdb->prefix . 'programs';
		$programweekstable_name = $wpdb->prefix . 'programweeks';
		$programdatestable_name = $wpdb->prefix . 'programdates';
		$programbookingstable_name = $wpdb->prefix . 'programbookings';
		$paymentaccounttable_name = $wpdb->prefix . 'paymentaccount';
		$pricesettingstable_name = $wpdb->prefix . 'pricesettings';
		$accommodationstable_name = $wpdb->prefix . 'accommodations';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$programtable_name}'" ) != $programtable_name ) {

			$programtablecreate = "CREATE TABLE $programtable_name (
			  	`pg_id` int(11) NOT NULL AUTO_INCREMENT,
			  	`pg_name` varchar(255) NOT NULL,
			  	`pg_status` int(11) NOT NULL,
			  	`pg_created_date` datetime NOT NULL,
			  	`pg_updated_date` datetime DEFAULT NULL,
			  	PRIMARY KEY (`pg_id`)
			); ";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $programtablecreate );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$programweekstable_name}'" ) != $programweekstable_name ) {

			$programweekstablecreate = "CREATE TABLE $programweekstable_name (
			  	`pw_id` int(11) NOT NULL AUTO_INCREMENT,
			  	`pg_id` int(11) NOT NULL,
			  	`pw_week` int(11) NOT NULL,
			  	`pw_week_rate` int(11) NOT NULL,
			  	`pw_status` int(11) NOT NULL,
			  	`pw_created_date` datetime NOT NULL,
			  	`pw_updated_date` datetime DEFAULT NULL,
			  	PRIMARY KEY (`pw_id`)
			); ";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $programweekstablecreate );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$programdatestable_name}'" ) != $programdatestable_name ) {

			$programdatestablecreate = "CREATE TABLE $programdatestable_name (
			  	`pd_id` int(11) NOT NULL AUTO_INCREMENT,
			  	`pg_id` int(11) NOT NULL,
			  	`pd_date` date NOT NULL,
			  	`pd_status` int(11) NOT NULL,
			  	`pd_created_date` datetime NOT NULL,
			  	`pd_updated_date` datetime DEFAULT NULL,
			  	PRIMARY KEY (`pd_id`)
			); ";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $programdatestablecreate );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$programbookingstable_name}'" ) != $programbookingstable_name ) {

			$programbookingstablecreate = "CREATE TABLE $programbookingstable_name (
			  	`pb_id` int(11) NOT NULL,
			  	`pb_school_city` varchar(255) NOT NULL,
			  	`pg_id` int(11) NOT NULL,
			  	`pb_transaction_id` varchar(255) NOT NULL,
			  	`pb_age` varchar(10) NOT NULL,
			  	`pb_from_to_date` varchar(200) NOT NULL,
			  	`pw_id` int(11) NOT NULL,
			  	`pb_program_fees` varchar(50) NOT NULL,
			  	`pb_registration_fees` varchar(50) NOT NULL,
			  	`pb_accommodation_fee` varchar(50) NOT NULL,
			  	`pb_accommodation_placement_fee` varchar(50) NOT NULL,
			  	`pb_accommodation_name` varchar(255) NOT NULL,
			  	`pb_username` varchar(100) NOT NULL,
			  	`pb_birthdate` date NOT NULL,
			  	`pb_gender` varchar(20) NOT NULL,
			  	`pb_email` varchar(200) NOT NULL,
			  	`pb_mobile` varchar(50) NOT NULL,
			  	`pb_nationality` varchar(100) NOT NULL,
			  	`pb_country_citizenship` int(11) NOT NULL,
			  	`pb_address1` varchar(255) NOT NULL,
			  	`pb_address2` varchar(255) NOT NULL,
			  	`pb_city` varchar(255) NOT NULL,
			  	`pb_state` varchar(255) NOT NULL,
			  	`pb_zipcode` varchar(50) NOT NULL,
			  	`pb_country` int(11) NOT NULL,
			  	`pb_english_level` varchar(255) NOT NULL,
			  	`pb_special_request` text NOT NULL,
			  	`pb_donation_amount` varchar(100) NOT NULL,
			  	`pb_donor_card_holder_name` varchar(255) NOT NULL,
			  	`pb_donor_card_number` varchar(50) NOT NULL,
			  	`pb_donor_cvv` varchar(50) NOT NULL,
			  	`pb_donor_card_expiry` varchar(50) NOT NULL,
			  	`pb_created_date` datetime NOT NULL,
			  	`pb_updated_date` datetime DEFAULT NULL,
			  	PRIMARY KEY (`pd_id`)
			); ";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $programbookingstablecreate );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$paymentaccounttable_name}'" ) != $paymentaccounttable_name ) {

			$paymentaccounttablecreate = "CREATE TABLE $paymentaccounttable_name (
			  	`pa_id` int(11) NOT NULL,
			  	`pa_login_id` varchar(255) NOT NULL,
			  	`pa_transcation_key` varchar(255) NOT NULL,
			  	`pa_mode` varchar(20) NOT NULL,
			  	`pa_description` varchar(255) NOT NULL,
			  	`pa_updated_date` datetime NOT NULL,
			  	PRIMARY KEY (`pa_id`)
			); ";

			$paymentaccounttabledata = "INSERT INTO `wp_paymentaccount` (`pa_id`, `pa_login_id`, `pa_transcation_key`, `pa_mode`, `pa_description`, `pa_updated_date`) VALUES ('1', '5g9A3H4FbW', '3k5283hNZnBG59G3', 'Live', '', '2018-12-11 00:00:00');";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $paymentaccounttablecreate );
			dbDelta( $paymentaccounttabledata );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$pricesettingstable_name}'" ) != $pricesettingstable_name ) {

			$pricesettingstablecreate = "CREATE TABLE $pricesettingstable_name (
			  	`ps_id` int(11) NOT NULL,
			  	`ps_admission_fee` int(11) NOT NULL,
			  	`ps_accommodation_fee` int(11) NOT NULL,
			  	`ps_updated_date` datetime NOT NULL,
			  	PRIMARY KEY (`ps_id`)
			); ";

			$pricesettingstabledata = "INSERT INTO `wp_pricesettings` (`ps_id`, `ps_admission_fee`, `ps_accommodation_fee`, `ps_updated_date`) VALUES (1, 100, 80, '2018-12-20 05:59:55');";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $pricesettingstablecreate );
			dbDelta( $pricesettingstabledata );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$accommodationstable_name}'" ) != $accommodationstable_name ) {

			$accommodationstablecreate = "CREATE TABLE $accommodationstable_name (
			  	`ac_id` int(11) NOT NULL,
			  	`ac_name` varchar(255) NOT NULL,
			  	`ac_address` text NOT NULL,
			  	`ac_description` text NOT NULL,
			  	`ac_price` varchar(50) NOT NULL,
			  	`ac_image` varchar(255) NOT NULL,
			  	`ac_status` int(11) NOT NULL,
			  	`ac_created_date` datetime NOT NULL,
			  	`ac_updated_date` datetime DEFAULT NULL,
			  	PRIMARY KEY (`ac_id`)
			); ";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $accommodationstablecreate );
		}
    }

    /* Deactive Plugin */
    function wpa_uninstall() {
    }

	/** Display the program list table page */
	public function program_list_table()
    {
    	self::cssandjs();
    	$action = ($_GET['action']) ? $_GET['action'] : '';

    	if($action == 'edit' || $action == 'add')
    	{
    		self::addupdate_program();
    		exit; die();
    	}
    	
        $programListTable = new Program_List_Table();
        $programListTable->prepare_items();
        ?>
            <div class="wrap">
                <h1 class="wp-heading-inline"> Manage Programs</h1> 
	    		<a class="page-title-action add-new-program" href="<?= get_site_url();?>/wp-admin/admin.php?page=manage-program&action=add" >Add Program</a>
	    		
                <?php $programListTable->display(); ?>
            </div>
            <script type="text/javascript">
				$('.delete-program').click(function(){
					var pg_id = $(this).attr('pg_id');
					swal({
					  title: "Are you sure?",
					  text: "Once deleted, you will not be able to recover this program!",
					  icon: "warning",
					  buttons: true,
					  dangerMode: true,
					})
					.then((willDelete) => {
					  if (willDelete) {
					  	$.blockUI();
					    $.ajax({
							type: "POST",
							url: "<?= get_site_url();?>/wp-admin/admin-ajax.php",
							data: {action:'program_delete',pg_id:pg_id},
							success: function(data){
								if(data == 'success'){
									$.unblockUI();
									swal("Well Done", "Program deleted successfully !", "success");
								    setTimeout(function(){ 
										window.location.reload();
									}, 1000);
								}
								else
								{
									$.unblockUI();
									swal("Opps..", "Something went wrong. Please try again later !", "warning");
								}
							}
						});
					  } 
					});
				});
	    	</script>
        <?php
    }

    /** Display the program list table page */
	public function programweeks_list_table()
    {
    	self::cssandjs();
    	$action = ($_GET['action']) ? $_GET['action'] : '';

    	if($action == 'edit' || $action == 'add')
    	{
    		self::addupdate_programweeks();
    		exit; die();
    	}
    	
        $programweeksListTable = new Programweeks_List_Table();
        $programweeksListTable->prepare_items();
        ?>
            <div class="wrap">
                <h1 class="wp-heading-inline"> Manage Program Weeks</h1> 
	    		<a class="page-title-action" href="<?= get_site_url();?>/wp-admin/admin.php?page=manage-program-weeks&action=add" >Add Program Weeks</a>
	    		
                <?php $programweeksListTable->display(); ?>
            </div>
            <script type="text/javascript">
				$('.delete-programweeks').click(function(){
					var pw_id = $(this).attr('pw_id');
					swal({
					  title: "Are you sure?",
					  text: "Once deleted, you will not be able to recover this program week!",
					  icon: "warning",
					  buttons: true,
					  dangerMode: true,
					})
					.then((willDelete) => {
					  if (willDelete) {
					  	$.blockUI();
					    $.ajax({
							type: "POST",
							url: "<?= get_site_url();?>/wp-admin/admin-ajax.php",
							data: {action:'programweeks_delete',pw_id:pw_id},
							success: function(data){
								if(data == 'success'){
									$.unblockUI();
									swal("Well Done", "Program weeks deleted successfully !", "success");
								    setTimeout(function(){ 
										window.location.reload();
									}, 1000);
								}
								else
								{
									$.unblockUI();
									swal("Opps..", "Something went wrong. Please try again later !", "warning");
								}
							}
						});
					  } 
					});
				});
	    	</script>
        <?php
    }

    /** Display the program list table page */
	public function programdates_list_table()
    {
    	self::cssandjs();
    	$action = ($_GET['action']) ? $_GET['action'] : '';

    	if($action == 'edit' || $action == 'add')
    	{
    		self::addupdate_programdates();
    		exit; die();
    	}
    	
        $programdatesListTable = new Programdates_List_Table();
        $programdatesListTable->prepare_items();
        ?>
            <div class="wrap">
                <h1 class="wp-heading-inline"> Manage Program Dates</h1> 
	    		<a class="page-title-action" href="<?= get_site_url();?>/wp-admin/admin.php?page=manage-program-dates&action=add" >Add Program Date</a>
	    		
                <?php $programdatesListTable->display(); ?>
            </div>
            <script type="text/javascript">
				$('.delete-programdates').click(function(){
					var pd_id = $(this).attr('pd_id');
					swal({
					  title: "Are you sure?",
					  text: "Once deleted, you will not be able to recover this program date!",
					  icon: "warning",
					  buttons: true,
					  dangerMode: true,
					})
					.then((willDelete) => {
					  if (willDelete) {
					  	$.blockUI();
					    $.ajax({
							type: "POST",
							url: "<?= get_site_url();?>/wp-admin/admin-ajax.php",
							data: {action:'programdates_delete',pd_id:pd_id},
							success: function(data){
								if(data == 'success'){
									$.unblockUI();
									swal("Well Done", "Program date deleted successfully !", "success");
								    setTimeout(function(){ 
										window.location.reload();
									}, 1000);
								}
								else
								{
									$.unblockUI();
									swal("Opps..", "Something went wrong. Please try again later !", "warning");
								}
							}
						});
					  }
					});
				});
	    	</script>
        <?php
    }

    /** Display the program booking table page **/
    public function programbookings_list_table()
    {
    	self::cssandjs();
    	$action = ($_GET['action']) ? $_GET['action'] : '';

    	if($action == 'view')
    	{
    		self::view_programbooking();
    		exit; die();
    	}
    	
        $programbookingsListTable = new Programbookings_List_Table();
        $programbookingsListTable->prepare_items();
        ?>
            <div class="wrap">
                <h1 class="wp-heading-inline"> Manage Program Bookings</h1> 
                <?php $programbookingsListTable->display(); ?>
            </div>
        <?php
    }

    /** Display the accommodation table page */
	public function accommodations_list_table()
    {
    	self::cssandjs();
    	$action = ($_GET['action']) ? $_GET['action'] : '';

    	if($action == 'edit' || $action == 'add')
    	{
    		self::addupdate_accommodations();
    		exit; die();
    	}
    	
        $accommodationsListTable = new Accommodations_List_Table();
        $accommodationsListTable->prepare_items();
        ?>
            <div class="wrap">
                <h1 class="wp-heading-inline"> Manage Accomodation</h1> 
	    		<a class="page-title-action" href="<?= get_site_url();?>/wp-admin/admin.php?page=manage-accommodations&action=add" >Add Accommodations</a>
	    		
                <?php $accommodationsListTable->display(); ?>
            </div>
            <script type="text/javascript">
				$('.delete-accommodations').click(function(){
					var ac_id = $(this).attr('ac_id');
					swal({
					  title: "Are you sure?",
					  text: "Once deleted, you will not be able to recover this accommodations!",
					  icon: "warning",
					  buttons: true,
					  dangerMode: true,
					})
					.then((willDelete) => {
					  if (willDelete) {
					  	$.blockUI();
					    $.ajax({
							type: "POST",
							url: "<?= get_site_url();?>/wp-admin/admin-ajax.php",
							data: {action:'accommodations_delete',ac_id:ac_id},
							success: function(data){
								if(data == 'success'){
									$.unblockUI();
									swal("Well Done", "Accommodations deleted successfully !", "success");
								    setTimeout(function(){ 
										window.location.reload();
									}, 1000);
								}
								else
								{
									$.unblockUI();
									swal("Opps..", "Something went wrong. Please try again later !", "warning");
								}
							}
						});
					  }
					});
				});
	    	</script>
        <?php
    }
    
    /** function for add update program */
    function addupdate_program()
    { 
    	self::cssandjs();
    	global $wpdb;
    	$pg_id = ($_GET['pg']) ? $_GET['pg'] : '';
    	$title = ($pg_id == '') ? 'Add New Program' : 'Edit Program';
    	$button = ($pg_id == '') ? 'Add New Program' : 'Update Program';

    	$programdetails = array();
    	if($pg_id)
    	{
    		$programdetails = $wpdb->get_results( 'SELECT * FROM wp_programs WHERE pg_id='.$pg_id);
    	}
    	?>

    	<div class="wrap">
    		<h1 class="wp-heading-inline"> <?=  $title ?></h1>
    		<form method="post" name="addupdate_program" id="addupdate_program" class="validate" novalidate="novalidate">
    			<input type="hidden" name="pg_id" id="pg_id" value="<?=  $pg_id ?>">
    			<input type="hidden" name="action" value="addupdate_program_data">
    			<table class="form-table">
    				<tbody>
    					<tr class="form-field form-required">
							<th><label for="pg_name">Program Name <span class="description">(required)</span></label></th>
							<td><input name="pg_name" type="text" id="pg_name" value="<?= $programdetails[0]->pg_name ?>" aria-required="true"  maxlength="60"></td>
						</tr>
						<tr class="form-field">
							<th><label for="pg_status">Status </label></th>
							<td>
								<select class="form-control" id="pg_status" name="pg_status">
						      		<option value="1" <?php echo ($programdetails[0]->pg_status == '1')?"selected":"";?>>Active</option>
						      		<option value="2" <?php echo ($programdetails[0]->pg_status == '2')?"selected":"";?>>Inactive</option>
						  		</select>
						  	</td>
						</tr>
    				</tbody>
    			</table>
    			<p class="submit"><input type="submit" class="button button-primary" value="<?=  $button ?>"></p>
				
    		</form>
    		<script type="text/javascript">
    			$("#addupdate_program").validate({
    				rules: {
    					pg_name: {
    						required: true,
    					},
    					pg_status: {
    						required: true,
    					}
    				},
    				submitHandler: function(e) {
    					$('input[type="submit"]').attr('disabled','disabled');
			            var pg_id = $('#pg_id').val();
			            var fdata = $('#addupdate_program').serialize(); 
			            $.blockUI();
					    $.ajax({
							type: "POST",
							url: "<?= get_site_url();?>/wp-admin/admin-ajax.php",
							data: fdata,
							success: function(data){
								if(data == 'success'){
									$.unblockUI();
									if(pg_id == '')
									{
										swal("Well Done", "Program inserted successfully !", "success");
									}
									else
									{
										swal("Well Done", "Program updated successfully !", "success");
									}
								    setTimeout(function(){ 
										window.location.href='<?= get_site_url();?>/wp-admin/admin.php?page=manage-program';
									}, 1000);
								}
								else if(data == 'duplicate'){
									$.unblockUI();
									swal("Opps..", "Program name already exist !", "warning");
									$('input[type="submit"]').removeAttr('disabled');
								}
								else
								{
									$.unblockUI();
									swal("Opps..", "Something went wrong. Please try again later !", "warning");
									$('input[type="submit"]').removeAttr('disabled');
								}
							}
						});
				    }
    			});
    		</script>
    	</div>
    <?php }

    /** function for add update programweeks */
    function addupdate_programweeks()
    { 
    	self::cssandjs();
    	global $wpdb;
    	$pw_id = ($_GET['pw']) ? $_GET['pw'] : '';
    	$title = ($pw_id == '') ? 'Add New Program Weeks' : 'Edit Program Weeks';
    	$button = ($pw_id == '') ? 'Add New Program Weeks' : 'Update Program Weeks';

    	$programweeksdetails = array();
    	if($pw_id)
    	{
    		$programweeksdetails = $wpdb->get_results( 'SELECT * FROM wp_programweeks WHERE pw_id='.$pw_id);
    	}
    	?>

    	<div class="wrap">
    		<h1 class="wp-heading-inline"> <?=  $title ?></h1>
    		<form method="post" name="addupdate_programweeks" id="addupdate_programweeks" class="validate" novalidate="novalidate">
    			<input type="hidden" name="pw_id" id="pw_id" value="<?=  $pw_id ?>">
    			<input type="hidden" name="action" value="addupdate_programweeks_data">
    			<table class="form-table">
    				<tbody>
    					<tr class="form-field form-required">
							<th><label for="pg_id">Program <span class="description">(required)</span></label></th>					
							<td>
								<select class="form-control" id="pg_id" name="pg_id">
						    	<?php
									$programs = $wpdb->get_results( 'SELECT * FROM wp_programs');
									foreach($programs as $program){
								?>
						      		<option value="<?=$program->pg_id?>" <?php echo ($program->pg_id == $programweeksdetails[0]->pg_id)?"selected":"";?>><?=$program->pg_name?></option>
						      	<?php } ?>
						  		</select>
						  	</td>
						</tr>
						<tr class="form-field form-required">
							<th><label for="pw_week">Program Weeks <span class="description">(required)</span></label></th>
							<td><input class="onlydigit" name="pw_week" type="text" id="pw_week" value="<?= $programweeksdetails[0]->pw_week ?>" aria-required="true" maxlength="2"></td>
						</tr>
						<tr class="form-field form-required">
							<th><label for="pw_week_rate">Rate/Week <span class="description">(required)</span></label></th>
							<td><input class="onlydigit" name="pw_week_rate" type="text" id="pw_week_rate" value="<?= $programweeksdetails[0]->pw_week_rate ?>" aria-required="true" maxlength="6"></td>
						</tr>
						<tr class="form-field">
							<th><label for="pw_status">Status </label></th>
							<td>
								<select class="form-control" id="pw_status" name="pw_status">
						      		<option value="1" <?php echo ($programweeksdetails[0]->pw_status == '1')?"selected":"";?>>Active</option>
						      		<option value="2" <?php echo ($programweeksdetails[0]->pw_status == '2')?"selected":"";?>>Inactive</option>
						  		</select>
						  	</td>
						</tr>
    				</tbody>
    			</table>
    			<p class="submit"><input type="submit" class="button button-primary" value="<?=  $button ?>"></p>
    		</form>
    		<script type="text/javascript">
    			$("#addupdate_programweeks").validate({
    				rules: {
    					pg_id: {
    						required: true,
    					},
    					pw_week: {
    						required: true,
    						number: true,
    					},
    					pw_week_rate: {
    						required: true,
    						number: true,
    					},
    					pw_status: {
    						required: true,
    					}
    				},
    				submitHandler: function(e) {
    					$('input[type="submit"]').attr('disabled','disabled');
			            var pw_id = $('#pw_id').val();
			            var fdata = $('#addupdate_programweeks').serialize(); 
			            $.blockUI();
					    $.ajax({
							type: "POST",
							url: "<?= get_site_url();?>/wp-admin/admin-ajax.php",
							data: fdata,
							success: function(data){
								if(data == 'success'){
									$.unblockUI();
									if(pw_id == '')
									{
										swal("Well Done", "Program Weeks inserted successfully !", "success");
									}
									else
									{
										swal("Well Done", "Program Weeks updated successfully !", "success");
									}
									
								    setTimeout(function(){ 
										window.location.href='<?= get_site_url();?>/wp-admin/admin.php?page=manage-program-weeks';
									}, 1000);
								}
								else if(data == 'duplicate'){
									$.unblockUI();
									swal("Opps..", "Program weeks already exist !", "warning");
									$('input[type="submit"]').removeAttr('disabled');
								}
								else
								{
									$.unblockUI();
									swal("Opps..", "Something went wrong. Please try again later !", "warning");
									$('input[type="submit"]').removeAttr('disabled');
								}
							}
						});
				    }
    			});
    		</script>
    	</div>
    <?php }

    /** function for add update programweeks */
    function addupdate_programdates()
    { 
    	self::cssandjs();
    	global $wpdb;
    	$pd_id = ($_GET['pd']) ? $_GET['pd'] : '';
    	$title = ($pd_id == '') ? 'Add New Program Date' : 'Edit Program Date';
    	$button = ($pd_id == '') ? 'Add New Program Date' : 'Update Program Date';

    	$programdatesdetails = array();
    	$pd_date = date('d M Y');
    	if($pd_id)
    	{
    		$programdatesdetails = $wpdb->get_results( 'SELECT * FROM wp_programdates WHERE pd_id='.$pd_id);
    		$pd_date = date('d M Y',strtotime($programdatesdetails[0]->pd_date));
    	}
    	?>
    	<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ) .'assests/bootstrap.min.css'; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ) .'assests/datepicker.css'; ?>">
		<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) .'assests/datepicker.js'; ?>"></script>
    	<div class="wrap">
    		<h1 class="wp-heading-inline"> <?=  $title ?></h1>
    		<form method="post" name="addupdate_programdates" id="addupdate_programdates" class="validate" novalidate="novalidate">
    			<input type="hidden" name="pd_id" id="pd_id" value="<?=  $pd_id ?>">
    			<input type="hidden" name="action" value="addupdate_programdates_data">
    			<table class="form-table">
    				<tbody>
    					<tr class="form-field form-required">
							<th><label for="pg_id">Program <span class="description">(required)</span></label></th>
							<td>
								<select class="form-control" id="pg_id" name="pg_id">
						    	<?php
									$programs = $wpdb->get_results( 'SELECT * FROM wp_programs');
									foreach($programs as $program){
								?>
						      		<option value="<?=$program->pg_id?>" <?php echo ($program->pg_id == $programdatesdetails[0]->pg_id)?"selected":"";?>><?=$program->pg_name?></option>
						      	<?php } ?>
						  		</select>
						  	</td>
						</tr>
						<tr class="form-field form-required">
							<th><label for="pd_date">Program Date <span class="description">(required)</span></label></th>
							<td><input name="pd_date" type="text" id="pd_date" value="<?= $pd_date ?>" aria-required="true"></td>
						</tr>
						<tr class="form-field">
							<th><label for="pd_status">Status </label></th>
							<td>
								<select class="form-control" id="pd_status" name="pd_status">
						      		<option value="1" <?php echo ($programdatesdetails[0]->pd_status == '1')?"selected":"";?>>Active</option>
						      		<option value="2" <?php echo ($programdatesdetails[0]->pd_status == '2')?"selected":"";?>>Inactive</option>
						  		</select>
						  	</td>
						</tr>
    				</tbody>
    			</table>
    			<p class="submit"><input type="submit" class="button button-primary" value="<?=  $button ?>"></p>
    		</form>
    		<script type="text/javascript">
				$('#pd_date').datepicker({     
	                autoclose: true,
	                //startDate: new Date(),
	                format: 'dd M yyyy',
	            }).attr('readonly','true');
    			$("#addupdate_programdates").validate({
    				rules: {
    					pg_id: {
    						required: true,
    					},
    					pw_status: {
    						required: true,
    					}
    				},
    				submitHandler: function(e) {
    					$('input[type="submit"]').attr('disabled','disabled');
			            var pd_id = $('#pd_id').val();
			            var fdata = $('#addupdate_programdates').serialize(); 
			            $.blockUI();
					    $.ajax({
							type: "POST",
							url: "<?= get_site_url();?>/wp-admin/admin-ajax.php",
							data: fdata,
							success: function(data){
								if(data == 'success'){
									$.unblockUI();
									if(pd_id == '')
									{
										swal("Well Done", "Program date inserted successfully !", "success");
									}
									else
									{
										swal("Well Done", "Program date updated successfully !", "success");
									}
									
								    setTimeout(function(){ 
										window.location.href='<?= get_site_url();?>/wp-admin/admin.php?page=manage-program-dates';
									}, 1000);
								}
								else if(data == 'duplicate'){
									$.unblockUI();
									swal("Opps..", "Program date already exist !", "warning");
									$('input[type="submit"]').removeAttr('disabled');
								}
								else
								{
									$.unblockUI();
									swal("Opps..", "Something went wrong. Please try again later !", "warning");
									$('input[type="submit"]').removeAttr('disabled');
								}
							}
						});
				    }
    			});
    		</script>
    	</div>
    <?php }

    
    /** function for add update accommodations */
    function addupdate_accommodations()
    { 
    	self::cssandjs();
    	global $wpdb;
    	$ac_id = ($_GET['ac']) ? $_GET['ac'] : '';
    	$title = ($ac_id == '') ? 'Add New Accommodation' : 'Edit Accommodation';
    	$button = ($ac_id == '') ? 'Add New Accommodation' : 'Update Accommodation';

    	$accommodationdetails = array();
    	if($ac_id)
    	{
    		$accommodationdetails = $wpdb->get_results( 'SELECT * FROM wp_accommodations WHERE ac_id='.$ac_id);
    	}
    	?>
    	<div class="wrap">
    		<h1 class="wp-heading-inline"> <?=  $title ?></h1>
    		<form method="post" id="addupdate_accommodations">
    			<input type="hidden" name="ac_id" id="ac_id" value="<?=  $ac_id ?>">
    			<input type="hidden" name="action" value="addupdate_accommodations_data">
    			<table class="form-table">
    				<tbody>
						<tr class="form-field form-required">
							<th><label for="ac_name">Name <span class="description">(required)</span></label></th>
							<td><input name="ac_name" type="text" id="ac_name" value="<?= $accommodationdetails[0]->ac_name ?>" aria-required="true"></td>
						</tr>
						<tr class="form-field form-required">
							<th><label for="ac_address">Address <span class="description">(required)</span></label></th>
							<td><input name="ac_address" type="text" id="ac_address" value="<?= $accommodationdetails[0]->ac_address ?>" aria-required="true"></td>
						</tr>
						<tr class="form-field form-required">
							<th><label for="ac_price">Price <span class="description">(required)</span></label></th>
							<td><input name="ac_price" type="text" class="onlydigit" id="ac_price" value="<?= $accommodationdetails[0]->ac_price ?>" aria-required="true"></td>
						</tr>
						<tr class="form-field form-required">
							<th><label for="ac_description">Description</label></th>
							<td><textarea name="ac_description" id="ac_description"><?= $accommodationdetails[0]->ac_description ?></textarea></td>
						</tr>
						<tr class="form-field form-required">
							<th><label for="ac_image">Image </label></th>
							<td>
								<input name="ac_image" type="file" id="ac_image">
								<?php 
									if($accommodationdetails[0]->ac_image != '')
									{ $image = $accommodationdetails[0]->ac_image; ?>
										<img width="100px" height="100px" src="<?php echo plugins_url().'/program-manage/assests/images/'.$image; ?>">
									<?php }
								?>
							</td>
						</tr>
						<tr class="form-field">
							<th><label for="ac_status">Status </label></th>
							<td>
								<select class="form-control" id="ac_status" name="ac_status">
						      		<option value="1" <?php echo ($accommodationdetails[0]->ac_status == '1')?"selected":"";?>>Active</option>
						      		<option value="2" <?php echo ($accommodationdetails[0]->ac_status == '2')?"selected":"";?>>Inactive</option>
						  		</select>
						  	</td>
						</tr>
    				</tbody>
    			</table>
    			<p class="submit"><input type="submit" class="button button-primary" value="<?=  $button ?>"></p>
    		</form>
    		<script type="text/javascript">
    			$("#addupdate_accommodations").validate({
    				rules: {
    					ac_name: {
    						required: true,
    					},
    					ac_address: {
    						required: true,
    					},
    					ac_price: {
    						required: true,
    					},
    					ac_status: {
    						required: true,
    					}
    				},
    				submitHandler: function(e) {
    					$('input[type="submit"]').attr('disabled','disabled');
			            var ac_id = $('#ac_id').val();
			            var form=document.getElementById('addupdate_accommodations');
			            var fdata = new FormData(form);
			            //var fdata = $('#addupdate_accommodations').serialize(); 
			            $.blockUI();
					    $.ajax({
							type: "POST",
							url: "<?= get_site_url();?>/wp-admin/admin-ajax.php",
							data: fdata,
			                contentType: false,
			                processData: false,
							success: function(data){
								if(data == 'success'){
									$.unblockUI();
									if(ac_id == '')
									{
										swal("Well Done", "Accommodation inserted successfully !", "success");
									}
									else
									{
										swal("Well Done", "Accommodation updated successfully !", "success");
									}
									
								    setTimeout(function(){ 
										window.location.href='<?= get_site_url();?>/wp-admin/admin.php?page=manage-accommodations';
									}, 1000);
								}
								else if(data == 'duplicate'){
									$.unblockUI();
									swal("Opps..", "Accommodation already exist !", "warning");
									$('input[type="submit"]').removeAttr('disabled');
								}
								else
								{
									$.unblockUI();
									swal("Opps..", "Something went wrong. Please try again later !", "warning");
									$('input[type="submit"]').removeAttr('disabled');
								}
							}
						});
				    }
    			});
    		</script>
    	</div>
    <?php }

    /** function for view programbooking */
    function view_programbooking()
    { 
    	global $wpdb;
    	$pb_id = ($_GET['pb']) ? $_GET['pb'] : '';
    	$title = 'View Booking Details';

    	$programbookingsdetails = array();
    	if($pb_id)
    	{
    		$programbookingsdetails = $wpdb->get_results( 'SELECT pb.*, pg.pg_name, pw.pw_week FROM wp_programbookings as pb LEFT JOIN wp_programs as pg ON (pg.pg_id = pb.pg_id) LEFT JOIN wp_programweeks as pw ON (pw.pw_id = pb.pw_id) WHERE pb_id='.$pb_id);
    		$pb_birthdate = date('d M Y',strtotime($programbookingsdetails[0]->pb_birthdate));
    	}
    	?>
    	<div class="wrap">
    		<h1 class="wp-heading-inline"> <?=  $title ?></h1>
			<table class="form-table">
				<tbody>
					<tr>
						<th colspan="2"><h3> Program Details</h3></th>
					</tr>
					<tr class="form-field">
						<th><label for="pg_name">Program Name</label></th>
						<td><input id ="pg_name" type="text" value="<?= $programbookingsdetails[0]->pg_name ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_from_to_date">Program Duration</label></th>
						<td><input id ="pb_from_to_date" type="text" value="<?= $programbookingsdetails[0]->pb_from_to_date ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pw_week">Program Week</label></th>
						<td><input id ="pw_week" type="text" value="<?= $programbookingsdetails[0]->pw_week ?> Week" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_program_fees">Program Fee</label></th>
						<td><input id ="pb_program_fees" type="text" value="<?= $programbookingsdetails[0]->pb_program_fees ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_registration_fees">Program Registration Fee</label></th>
						<td><input id ="pb_registration_fees" type="text" value="<?= $programbookingsdetails[0]->pb_registration_fees ?>" readonly></td>
					</tr>

					<?php  if(($programbookingsdetails[0]->pb_accommodation_fee) > 0) { ?>

					<tr>
						<th colspan="2"><h3> Accommodation Details</h3></th>
					</tr>
					
					<tr class="form-field">
						<th><label for="pb_accommodation_name">Accommodation Name</label></th>
						<td><input id ="pb_accommodation_name" type="text" value="<?= $programbookingsdetails[0]->pb_accommodation_name ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_accommodation_fee">Accommodation Fee</label></th>
						<td><input id ="pb_accommodation_fee" type="text" value="<?= $programbookingsdetails[0]->pb_accommodation_fee ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_accommodation_placement_fee">Accommodation Placement Fee</label></th>
						<td><input id ="pb_accommodation_placement_fee" type="text" value="<?= $programbookingsdetails[0]->pb_accommodation_placement_fee ?>" readonly></td>
					</tr>

					<?php } ?>
					
					<tr>
						<th colspan="2"><h3> User Details</h3></th>
					</tr>
					
					<tr class="form-field">
						<th><label for="pb_username">User Name</label></th>
						<td><input id ="pb_username" type="text" value="<?= $programbookingsdetails[0]->pb_username ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_birthdate">Birthdate</label></th>
						<td><input id ="pb_birthdate" type="text" value="<?= $pb_birthdate ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_gender">Gender</label></th>
						<td><input id ="pb_gender" type="text" value="<?= ucfirst($programbookingsdetails[0]->pb_gender) ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_email">Email</label></th>
						<td><input id ="pb_email" type="text" value="<?= $programbookingsdetails[0]->pb_email ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_mobile">Mobile</label></th>
						<td><input id ="pb_mobile" type="text" value="<?= $programbookingsdetails[0]->pb_mobile ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_nationality">Nationality</label></th>
						<td>
							<?php 
								$pb_nationality = $programbookingsdetails[0]->pb_nationality;
								$nationalitydetails = $wpdb->get_results( 'SELECT name FROM wp_countries WHERE id='.$pb_nationality);
							?>
						<input id ="pb_nationality" type="text" value="<?= $nationalitydetails[0]->name ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_country_citizenship">Country Citizenship</label></th>
						<td>
							<?php 
								$pb_country_citizenship = $programbookingsdetails[0]->pb_country_citizenship;
								$citizenshipdetails = $wpdb->get_results( 'SELECT name FROM wp_countries WHERE id='.$pb_country_citizenship);
							?>
							<input id ="pb_country_citizenship" type="text" value="<?= $citizenshipdetails[0]->name ?>" readonly>
						</td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_address1">Address 1</label></th>
						<td><input id ="pb_address1" type="text" value="<?= $programbookingsdetails[0]->pb_address1 ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_address2">Address 2</label></th>
						<td><input id ="pb_address2" type="text" value="<?= $programbookingsdetails[0]->pb_address2 ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_city">City</label></th>
						<td><input id ="pb_city" type="text" value="<?= $programbookingsdetails[0]->pb_city ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_state">State</label></th>
						<td><input id ="pb_state" type="text" value="<?= $programbookingsdetails[0]->pb_state ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_zipcode">Zip Code</label></th>
						<td><input id ="pb_zipcode" type="text" value="<?= $programbookingsdetails[0]->pb_zipcode ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_country">Country</label></th>
						<td>
							<?php 
								$pb_country = $programbookingsdetails[0]->pb_country;
								$countrydetails = $wpdb->get_results( 'SELECT name FROM wp_countries WHERE id='.$pb_country);
							?>
						<input id ="pb_country" type="text" value="<?= $countrydetails[0]->name ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_english_level">English Level</label></th>
						<td><input id ="pb_english_level" type="text" value="<?= $programbookingsdetails[0]->pb_english_level ?>" readonly></td>
					</tr>

					<tr class="form-field">
						<th><label for="pb_special_request">Special Request</label></th>
						<td><textarea id ="pb_special_request" readonly><?= $programbookingsdetails[0]->pb_special_request ?></textarea></td>
					</tr>

					<tr>
						<th colspan="2"><h3> Payment Details</h3></th>
					</tr>
					<tr class="form-field">
						<th><label for="pb_transaction_id">Transaction Id </label></th>
						<td><input id ="pb_transaction_id" type="text" value="<?= $programbookingsdetails[0]->pb_transaction_id ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_donation_amount">Payment Amount</label></th>
						<td><input id ="pb_donation_amount" type="text" value="<?= $programbookingsdetails[0]->pb_donation_amount ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_donor_card_number">Card Number</label></th>
						<td><input id ="pb_donor_card_number" type="text" value="<?= $programbookingsdetails[0]->pb_donor_card_number ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_donor_card_holder_name">Card Holder Name</label></th>
						<td><input id ="pb_donor_card_holder_name" type="text" value="<?= $programbookingsdetails[0]->pb_donor_card_holder_name ?>" readonly></td>
					</tr>
					<!-- <tr class="form-field">
						<th><label for="pb_donor_cvv">CVV</label></th>
						<td><input id ="pb_donor_cvv" type="text" value="<?= ucfirst($programbookingsdetails[0]->pb_donor_cvv) ?>" readonly></td>
					</tr>
					<tr class="form-field">
						<th><label for="pb_donor_card_expiry">Card Expiry </label></th>
						<td><input id ="pb_donor_card_expiry" type="text" value="<?= $programbookingsdetails[0]->pb_donor_card_expiry ?>" readonly></td>
					</tr> -->
					
				</tbody>
			</table>
    	</div>
    <?php }

    /** function for update payment account */
    function update_paymentaccount()
    { 
    	self::cssandjs();
    	global $wpdb;
    	$title = 'Update Payment Account';
    	$button = 'Update Payment Account';

    	$pd_date = date('d M Y');
    	$programaccountdetails = $wpdb->get_results( 'SELECT * FROM wp_paymentaccount WHERE pa_id="1" ');
    	?>
    	<div class="wrap">
    		<h1 class="wp-heading-inline"> <?=  $title ?></h1>
    		<form method="post" name="update_paymentaccount" id="update_paymentaccount" class="validate" novalidate="novalidate">
    			<input type="hidden" name="action" value="update_paymentaccount_data">
    			<table class="form-table">
    				<tbody>
    					<tr class="form-field form-required">
							<th><label for="pa_login_id">Authorize.net Login ID <span class="description">(required)</span></label></th>
							<td><input name="pa_login_id" type="text" id="pa_login_id" value="<?= $programaccountdetails[0]->pa_login_id ?>" aria-required="true"></td>
						</tr>
						<tr class="form-field form-required">
							<th><label for="pa_transcation_key">Authorize.net Transaction Key <span class="description">(required)</span></label></th>
							<td><input name="pa_transcation_key" type="text" id="pa_transcation_key" value="<?= $programaccountdetails[0]->pa_transcation_key ?>" aria-required="true"></td>
						</tr>
						<tr class="form-field">
							<th><label for="pa_mode">Mode(Live/Test Sandbox) </label></th>
							<td>
								<select class="form-control" id="pa_mode" name="pa_mode">
						      		<option value="Live" <?php echo ($programaccountdetails[0]->pa_mode == 'Live')?"selected":"";?>>Live</option>
						      		<option value="Test" <?php echo ($programaccountdetails[0]->pa_mode == 'Test')?"selected":"";?>>Test/Sandbox</option>
						  		</select>
						  	</td>
						</tr>
						<tr class="form-field form-required">
							<th><label for="pa_description">Processor Description</label></th>
							<td><input name="pa_description" type="text" id="pa_description" value="<?= $programaccountdetails[0]->pa_description ?>"></td>
						</tr>
    				</tbody>
    			</table>
    			<p class="submit"><input type="submit" class="button button-primary" value="<?=  $button ?>"></p>
    		</form>
    		<script type="text/javascript">
    			$("#update_paymentaccount").validate({
    				rules: {
    					pa_login_id: {
    						required: true,
    					},
    					pa_transcation_key: {
    						required: true,
    					}
    				},
    				submitHandler: function(e) {
    					$('input[type="submit"]').attr('disabled','disabled');
			            var fdata = $('#update_paymentaccount').serialize(); 
			            $.blockUI();
					    $.ajax({
							type: "POST",
							url: "<?= get_site_url();?>/wp-admin/admin-ajax.php",
							data: fdata,
							success: function(data){
								if(data == 'success'){
									$.unblockUI();
									swal("Well Done", "Payment account updated successfully !", "success");
									
								    setTimeout(function(){ 
										window.location.href='<?= get_site_url();?>/wp-admin/admin.php?page=update-paymentaccount';
									}, 1000);
								}
								else
								{
									$.unblockUI();
									swal("Opps..", "Something went wrong. Please try again later !", "warning");
									$('input[type="submit"]').removeAttr('disabled');
								}
							}
						});
				    }
    			});
    		</script>
    	</div>
    <?php }

    /** function for update price settings */
    function update_pricesettings()
    { 
    	self::cssandjs();
    	global $wpdb;
    	$title = 'Update Prices';
    	$button = 'Update Prices';

    	$pd_date = date('d M Y');
    	$pricesettingsdetails = $wpdb->get_results( 'SELECT * FROM wp_pricesettings WHERE ps_id="1" ');
    	?>
    	<div class="wrap">
    		<h1 class="wp-heading-inline"> <?=  $title ?></h1>
    		<form method="post" name="update_pricesettings" id="update_pricesettings" class="validate" novalidate="novalidate">
    			<input type="hidden" name="action" value="update_pricesettings_data">
    			<table class="form-table">
    				<tbody>
    					<tr class="form-field form-required">
							<th><label for="ps_admission_fee">Admission Fee <span class="description">(required)</span></label></th>
							<td><input name="ps_admission_fee" class="onlydigit" type="text" id="ps_admission_fee" value="<?= $pricesettingsdetails[0]->ps_admission_fee ?>" aria-required="true"></td>
						</tr>
						<tr class="form-field form-required">
							<th><label for="ps_accommodation_fee">Accommodation Fee <span class="description">(required)</span></label></th>
							<td><input name="ps_accommodation_fee" class="onlydigit" type="text" id="ps_accommodation_fee" value="<?= $pricesettingsdetails[0]->ps_accommodation_fee ?>" aria-required="true"></td>
						</tr>
    				</tbody>
    			</table>
    			<p class="submit"><input type="submit" class="button button-primary" value="<?=  $button ?>"></p>
    		</form>
    		<script type="text/javascript">
    			$("#update_pricesettings").validate({
    				rules: {
    					ps_admission_fee: {
    						required: true,
    					},
    					ps_accomodation_fee: {
    						required: true,
    					}
    				},
    				submitHandler: function(e) {
    					$('input[type="submit"]').attr('disabled','disabled');
			            var fdata = $('#update_pricesettings').serialize(); 
			            $.blockUI();
					    $.ajax({
							type: "POST",
							url: "<?= get_site_url();?>/wp-admin/admin-ajax.php",
							data: fdata,
							success: function(data){
								if(data == 'success'){
									$.unblockUI();
									swal("Well Done", "Price setttings updated successfully !", "success");
									
								    setTimeout(function(){ 
										window.location.reload();
									}, 1000);
								}
								else
								{
									$.unblockUI();
									swal("Opps..", "Something went wrong. Please try again later !", "warning");
									$('input[type="submit"]').removeAttr('disabled');
								}
							}
						});
				    }
    			});
    		</script>
    	</div>
    <?php }
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/** Create a new table class that will extend the WP_List_Table */
class Program_List_Table extends WP_List_Table
{

	/** Get the table data */
    private function table_data()
    {
        $data = array();
		global $wpdb;
		$programresults = $wpdb->get_results( 'SELECT * FROM wp_programs');
		$count = 1;
		foreach($programresults as $programdata){

			if($programdata->pg_status == '1')
				$Status = 'Active';
			else
				$Status = 'Inactive';

	        $data[] = array(
	        			'sr_no'   	=> $count,
	        			'pg_id'   	=> $programdata->pg_id,
	                    'pg_name'   => $programdata->pg_name,
	                    'pg_status' => $Status
	                    );
	        $count++;
       	}
        
        return $data;
    }

    /** Override the parent columns method. Defines the columns to use in your listing table */
    public function get_columns()
    {
        $columns = array(
        	'sr_no'        	=> 'No',
            'pg_name'       => 'Name',
            'pg_status' 	=> 'Status'
        );
        return $columns;
    }

    /** Prepare the items for the table to process */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /** Define what data to show on each column of the table */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'sr_no':
            case 'pg_name':
            case 'pg_status':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
    
    /** Define which columns are hidden */
    public function get_hidden_columns()
    {
        return array();
    }
    /** Define the sortable columns */
    public function get_sortable_columns()
    {
    	$sortable_columns = array(
    		'sr_no'  => array('sr_no',false),
		    'pg_name'  => array('pg_name',false),
		);
        return $sortable_columns;
    }
    
    
    /** Allows you to sort the data by the variables set in the $_GET */
    private function sort_data( $a, $b )
    {
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'sr_no'; //If no sort, default to title
	  	$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
	  	$result = strnatcmp($a[$orderby], $b[$orderby]); //Determine sort order
	  	return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }

    /* display edit - delete action */
    function column_pg_name($item) {
	  $actions = array(
	            'edit'      => sprintf('<a href="?page=%s&action=%s&pg=%s">Edit</a>',$_REQUEST['page'],'edit',$item['pg_id']),
	            'delete'    => sprintf('<a href="javascript:void(0)" class="delete-program" pg_id = "'.$item['pg_id'].'">Delete</a>'),
	        );

	  return sprintf('%1$s %2$s', $item['pg_name'], $this->row_actions($actions) );
	}

	/*function get_bulk_actions() {
	  $actions = array(
	    'delete'    => 'Delete'
	  );
	  return $actions;
	}

	function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="pg_id[]" value="%s" />', $item['pg_id']
        );    
    }*/
}

/** Create a new program week table class that will extend the WP_List_Table */
class Programweeks_List_Table extends WP_List_Table
{
	/** Get the table data */
    private function table_data()
    {
        $data = array();
		global $wpdb;
		$programweeksresults = $wpdb->get_results( 'SELECT pw.*, pg.pg_name FROM wp_programweeks as pw LEFT JOIN wp_programs as pg ON (pg.pg_id = pw.pg_id)');
		$count = 01;
		foreach($programweeksresults as $programweekdata){

			if($programweekdata->pw_status == '1')
				$Status = 'Active';
			else
				$Status = 'Inactive';

	        $data[] = array(
				'sr_no'   			=> $count,
				'pw_id'   			=> $programweekdata->pw_id,
				'pw_week'   		=> ($programweekdata->pw_week).' week',
				'pg_name'   		=> $programweekdata->pg_name,
		        'pw_week_rate'   	=> $programweekdata->pw_week_rate,
		        'pw_status' 		=> $Status
	        );
	        $count++;
       	}
        
        return $data;
    }

    /** Override the parent columns method. Defines the columns to use in your listing table */
    public function get_columns()
    {
        $columns = array(
        	'sr_no'        	=> 'No',
            'pw_week'       => 'Program Weeks',
            'pg_name'       => 'Program Name',
            'pw_week_rate'  => 'Rate/Week',
            'pw_status' 	=> 'Status'
        );
        return $columns;
    }

    /** Prepare the items for the table to process */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /** Define what data to show on each column of the table */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'sr_no':
            case 'pg_name':
            case 'pw_week':
            case 'pw_week_rate':
            case 'pw_status':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
    
    /** Define which columns are hidden */
    public function get_hidden_columns()
    {
        return array();
    }
    /** Define the sortable columns */
    public function get_sortable_columns()
    {
    	$sortable_columns = array(
    		'sr_no'  => array('sr_no',false),
		    'pg_name'  => array('pg_name',false),
		    'pw_week'  => array('pw_week',false),
		);
        return $sortable_columns;
    }
    
    
    /** Allows you to sort the data by the variables set in the $_GET */
    private function sort_data( $a, $b )
    {
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'sr_no'; //If no sort, default to title
	  	$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
	  	$result = strnatcmp($a[$orderby], $b[$orderby]); //Determine sort order
	  	return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }

    /* display edit - delete action */
    function column_pw_week($item) {
	  $actions = array(
	            'edit'      => sprintf('<a href="?page=%s&action=%s&pw=%s">Edit</a>',$_REQUEST['page'],'edit',$item['pw_id']),
	            'delete'    => sprintf('<a href="javascript:void(0)" class="delete-programweeks" pw_id = "'.$item['pw_id'].'">Delete</a>'),
	        );

	  return sprintf('%1$s %2$s', $item['pw_week'], $this->row_actions($actions) );
	}
}

/** Create a new program dates table class that will extend the WP_List_Table */
class Programdates_List_Table extends WP_List_Table
{
	/** Get the table data */
    private function table_data()
    {
        $data = array();
		global $wpdb;
		$programdatesresults = $wpdb->get_results( 'SELECT pd.*, pg.pg_name FROM wp_programdates as pd LEFT JOIN wp_programs as pg ON (pg.pg_id = pd.pg_id)');
		$count = 1;
		foreach($programdatesresults as $programdatedata){

			if($programdatedata->pd_status == '1')
				$Status = 'Active';
			else
				$Status = 'Inactive';

    		$data[] = array(
    			'sr_no'   			=> $count,
    			'pd_id'   			=> $programdatedata->pd_id,
    			'pg_name'   		=> $programdatedata->pg_name,
                'pd_date'   		=> date('d M Y',strtotime($programdatedata->pd_date)),
                'pd_status' 		=> $Status
            );
       		$count++;
       	}
        
        return $data;
    }

    /** Override the parent columns method. Defines the columns to use in your listing table */
    public function get_columns()
    {
        $columns = array(
        	'sr_no'        	=> 'No',
            'pd_date'       => 'Program Date',
            'pg_name'       => 'Program Name',
            'pd_status' 	=> 'Status'
        );
        return $columns;
    }

    /** Prepare the items for the table to process */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /** Define what data to show on each column of the table */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'sr_no':
            case 'pg_name':
            case 'pd_date':
            case 'pd_status':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
    
    /** Define which columns are hidden */
    public function get_hidden_columns()
    {
        return array();
    }
    /** Define the sortable columns */
    public function get_sortable_columns()
    {
    	$sortable_columns = array(
    		'sr_no'  => array('sr_no',false),
		    'pg_name'  => array('pg_name',false),
		    'pd_date'  => array('pd_date',false),
		);
        return $sortable_columns;
    }
    
    
    /** Allows you to sort the data by the variables set in the $_GET */
    private function sort_data( $a, $b )
    {
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'sr_no'; //If no sort, default to title
	  	$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
	  	$result = strnatcmp($a[$orderby], $b[$orderby]); //Determine sort order
	  	return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }

    /* display edit - delete action */
    function column_pd_date($item) {
	  $actions = array(
	            'edit'      => sprintf('<a href="?page=%s&action=%s&pd=%s">Edit</a>',$_REQUEST['page'],'edit',$item['pd_id']),
	            'delete'    => sprintf('<a href="javascript:void(0)" class="delete-programdates" pd_id = "'.$item['pd_id'].'">Delete</a>'),
	        );

	  return sprintf('%1$s %2$s', $item['pd_date'], $this->row_actions($actions) );
	}
}

/** Create a new program bookings table class that will extend the WP_List_Table */
class Programbookings_List_Table extends WP_List_Table
{
	/** Get the table data */
    private function table_data()
    {
        $data = array();
		global $wpdb;
		$programbookingsresults = $wpdb->get_results( 'SELECT pb.*, pg.pg_name FROM wp_programbookings as pb LEFT JOIN wp_programs as pg ON (pg.pg_id = pb.pg_id) ORDER BY pb.pb_id DESC');
		$count = 1;
		foreach($programbookingsresults as $programbookingdata){

    		$data[] = array(
    			'sr_no'   			=> $count,
    			'pb_id'   			=> $programbookingdata->pb_id,
    			'pb_username'   	=> $programbookingdata->pb_username,
    			'pg_name'   		=> $programbookingdata->pg_name,
    			'pb_from_to_date'   => $programbookingdata->pb_from_to_date,
    			'pb_program_fees'  	=> $programbookingdata->pb_program_fees,
    			'pb_donation_amount'=> $programbookingdata->pb_donation_amount,
    			'pb_transaction_id'	=> $programbookingdata->pb_transaction_id,
            );
       		$count++;
       	}
        
        return $data;
    }

    /** Override the parent columns method. Defines the columns to use in your listing table */
    public function get_columns()
    {
        $columns = array(
        	'sr_no'        		=> 'No',
            'pb_username'       => 'Name',
            'pg_name'       	=> 'Program Name',
            'pb_from_to_date' 	=> 'Duration',
            'pb_program_fees' 	=> 'Course Fee',
            'pb_donation_amount'=> 'Total Fee',
            'pb_transaction_id'	=> 'Transaction Id',
        );
        return $columns;
    }

    /** Prepare the items for the table to process */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /** Define what data to show on each column of the table */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'sr_no':
            case 'pb_username':
            case 'pg_name':
            case 'pb_from_to_date':
            case 'pb_program_fees':
            case 'pb_donation_amount':
            case 'pb_transaction_id':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
    
    /** Define which columns are hidden */
    public function get_hidden_columns()
    {
        return array();
    }
    /** Define the sortable columns */
    public function get_sortable_columns()
    {
    	$sortable_columns = array(
    		'sr_no'  => array('sr_no',false),
		    'pb_username'  => array('pb_username',false),
		    'pg_name'  => array('pg_name',false),
		);
        return $sortable_columns;
    }
    
    
    /** Allows you to sort the data by the variables set in the $_GET */
    private function sort_data( $a, $b )
    {
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'sr_no'; //If no sort, default to title
	  	$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
	  	$result = strnatcmp($a[$orderby], $b[$orderby]); //Determine sort order
	  	return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }

    /* display edit - delete action */
    function column_pb_username($item) {
	  $actions = array(
	            'view'      => sprintf('<a href="?page=%s&action=%s&pb=%s">View Details</a>',$_REQUEST['page'],'view',$item['pb_id']),
	        );

	  return sprintf('%1$s %2$s', $item['pb_username'], $this->row_actions($actions) );
	}
}

/** Create a new Accommodations_List_Table class that will extend the WP_List_Table */
class Accommodations_List_Table extends WP_List_Table
{
	/** Get the table data */
    private function table_data()
    {
        $data = array();
		global $wpdb;
		$accommodationsresults = $wpdb->get_results( 'SELECT * FROM wp_accommodations');
		$count = 1;
		foreach($accommodationsresults as $accommodationdata){

			if($accommodationdata->ac_status == '1')
				$Status = 'Active';
			else
				$Status = 'Inactive';

    		$data[] = array(
    			'sr_no'   			=> $count,
    			'ac_id'   			=> $accommodationdata->ac_id,
    			'ac_name'   		=> $accommodationdata->ac_name,
    			'ac_price'   		=> $accommodationdata->ac_price,
                'ac_status' 		=> $Status
            );
       		$count++;
       	}
        
        return $data;
    }

    /** Override the parent columns method. Defines the columns to use in your listing table */
    public function get_columns()
    {
        $columns = array(
        	'sr_no'        	=> 'No',
            'ac_name'       => 'Name',
            'ac_price'       => 'Price',
            'ac_status' 	=> 'Status'
        );
        return $columns;
    }

    /** Prepare the items for the table to process */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /** Define what data to show on each column of the table */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'sr_no':
            case 'ac_name':
            case 'ac_price':
            case 'ac_status':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
    
    /** Define which columns are hidden */
    public function get_hidden_columns()
    {
        return array();
    }
    /** Define the sortable columns */
    public function get_sortable_columns()
    {
    	$sortable_columns = array(
    		'sr_no'  => array('sr_no',false),
		    'ac_name'  => array('ac_name',false),
		    'ac_price'  => array('ac_price',false),
		);
        return $sortable_columns;
    }
    
    
    /** Allows you to sort the data by the variables set in the $_GET */
    private function sort_data( $a, $b )
    {
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'sr_no'; //If no sort, default to title
	  	$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
	  	$result = strnatcmp($a[$orderby], $b[$orderby]); //Determine sort order
	  	return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }

    /* display edit - delete action */
    function column_ac_name($item) {
	  $actions = array(
	            'edit'      => sprintf('<a href="?page=%s&action=%s&ac=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ac_id']),
	            'delete'    => sprintf('<a href="javascript:void(0)" class="delete-accommodations" ac_id = "'.$item['ac_id'].'">Delete</a>'),
	        );

	  return sprintf('%1$s %2$s', $item['ac_name'], $this->row_actions($actions) );
	}
}

//action for delete program
add_action( 'wp_ajax_program_delete', 'program_delete' );
add_action( 'wp_ajax_nopriv_program_delete', 'program_delete' );
function program_delete(){
	global $wpdb;
	$pg_id = $_POST['pg_id'];
	$sql = "DELETE FROM wp_programs WHERE pg_id='".$pg_id."'";
	$results = $wpdb->query($sql);
	if($results == true){
		echo 'success';
	}
	else
	{
		echo 'fail';
	}
	exit();die();
}

//action for delete programweeks
add_action( 'wp_ajax_programweeks_delete', 'programweeks_delete' );
add_action( 'wp_ajax_nopriv_programweeks_delete', 'programweeks_delete' );
function programweeks_delete(){
	global $wpdb;
	$pw_id = $_POST['pw_id'];
	$sql = "DELETE FROM wp_programweeks WHERE pw_id='".$pw_id."'";
	$results = $wpdb->query($sql);
	if($results == true){
		echo 'success';
	}
	else
	{
		echo 'fail';
	}
	exit();die();
}

//action for delete programdates
add_action( 'wp_ajax_programdates_delete', 'programdates_delete' );
add_action( 'wp_ajax_nopriv_programdates_delete', 'programdates_delete' );
function programdates_delete(){
	global $wpdb;
	$pd_id = $_POST['pd_id'];
	$sql = "DELETE FROM wp_programdates WHERE pd_id='".$pd_id."'";
	$results = $wpdb->query($sql);
	if($results == true){
		echo 'success';
	}
	else
	{
		echo 'fail';
	}
	exit();die();
}

//action for delete programdates
add_action( 'wp_ajax_accommodations_delete', 'accommodations_delete' );
add_action( 'wp_ajax_nopriv_accommodations_delete', 'accommodations_delete' );
function accommodations_delete(){
	global $wpdb;
	$ac_id = $_POST['ac_id'];
	$sql = "DELETE FROM wp_accommodations WHERE ac_id='".$ac_id."'";
	$results = $wpdb->query($sql);
	if($results == true){
		echo 'success';
	}
	else
	{
		echo 'fail';
	}
	exit();die();
}



//action for add/update program
add_action( 'wp_ajax_addupdate_program_data', 'addupdate_program_data' );
add_action( 'wp_ajax_nopriv_addupdate_program_data', 'addupdate_program_data' );
function addupdate_program_data(){
	global $wpdb;
	$pg_id = $_POST['pg_id'];
	$pg_name = $_POST['pg_name'];
	$pg_status = $_POST['pg_status'];
	$date = date('Y-m-d H:i:s');

	$pgresult = $wpdb->query("SELECT * FROM `wp_programs` WHERE `pg_name`= '".$pg_name."' AND `pg_id` != '".$pg_id."'");
	
	if($pgresult > '0')
	{
		echo 'duplicate';
		exit();die();
	}
	if($pg_id == '')
	{
		$sql = "INSERT INTO `wp_programs` ( `pg_name`, `pg_status`, `pg_created_date`, `pg_updated_date`) VALUES ('".$pg_name."', '".$pg_status."','".$date."', NULL);";
	}
	else
	{
		$sql = "UPDATE `wp_programs` SET `pg_name` = '".$pg_name."', `pg_status` = '".$pg_status."', `pg_updated_date` = '".$date."' WHERE `wp_programs`.`pg_id` = '".$pg_id."' ";
	}
	$results = $wpdb->query($sql);
	if($results == true){
		echo 'success';
	}
	else
	{
		echo 'fail';
	}
	exit();die();
}

//action for add/update program
add_action( 'wp_ajax_addupdate_programweeks_data', 'addupdate_programweeks_data' );
add_action( 'wp_ajax_nopriv_addupdate_programweeks_data', 'addupdate_programweeks_data' );
function addupdate_programweeks_data(){
	global $wpdb;
	$pw_id = $_POST['pw_id'];
	$pg_id = $_POST['pg_id'];
	$pw_week = $_POST['pw_week'];
	$pw_week_rate = $_POST['pw_week_rate'];
	$pw_tra_stu_rate = $_POST['pw_tra_stu_rate'];
	$pw_status = $_POST['pw_status'];
	$date = date('Y-m-d H:i:s');

	$pwresult = $wpdb->query("SELECT * FROM `wp_programweeks` WHERE `pw_week`= '".$pw_week."' AND `pg_id` = '".$pg_id."' AND `pw_id` != '".$pw_id."'");
	
	if($pwresult > '0')
	{
		echo 'duplicate';
		exit();die();
	}
	if($pw_id == '')
	{
		$sql = "INSERT INTO `wp_programweeks` ( `pg_id`, `pw_week`, `pw_week_rate`, `pw_status`, `pw_created_date`, `pw_updated_date`) VALUES ('".$pg_id."', '".$pw_week."', '".$pw_week_rate."', '".$pw_status."','".$date."', NULL);";
	}
	else
	{
		$sql = "UPDATE `wp_programweeks` SET `pg_id` = '".$pg_id."', `pw_week` = '".$pw_week."', `pw_week_rate` = '".$pw_week_rate."', `pw_status` = '".$pw_status."', `pw_updated_date` = '".$date."' WHERE `wp_programweeks`.`pw_id` = '".$pw_id."' ";
	}
	$results = $wpdb->query($sql);
	if($results == true){
		echo 'success';
	}
	else
	{
		echo 'fail';
	}
	exit();die();
}

//action for add/update program
add_action( 'wp_ajax_addupdate_programdates_data', 'addupdate_programdates_data' );
add_action( 'wp_ajax_nopriv_addupdate_programdates_data', 'addupdate_programdates_data' );
function addupdate_programdates_data(){
	global $wpdb;
	$pd_id = $_POST['pd_id'];
	$pg_id = $_POST['pg_id'];
	$pd_date = date('Y-m-d',strtotime($_POST['pd_date']));
	$pd_status = $_POST['pd_status'];
	$date = date('Y-m-d H:i:s');

	$pdresult = $wpdb->query("SELECT * FROM `wp_programdates` WHERE `pd_date`= '".$pd_date."' AND `pg_id` = '".$pg_id."' AND `pd_id` != '".$pd_id."'");
	
	if($pdresult > '0')
	{
		echo 'duplicate';
		exit();die();
	}
	if($pd_id == '')
	{
		$sql = "INSERT INTO `wp_programdates` ( `pg_id`, `pd_date`, `pd_status`, `pd_created_date`, `pd_updated_date`) VALUES ('".$pg_id."', '".$pd_date."', '".$pd_status."','".$date."', NULL);";
	}
	else
	{
		$sql = "UPDATE `wp_programdates` SET `pg_id` = '".$pg_id."', `pd_date` = '".$pd_date."', `pd_status` = '".$pd_status."', `pd_updated_date` = '".$date."' WHERE `wp_programdates`.`pd_id` = '".$pd_id."' ";
	}
	$results = $wpdb->query($sql);
	if($results == true){
		echo 'success';
	}
	else
	{
		echo 'fail';
	}
	exit();die();
}


//action for add/update accommodations
add_action( 'wp_ajax_addupdate_accommodations_data', 'addupdate_accommodations_data' );
add_action( 'wp_ajax_nopriv_addupdate_accommodations_data', 'addupdate_accommodations_data' );
function addupdate_accommodations_data(){
	global $wpdb;
	$ac_id = $_POST['ac_id'];
	$ac_name = $_POST['ac_name'];
	$ac_address = $_POST['ac_address'];
	$ac_price = $_POST['ac_price'];
	$ac_description = $_POST['ac_description'];
	$ac_status = $_POST['ac_status'];
	$date = date('Y-m-d H:i:s');

	$uploaddir = dirname(__FILE__).'/assests/images/';
	$ac_image = '';

	$acresult = $wpdb->query("SELECT * FROM `wp_accommodations` WHERE `ac_address`= '".$ac_address."' AND `ac_name` = '".$ac_name."' AND `ac_id` != '".$ac_id."'");
	
	if($acresult > '0')
	{
		echo 'duplicate';
		exit();die();
	}
	if($ac_id == '')
	{
		if($_FILES['ac_image']['name'])
		{
			$ac_image = uniqid().'.jpg';
    		$uploadfile = $uploaddir . basename($ac_image);
			move_uploaded_file($_FILES['ac_image']['tmp_name'], $uploadfile);
		}
		$sql = "INSERT INTO `wp_accommodations` ( `ac_name`, `ac_address`, `ac_price`, `ac_description`, `ac_image`, `ac_status`, `ac_created_date`, `ac_updated_date`) VALUES ('".$ac_name."', '".$ac_address."', '".$ac_price."', '".$ac_description."', '".$ac_image."', '".$ac_status."','".$date."', NULL);";
	}
	else
	{
		$sql = "UPDATE `wp_accommodations` SET `ac_name` = '".$ac_name."', `ac_address` = '".$ac_address."', `ac_price` = '".$ac_price."', `ac_description` = '".$ac_description."', `ac_status` = '".$ac_status."', `ac_updated_date` = '".$date."' ";

		if($_FILES['ac_image']['name'])
		{
			$accommodationdetails = $wpdb->get_results( 'SELECT * FROM wp_accommodations WHERE ac_id='.$ac_id);
			if($accommodationdetails[0]->ac_image)
			{
				$old_file = $uploaddir.$accommodationdetails[0]->ac_image;
				unlink($old_file);
			}
			$ac_image = uniqid().'.jpg';
			$uploadfile = $uploaddir . basename($ac_image);
			move_uploaded_file($_FILES['ac_image']['tmp_name'], $uploadfile);
			$sql .= ", `ac_image` = '".$ac_image."' ";
		}
		$sql .= " WHERE `wp_accommodations`.`ac_id` = '".$ac_id."' ";
	}
	$results = $wpdb->query($sql);
	if($results == true){
		echo 'success';
	}
	else
	{
		echo 'fail';
	}
	exit();die();
}

//action for add/update program
add_action( 'wp_ajax_update_paymentaccount_data', 'update_paymentaccount_data' );
add_action( 'wp_ajax_nopriv_update_paymentaccount_data', 'update_paymentaccount_data' );
function update_paymentaccount_data(){
	global $wpdb;
	$pa_login_id = $_POST['pa_login_id'];
	$pa_transcation_key = $_POST['pa_transcation_key'];
	$pa_description = $_POST['pa_description'];
	$pa_mode = $_POST['pa_mode'];
	$date = date('Y-m-d H:i:s');

	$sql = "UPDATE `wp_paymentaccount` SET `pa_login_id` = '".$pa_login_id."', `pa_transcation_key` = '".$pa_transcation_key."', `pa_description` = '".$pa_description."', `pa_mode` = '".$pa_mode."', `pa_updated_date` = '".$date."' WHERE `wp_paymentaccount`.`pa_id` = 1 ";

	$results = $wpdb->query($sql);
	if($results == true){
		echo 'success';
	}
	else
	{
		echo 'fail';
	}
	exit();die();
}

//action for add/update price settings
add_action( 'wp_ajax_update_pricesettings_data', 'update_pricesettings_data' );
add_action( 'wp_ajax_nopriv_update_pricesettings_data', 'update_pricesettings_data' );
function update_pricesettings_data(){
	global $wpdb;
	$ps_admission_fee = $_POST['ps_admission_fee'];
	$ps_accommodation_fee = $_POST['ps_accommodation_fee'];
	$date = date('Y-m-d H:i:s');

	$sql = "UPDATE `wp_pricesettings` SET `ps_admission_fee` = '".$ps_admission_fee."', `ps_accommodation_fee` = '".$ps_accommodation_fee."', `ps_updated_date` = '".$date."' WHERE `wp_pricesettings`.`ps_id` = 1 ";

	$results = $wpdb->query($sql);
	if($results == true){
		echo 'success';
	}
	else
	{
		echo 'fail';
	}
	exit();die();
}


?>