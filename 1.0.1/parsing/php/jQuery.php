<?php
require("../../conf/props.php");
$properties=new properties();

/* CONNECT TO DB */
include("../../conf/connect.php");

$aResponse['error'] = false;
$aResponse['message'] = '';

// ONLY FOR THE DEMO, YOU CAN REMOVE THIS VAR
	$aResponse['server'] = ''; 
// END ONLY FOR DEMO
	
	
if(isset($_POST['action']))
{
	if(htmlentities($_POST['action'], ENT_QUOTES, 'UTF-8') == 'rating')
	{
		/*
		* vars
		*/
		$id = intval($_POST['idBox']);
		$rate = floatval($_POST['rate']);
		$datevote_y=date("Y");
		$datevote_m=date("m");
		$datevote_d=date("d");
		
		$datevote=$datevote_y."-".$datevote_m."-".$datevote_d;
		
		/* GET IP */
		$ip=$_SERVER['REMOTE_ADDR'];
		if(isset($_COOKIE['vv_session'])){$session=$_COOKIE['vv_session'];}
		
		/* COUNT NUM OF ENTRIES */
		$FIND_ALL_ENTRIES=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries");
		$NUM_ENTRIES=mysql_num_rows($FIND_ALL_ENTRIES);

		$FIND_SESSION=mysql_query("SELECT * FROM {$properties->DB_PREFIX}who WHERE ip='$ip' AND session='$session'");
		if(mysql_num_rows($FIND_SESSION)<1){
			/* NO SESSION; UM WHY? IDK...THERE SHOULD BE. HACKERS :( */
		} else {
			while($FETCH_FIND_SESSION=mysql_fetch_array($FIND_SESSION)){
				$has_voted_times=$FETCH_FIND_SESSION['has_voted_times'];
			}
			if($properties->CAN_VOTE_PER == "once"){
				if($has_voted_times<1){
					$can_vote="yes";
				} else if($has_voted_times==1){
					$can_vote="no";
				}
			} else if($properties->CAN_VOTE_PER == "amtentries") {
				if($has_voted_times<1){
				$can_vote="yes";
				} else if( ($has_voted_times>0) && ($has_voted_times<$NUM_ENTRIES) ){
					$can_vote="yes";
				} else if($has_voted_times==$NUM_ENTRIES){
					$can_vote="no";
				} else if($has_voted_times==1){
					$can_vote="no";
				}
			} else if($properties->CAN_VOTE_PER == "inf") {
				$can_vote="yes";				
			}
		}
		if($can_vote="yes"){
			mysql_query("UPDATE {$properties->DB_PREFIX}entries SET rate = rate+".$rate." WHERE id='".$id."'");
			mysql_query("UPDATE {$properties->DB_PREFIX}entries SET totalvotes = totalvotes+".$rate." WHERE id='".$id."'");
			mysql_query("UPDATE {$properties->DB_PREFIX}stats SET total_voted = total_voted+".$rate."");
			mysql_query("UPDATE {$properties->DB_PREFIX}who SET has_voted_times = has_voted_times+1 WHERE ip='".$ip."' AND session='".$session."'");
			mysql_query("UPDATE {$properties->DB_PREFIX}who SET lastvote = '".$datevote."' WHERE ip='".$ip."' AND session='".$session."'");
		} else if($can_vote=="no"){
			/* DON'T UPDATE SINCE THEY CANNOT VOTE ANY MORE */
		}
		
		// if request successful
		$success = true;
		// else $success = false;
		
		
		// json datas send to the js file
		if($success)
		{
			$aResponse['message'] = 'Your rate has been successfully recorded. Thanks for your rate :)';
			
			// ONLY FOR THE DEMO, YOU CAN REMOVE THE CODE UNDER
				$aResponse['server'] = '<strong>Success answer :</strong> Success : Your rate has been recorded. Thanks for your rate :)<br />';
				$aResponse['server'] .= '<strong>Rate received :</strong> '.$rate.'<br />';
				$aResponse['server'] .= '<strong>ID to update :</strong> '.$id;
			// END ONLY FOR DEMO
			
			/* GET NEW VALUES */
			$FIND_ALL_ENTRIES1=mysql_query("SELECT * FROM {$properties->DB_PREFIX}stats");
			while($FETCH_ALL_ENTRIES1=mysql_fetch_array($FIND_ALL_ENTRIES1)){
				$total_voted=$FETCH_ALL_ENTRIES1['total_voted'];
			}
			
			if($properties->SHOW_RESULTS_IMM == "yes"){$SHOW_RESULTS="inline";}else if($properties->SHOW_RESULTS_IMM == "no"){$SHOW_RESULTS="none";}
			
			$aResponse['result'] = '<div id=\'resultsContainer\' style=\'display: '.$SHOW_RESULTS.'\'><hr><h2>Results</h2><p><div class=\'resultsTable\'>';
			$aResponse['result'].= '<div class=\'rtRow\'><div class=\'rtLeftCol bold underline\'>Video Name</div><div class=\'rtRightCol\'><div class=\'innerResultsTable\'><div class=\'irtRow\'><div class=\'irtLeftCol bold underline center\'>Total Votes (<a title=\'The percent of All voters who voted for this video\' style=\'cursor:pointer;\'>%</a>)</div><div class=\'irtRightCol bold underline\'>Position</div></div></div></div></div>';
			$FIND_ALL_ENTRIES=mysql_query("SELECT * FROM {$properties->DB_PREFIX}entries ORDER BY rate DESC");
			$pos=0;
			while($FETCH_ALL_ENTRIES=mysql_fetch_array($FIND_ALL_ENTRIES)){
				$id=$FETCH_ALL_ENTRIES['id'];
				$name=$FETCH_ALL_ENTRIES['name'];
				$source=$FETCH_ALL_ENTRIES['source'];
				$type=$FETCH_ALL_ENTRIES['type'];
				$totalvotes=$FETCH_ALL_ENTRIES['totalvotes'];
				//if($totalvotes<1){$avg=0;}else{$avg=round($rate / $totalvotes,1);}
				$percentof=round(($totalvotes / $total_voted) * 100);
				/*$aResponse['result'] .= '<script type=\'text/javascript\'>$(function(){$\'"#progressbar_'.$id.'\').progressbar({value: '.$totalvotes.'});});</script>';*/
				$pos++;
				$nameString="";
				if(strlen($name)>37){$nameString=substr($name,0,37)."...";}else{$nameString=$name;}
				$aResponse['result'] .= '
				<div class=\'rtRow\'>
				
					<div class=\'rtLeftCol\'>'.$nameString.'</div>
					<div class=\'rtRightCol\'>
						<div class=\'innerResultsTable\'>
							<div class=\'irtRow\'>
								<div class=\'irtLeftCol\'>
									
									<div class=\'irtLeftColTable\'>
										<div class=\'irtLeftColTableRow\'>
											<div class=\'irtLeftColTableRowLeftCol bold\'>'.$totalvotes.'</div>
											<div class=\'irtLeftColTableRowRightCol\'>('.$percentof.'%)</div>
										</div>
									</div>
									
								</div>
								<div class=\'irtRightCol\'>
								#'.$pos.'
								</div>
							</div>
						</div>
					</div>
				</div>';
			}
			$aResponse['result'] .= '</div></p></div>';
						
			echo json_encode($aResponse);
		}
		else
		{
			$aResponse['error'] = true;
			$aResponse['message'] = 'An error occured during the request. Please retry';
			
			// ONLY FOR THE DEMO, YOU CAN REMOVE THE CODE UNDER
				$aResponse['server'] = '<strong>ERROR :</strong> Your error if the request crash !';
			// END ONLY FOR DEMO
			
			
			echo json_encode($aResponse);
		}
	}
	else
	{
		$aResponse['error'] = true;
		$aResponse['message'] = '"action" post data not equal to \'rating\'';
		
		// ONLY FOR THE DEMO, YOU CAN REMOVE THE CODE UNDER
			$aResponse['server'] = '<strong>ERROR :</strong> "action" post data not equal to \'rating\'';
		// END ONLY FOR DEMO
			
		
		echo json_encode($aResponse);
	}
}
else
{
	$aResponse['error'] = true;
	$aResponse['message'] = '$_POST[\'action\'] not found';
	
	// ONLY FOR THE DEMO, YOU CAN REMOVE THE CODE UNDER
		$aResponse['server'] = '<strong>ERROR :</strong> $_POST[\'action\'] not found';
	// END ONLY FOR DEMO
	
	
	echo json_encode($aResponse);
}