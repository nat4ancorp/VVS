<?php
/* add configuration files */
require("conf/props.php");

/* create a new instance of the properties class */
$properties=new properties();

/* connect to the database via including the config connect file */
include("conf/connect.php");

if(isset($_GET['action'])){
	/* DEPENDS ON ACTION */
	switch($_GET['action']){
		case 'del':
		/* DELETING */
		//get important variables
		$entry_id	= $_GET['entry_id'];
	
		$QUERY=mysql_query("DELETE FROM {$properties->DB_PREFIX}entries WHERE id='".$entry_id."'") or die(mysql_error());
		
		if(!$QUERY){
			echo "Something went wrong! Error: ".mysql_error($QUERY);
		} else {
			echo "The Entry #".$entry_id." has been deleted.";	
		}
		break;
	}
} else {
	/* EDITING */
	//get important variables
	$what		= $_GET['what'];
	$entry_id	= $_GET['entry_id'];
	$value		= $_GET['value'];
	
	$value=mysql_real_escape_string($value);
	
	if($value==""){$value="";}
	
	$QUERY=mysql_query("UPDATE {$properties->DB_PREFIX}entries SET ".$what."='".$value."' WHERE id='".$entry_id."'") or die(mysql_error());
	
	if(!$QUERY){
		echo "Something went wrong! Error: ".mysql_error($QUERY);
	} else {
		echo "The Entry #".$entry_id." has been updated with the ".$what.": ".$value;	
	}	
}
?>