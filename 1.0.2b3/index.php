<!DOCTYPE html>
<html>
<head>
<meta charset=utf-8 />

<title>Video Voting</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script type="text/javascript" src="js/jQuery/jquery.js"></script>
<script type="text/javascript" src="js/jQuery/jquery-ui.js"></script>
<script type="text/javascript" src="js/JWPlayer/embed/swfobject.js"></script>
<script type='text/javascript' src='js/JWPlayer/jwplayer.js'></script>
<script type="text/javascript" src="js/jRating/jRating.jquery.js"></script>
<script type="text/javascript" src="js/jRating/jNotify.jquery.js"></script>
<script type="text/javascript" src="js/general.js"></script>

<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
<link rel="stylesheet" href="css/jRating/jRating.jquery.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/jRating/jNotify.jquery.css" type="text/css" />
<link rel="stylesheet" href="css/general.css" />
<?php
/* add configuration files */
require("conf/props.php");

/* create a new instance of the properties class */
$properties=new properties();

/* Determine OrderBy */
if($properties->ORDER_BY=="random"){$ORDERBY="rand()";}
if($properties->ORDER_BY=="bytype"){$ORDERBY="type";}
if($properties->ORDER_BY=="byname"){$ORDERBY="name";}
if($properties->ORDER_BY=="byid"){$ORDERBY="id";}

/* assign the JWPlayer Premium if you have one; NOTE: Don't place actual key here, place in conf/props.php file */
?><script type="text/javascript">jwplayer.key="<?php echo $properties->JW_LICENSE;?>";</script><?php

/* connect to the database via including the config connect file */
include("conf/connect.php");

/* get the user's IP */
//$ip=$_SERVER['REMOTE_ADDR'];

/* get the user's real IP */
function get_ip_address() {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    return $ip;
                }
            }
        }
    }
}
$ip=get_ip_address();

/* create a variable - session by taking the IP and randomizing a 14 digit shuffled number */
$session=str_shuffle($ip.rand("000000000000000","999999999999999"));

/* PUT HARMLESS SESSION COOKIE FOR LOGGING TO EXPIRE IN 20 YEARS */
/* STEP 1: FIRST FIND EXISTING COOKIE IN DATABASE */

/* STEP 1.5: FIND MAC ADDRESS */
/*ob_start(); // Turn on output buffering
system('ipconfig /all'); //Execute external program to display output
$mycom=ob_get_contents(); // Capture the output into a variable
ob_clean(); // Clean (erase) the output buffer

$findme = "Physical";
$pmac = strpos($mycom, $findme); // Find the position of Physical text
$mac=substr($mycom,($pmac+36),17); // Get Physical Address*/

