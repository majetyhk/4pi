<?php

session_start(); 
require_once('../../QOB/qob.php');
require_once('../fetch.php');
require_once('miniClasses/miniPost.php');
	//require_once('../QOB/qobConfig.php');
	//require_once('../QOB/helper.php');
/* $userIdHash=$_SESSION['vj']=hash("sha512","COE12B017".SALT);
	$_SESSION['tn']=hash("sha512",$userIdHash.SALT2);

	//Inputs for testing
	$_POST['_postId']="8122b703cb14aa7fe4370e91dc2757ebd3dc7ace4be8a20642ef42e9f362d10ed57f29cfba40975fa15457fcf2fbab764bb19fb8e9f92e8cab7fa04a19fa47a5";
$_POST['_postContent']="some random new edited text";
$_POST['_share']="EDS,COE,COE11B,";
$_POST['_validity']=15;
$_POST['_subject']="post subject 4"; */
	//Inputs for testing ends  

/*
Code 3: SUCCESS!!
Code 13: SECURITY ALERT!! SUSPICIOUS BEHAVIOUR!!
Code 12: Database ERROR!!
code 14: Suspicious Behaviour and Blocked!
Code 16: Erroneous Entry By USER!!
Code 11: Session Variables unset!!
*/

/*if(!(isset($_SESSION['vj'])&&isset($_SESSION['tn'])))
{
	echo 11;
	exit();
}

//Actual Code Starts
	$conn= new QoB();
	$userIdHash=$_SESSION['vj'];
	//Checking the session varianles. Second Level Protection
	if(hash("sha512",$userIdHash.SALT2)!=$_SESSION['tn'])
	{
		notifyAdmin("Suspicious session variable in editPost",$userIdHash);
		$_SESSION=array();
		session_destroy();
		echo 13;
	}
	else
	{
		//Checking if the user Exists with the given hash! Third Level protection!!
		if(($user=getUserFromHash($userIdHash))==false)
		{
			notifyAdmin("Critical Error!! in editPost!!",$userIdHash);
			$_SESSION=array();
			session_destroy();
			echo 13;
		}
		else
		{

			$postIdHash=$_POST['_postId'];
			if(($post=getPostFromHash($postIdHash))==false)
			{
				//Detected tampered postIdHash
				blockUserByHash($userIdHash,"Messing with PostIdHash!! In editpost");
				$_SESSION=array();
				session_destroy();
				echo 14;
			}
			else
			{
				$postId=$post['postId'];
				$userId=$user['userId'];
				$postUserId=$post['userId'];
				$followers=$post['followers'];
				if($userId==$postUserId)
				{
					$content=trim($_POST['_postContent']);//1
					$rawsharedWith=trim($_POST['_share']);
					if($content==""||$rawsharedWith=="")
					{
						blockUserByHash($userIdHash,"Messing with PostIdHash!! In editpost");
						$_SESSION=array();
						session_destroy();
						echo 14;
						exit();
					}
					
					$splitSharedWith=explode(",",$rawsharedWith);
					$n=count($splitSharedWith);
					$sharedWith="";
					if(stripos($rawsharedWith,"All")===false)
					{
						if($rawsharedWith!=",")
						{
							for($i=0;$i<$n;$i++)
							{
								if($splitSharedWith[$i]!="")
								{
									//echo $i.",".$splitSharedWith[$i]."<br/>";
									$out=newValidateSharedWith($splitSharedWith[$i]);
									if($out=="Invalid")
									{
										echo 16;
										exit();
									}
									else
									{
										//echo $out;
										if($sharedWith=="")
										{
											$sharedWith=$out;
										}
										else
										{
											$sharedWith=$sharedWith.",".$out;
										}
									}
								}
							}//2
						}
						else
						{
							echo 16;
							exit();
						}	
					}
					else
					{
						$sharedWith="All";
					}
					$subject=$_POST['_subject'];//3
					$lifetime=$_POST['_validity'];
					$isPermanent=false;
					$lastUpdated=time();//7+4=11
					if($lifetime==9999)
					{
						$requestPermanence=true;
						$lifetime=180;
					}
					else if($lifetime==1||$lifetime==7||$lifetime==15||$lifetime==30||$lifetime==90||$lifetime==180||$lifetime==360)
					{
						$requestPermanence=false;
					}
					else
					{
						if(blockUserByHash($_SESSION['vj'],"Illegal Post Lifetime in EditPost",$postId))
						{
							$_SESSION=array();
							session_destroy();
							echo 14;
						}
						else
						{
							notifyAdmin("Illegal Post Lifetime In Edit Post".$postId,$userId);
							$_SESSION=array();
							session_destroy();
							echo 13;
						}
					}
					$lifetime=$lifetime*86400+time();
					$filesAttached="";
					$updatePostSQL="UPDATE post SET content = ?,
						sharedWith = ?,
						subject = ?,
						lifetime = ?,
						lastUpdated = ?,
						requestPermanence = ?, filesAttached = ? WHERE postIdHash = ?";
					$values[0]=array($content=>'s');

					$values[1]=array($sharedWith=>'s');
					$values[2]=array($subject=>'s');//subject
					$values[3]=array($lifetime=>'s');//lifetime
					$values[4]=array($lastUpdated=>'s');//lastUpdated

					$values[5]=array($requestPermanence=>'i');//requestPermanence
					$values[6]=array($filesAttached =>'s');//filesAttached
					$values[7]=array($postIdHash=>'s');//postIdhash
					$SQLResponse=$conn->update($updatePostSQL,$values);
					if($conn->error==""&&$SQLResponse==true)
					{
							$postUserName=$user['name'];
							$postValidity=$lifetime;
							$postSubject=$subject;
							$postContent=$content;
							$noOfStars=$post['starCount'];
							$noOfComments=$post['commentCount'];
							$noOfMailTos=$post['mailCount'];
							$postSeenNumber=$post['seenCount'];
							$postCreationTime=$post['timestamp'];
							if(stripos($followers,$userId)===false)
							{
								$followPost=0;
							}
							else
							{
								$followPost=1;
							}
							$comments="";
							$postUserIdHash=$userIdHash;
							$hasStarred=isThere($post['starredBy'],$userId);

							$postObj=new miniPost($postIdHash,$sharedWith,$postValidity,$postUserName,$postSubject,$postContent, 
							$noOfStars,$noOfComments, $noOfMailTos,$postSeenNumber,$postCreationTime,$followPost,$postUserIdHash,$userId,$hasStarred,$comments,1);
							print_r(json_encode($postObj));
					}
					else{
						notifyAdmin("Conn.Error:".$conn->error."!! In EditPost",$userId);
						echo 12;
					}
				}
				else
				{
					blockUserByHash($userIdHash,"Illegal attempt to modify Post!");
					$_SESSION=array();
					session_destroy();
					echo 13;
				}
			}

		}

	}*/
?>
