<?php
    /*

     * class Cron_IndexController

     * Date: 14-Jan-2015

     * Developer: Niranjan Kumar

     * Modified By: Niranjan Kumar

     * 

     */

class Cron_IndexController extends Zend_Controller_Action {
  
     public function init() {       
         // $this->view->addScriptPath(COMMON_VIEW);
         $this->smsObj = new Meetingschedule_Model_Meetingschedule();
		 $this->QueryDashboardObj = new Dashboard_Model_Managequery();
		 $this->QueryObj = new Managequery_Model_Managequery();
		 $this->db = Zend_Registry::get('db');
         $this->_helper->layout->disableLayout();
		 date_default_timezone_set("Asia/Kolkata");
      }
 
    public function indexAction()
	 {
	$currentDate=strtotime(date("m/d/Y",time())); 	 
    $this->smsObj->AutoSentSMS($currentDate);
	
	
	  $currentDate=date("m/d/Y",time()); 
 $Date = date("m/d/Y",strtotime($currentDate));
 $currentDate= strtotime($Date. ' - '.(1).' days');
 $currentDate= strtotime($Date. ' + '.(1).' days');
	 
	$this->QueryObj->AutoLoadCron($currentDate);
		 
	 $to = "niranjan421989@gmail.com";
     $subject = "Test mail";
     $message = "Hello! This is a simple email message.";
     $from = "support@rapidcollaborate.com";
     $headers = "From:" . $from;
     //mail($to,$subject,$message,$headers);
     //echo "Mail Sent.";
		 
	 }
	 
 
	 public function automationMailAction()
	 {
	 $LeadsData=$this->QueryDashboardObj->getFirstAutoFollowupQuery();	
echo "<pre>";
print_r($LeadsData);
echo "</pre>";	 
		echo count($LeadsData);   die;
	 }
 
   public function webhookAction()
	 {
	 \Postmark\Autoloader::register();
	  $inbound = new \Postmark\Inbound(file_get_contents('php://input'));
	 
	  $subject=$inbound->Subject();
	  $FromEmail=$inbound->FromEmail();
	  $to_email=$inbound->To();
	  $ToFull=$inbound->ToFull();
	  $cc=$inbound->Cc();
	  $CcFull=$inbound->CcFull();
	  $bcc=$inbound->Bcc();
	  $BccFull=$inbound->BccFull();
	  
	  $FromFull=$inbound->FromFull();
	  $FromName=$inbound->FromName();
	  $Date=$inbound->Date();
	  $OriginalRecipient=$inbound->OriginalRecipient();
	  $ReplyTo=$inbound->ReplyTo();
	  $MailboxHash=$inbound->MailboxHash();
	  $Tag=$inbound->Tag();
	  $MessageID=$inbound->MessageID();
	  $TextBody=$inbound->TextBody();
	  $HtmlBody=$inbound->HtmlBody();
	  $StrippedTextReply=$inbound->StrippedTextReply();
	  $refMessageId=$inbound->HeadersRefrence();
	  
	  $toEmails=array();
if($ToFull)
{
	foreach($ToFull as $toooo)
	{
	$toEmails[]=$toooo->Email;	
	}
}
	  
	  $ccEmails=array();
if($CcFull)
{
	foreach($CcFull as $cc)
	{
	$ccEmails[]=$cc->Email;	
	}
}
	  
	  $filesArray=array();
	  foreach($inbound->Attachments() as $attachment)
	  {
	$filesArray[]=$attachment->Name;
	$attachment->ContentType;
	$attachment->ContentLength;
	$attachment->Download('public/UploadFolder/'); //takes directory as first argument
      }

$comments_file="";
if($filesArray)
{
$comments_file=implode("||",$filesArray);
}

if($HtmlBody)
{
$comments=str_replace("\r\n","<br>",$HtmlBody);
}
else if($TextBody)
{
$comments=str_replace("\r\n","<br>",$TextBody);
}
else
{
$comments=str_replace("\r\n","<br>",$StrippedTextReply);
}


if($refMessageId)
{
$commentInfo=$this->QueryObj->getMainMessageID($refMessageId);
}
  
////////////////////////////////////////////////////////////////////////
if($refMessageId!="" and $commentInfo['id'])
{
$assign_id=$commentInfo['ass_query_id'];
$userid=$commentInfo['user_id'];
$QueryInfo = $this->QueryObj->getViewQueryId($assign_id);

 

$this->QueryObj->updateUserQuery(array("open_status"=>0,"open_date"=>time(),'update_status_date'=>time()),$assign_id);


$array=array("ass_query_id"=>$assign_id,
"query_id"=>$QueryInfo['id'],
"user_id"=>$userid,
"comments"=>$comments,
"comments_file"=>$comments_file,
"date"=>strtotime($Date),
"query_status"=>$QueryInfo['update_status'],
"comments_sent_type"=>'client',
"subject"=>$subject,
"from_email"=>$FromEmail,
"FromName"=>$FromName,
"to_email"=>$ToFull[0]->Email,
"cc_email"=>implode(", ",$ccEmails),
"bcc_email"=>($BccFull)?$BccFull[0]->Email:"",
"message_id"=>$MessageID
);
$insert= $this->QueryObj->insertComment($array);
}
else
{
$array=array(
"FromName"=>$FromName,
"FromEmail"=>$FromEmail,
"ToEmail"=>implode(",",$toEmails),
"ToName"=>$ToFull[0]->Name,
"OriginalRecipient"=>$OriginalRecipient,
"MessageID"=>$MessageID,
"Date"=>$Date,
"TextBody"=>$comments,
"Attachments"=>$comments_file,
"Subject"=>$subject,
"cc_email"=>implode(",",$ccEmails),
"bcc_email"=>($BccFull)?$BccFull[0]->Email:"",
"created_date"=>time()
);	
$insert= $this->QueryObj->insertClientExternalmail($array);
}
 
die;
	 }
 
public function gettodaycronAction()
{
$date=date("Y-m-d");	
$result=$this->QueryObj->gettodaycroncheck($date);
echo "<pre>";
print_r($result);
echo "<pre>";
die;
} 
public function dailycronrunAction()
{
$this->getblanktagquery();
die;	
}
public function getblanktagquery()
{
$result=$this->QueryObj->getBlankTagQuery();
$resultArray=array();
foreach($result as $key=>$val)
{
$totalTags=($val['tags'])?explode(",",$val['tags']):array();
$resultArray[]=array(
"assign_id"=>$val['assign_id'],
"user_id"=>$val['user_id'],
"tags"=>$val['tags'],
"totalTags"=>count($totalTags),
"user_status"=>$val['user_status'],
"update_status"=>$val['update_status'],
"user_name"=>$val['user_name'],
"open_status"=>$val['open_status'],
"assign_date"=>($val['assign_date'])?date("d M Y",$val['assign_date']):"",
"update_status_date"=>($val['update_status_date'])?date("d M Y",$val['update_status_date']):"",
"open_date"=>($val['open_date'])?date("d M Y",$val['open_date']):"",
"assign_follow_up_date"=>($val['assign_follow_up_date'])?date("d M Y",$val['assign_follow_up_date']):"",
);
if(count($totalTags) < 2)
{
$update=$this->QueryObj->updateUserQuery(array("open_status"=>1,"open_date"=>time()),$val['assign_id']);	
}
}
echo count($result);
echo "<pre>";
print_r($resultArray);
echo "<pre>";
die;	
}
}

