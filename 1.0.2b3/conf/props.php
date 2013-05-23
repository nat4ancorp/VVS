<?php
/* ------------------------------------------------------------------------------------------------------------------------ 
VIDEO VOTING SCRIPT CORE FILE

CODING BY: NATHAN SMYTH
DESIGN BY: JOSHUA "THE" MATHIS
REFINED AND FINALIZED BY: ANTON DOMRATCHEV

THIS SCRIPT WAS INTENDED TO BE USED "AS IS" BY THE TEXAS WEDDING GUIDE AND IS ALSO AVAILABLE UNDER MIT LICENSING
ON GITHUB (PRIVATE REPO) AT  
--------------------------------------------------------------------------------------------------------------------------- */
class properties {
	/* GENERAL VARIABLES */
	public $TITLE					= "VVS";					/* Give this voting page a snazzy title! */
	public $DISP_ADMIN_LINK			= "no";						/* yes - will display the admin link; no - will not display link and you can access the admin from ?page=admin*/
	public $WEB_URL					= "http://10.10.10.230/testbed/NATHAN/2013/testVVS/"; 		/* The landing page URL */ // remote: www.texasweddingsltd.com/video-voting	
	public $RATING_STYLE			= "stars"; 					/* radio (deprecated; do not use) or stars */
	public $JW_LICENSE				= "";						/* Insert your JWPlayer License Key */
	public $CAN_VOTE_PER			= "once";					/* OPTIONS ARE: 
																	"once" - can only vote once.
																	"amtentries" - for the amount of entries liste which allows the voter to vote on each entry once then it disables them upon reload.  
																	"inf" - there is no limit to the ammount of times you can vote */
																		
	public $DAILY_VOTING			= "yes";					/* This option also once per day voting. If yes, then the system will detect the date and if it is greater than the last vote day it will reset the vote counter. If no, then voting will be 			
												   				limited to once per computer unless the user resets their cookies */
																
	public $SHOW_RESULTS_IMM		= "no"; 					/* When set to yes, the results will show immediately after the person votes or on page load if the user has voted, no will cause no results to be displayed. */
	public $VS_STEP					= 'true'; 					/* _STEP is a boolean (true or false) value that will fill each star as you move your 
											     				mouse over them. This must be encapulated with single quotes or it will break. */
	public $VS_LENGTH				= '1'; 						/* _LENGTH is how many stars to show */
	public $VS_RATEMAX				= '1'; 						/* _RATEMAX is the total the stars go up to in rate */
	public $VS_NBRATES				= '1';						/* _NBRATES is the number of times a user can vote - THIS OPTION IS NO LONGER USED SINCE THE SYSTEM SOMETHING DIFFERENT TO MANAGE THE NUMBER OF RATES SOMEONE CAN DO in other words: leave this option alone */
	public $VS_PARSEPATH			= 'parsing/php/jQuery.php'; /* _PARSEPATH is the path to the php file that parses all the data. It is relative to the root directory. */
	public $AMT_STORY_DISP			= 70;						/* This is the amount of how much to display on the story */
	public $TRUN_TITLE_LIMIT		= 125;						/* This is how long the title above each entry is. */
	public $ORDER_BY				= 'bytype';					/* This option sets how the entries are displayed. */
																/* OPTIONS ARE: 
																	  "random" - randomly displays 
																	  "bytype" - displays by type
																	  "byname" - displays by name
																	  "byid"   - displays by id 
															    */
																

	/* DATABASE INFO */
	public $DB_HOST					= "localhost";
	public $DB_USER					= "root";				// remote: wgbe
	public $DB_PASS					= "";			// remote: granb5rry
	public $DB_NAME					= "nathan_videovotes";
	public $DB_PREFIX				= "vv_";
}
?>