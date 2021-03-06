<?php
session_start();	
error_reporting(E_ALL ^ E_NOTICE);
require_once('../QOB/qob.php');
require_once('eventHandlers/miniEvent.php');
require_once('fetch.php');
$_SESSION['jx']="1004"; //1001 for latest Polls 1002 for upcoming events 1003 for winners 1004 for latestPolls
//Testing Content Starts
	/*$userIdHash=$_SESSION['vj']=hash("sha512","MDS13M001".SALT);
	$_SESSION['tn']=hash("sha512",$userIdHash.SALT2);
	$_POST['_refresh']=0;
	$_POST['sgk']=array();*/

//Testing Content Ends
/*
Code 3: SUCCESS!!
Code 5: Attempt to redo a already done task!
Code 6: Content Unavailable!
Code 13: SECURITY ALERT!! SUSPICIOUS BEHAVIOUR!!
Code 12: Database ERROR!!
code 14: Suspicious Behaviour and Blocked!
Code 16: Erroneous Entry By USER!!
Code 11: Session Variables unset!!

*/

if(!(isset($_SESSION['vj'])&&isset($_SESSION['tn'])))
{
	echo 11;
	exit();
}

//Upcoming Event Offset - vgr
//Processed Event Hashes - sgk
$userIdHash=$_SESSION['vj'];
//$refresh=$_POST['_refresh'];
$ProcessedHashes=array();
$requestedEvent=$_POST['_eventId'];


$conn=new QoB();
if(hash("sha512",$userIdHash.SALT2)!=$_SESSION['tn'])
{
	if(blockUserByHash($userIdHash,"Suspicious Session Variable in single event")>0)
	{
		$_SESSION=array();
		session_destroy();
		echo 14;
		exit();
	}
	else
	{
		notifyAdmin("Suspicious Session Variable in single event",$userIdHash.",sh:".$_SESSION['tn']);
		$_SESSION=array();
		session_destroy();
		echo 13;
		exit();
	}
}
else
{
	if(($user=getUserFromHash($userIdHash))==false)
	{
		notifyAdmin("Critical Error In singleevent",$userIdHash);
		$_SESSION=array();
		session_destroy();
		echo 13;
		exit();
	}
	else
	{
		$userId=$user['userId'];
		$i=0;
		

		$finalStudentRegex=getRollNoRegex($userId);
		$getRequestedEventsSQL="SELECT event.*, users.name FROM event INNER JOIN users ON event.userId=users.userId WHERE sharedWith REGEXP ? AND eventIdHash=? ";

		$values[0]=array($finalStudentRegex => 's');
		$values[1]=array($requestedEvent => 's');	
		//var_dump($values);
		//echo $getLatestEventsSQL;
		$displayCount=0;
		$result=$conn->select($getRequestedEventsSQL,$values);
		if($conn->error=="")
		{
			//Success
			if(($event=$conn->fetch($result)))
			{

				$eventObj=getEventObject($event,$userId);
				print_r(json_encode($eventObj));
				
			}
			else
			{
				echo 404;
				exit();
			}
		}
		else
		{
			notifyAdmin("Conn.Error".$conn->error."! While fetching in single events",$userId);
			echo 12;
			exit();
		}
	}
}