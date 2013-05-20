<?php
//error_reporting(0);

/* add configuration files */
require("conf/props.php");

/* create a new instance of the properties class */
$properties=new properties();

/* connect to the database via including the config connect file */
include("conf/connect.php");

//get stuff
@$saved_name=$_POST['saved_name'];
@$saved_type=$_POST['saved_type'];
@$saved_originator=$_POST['saved_originator'];
@$saved_story=mysql_real_escape_string($_POST['saved_story']);

@$target_path = "uploads/";
@$target_path = $target_path . basename( $_FILES['uploadedfile']['name']); 
if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
    echo "The file ".  basename( $_FILES['uploadedfile']['name']). 
    " has been uploaded. <a href=\"index.php?page=admin\">I'm done here, go back to Admin Panel</a>";
	@$saved_source=basename($_FILES['uploadedfile']['name']);
	
	mysql_query("INSERT INTO {$properties->DB_PREFIX}entries (name,source,originator,type,story) VALUES ('".$saved_name."','".$saved_source."','".$saved_originator."','".$saved_type."','".$saved_story."')") or die(mysql_error());	
} else{
    echo "There was an error uploading the file, please try again!";
}
?>