if(isset($_COOKIE['vv_session'])){$session_stored=$_COOKIE['vv_session'];}else{$session_stored="";}
$FIND_SESSION=mysql_query("SELECT * FROM {$properties->DB_PREFIX}who WHERE ip='$ip'");
if(mysql_num_rows($FIND_SESSION)<1){
	/* NO SESSION; CLEAR STRAGLERS (UNSET SESSION) */
	setcookie("vv_session","",time()-(20 * 365 * 24 * 60 * 60));
	/* STEP 2: SET NEW COOKIE */
	setcookie("vv_session",$session,time()+(20 * 365 * 24 * 60 * 60));
	/* STEP 3: INSERT LOGGED INFO */
	mysql_query("INSERT INTO {$properties->DB_PREFIX}who (ip,session,has_voted_times) VALUES ('$ip','$session','0')") or die('Error: '.mysql_error());	
} else {
	/* SESSION FOUND; USE IT AND DONT SET NEW ONE */
	if($session_stored==""){
		/* USER CLEARED COOKIES TO TRY AND RE VOTE; SET COOKIE */
		while($FETCH_SESSION=mysql_fetch_array($FIND_SESSION)){
			$session=$FETCH_SESSION['session'];
		}
		setcookie("vv_session",$session,time()+(20 * 365 * 24 * 60 * 60));
		header("Refresh: 0;");
	} else {
		/* IT'S ALL GOOD; USER COOKIE IS STILL THERE */
	}
}
?>
</head>
<body>
<center>
<?php
if(isset($_GET['page'])){
	switch($_GET['page']){
		case 'admin':
			/* DETECT LOGIN */
			if(isset($_COOKIE['vv_admin_session'])){
				/* LOGGED SESSION */
				
				/* GET THE DETAILS */
				$GET_USER_DETAILS=mysql_query("SELECT * FROM {$properties->DB_PREFIX}admins WHERE logged_in='yes' AND logged_ip='$ip' AND logged_session='".$_COOKIE['vv_admin_session']."'");
				if(mysql_num_rows($GET_USER_DETAILS)<1){
					/* COOKIE AND IP DOES NOT MATCH IN DB; USER MAY HAVE TAMPERED WITH COOKIE */
					$logged_in="no";
				} else {
					/* USER FOUND BY COOKIE AND IP; CHECK FOR status */
					while($FETCH_USER_DETAILS=mysql_fetch_array($GET_USER_DETAILS)){
						$status=$FETCH_USER_DETAILS['status'];
						$username_logged=$FETCH_USER_DETAILS['uname'];
					}
					switch($status){
						case 'active':
							/* USER IS ACTIVE */
							$logged_in="yes";
						break;
						
						case 'suspended':
							/* USER IS SUSPENDED */
							$logged_in="no";
						break;
						
						case 'deleted':
							/* USER DOES NOT EXIST */
							$logged_in="no";
						break;
						
						case 'pending':
							/* USER IS PENDING */
							$logged_in="no";
						break;
					}
					
				}
			} else {
				/* NO SESSION LOGGED; DISPLAY LOGIN */
				$logged_in="no";	
			}

			if($logged_in == "yes"){
				if(isset($_POST['logout'])){
					$username=$_POST['username_logged'];
					/* USER REQUEST TO LOG OUT */
					/* DELETE COOKIE */
					$adminsession=$_COOKIE['vv_admin_session'];
					setcookie("vv_admin_session",$adminsession,time()-(20 * 365 * 24 * 60 * 60));
							
					/* STEP 4: POST UPDATE */
					mysql_query("UPDATE {$properties->DB_PREFIX}admins SET logged_in='no' WHERE uname='$username'");
					mysql_query("UPDATE {$properties->DB_PREFIX}admins SET logged_session='' WHERE uname='$username'");
					mysql_query("UPDATE {$properties->DB_PREFIX}admins SET logged_ip='' WHERE uname='$username'");
					
					/* STEP 5: POST MESSAGE */
					?>
                    <div class="admin-response-success">You have successfully been logged out.<br /><a href="<?php echo $properties->WEB_URL?>?page=admin">Go home</a></div>
                    <?php
				} else {
					?>
					<h1><a href="<?php echo $properties->WEB_URL;?>" style="text-decoration:none;"> < </a> <a href="<?php echo $properties->WEB_URL;?>?page=admin"><?php echo $properties->TITLE;?>'s Administration Panel</a></h1>
					<form method="post" action="">
                    <input type="hidden" name="username_logged" value="<?php echo $username_logged;?>" />
						<input type="submit" name="logout" value="Logout">
					</form>
                    <br />
                    <div class="admin-table no-border">
                        <div class="admin-tablerow">
                            <div class="admin-tablerow1coltop">
                                Name
                            </div>
                            <div class="admin-tablerow2coltop">
                                Source
                            </div>
                            <div class="admin-tablerow3coltop">
                                Type
                            </div>
                            <div class="admin-tablerow4coltop">
                                Story
                            </div>
                            <div class="admin-tablerow5coltop">
                                Votes
                            </div>
                            <div class="admin-tablerow6coltop">
                                Rate
                            </div>
                            <div class="admin-tablerow7coltop">
                                Status
                            </div>
                        </div>
                    </div>
					<h2>Active Video Entries</h2>
						<?php
						if(isset($_POST['_changer'])){
							
						} else {
							/* find and list all entries */
							$FIND_ALL_ENTRIES2=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries WHERE status='active' ORDER BY name");
							if(mysql_num_rows($FIND_ALL_ENTRIES2)<1){
								?>
								No entries found...
								<?php
							} else {
								?>
								<div class="admin-table">
								<?php
								while($FETCH_ALL_ENTRIES2=mysql_fetch_array($FIND_ALL_ENTRIES2)){
									?>
									<div class="admin-tablerow">
										<div class="admin-tablerow1col">
											<input type="hidden" id="placeholder-currentName-<?php echo $FETCH_ALL_ENTRIES2['id'];?>" value="<?php echo $FETCH_ALL_ENTRIES2['name'];?>" />
											<div class="placeholder-link" id="placeholder-name-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
												<?php if(strlen($FETCH_ALL_ENTRIES2['name'])>25){echo "<a onclick=\"edit(".$FETCH_ALL_ENTRIES2['id'].",'name','focus')\" title=\"".$FETCH_ALL_ENTRIES2['name']."\">".substr($FETCH_ALL_ENTRIES2['name'],0,25)."...</a>";}else{echo "<a onclick=\"edit(".$FETCH_ALL_ENTRIES2['id'].",'name','focus')\">".$FETCH_ALL_ENTRIES2['name']."</a>";}?>
											</div>							
										</div>
										<div class="admin-tablerow2col">
											<input type="hidden" id="placeholder-currentSource-<?php echo $FETCH_ALL_ENTRIES2['id'];?>" value="<?php echo $FETCH_ALL_ENTRIES2['source'];?>" />
											<div class="placeholder-link" id="placeholder-source-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
												<?php if(strlen($FETCH_ALL_ENTRIES2['source'])>29){echo "<a onclick=\"edit(".$FETCH_ALL_ENTRIES2['id'].",'source','focus')\" title=\"".$FETCH_ALL_ENTRIES2['source']."\">".substr($FETCH_ALL_ENTRIES2['source'],0,29)."...</a>";}else{echo "<a onclick=\"edit(".$FETCH_ALL_ENTRIES2['id'].",'source','focus')\">".$FETCH_ALL_ENTRIES2['source']."</a>";}?>
											</div>
										</div>
										<div class="admin-tablerow3col">
											<input type="hidden" id="placeholder-currentType-<?php echo $FETCH_ALL_ENTRIES2['id'];?>" value="<?php echo $FETCH_ALL_ENTRIES2['type'];?>" />
											<div class="placeholder-link" id="placeholder-type-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
												<?php if(strlen($FETCH_ALL_ENTRIES2['type'])>29){echo "<a onclick=\"edit(".$FETCH_ALL_ENTRIES2['id'].",'type','focus')\" title=\"".$FETCH_ALL_ENTRIES2['type']."\">".substr($FETCH_ALL_ENTRIES2['type'],0,29)."...</a>";}else{echo "<a onclick=\"edit(".$FETCH_ALL_ENTRIES2['id'].",'type','focus')\">".$FETCH_ALL_ENTRIES2['type']."</a>";}?>
											</div>
										</div>
                                        <div class="admin-tablerow4col">
											<input type="hidden" id="placeholder-currentStory-<?php echo $FETCH_ALL_ENTRIES2['id'];?>" value="<?php echo $FETCH_ALL_ENTRIES2['story'];?>" />
											<div class="placeholder-link" id="placeholder-story-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
												<?php if($FETCH_ALL_ENTRIES2['story']==""){/* BLANK STORY; MAKE LINK TO EDIT SINCE THERE IS NOTHING TO SELECT */echo "[<a onclick=\"edit(".$FETCH_ALL_ENTRIES2['id'].",'story','focus')\" style=\"cursor:pointer;\">Add a Story</a>]";}else{if(strlen($FETCH_ALL_ENTRIES2['story'])>20){echo "<a onclick=\"edit(".$FETCH_ALL_ENTRIES2['id'].",'story','focus')\" title=\"".$FETCH_ALL_ENTRIES2['story']."\">".substr($FETCH_ALL_ENTRIES2['story'],0,20)."...</a>";}else{echo "<a onclick=\"edit(".$FETCH_ALL_ENTRIES2['id'].",'story','focus')\">".$FETCH_ALL_ENTRIES2['story']."</a>";}}?>
											</div>
										</div>
										<div class="admin-tablerow5col">
											<?php echo $FETCH_ALL_ENTRIES2['totalvotes'];?>
										</div>
										<div class="admin-tablerow6col">
											<?php echo $FETCH_ALL_ENTRIES2['rate'];?>
										</div>
										<div class="admin-tablerow6col">
											<select name="_changer" onChange="save('status','<?php echo $FETCH_ALL_ENTRIES2['id'];?>',this.value)">
												<?php
												if($FETCH_ALL_ENTRIES2['status']=="active"){?><option value="active" selected="selected" disabled="disabled">Active</option><?php }else{?><option value="active">Active</option><?php }
												
												if($FETCH_ALL_ENTRIES2['status']=="inactive"){?><option value="inactive" selected="selected" disabled="disabled">Inactive</option><?php }else{?><option value="inactive">Inactive</option><?php }
												
												if($FETCH_ALL_ENTRIES2['status']=="deleted"){?><option value="deleted" selected="selected" disabled="disabled">Deleted</option><?php }else{?><option value="deleted">Deleted</option><?php }
												
												?>
											</select>
										</div>
									</div>
									<?php
								}
							}	
						}
						?>
					</div>
	
					<h2>Inactive Video Entries</h2>
						<?php
						/* find and list all entries */
						$FIND_ALL_ENTRIES2=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries WHERE status='inactive' ORDER BY name");
						if(@mysql_num_rows($FIND_ALL_ENTRIES2)<1){
							?>
							No entries found...
							<?php
						} else {
							?>
							<div class="admin-table">
							<?php
							while($FETCH_ALL_ENTRIES2=mysql_fetch_array($FIND_ALL_ENTRIES2)){
								?>
								<div class="admin-tablerow" id="draggable-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
									<div class="admin-tablerow1col">
										<input type="hidden" id="placeholder-currentName-<?php echo $FETCH_ALL_ENTRIES2['id'];?>" value="<?php echo $FETCH_ALL_ENTRIES2['name'];?>" />
										<div class="placeholder-link" id="placeholder-name-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
											<?php if(strlen($FETCH_ALL_ENTRIES2['name'])>25){echo "<a title=\"".$FETCH_ALL_ENTRIES2['name']."\">".substr($FETCH_ALL_ENTRIES2['name'],0,25)."...</a>";}else{echo $FETCH_ALL_ENTRIES2['name'];}?>
										</div>							
									</div>
									<div class="admin-tablerow2col">
										<input type="hidden" id="placeholder-currentSource-<?php echo $FETCH_ALL_ENTRIES2['id'];?>" value="<?php echo $FETCH_ALL_ENTRIES2['source'];?>" />
										<div class="placeholder-link" id="placeholder-source-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
											<?php if(strlen($FETCH_ALL_ENTRIES2['source'])>29){echo "<a title=\"".$FETCH_ALL_ENTRIES2['source']."\">".substr($FETCH_ALL_ENTRIES2['source'],0,29)."...</a>";}else{echo $FETCH_ALL_ENTRIES2['source'];}?>
										</div>
									</div>
									<div class="admin-tablerow3col">
										<input type="hidden" id="placeholder-currentType-<?php echo $FETCH_ALL_ENTRIES2['id'];?>" value="<?php echo $FETCH_ALL_ENTRIES2['type'];?>" />
										<div class="placeholder-link" id="placeholder-type-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
											<?php if(strlen($FETCH_ALL_ENTRIES2['type'])>29){echo "<a title=\"".$FETCH_ALL_ENTRIES2['type']."\">".substr($FETCH_ALL_ENTRIES2['type'],0,29)."...</a>";}else{echo $FETCH_ALL_ENTRIES2['type'];}?>
										</div>
									</div>
                                    <div class="admin-tablerow4col">
											<input type="hidden" id="placeholder-currentStory-<?php echo $FETCH_ALL_ENTRIES2['id'];?>" value="<?php echo $FETCH_ALL_ENTRIES2['story'];?>" />
											<div class="placeholder-link" id="placeholder-story-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
												<?php if(strlen($FETCH_ALL_ENTRIES2['story'])>20){echo "<a title=\"".$FETCH_ALL_ENTRIES2['story']."\">".substr($FETCH_ALL_ENTRIES2['story'],0,20)."...</a>";}else{echo $FETCH_ALL_ENTRIES2['story'];}?>
											</div>
										</div>
									<div class="admin-tablerow4col">
										<?php echo $FETCH_ALL_ENTRIES2['totalvotes'];?>
									</div>
									<div class="admin-tablerow5col">
										<?php echo $FETCH_ALL_ENTRIES2['rate'];?>
									</div>
                                    <div class="admin-tablerow6col">
										<select name="_changer" onChange="save('status','<?php echo $FETCH_ALL_ENTRIES2['id'];?>',this.value)">
                                        	<?php
											if($FETCH_ALL_ENTRIES2['status']=="active"){?><option value="active" selected="selected" disabled="disabled">Active</option><?php }else{?><option value="active">Active</option><?php }
											
											if($FETCH_ALL_ENTRIES2['status']=="inactive"){?><option value="inactive" selected="selected" disabled="disabled">Inactive</option><?php }else{?><option value="inactive">Inactive</option><?php }
											
											if($FETCH_ALL_ENTRIES2['status']=="deleted"){?><option value="deleted" selected="selected" disabled="disabled">Deleted</option><?php }else{?><option value="deleted">Deleted</option><?php }
											
											?>
                                        </select>
									</div>
								</div>
								<?php
							}
						}
						?>
					</div>
	
					<h2>Deleted Video Entries</h2>
						<?php
						/* find and list all entries */
						$FIND_ALL_ENTRIES2=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries WHERE status='deleted' ORDER BY name");
						if(@mysql_num_rows($FIND_ALL_ENTRIES2)<1){
							?>				
							No entries found...
							<?php
						} else {
							?>
							<div class="admin-table">
							<?php
							while($FETCH_ALL_ENTRIES2=mysql_fetch_array($FIND_ALL_ENTRIES2)){
								?>
								<div class="admin-tablerow">
									<div class="admin-tablerow1col">
                                    	[<a onClick="del('<?php echo $FETCH_ALL_ENTRIES2['id'];?>')" style="cursor:pointer;">delete</a>]
										<input type="hidden" id="placeholder-currentName-<?php echo $FETCH_ALL_ENTRIES2['id'];?>" value="<?php echo $FETCH_ALL_ENTRIES2['name'];?>" />
										<div class="placeholder-link" id="placeholder-name-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
											<?php if(strlen($FETCH_ALL_ENTRIES2['name'])>25){echo "<a title=\"".$FETCH_ALL_ENTRIES2['name']."\">".substr($FETCH_ALL_ENTRIES2['name'],0,25)."...</a>";}else{echo $FETCH_ALL_ENTRIES2['name'];}?>
										</div>							
									</div>
									<div class="admin-tablerow2col">
										<input type="hidden" id="placeholder-currentSource-<?php echo $FETCH_ALL_ENTRIES2['id'];?>" value="<?php echo $FETCH_ALL_ENTRIES2['source'];?>" />
										<div class="placeholder-link" id="placeholder-source-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
											<?php if(strlen($FETCH_ALL_ENTRIES2['source'])>29){echo "<a title=\"".$FETCH_ALL_ENTRIES2['source']."\">".substr($FETCH_ALL_ENTRIES2['source'],0,29)."...</a>";}else{echo $FETCH_ALL_ENTRIES2['source'];}?>
										</div>
									</div>
									<div class="admin-tablerow3col">
										<input type="hidden" id="placeholder-currentType-<?php echo $FETCH_ALL_ENTRIES2['id'];?>" value="<?php echo $FETCH_ALL_ENTRIES2['type'];?>" />
										<div class="placeholder-link" id="placeholder-type-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
											<?php if(strlen($FETCH_ALL_ENTRIES2['type'])>29){echo "<a title=\"".$FETCH_ALL_ENTRIES2['type']."\">".substr($FETCH_ALL_ENTRIES2['type'],0,29)."...</a>";}else{echo $FETCH_ALL_ENTRIES2['type'];}?>
										</div>
									</div>
                                    <div class="admin-tablerow4col">
											<input type="hidden" id="placeholder-currentStory-<?php echo $FETCH_ALL_ENTRIES2['id'];?>" value="<?php echo $FETCH_ALL_ENTRIES2['story'];?>" />
											<div class="placeholder-link" id="placeholder-story-<?php echo $FETCH_ALL_ENTRIES2['id'];?>">
												<?php if(strlen($FETCH_ALL_ENTRIES2['story'])>20){echo "<a title=\"".$FETCH_ALL_ENTRIES2['story']."\">".substr($FETCH_ALL_ENTRIES2['story'],0,20)."...</a>";}else{echo $FETCH_ALL_ENTRIES2['story'];}?>
											</div>
										</div>
									<div class="admin-tablerow4col">
										<?php echo $FETCH_ALL_ENTRIES2['totalvotes'];?>
									</div>
									<div class="admin-tablerow5col">
										<?php echo $FETCH_ALL_ENTRIES2['rate'];?>
									</div>
                                    <div class="admin-tablerow6col">
										<select name="_changer" onChange="save('status','<?php echo $FETCH_ALL_ENTRIES2['id'];?>',this.value)">
                                        	<?php
											if($FETCH_ALL_ENTRIES2['status']=="active"){?><option value="active" selected="selected" disabled="disabled">Active</option><?php }else{?><option value="active">Active</option><?php }
											
											if($FETCH_ALL_ENTRIES2['status']=="inactive"){?><option value="inactive" selected="selected" disabled="disabled">Inactive</option><?php }else{?><option value="inactive">Inactive</option><?php }
											
											if($FETCH_ALL_ENTRIES2['status']=="deleted"){?><option value="deleted" selected="selected" disabled="disabled">Deleted</option><?php }else{?><option value="deleted">Deleted</option><?php }
											
											?>
                                        </select>
									</div>
								</div>
								<?php
							}
						}
						?>
					</div>
                    
                    <h2>Add an entry</h2>
                    <?php
					if(isset($_POST['entry_save'])){
						/* SAVING ENTRY */
						$error_console="";
						
						/* STEP 1: GET DATA */
						$entry_name=$_POST['entry_name'];
						$entry_type=$_POST['entry_type'];
						$entry_source=$_POST['entry_source'];
						$entry_originator=$_POST['entry_originator'];
						$entry_story=$_POST['entry_story'];
						
						/* STEP 2: CHECK FOR ACCURACY */
						if($entry_name==""){$error_console.="You must provide a creative name.<br />";}
						if($entry_type==""){$error_console.="You must tell me what type of video this is.<br />";}
						if($entry_source=="" && $entry_type != "upload"){$error_console.="You must provide a source for the video.<br />";}
						if($entry_originator==""){$error_console.="You must tell me who this video came from.<br />";}
						//if($entry_story==""){$error_console.="You must tell me the story behind this entry.<br />";}
						
						if($error_console != ""){
							/* ERRORS AMONG US! */
							echo "<div class=\"admin-response-error\">".$error_console."</div><div class=\"admin-response-tools\"><a onclick=\"history.go(-1)\" style=\"cursor:pointer;\">Go Back</a></div>";
						} else {
							/* CHECK TO SEE IF THEY WANT TO UPLOAD A FILE */
							if($entry_type == "upload" || $entry_type == "image"){
								/* UPLOAD TYPE AND DISPLAY UPLOAD FORM */
								?>
                                <form enctype="multipart/form-data" action="upload.php" method="POST" >
                                	<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
                                    <div class="formLayoutMid">
                                        <div class="formLayoutMidRow">
                                            <div class="formLayoutMidRowLeftCol">
                                                 <label>File to Upload</label>
                                            </div>
                                            <div class="formLayoutMidRowRightCol">
                                            	<?php
												$entry_name=$_POST['entry_name'];
												$entry_type=$_POST['entry_type'];
												$entry_originator=$_POST['entry_originator'];
												$entry_story=mysql_real_escape_string($_POST['entry_story']);
												?>
                                            	<input type="hidden" name="saved_name" value="<?php echo $entry_name;?>" />
                                                <input type="hidden" name="saved_type" value="<?php echo $entry_type;?>" />
                                                <input type="hidden" name="saved_originator" value="<?php echo $entry_originator;?>" />
                                                <?php if($entry_story==""){?><input type="hidden" name="saved_story" value="<?php echo $entry_story;?>" /><?php }?>
                                                <input name="uploadedfile" type="file" />
                                            </div>
                                        </div>
                                        <div class="formLayoutMidRow">
                                            <div class="formLayoutMidRowLeftCol">
                                                 
                                            </div>
                                            <div class="formLayoutMidRowRightCol">
                                                <input type="submit" value="Upload File" />
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <?php
							} else {
								/* NO UPLOAD TYPE */
								/* NO ERRORS; YAY!!! */
								echo "<div class=\"admin-response-success\">The entry has been successfully saved!</div><div class=\"admin-response-tools\"><a href=\"".$properties->WEB_URL."?page=admin\" style=\"cursor:pointer;\">Continue</a></div>";
								
								/* STEP 3: SAVE DATA */
								if($entry_story==""){
									mysql_query("INSERT INTO {$properties->DB_PREFIX}entries (name,source,originator,type,story) VALUES ('".$entry_name."','".$entry_source."','".$entry_originator."','".$entry_type."','')");
								} else {
									mysql_query("INSERT INTO {$properties->DB_PREFIX}entries (name,source,originator,type,story) VALUES ('".$entry_name."','".$entry_source."','".$entry_originator."','".$entry_type."','".$entry_story."')");
								}
							}
						}
					} else {
						?>
						<form method="post" action="">
							<div class="formLayoutMid">
								<div class="formLayoutMidRow">
									<div class="formLayoutMidRowLeftCol">
										 <label>Name</label>
									</div>
									<div class="formLayoutMidRowRightCol">
										<input type="text" name="entry_name">
									</div>
								</div>
								
                                <div class="formLayoutMidRow">
									<div class="formLayoutMidRowLeftCol">
										 <label>Originator</label>
									</div>
									<div class="formLayoutMidRowRightCol">
										<input type="text" name="entry_originator">
									</div>
								</div>
                                
								<div class="formLayoutMidRow">
									<div class="formLayoutMidRowLeftCol">
										<label>Type</label>            
									</div>
									<div class="formLayoutMidRowRightCol">
										<select name="entry_type">
											<option value="upload">Upload</option>
											<option value="youtube">Youtube</option>
                                        </select>
									</div>
								</div>
								
								<div class="formLayoutMidRow">
									<div class="formLayoutMidRowLeftCol">
										<label>Source</label>
									</div>
									<div class="formLayoutMidRowRightCol">
										<input type="text" name="entry_source"> <br /><span style="font-size:12px;">* leave blank if you selected &quot;Upload&quot; from above.</span>
									</div>
								</div>
                                
                                <div class="formLayoutMidRow">
									<div class="formLayoutMidRowLeftCol">
										<label>Story</label>
									</div>
									<div class="formLayoutMidRowRightCol">
										<textarea name="entry_story" cols="30" rows="5"></textarea>
									</div>
								</div>
								
								<div class="formLayoutMidRow">
									<div class="formLayoutMidRowLeftCol">
										
									</div>
									<div class="formLayoutMidRowRightCol">
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="entry_save" value="Save">
									</div>
								</div>
								
								<div class="formLayoutMidRow">
									<div class="formLayoutMidRowLeftCol">
										
									</div>
									<div class="formLayoutMidRowRightCol">
										
									</div>
								</div>
							</div>
						</form>
						<?php
					}
					?>
					<?php	
				}
			} else {
				?>
                <h1><a href="<?php echo $properties->WEB_URL;?>" style="text-decoration:none;"> < </a> <a href="<?php echo $properties->WEB_URL;?>?page=admin"><?php echo $properties->TITLE;?>'s Administration Panel</a></h1>
				You must be logged in to use this feature.
				<hr>
				<center>
					<h1><a href="<?php echo $properties->WEB_URL;?>?page=admin">Login</a></h1>
					<?php
					if(isset($_POST['login'])){
						?>
						<form method="POST" action="">
							<div class="formLayout">
								<div class="formLayoutRow">
									<div class="formLayoutRowLeftCol">
										<label>Username</label>
										<input type="text" name="admin_username" disabled="disabled">
										<br />
										<label>Password</label>
										<input type="password" name="admin_password" disabled="disabled">
									</div>
									<div class="formLayoutRowRightCol">
										<input type="submit" name="login" value="Login" disabled="disabled">
									</div>
								</div>
							</div>
						</form>
						<?php
						/* STEP 1: GET POST DATA */
						$username=$_POST['admin_username'];
						$password=$_POST['admin_password'];

						/* STEP 2: CHECK FOR ACCURACY */
						$error_console="";
						if($username == ""){$error_console.="Username must not be blank<br />";}
						if($password == ""){$error_console.="Password must not be blank<br />";}
						
						/* FIND OUT IF USER IS IN DB */
						$DETECT_USER_IN_DB=mysql_query("SELECT * FROM {$properties->DB_PREFIX}admins WHERE uname='$username'");
						if(mysql_num_rows($DETECT_USER_IN_DB)<1){
							/* USER DOES NOT EXIST */
							if($username==""){/* DONT DISPLAY SINCE THEY DID NOT PROVIDE */}else{$error_console.="<b>".$username."</b> does not exist!<br />";}
						} else {
							/* USER EXISTS; CHECK FOR PASSWORD */
							while($FETCH_USER_PASSWORD=mysql_fetch_array($DETECT_USER_IN_DB)){
								$real_password=$FETCH_USER_PASSWORD['upass'];
							}
							if(hash("sha256",sha1(md5($password))) == $real_password){
								/* PASSWORD CHECKS OUT; CHECK FOR STATUS */
								while($FETCH_DETECT_USER_IN_DB=mysql_fetch_array($DETECT_USER_IN_DB)){
									@$status=$FETCH_DETECT_USER_IN_DB['status'];
								}
								switch(@$status){
									case 'active':
										/* USER IS ACTIVE */
										$error_console.="";
									break;
									
									case 'suspended':
										/* USER IS SUSPENDED */
										$error_console.="Your account has been suspended.<br />";
									break;
									
									case 'deleted':
										/* USER DOES NOT EXIST */
										$error_console.="The account you specified does not exist.<br />";
									break;
									
									case 'pending':
										/* USER IS PENDING */
										$error_console.="Your account is still pending.<br />";
									break;
								}
							} else {
								/* PASSWORD FAILED; ERROR */
								if($password==""){/* DON'T MAKE ERROR; THEY DIDN'T PROVIDE PASS */}else{$error_console.="The password you provided is incorrect.<br />";}	
							}
						}
						
						if($error_console != ""){
							/* ERRORS AMONG US! */
							echo "<div class=\"admin-response-error\">".$error_console."</div><div class=\"admin-response-tools\"><a onclick=\"history.go(-1)\" style=\"cursor:pointer;\">Go Back</a></div>";
						} else {
							/* NO ERRORS; YAY!!! */
							echo "<div class=\"admin-response-success\">You have been successfully logged in!</div><div class=\"admin-response-tools\"><a href=\"".$properties->WEB_URL."?page=admin\" style=\"cursor:pointer;\">Continue</a></div>";
							
							/* STEP 3: CREATE SESSION */
							$adminsession=str_shuffle($ip.rand("000000000000000","999999999999999"));
							setcookie("vv_admin_session",$adminsession,time()+(20 * 365 * 24 * 60 * 60));
							
							/* STEP 4: POST UPDATE */
							mysql_query("UPDATE {$properties->DB_PREFIX}admins SET logged_in='yes' WHERE uname='$username'");
							mysql_query("UPDATE {$properties->DB_PREFIX}admins SET logged_ip='$ip' WHERE uname='$username'");
							mysql_query("UPDATE {$properties->DB_PREFIX}admins SET logged_session='$adminsession' WHERE uname='$username'");
						}
						?>
						<?php
					} else {
						?>
						<form method="POST" action="">
							<div class="formLayout">
								<div class="formLayoutRow">
									<div class="formLayoutRowLeftCol">
										<label>Username</label>
										<input type="text" name="admin_username">
										<br />
										<label>Password</label>
										<input type="password" name="admin_password">
									</div>
									<div class="formLayoutRowRightCol">
										<input type="submit" name="login" value="Login">
									</div>
								</div>
							</div>
						</form>
						<?php
					}
					?>
				</center>
				<?php
			}
		break;

		case 'more':
			?>
            <form id="closing" method="post">
            <input type="button" name="close" value="Close" onClick="Close();" />
            </form>
            <?php
			$id=$_GET['id'];
			/* CREATING THE DYNAMIC VARS FOR ENTRIES */
			$FIND_ALL_ENTRIES=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries WHERE id='{$id}'");
			if(mysql_num_rows($FIND_ALL_ENTRIES)<1){
				echo "The Video with ID: {$id} was not found. :(";
			} else {
				while($FETCH_ALL_ENTRIES=mysql_fetch_array($FIND_ALL_ENTRIES)){
					$id=$FETCH_ALL_ENTRIES['id'];
					$name=$FETCH_ALL_ENTRIES['name'];
					$source=$FETCH_ALL_ENTRIES['source'];
					$type=$FETCH_ALL_ENTRIES['type'];
					$totalvotes=$FETCH_ALL_ENTRIES['totalvotes'];
					$story=$FETCH_ALL_ENTRIES['story'];
				}
				?>
                <center><h1><?php if(strlen($name)>75){echo "<a title=\"".$name."\" style=\"cursor:help;\">".substr($name,0,75)."..."."</a>";}else{echo $name;}?></h1></center>
	            <?php
				if($type=="image"){
					?>
                    <img src="uploads/<?php echo $source;?>" width="640" height="480" alt="" title="" />
					<?php
                } else {
					?>
                    <div id='mediaplayer_finalists_<?php echo $id;?>'></div>
					<?php
                }
				?>
                <center>
					<?php
                    if($properties->RATING_STYLE=="stars"){
                        ?>
                        <br />
                        <div class="vote_<?php echo $id;?>" data-average="10" data-id="<?php echo $id;?>" style="text-align:left;"></div>
                        <?php
                    } else if($properties->RATING_STYLE=="radio") {
                        ?>
                        <script type="text/javascript">
                        $('#vote').click( function() {
                            $.ajax({
                                url: 'parsing/php/radio.php',
                                type: 'post',
                                dataType: 'json',
                                data: $('form#vote').serialize(),
                                success: function(data) {
                                    alert(html(response));
                                    $("#vote-result").slideUp(300).delay(1600).fadeIn("slow")	
                                }
                            });
                        });
                        </script>
                        <form id="vote">
                            <input type="radio" class="vote" name="vote" id="vote" value="<?php echo $id;?>" />
                        </form>
                        <?php
                    }
                    ?>
                </center>
                
                <div class="story-full"><?php echo $story;?></div>
                
                <script type="text/javascript">
				<?php
				/* GET LOGGED VARS */
				if(isset($_COOKIE['vv_session'])){
					$session=$_COOKIE['vv_session'];
					$FIND_SESSION=mysql_query("SELECT * FROM {$properties->DB_PREFIX}who WHERE ip='$ip' AND session='$session'");
					if(mysql_num_rows($FIND_SESSION)<1){
						/* NO SESSION */
						$has_voted_times=0;
						$can_vote="yes";
					} else {
						/* COUNT THE NUMBER OF ENTRIES */
						$NUM_ENTRIES=mysql_num_rows($FIND_ALL_ENTRIES);
		
						while($FETCH_SESSION=mysql_fetch_array($FIND_SESSION)){
							$has_voted_times=$FETCH_SESSION['has_voted_times'];
							
							if($properties->DAILY_VOTING == "yes"){
								/* DAILY VOTING ACTIVE */
								$lastvote=$FETCH_SESSION['lastvote'];
								
								//break the date
								$lastvote_y=substr($lastvote,0,4);
								$lastvote_m=substr($lastvote,5,2);
								$lastvote_d=substr($lastvote,8,2);
								
								//get today's date
								$today=date("Y-m-d");
								
								//break today's date
								$today_y=substr($today,0,4);
								$today_m=substr($today,5,2);
								$today_d=substr($today,8,2);
								
								//voted times counter
								if(($today_y == $lastvote_y) && ($today_m == $lastvote_m) && ($today_d == $lastvote_d)){/* ALREADY VOTED; DO NOTHING */}else{/* NEW (DAY,MONTH,YEAR); RESET HAS_VOTED_TIMES */mysql_query("UPDATE {$properties->DB_PREFIX}who SET has_voted_times = 0 WHERE ip='".$ip."' AND session='".$session."'");}
							}
							
							if($properties->CAN_VOTE_PER == "once"){
								if($has_voted_times<1){
									$can_vote="yes";
								} else if($has_voted_times>=1){
									$can_vote="no";
								}
							} else if($properties->CAN_VOTE_PER == "amtentries") {
								if($has_voted_times<1){
									$can_vote="yes";
								} else if( ($has_voted_times>0) && ($has_voted_times<$NUM_ENTRIES) ){
									$can_vote="yes";
								} else if($has_voted_times==$NUM_ENTRIES){
									$can_vote="no";
								} else if($has_voted_times>$NUM_ENTRIES){
									$can_vote="no";
								}				
							} else if($properties->CAN_VOTE_PER == "inf") {
								$can_vote="yes";				
							}
						}
					}
				} else {
					$session="";
					$can_vote="yes";
				}
		
				if($can_vote=="no"){
					/* COMPUTER (USER) ALREADY VOTED */
					
				} else if($can_vote=="yes") {
					?>
					$(".vote_<?php echo $id;?>").jRating({
						step: <?php echo $properties->VS_STEP;?>,
						length: <?php echo $properties->VS_LENGTH;?>,
						rateMax: <?php echo $properties->VS_RATEMAX;?>,
						nbRates: <?php echo $properties->VS_NBRATES;?>,
						phpPath: '<?php echo $properties->VS_PARSEPATH;?>',
						onSuccess : function(){
							$("#vote-result").slideUp(300).delay(1600).fadeOut(),
							jSuccess('Success : your rate has been saved :)',{
								HorizontalPosition:'center',
								VerticalPosition:'top',
							}),
							$("#vote-result").slideUp(300).delay(1600).fadeIn("slow")
							<?php
							if($properties->CAN_VOTE_PER == "once"){
								$CHECK_ALL_ENTRIES=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries ORDER BY id");
								$TOTAL_ENTRIES=mysql_num_rows($CHECK_ALL_ENTRIES);
								
								while($FETCH_CAE=mysql_fetch_array($CHECK_ALL_ENTRIES)){
									/* NORMAL ITEMS */
									?>,$(".vote_<?php echo $FETCH_CAE['id']?>").fadeOut()<?php
								}
							} else if($properties->CAN_VOTE_PER == "amtentries"){
								/* DONT FADE THEM OUT */
							}
							?>
		
						},
						onError : function(){
							jError('Error : please retry');
						}
					})
					<?php
				}
				?>
				jwplayer('mediaplayer_finalists_<?php echo $id;?>').setup({
                    'flashplayer': 'js/JWPlayer/jwplayer.swf',
                    'id': 'playerID',
                    'width': '640',
                    'height': '480',
                    'file': '<?php if($type=="youtube"){?>http://www.youtube.com/watch?v=<?php echo $source;?><?php }else if($type=="vimeo"){/* HOSTED ON TXW : upload to http://downloadvimeo.com/ and download to the uploads folder */?>uploads/<?php echo $source;}?>',
                    'controlbar': 'bottom'
                });
				</script>
                <?php
			}
		break;

		default:
			echo "I think something is broke or someone is trying to hack us therefore I was not able to show you anything. :)";
			echo "<br />";
			echo "<a href=\"".$properties->WEB_URL."\">Go Back</a>";
		break;
	}
} else {
	?>
	<h1><a href="<?php echo $properties->WEB_URL;?>"><?php echo $properties->TITLE;?></a></h1>
	<table cols="4">
		<tr>
	    	<?php
			/* CREATING THE DYNAMIC VARS FOR ENTRIES */
			$FIND_ALL_ENTRIES=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries WHERE status='active' ORDER BY {$ORDERBY}");
			if(mysql_num_rows($FIND_ALL_ENTRIES)<1){
				echo "No Video Entries were found. :(";
				$can_vote="no";
			} else {
				while($FETCH_ALL_ENTRIES=mysql_fetch_array($FIND_ALL_ENTRIES)){
					$id=$FETCH_ALL_ENTRIES['id'];
					$name=$FETCH_ALL_ENTRIES['name'];
					$sources=$FETCH_ALL_ENTRIES['source'];
					$type=$FETCH_ALL_ENTRIES['type'];
					$totalvotes=$FETCH_ALL_ENTRIES['totalvotes'];
					$story=$FETCH_ALL_ENTRIES['story'];
					
					if(strlen($story)>$properties->AMT_STORY_DISP){$story=substr($story,0,$properties->AMT_STORY_DISP - 2)."...<a href=\"?page=more&id={$id}\" target=\"_blank\">[more]</a>";}else{$story=$story;}
					?>
                    <td>
	                	<center><div style="width:150px;height:60px;line-height:1.2em;text-align:center;word-wrap:break-word;"><?php if(strlen($name)>$properties->TRUN_TITLE_LIMIT){echo "<a title=\"".$name."\" style=\"cursor:help;\">".substr($name,0,$properties->TRUN_TITLE_LIMIT)."..."."</a>";}else{echo $name;}?></div></center>
	                <?php
					if($type=="upload"){
						?><center><img src="uploads/<?php echo $sources;?>" width="150" height="150" /></center><?php
						
                    } else {
						?><center><div id="mediaplayer_finalists_<?php echo $id;?>"></div></center><?php
                    }
					?>
                    <br />
                    <div class="story"><?php echo "<a href=\"?page=more&id={$id}\" target=\"_blank\">[Read Full Story]</a>";//echo $story;?></div>
                    <br />
                    <?php if($type=="youtube"){?><a href="http://www.youtube.com/watch_popup?v=<?php echo $sources;?>" target="_blank">Watch Full Screen</a><?php }else{/* NOT YOUTUBE */}?>
                    <center>
	                	<?php
						if($properties->RATING_STYLE=="stars"){
							?>
	                        <div class="vote_<?php echo $id;?>" data-average="10" data-id="<?php echo $id;?>" style="text-align:left;"></div>
	                        <?php
						} else if($properties->RATING_STYLE=="radio") {
							?>
	                        <script type="text/javascript">
							$('#vote').click( function() {
								$.ajax({
									url: 'parsing/php/radio.php',
									type: 'post',
									dataType: 'json',
									data: $('form#vote').serialize(),
									success: function(data) {
										alert(html(response));
										$("#vote-result").slideUp(300).delay(1600).fadeIn("slow")	
									}
								});
							});
							</script>
							<form id="vote">
								<input type="radio" class="vote" name="vote" id="vote" value="<?php echo $id;?>" />
							</form>
							<?php
						}
						?>
	                </center>
                    <script type="text/javascript">
					<?php
					/* GET LOGGED VARS */
					if(isset($_COOKIE['vv_session'])){
						$session=$_COOKIE['vv_session'];
						$FIND_SESSION=mysql_query("SELECT * FROM {$properties->DB_PREFIX}who WHERE ip='$ip' AND session='$session'");
						if(mysql_num_rows($FIND_SESSION)<1){
							/* NO SESSION */
							$has_voted_times=0;
							$can_vote="yes";
						} else {
							/* COUNT THE NUMBER OF ENTRIES */
							$NUM_ENTRIES=mysql_num_rows($FIND_ALL_ENTRIES);
			
							while($FETCH_SESSION=mysql_fetch_array($FIND_SESSION)){
								$has_voted_times=$FETCH_SESSION['has_voted_times'];
								
								if($properties->DAILY_VOTING == "yes"){
									/* DAILY VOTING ACTIVE */
									$lastvote=$FETCH_SESSION['lastvote'];
									
									//break the date
									$lastvote_y=substr($lastvote,0,4);
									$lastvote_m=substr($lastvote,5,2);
									$lastvote_d=substr($lastvote,8,2);
									
									//get today's date
									$today=date("Y-m-d");
									
									//break today's date
									$today_y=substr($today,0,4);
									$today_m=substr($today,5,2);
									$today_d=substr($today,8,2);
									
									//voted times counter
									if(($today_y == $lastvote_y) && ($today_m == $lastvote_m) && ($today_d == $lastvote_d)){/* ALREADY VOTED; DO NOTHING */}else{/* NEW (DAY,MONTH,YEAR); RESET HAS_VOTED_TIMES */mysql_query("UPDATE {$properties->DB_PREFIX}who SET has_voted_times = 0 WHERE ip='".$ip."' AND session='".$session."'");}
								}
								
								if($properties->CAN_VOTE_PER == "once"){
									if($has_voted_times<1){
										$can_vote="yes";
									} else if($has_voted_times>=1){
										$can_vote="no";
									}
								} else if($properties->CAN_VOTE_PER == "amtentries") {
									if($has_voted_times<1){
										$can_vote="yes";
									} else if( ($has_voted_times>0) && ($has_voted_times<$NUM_ENTRIES) ){
										$can_vote="yes";
									} else if($has_voted_times==$NUM_ENTRIES){
										$can_vote="no";
									} else if($has_voted_times>$NUM_ENTRIES){
										$can_vote="no";
									}				
								} else if($properties->CAN_VOTE_PER == "inf") {
									$can_vote="yes";				
								}
							}
						}
					} else {
						$session="";
						$can_vote="yes";
					}
					
					if($can_vote=="no"){
						/* COMPUTER (USER) ALREADY VOTED */
						
					} else if($can_vote=="yes") {
						?>
						$(".vote_<?php echo $id;?>").jRating({
							step: <?php echo $properties->VS_STEP;?>,
							length: <?php echo $properties->VS_LENGTH;?>,
							rateMax: <?php echo $properties->VS_RATEMAX;?>,
							nbRates: <?php echo $properties->VS_NBRATES;?>,
							phpPath: '<?php echo $properties->VS_PARSEPATH;?>',
							onSuccess : function(){
								$("#vote-result").slideUp(300).delay(1600).fadeOut(),
								jSuccess('Success : your rate has been saved :)',{
									HorizontalPosition:'center',
									VerticalPosition:'top',
								}),
								$("#vote-result").slideUp(300).delay(1600).fadeIn("slow")
								<?php
								if($properties->CAN_VOTE_PER == "once"){
									$CHECK_ALL_ENTRIES=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries ORDER BY id");
									$TOTAL_ENTRIES=mysql_num_rows($CHECK_ALL_ENTRIES);
									
									while($FETCH_CAE=mysql_fetch_array($CHECK_ALL_ENTRIES)){
										/* NORMAL ITEMS */
										?>,$(".vote_<?php echo $FETCH_CAE['id']?>").fadeOut()<?php
									}
				
								} else if($properties->CAN_VOTE_PER == "amtentries"){
									/* DONT FADE THEM OUT */
								}
								?>
			
							},
							onError : function(){
								jError('Error : please retry');
							}
						})
						<?php
					}
				    ?>
					</script>
	                </td>
	                <?php
				}
			}
			?>
	    </tr>
	</table>
	<script type="text/javascript">
	  <?php  
	  $FIND_ALL_ENTRIES=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries WHERE status='active' ORDER BY {$ORDERBY}");
	  while($FETCH_ALL_ENTRIES=mysql_fetch_array($FIND_ALL_ENTRIES)){
		$id=$FETCH_ALL_ENTRIES['id'];
		$name=$FETCH_ALL_ENTRIES['name'];
		$source=$FETCH_ALL_ENTRIES['source'];
		$type=$FETCH_ALL_ENTRIES['type'];
		$totalvotes=$FETCH_ALL_ENTRIES['totalvotes'];
		$story=$FETCH_ALL_ENTRIES['story'];
				
		?>
		jwplayer('mediaplayer_finalists_<?php echo $id;?>').setup({
			'flashplayer': 'js/JWPlayer/jwplayer.swf',
			'id': 'playerID',
			'width': '150',
			'height': '150',
			'file': '<?php if($type=="youtube"){?>http://www.youtube.com/watch?v=<?php echo $source;?><?php }else if($type=="vimeo"){/* HOSTED ON TXW : upload to http://downloadvimeo.com/ and download to the uploads folder */?>uploads/<?php echo $source;}?>',
			'controlbar': 'bottom'
		});
		<?php
		
		/* GET LOGGED VARS */
		if(isset($_COOKIE['vv_session'])){
			$session=$_COOKIE['vv_session'];
			$FIND_SESSION=mysql_query("SELECT * FROM {$properties->DB_PREFIX}who WHERE ip='$ip' AND session='$session'");
			if(mysql_num_rows($FIND_SESSION)<1){
				/* NO SESSION */
				$has_voted_times=0;
				$can_vote="yes";
			} else {
				/* COUNT THE NUMBER OF ENTRIES */
				$NUM_ENTRIES=mysql_num_rows($FIND_ALL_ENTRIES);

				while($FETCH_SESSION=mysql_fetch_array($FIND_SESSION)){
					$has_voted_times=$FETCH_SESSION['has_voted_times'];
					
					if($properties->DAILY_VOTING == "yes"){
						/* DAILY VOTING ACTIVE */
						$lastvote=$FETCH_SESSION['lastvote'];
						
						//break the date
						$lastvote_y=substr($lastvote,0,4);
						$lastvote_m=substr($lastvote,5,2);
						$lastvote_d=substr($lastvote,8,2);
						
						//get today's date
						$today=date("Y-m-d");
						
						//break today's date
						$today_y=substr($today,0,4);
						$today_m=substr($today,5,2);
						$today_d=substr($today,8,2);
						
						//voted times counter
						if(($today_y == $lastvote_y) && ($today_m == $lastvote_m) && ($today_d == $lastvote_d)){/* ALREADY VOTED; DO NOTHING */}else{/* NEW (DAY,MONTH,YEAR); RESET HAS_VOTED_TIMES */mysql_query("UPDATE {$properties->DB_PREFIX}who SET has_voted_times = 0 WHERE ip='".$ip."' AND session='".$session."'");}
					}
					
					if($properties->CAN_VOTE_PER == "once"){
						if($has_voted_times<1){
							$can_vote="yes";
						} else if($has_voted_times>=1){
							$can_vote="no";
						}
					} else if($properties->CAN_VOTE_PER == "amtentries") {
						if($has_voted_times<1){
							$can_vote="yes";
						} else if( ($has_voted_times>0) && ($has_voted_times<$NUM_ENTRIES) ){
							$can_vote="yes";
						} else if($has_voted_times==$NUM_ENTRIES){
							$can_vote="no";
						} else if($has_voted_times>$NUM_ENTRIES){
							$can_vote="no";
						}				
					} else if($properties->CAN_VOTE_PER == "inf") {
						$can_vote="yes";				
					}
				}
			}
		} else {
			$session="";
			$can_vote="yes";
		}

		if($can_vote=="no"){
			/* COMPUTER (USER) ALREADY VOTED */
			?>
			/*$(".vote_<?php echo $id;?>").jRating({
				step: <?php echo $properties->VS_STEP;?>,
				length: <?php echo $properties->VS_LENGTH;?>,
				rateMax: <?php echo $properties->VS_RATEMAX;?>,
				nbRates: <?php echo $properties->VS_NBRATES;?>,
				phpPath: '<?php echo $properties->VS_PARSEPATH;?>',
				onSuccess : function(){
					$("#vote-result").slideUp(300).delay(1600).fadeOut(),
					jSuccess('Success : your rate has been saved :)',{
						HorizontalPosition:'center',
						VerticalPosition:'top',
					}),
					$("#vote-result").slideUp(300).delay(1600).fadeIn("slow")
				},
				onError : function(){
					jError('Error : please retry');
				}
			})
			
			$(".voteavg_<?php echo $id;?>").jRating({
				step: true,
				length: 5,
				rateMax: 5,
				nbRates: 1,
				phpPath: 'parsing/php/jQuery.php'
			})*/
			<?php
		} else if($can_vote=="yes") {
			?>
			$(".vote_<?php echo $id;?>").jRating({
				step: <?php echo $properties->VS_STEP;?>,
				length: <?php echo $properties->VS_LENGTH;?>,
				rateMax: <?php echo $properties->VS_RATEMAX;?>,
				nbRates: <?php echo $properties->VS_NBRATES;?>,
				phpPath: '<?php echo $properties->VS_PARSEPATH;?>',
				onSuccess : function(){
					$("#vote-result").slideUp(300).delay(1600).fadeOut(),
					jSuccess('Success : your rate has been saved :)',{
						HorizontalPosition:'center',
						VerticalPosition:'top',
					}),
					$("#vote-result").slideUp(300).delay(1600).fadeIn("slow")
					<?php
					if($properties->CAN_VOTE_PER == "once"){
						$CHECK_ALL_ENTRIES=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries ORDER BY id");
						$TOTAL_ENTRIES=mysql_num_rows($CHECK_ALL_ENTRIES);
						
						while($FETCH_CAE=mysql_fetch_array($CHECK_ALL_ENTRIES)){
							/* NORMAL ITEMS */
							?>,$(".vote_<?php echo $FETCH_CAE['id']?>").fadeOut()<?php
						}
					} else if($properties->CAN_VOTE_PER == "amtentries"){
						/* DONT FADE THEM OUT */
					}
					?>

				},
				onError : function(){
					jError('Error : please retry');
				}
			})
			<?php
		}
		?>
		<?php
	  }
	  ?>
	</script>
	<div id="vote-results"<?php if(mysql_num_rows($FIND_ALL_ENTRIES)<1){?>style="display:none;"<?php }?>>
		<?php
		if($can_vote=="yes"){
			/* LET THE JQ DO THE PARSING AND DISPLAYING */
		} else if ($can_vote=="no") {
			?>
	        <div id="resultsContainer" style="display: <?php if($properties->SHOW_RESULTS_IMM == "yes"){?>inline<?php }else if($properties->SHOW_RESULTS_IMM == "no"){?>none<?php }?>;">
				<?php
	            /* PARSE THE RESULTS SO THEY CAN SEE */
	            $FIND_ALL_ENTRIES1=mysql_query("SELECT * FROM {$properties->DB_PREFIX}stats");
	            while($FETCH_ALL_ENTRIES1=mysql_fetch_array($FIND_ALL_ENTRIES1)){
	                $total_voted=$FETCH_ALL_ENTRIES1['total_voted'];
	            }
	            $FIND_ALL_ENTRIES=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries WHERE status='active' ORDER BY rate DESC");
	            $pos=0;
	            
	            if($properties->SHOW_RESULTS_IMM == "yes"){$SHOW_RESULTS="inline";}else if($properties->SHOW_RESULTS_IMM == "no"){$SHOW_RESULTS="none";}

	            echo "<div id=\"resultsContainer\" style=\"display: '.$SHOW_RESULTS.'\"><hr><h2>Results</h2><p><div class=\"resultsTable\">";
					echo "<div class=\"rtRow\">
						<div class=\"rtLeftCol bold underline\">
							Video Name
						</div>
						<div class=\"rtRightCol\">
							<div class=\"innerResultsTable\">
								<div class=\"irtRow\">
									<div class=\"irtLeftCol bold underline center\">
										Total Votes (<a title=\"The percent of All voters who voted for this video\" style=\"cursor:pointer;\">%</a>)
									</div>
									<div class=\"irtRightCol bold underline\">
										Position
									</div>
								</div>
							</div>
						</div>
					</div>";

					$FIND_ALL_ENTRIES=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries WHERE status='active' ORDER BY rate DESC");
					$pos=0;
					while($FETCH_ALL_ENTRIES=mysql_fetch_array($FIND_ALL_ENTRIES)){
						$id=$FETCH_ALL_ENTRIES['id'];
						$name=$FETCH_ALL_ENTRIES['name'];
						$source=$FETCH_ALL_ENTRIES['source'];
						$type=$FETCH_ALL_ENTRIES['type'];
						$totalvotes=$FETCH_ALL_ENTRIES['totalvotes'];
						//if($totalvotes<1){$avg=0;}else{$avg=round($rate / $totalvotes,1);}
						$percentof=round(($totalvotes / $total_voted) * 100);
						$pos++;
						$nameString="";
						if(strlen($name)>37){$nameString=substr($name,0,37)."...";}else{$nameString=$name;}
						echo "
						<div class=\"rtRow\">
						
							<div class=\"rtLeftCol\">".$nameString."</div>
							<div class=\"rtRightCol\">
								<div class=\"innerResultsTable\">
									<div class=\"irtRow\">
										<div class=\"irtLeftCol\">
											
											<div class=\"irtLeftColTable\">
												<div class=\"irtLeftColTableRow\">
													<div class=\"irtLeftColTableRowLeftCol bold\">".$totalvotes."</div>
													<div class=\"irtLeftColTableRowRightCol\">(".$percentof."%)</div>
												</div>
											</div>
											
										</div>
										<div class=\"irtRightCol\">
										#".$pos."
										</div>
									</div>
								</div>
							</div>
						</div>";
					}
					echo "</div></p></div>";            
	            ?>
	        </div>
	        <?php
		}
		?>
	</div>
	<?php
	if(mysql_num_rows($FIND_ALL_ENTRIES)<1){
		/* NOTING */
	} else {
		?>
	    <h2>Rules</h2>
	    You <b>may</b> vote <b><?php if($properties->CAN_VOTE_PER == "once"){?>once<?php }else if($properties->CAN_VOTE_PER == "amtentries"){?>once per entry<?php }else if($properties->CAN_VOTE_PER == "inf"){?>as many times as you want<?php }?></b> <?php if($properties->DAILY_VOTING == "yes"){?> per day<?php }else if($properties->DAILY_VOTING == "no"){?><?php }?>
	    <br />You <b>may</b> <?php if($properties->SHOW_RESULTS_IMM == "no"){?><b>not</b> see the results<?php }else if($properties->SHOW_RESULTS_IMM == "yes"){?> see the results once you vote<?php }?> 
		<?php	
	}
	
	if($properties->DISP_ADMIN_LINK == "yes"){
		/* DISPLAYING LINK */
		?>
        <hr>
        <a href="?page=admin">Administration Panel</a>
        <?php
	} else if($properties->DISP_ADMIN_LINK == "no") {
		/* NO LINK */
	}
}
?>
</center>
</body>
</html>
