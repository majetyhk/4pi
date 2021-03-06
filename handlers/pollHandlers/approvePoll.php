<?php

//------Credits------//
//
//
//---Author : Hari Krishna Majety ,COE12B013.
//---Email: majetyhk@gmail.com
//
//
//---Credits Ends---//

session_start();	
require_once('../../QOB/qob.php');
require_once('./miniPoll.php');
require_once('../fetch.php');
//Testing Content Starts
	/*$userIdHash=$_SESSION['vj']=hash("sha512","COE12B013".SALT);
	$_SESSION['tn']=hash("sha512",$userIdHash.SALT2);
	$_POST['_pollId']="6c3b5a62fa26c9e18e026fdc3feae29b824103141efe1da6d93e2427511c72003b6cb3cd9a735d3719da8a3b4460e1b7e86778633efe878136467ce91d22c427";*/
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
//var_dump($_POST);
if(!(isset($_SESSION['vj'])&&isset($_SESSION['tn'])))
{
	echo 11;
	exit();
}

//Actual editPoll Code Starts
$pollIdHash=$_POST['_pollId'];
$pollStatus=$_POST['_status'];
if($pollStatus!=1&&$pollStatus!=-1)
{
	echo 16;
	exit();
}

$userIdHash=$_SESSION['vj'];
$conn= new QoB();
if(hash("sha512",$userIdHash.SALT2)!=$_SESSION['tn'])
{
	if(blockUserByHash($userIdHash,"Suspicious Session Variable in approve Poll")>0)//Happy Birthday to Myself!! Its October 21st!! 00:00 hrs
	{
		$_SESSION=array();
		session_destroy();
		echo 14;
		exit();
	}
	else
	{
		notifyAdmin("Suspicious Session Variable in approve Poll",$userIdHash.",sh:".$_SESSION['tn']);
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
		notifyAdmin("Critical Error In approve Poll",$userIdHash);
		$_SESSION=array();
		session_destroy();
		echo 13;
		exit();
	}
	else
	{
		$userId=$user['userId'];
		if(isCoCAS($userId)==false)
		{
			if(blockUserByHash($userIdHash,"Unauthorized Attempt to approve poll",$userId.",sh:".$pollIdHash)>0)
			{
				$_SESSION=array();
				session_destroy();
				echo 14;
				exit();
			}
			else
			{
				notifyAdmin("Unauthorized attempt to approve poll",$userId.",sh:".$pollIdHash);
				$_SESSION=array();
				session_destroy();
				echo 13;
				exit();
			}
		}
		if(($poll=getPollFromHash($pollIdHash))==false)
		{
			notifyAdmin("Suspicious pollIdHash in approve poll",$userId.",sh:".$pollIdHash);
			echo 6;
			exit();
		}
		$editPollSQL="UPDATE poll SET approvalStatus = ? WHERE pollIdHash= ?";

		$values[0]=array($pollStatus => 'i');
		$values[1]=array($pollIdHash => 's');
		$result=$conn->insert($editPollSQL,$values);
		$pollOwner=$poll['userId'];
		$pollId=$poll['pollId'];
		if($conn->error==""&&$result==true)
		{
			if($pollStatus==1)
			{
				sendNotification($userId,$pollOwner,13,$pollId,700);
			}
			else
			{
				sendNotification($userId,$pollOwner,14,$pollId,700);
			}
			
			echo 3;
			exit();
		}
		else
		{
			notifyAdmin("Conn.Error".$conn->error."! While approving Poll",$userId);
			echo 12;
			exit();
		}
		
	}
}
?>