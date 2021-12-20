<?php
use Postmark\PostmarkClient;
use Postmark\PostmarkAdminClient;
use Postmark\Models\PostmarkException;
use Postmark\Models\PostmarkAttachment;
    /*
     * class Template_IndexController
     * Date: 15-April-2013
     * Developer: Niranjan Singh
     * Modified By: Niranjan Singh
     * 
     */
 class Managequery_IndexController extends Zend_Controller_Action {
    /*
     * Date: 15-April-2013
     * Developer: Niranjan Singh
     * Modified By: Niranjan Singh
     * 
     */
    public function init() {
        parent::init();
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
		//////////////////Flash message session ////////////////
        $this->_s = Zend_Registry::get('session');
        if ($this->_s->message) {
            $this->view->successmessage = $this->_s->message;
            $this->_s->message = NULL;
        }
		date_default_timezone_set("Asia/Kolkata");
		if(!$userInfo)
		{
		$this->_redirect('login');	
		}
		$this->db = Zend_Registry::get('db');
		$date = new Zend_Date();
		/////check User type
         $this->view->leftsection='managequery';
		  $this->followupsettingObj = new Followupsetting_Model_Followupsetting();
          $this->QueryObj = new Managequery_Model_Managequery();
		  $this->userObj=$userObj = new Manageuser_Model_Manageuser();
		  $this->smsObj = new Meetingschedule_Model_Meetingschedule();
		  $this->teamsObj = new Teams_Model_Teams();
		  $this->tagsObj = new Tags_Model_Tags();
		  $this->template = new Template_Model_Template();
		  $this->QuoteObj = new Managequote_Model_Managequote();
		  $this->QueryDashboardObj = new Dashboard_Model_Managequery();
		  
		  $status = new Managequery_Model_Status();
		  $this->view->updateStatus=$status->getAllStatus();
		  $this->postmarkclient = new PostmarkClient("9e93dd6b-7993-47bb-87b6-eed1e8b937dc");
		  
		  $this->view->userInfo = $this->userInfo =$userObj->getUserId($userInfo->id);
		  
      }
     /*
     * To list the Query
     * Date: 14-OCT-2015
     * Developer: Niranjan Singh
     * Modified By: Niranjan Singh
     * 
     */
    public function indexAction()
 	 { 
	 if($this->getRequest()->getParam('ajaxify')==1)
	   {
		 $this->_helper->layout->disableLayout();
	   }
	     $this->view->WebsiteData=$this->QueryObj->fetchAllWebsite();
	      
   		if($this->getRequest()->getParam('Action')=="Delete")
	    {
		 $queryid=$this->getRequest()->getParam('checkid');
 		 $delete=$this->QueryObj->DeleteQuery($queryid);
		  $this->_redirect('managequery');
	    }
		
		$this->_redirect('managequery/userquery');
 
	 }
	 
	 
	public function loadmanagequeryAction()
	{
		 $this->_helper->layout->disableLayout();
 	     $this->view->WebsiteData=$this->QueryObj->fetchAllWebsite();
	     if($this->getRequest()->getParam('searchBtn')!="")
		  {
			  $whereStr ="";
 			
			$this->view->filter_date=$filter_date=$this->getRequest()->getParam('filter_date');
			$dateArray=explode(' - ',$filter_date);
			$fromdate=$dateArray[0]; 
		    $todate=$dateArray[1]; 
 		   
		   $todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));
 			 
 			//$this->view->search_type=$search_type=$this->getRequest()->getParam('search_type');
			$this->view->search_keywords=$search_keywords=$this->getRequest()->getParam('search_keywords');
			$this->view->website=$website=$this->getRequest()->getParam('website'); 
 			 if(isset($this->userInfo->id) and $this->userInfo->user_type=='sub-admin')
			{
			if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
			
			}
			else
			{
			  if($fromdate and $todate)
			{
              if($whereStr!="")
			   {
	         $whereStr .= " AND  created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
			}
			}
			
			if($search_keywords)
			{
		      $keyword = $search_keywords;
              if($whereStr!="")
			   {
				 if(isset($this->userInfo->id) and $this->userInfo->user_type=='admin')
				 {	    
				 $whereStr .= " AND  name LIKE '%".$keyword."%' or email_id LIKE '%".$keyword."%' or phone LIKE '%".$keyword."%'";
				 }
				 else
				 {
				 $whereStr .= " AND query.name LIKE '%".$keyword."%' or query.email_id LIKE '%".$keyword."%' or query.phone LIKE '%".$keyword."%'";	 
				 }
		       }
			 else{
				 if(isset($this->userInfo->id) and $this->userInfo->user_type=='admin')
				 {
					$whereStr .= " name LIKE '%".$keyword."%' or email_id LIKE '%".$keyword."%' or phone LIKE '%".$keyword."%'";
				 }
				 else
				 {
					$whereStr .= " query.name LIKE '%".$keyword."%' or query.email_id LIKE '%".$keyword."%' or query.phone LIKE '%".$keyword."%'"; 
				 }
		     
		         }
		    }
			
			 if($website)
			{
		      $keyword = $website;
              if($whereStr!="")
			   {
	         $whereStr .= " AND qr.website LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " qr.website LIKE '%".$keyword."%'";
		         }
		    }
   		  }
	 
	 
			if(isset($this->userInfo->id) and $this->userInfo->user_type=='sub-admin')
			{
			if($whereStr)
			{
			$this->view->queryArr =$Queryfetch= $this->QueryObj->getAllSubadminSearchQuery($this->userInfo->allocated_to,$whereStr);	
			}
			else
			{
			 $this->view->queryArr =$Queryfetch= $this->QueryObj->getAllSubadminQuery($this->userInfo->allocated_to);
			}
			 }
			else
			{
		    if($whereStr)
			{
			$this->view->queryArr =$Queryfetch= $this->QueryObj->getAllSearchQuery($whereStr);	
			}
			else
			{
			 $this->view->queryArr =$Queryfetch= $this->QueryObj->getAllQuery();
			}
			}
  		
   		if($this->getRequest()->getParam('Action')=="Delete")
	    {
		 $queryid=$this->getRequest()->getParam('checkid');
 		 $delete=$this->QueryObj->DeleteQuery($queryid);
		  $this->_redirect('managequery');
	    }
  		 
     
	 	
	}
       
    public function addqueryAction() 
 	{  
		$this->view->CountryData=$this->QueryObj->fetchAllCountry();
 		$this->view->WebsiteData=$this->QueryObj->fetchAllCategoryWebsite($this->userInfo->category);
		$this->view->serviceData=$this->template->getAllCategoryService($this->userInfo->category);
		$currentDate=date("m/d/Y",time());
		$this->view->todayTask=$this->QueryDashboardObj->UserPendingTodayTask(strtotime($currentDate),$this->userInfo->category);
		 
		  
		 
		$whereStr="";
		if($this->userInfo->user_type!='admin')
		{
		if($whereStr!="")
			   {
	         $whereStr .= " AND tm.id in (".$this->userInfo->team_id.")";
		       }
			 else{
		     $whereStr .= " tm.id in (".$this->userInfo->team_id.")";
		         }	
		}
		 
	   $this->view->teamsArr = $this->teamsObj->getAllTeams($whereStr);
		
		$this->view->tagsArr=$this->tagsObj->getAllCategoryTags($this->userInfo->category);
		
		if(isset($this->userInfo->id) and $this->userInfo->user_type=='admin')
		{
		$this->view->ActiveUsers=$this->userObj->fetchActiveUsers();
		}
		else
		{
		$this->view->ActiveUsers =$this->userObj->getSubadminAllUsers($this->userInfo->allocated_to); 
		}
		
		$this->view->priorityArr =$userfetch= $this->followupsettingObj->getAllPriority();
		
  			if($this->getRequest()->isPost('submitBtn') and $this->getRequest()->getParam('submitBtn')!="")
 			{
			  $query_code=$this->getRequest()->getParam('query_code');
 			  $name=$this->getRequest()->getParam('name');
 			  $email_id= $this->getRequest()->getParam('email_id');
 			  $phone=$this->getRequest()->getParam('phone');
 			  $rec_date=$this->getRequest()->getParam('date');
 			  $location=$this->getRequest()->getParam('location');
 			  $city=$this->getRequest()->getParam('city');
 			  
			  
			  
			  $alt_email_id=$this->getRequest()->getParam('alt_email_id');
 			  $alt_contact_no= $this->getRequest()->getParam('alt_contact_no');
  			  $company_name=$this->getRequest()->getParam('company_name');
 			  
  			  $website=$this->getRequest()->getParam('website');
			  $other_website=$this->getRequest()->getParam('other_website');
			  $area_of_study=$this->getRequest()->getParam('area_of_study');
  			  $requirement= $this->getRequest()->getParam('requirement');
			  $other_requirement=$this->getRequest()->getParam('other_requirement');
  			  $allocated_to=$this->getRequest()->getParam('allocated_to');
			  $allocated_to_name=trim($this->getRequest()->getParam('allocated_to_name'));
  			  $profile_id=$this->getRequest()->getParam('profile_id'); 
  			  $team_id=$this->getRequest()->getParam('team_id');
			  if($this->getRequest()->getParam('tags'))
			  {
  			  $tags=implode(",",$this->getRequest()->getParam('tags')); 
			  }
			  else
			  {
			  $tags="";  
			  }
  			  $entrytype=$this->getRequest()->getParam('entrytype');
			  
   			  $priorityArray=explode('|',$this->getRequest()->getParam('priority'));
			  
			  $websiteInfo=$this->template->getWebsiteInfo($website);
		 
			  $serviceInfo=$this->template->getServiceInfo($requirement);
			  
			  $subject='Enquiry at '.$websiteInfo['website'].' for  '.$serviceInfo['name'];
		 
 			  if($this->getRequest()->getParam('no_day'))
			  {
 			  $priority=$priorityArray[0];
			  $contact_by=$priorityArray[2];
			  $no_day=$this->getRequest()->getParam('no_day');
			  }
			  else
			  {
			  $priority=$priorityArray[0];
			  $no_day=$priorityArray[1]; 
			  $contact_by=$priorityArray[2];
			  }
			  $no_day=explode(',',$no_day);
			  $contact_by=explode(',',$contact_by);
			  
  			  $academic_level=$this->getRequest()->getParam('academic_level');
  			  $follow_up_date=$this->getRequest()->getParam('follow_up_date');
  			  $remarks=$this->getRequest()->getParam('remarks');
			  
 			  
			  
			  
 			  $Date = date("m/d/Y",strtotime($rec_date));
			
               //$follow_up_date= strtotime($Date. ' + '.($no_day[0]).' days');
               $follow_up_date= strtotime($rec_date);
			   
			   /////////////////////////////////////////////////////////////////////
			   $locatie = 'public/UploadFolder/'; 
            $time=time();
  	        $upload_file=array();
  		 	$count= count($_FILES['upload_file']['name']); 
			if ($count!="" && $count > 0) {
			for($i=0;$i<$count;$i++){
				$doc.=$_FILES['upload_file']['name'][$i].',';
				$folder = $locatie.time().$i.'_'.basename(preg_replace("/[^a-zA-Z0-9.]/","",$_FILES['upload_file']['name'][$i])) ; 
 				$base_name=basename($_FILES['upload_file']['name'][$i]);
			if(move_uploaded_file($_FILES['upload_file']['tmp_name'][$i], $folder)){
				$j=$i+1;
 				$upload_file['filename'][]=$base_name;
				$upload_file['file_path'][]=$folder;
 
   			 }
			}
            } 
			 
			   /////////////////////////////////////////////////////////////////////
   			  $this->view->errors = '';
			  
			  $DataCheck=$this->QueryObj->CheckExistQuery($name,$email_id,$website);
			  if($website == 8)
			  {
				sendwhatsappmsg_newapi($phone,$allocated_to_name);	
			  }
    if($DataCheck['id'])
	 {  
	$queryid=$DataCheck['id'];
	$assignUserInfo=$this->QueryObj->FetchQueryActiveAllocatedUser($queryid);
	//print_r($assignUserInfo);die;
 	$created_on=$DataCheck->created_on;
	 
//Get the current timestamp.
$now = time();
//Calculate the difference.
$difference = $now - $created_on;
//Convert seconds into days.
$days = floor($difference / (60*60*24) );
 
	if($days < 90)
	{
	$this->view->exist_name=$exist_name= $name.'-2';
$this->view->errors ='<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>Query already exists on the website with this email. Allocated to <strong>'.$assignUserInfo['user_name'].'</strong> on <strong>'.date("d M Y",$assignUserInfo['assign_date']).'</strong></div>';	
	}
	else
	{
////////////////////////////////////////////////////////////////////////////////////////	
$userInfo=$this->userObj->getUserId($allocated_to);


$array=array('phone'=>$phone,'date'=>$rec_date,'location'=>$location,'city'=>$city,'subject'=>$subject,'alt_email_id'=>$alt_email_id,'alt_contact_no'=>$alt_contact_no,'company_name'=>$company_name,'area_of_study'=>$area_of_study,'requirement'=>$requirement,'other_requirement'=>$other_requirement,'priority'=>$priority,'academic_level'=>$academic_level,'follow_up_date'=>$follow_up_date,'contact_by'=>$contact_by[0],'remarks'=>$remarks,'entrytype'=>$entrytype,'created_on'=>time());
			   
 			 $this->QueryObj->updateQuery($array,$queryid);

			 /*if($allocated_to==$assignUserInfo['user_id'])
			 {*/
			    $array2=array('user_id'=>$allocated_to,'update_status'=>1,"user_status"=>1,'website_id'=>$website,'other_website'=>$other_website,'profile_id'=>$profile_id,'team_id'=>$team_id,'tags'=>$tags,'assign_date'=>time(),"open_date"=>time(),"open_status"=>0,'update_status_date'=>time(),'assign_priority'=>$priority,'assign_follow_up_date'=>$follow_up_date,'assign_contact_by'=>$contact_by[0]);  
			   
			    $this->QueryObj->updateExistUserStatusQuery($array2,$assignUserInfo['assign_id']);
			 /*}*/
			 
	//////////////////////////////////////////////////////////////////
					for($i=0;$i< count($no_day);$i++)
					{ 
					if($i==0)
					{
					$status=1;	
					}
					else
					{
					$status=0;	
					}
					 $Date_current1 = date("d-m-Y",time());
                     $follow_up_date1= strtotime($Date_current1);
					 
                    $this->db->delete('tbl_followup_date','assign_id ='.$assignUserInfo['assign_id']); 
					$array2=array('assign_id'=>$assignUserInfo['assign_id'],'user_id'=>$allocated_to,'followup_day'=>$follow_up_date1,"query_id"=>$queryid,'contact_by'=>$contact_by[$i],'status'=>$status);	
				 
					  $this->QueryObj->addUserQueryFolloup($array2);
					}
					
		$arrayAct=array("user_id"=>$this->userInfo->id,'user_name'=>$this->userInfo->name,'message'=>'updated the query','action_date'=>time(),'query_id'=>$queryid,'query_assign_id'=>$assignUserInfo['assign_id']);
				 $this->QueryObj->insertActionHistory($arrayAct);
					
	////////////////////////////////////////////////////////////////////////////////////////////		   
		$this->_s->message ='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>Query already exists on the website with this email. <strong>But Updated Now</strong>  Allocated to <strong>'.$userInfo['name'].'</strong> on <strong>'.date("d M Y").'</strong></div>';
          $this->_redirect('managequery/addquery');	   
			  
////////////////////////////////////////////////////////////////////////////////////////	
	}
 
	 				
	 }
 			 
  			  if($this->view->errors=='')
 			   {
  			   $array=array('query_code'=>$query_code,'name'=>$name,'email_id'=>$email_id,'phone'=>$phone,'date'=>$rec_date,'location'=>$location,'city'=>$city,'subject'=>$subject,'alt_email_id'=>$alt_email_id,'alt_contact_no'=>$alt_contact_no,'company_name'=>$company_name,'area_of_study'=>$area_of_study,'requirement'=>$requirement,'other_requirement'=>$other_requirement,'priority'=>$priority,'academic_level'=>$academic_level,'follow_up_date'=>$follow_up_date,'contact_by'=>$contact_by[0],'remarks'=>$remarks,'entrytype'=>$entrytype,'created_on'=>time());	
             
			   $insert= $this->QueryObj->addQuery($array);
			   
			   //foreach($allocated_to as $user)
			   //{
				   $userInfo=$this->userObj->getUserId($allocated_to);
                    				   
				    if($userInfo['sms_notify']=="on")
					 {
					$this->QueryObj->SendMail($userInfo['username'],$userInfo['email_id'],$name,$email_id,$phone,$subject);
					$this->QueryObj->SendSMS($userInfo['username'],$userInfo['mobile_no'],$name,$email_id,$phone); 
					 }
				  if($userInfo['whatsaap_notification']=="on")
					 {
						 if($name!="" and $email_id!="")
						 {
					$this->QueryObj->SendWhatsAppSMS($userInfo['mobile_no'],$name,$email_id, $phone, $website);
						 }					
					 } 
				   
				    
				$array1=array('query_id'=>$insert,'user_id'=>$allocated_to,'website_id'=>$website,'other_website'=>$other_website,'profile_id'=>$profile_id,'team_id'=>$team_id,'tags'=>$tags,'assign_date'=>time(),"open_date"=>time(),"open_status"=>0,'update_status_date'=>time(),'assign_priority'=>$priority,'assign_follow_up_date'=>$follow_up_date,'assign_contact_by'=>$contact_by[0]);
				$insert1= $this->QueryObj->addUserQuery($array1);  
				
				for($i=0;$i< count($no_day);$i++)
				{ 
				if($i==0)
				{
				$status=1;	
				}
				else
				{
				$status=0;	
				}
				
				 $Date1 = date("d-m-Y",strtotime($rec_date));
                 $follow_up_date1= strtotime($Date1. ' + '.$no_day[$i].' days');
				
				 $array2=array('assign_id'=>$insert1,'user_id'=>$allocated_to,'followup_day'=>$follow_up_date1,"query_id"=>$insert,'contact_by'=>$contact_by[$i],'status'=>$status);	
				 $insert2= $this->QueryObj->addUserQueryFolloup($array2);
				}
				 $filename=$upload_file['filename'];
				$file_path=$upload_file['file_path'];
				for($i=0; $i<count($upload_file['filename']); $i++)
				{
				$array_F=array("query_id"=>$insert,"filename"=>$filename[$i],"file_path"=>$file_path[$i]);
				$insertFile=$this->QueryObj->insertQueryFiles($array_F);
				}
				 
				$arrayAct=array("user_id"=>$this->userInfo->id,'user_name'=>$this->userInfo->name,'message'=>'created query and assigned to '.$userInfo['username'],'action_date'=>time(),'query_id'=>$insert,'query_assign_id'=>$insert1);
				 $this->QueryObj->insertActionHistory($arrayAct);
				
				
 			  // }
				///////////////////////Mail////////////////////////////
				$userprInfo=$this->userObj->getUserProfileInfo($profile_id);
		
				$profile_name	=	explode('-',$userprInfo['profile_name']);			  
				$profile_email	=	$userprInfo['website_email'];
				$profile_signature	=	$userprInfo['signature'];
				
				$crm_name 		= trim($profile_name[0]);
				$crm_company 	= trim($profile_name[1]);
				
				if($profile_email!="" && $email_id!="")
				{
					$subject = 'Your Relationship Manager at '.$crm_company;
					$msg = 'I have been assigned your relationship manager. I will be contacting you through the day today to take discussions ahead.';
					
					if($userprInfo['website'] == 8)		// FiveVidya
					{
						$subject = 'FiveVidya <> PhD Help ';
						$msg = 'At FiveVidya, we are serious about PhD help and I will be your point of contact at FiveVidya. Please feel free to call me or email about your requirements.';
					}
					elseif($userprInfo['website'] == 11)		//PhDGuidance
					{
						$subject = 'Let\'s Begin PhD Research Guidance';
						$msg = 'Thanks for contacting PhD Guidance. We are one of the oldest and most trusted PhD consultation agencies in the country. We started our services in 2006 and till date have completed consultation of over 5000 PhD research scholars.<br/><br/>

						Please share your requirement details and I will proceed accordingly.';
					}
					elseif($userprInfo['website'] == 2)		//ThesisIndia
					{
						$subject = 'EMail from Thesis India';
						$msg = 'Your enquiry is well received at Thesis India and I shall be reaching out to you soon for a discussion about your requirement.<br/><br/>
						
						Let me know your preferred time and mode for discussion.';
					}
					elseif($userprInfo['website'] == 1)		//DissertationIndia
					{
						$subject = 'EMail from Dissertation India';
						$msg = 'Your enquiry is well received at Dissertation India and I shall be reaching out to you soon for a discussion about your requirement.<br/><br/>
						
						Let me know your preferred time and mode for discussion.';
					}
				
					$body='Hi '.$name.'<br/><br/>

					'.$msg.'<br/><br/>
					
					'.$profile_signature.'<br/>';

					$message = array(
						'html' => $body,
						'subject' => $subject,
						'from_email' => $profile_email,
						'from_name' => $crm_name,
						'to' => array(
							array(
								'email'=> $email_id,
								'name' => $name, 
								'type' => 'to'
							)
						)
					);
                     $cc="";
					 $bcc="";
					//SendMandrilMail($message);
					$replyEmail=NULL;
					$headers['ref_id']=$insert1;
					$attachment="";
					$sendResult = $this->postmarkclient->sendEmail($profile_email, $email_id,$replyEmail,$cc,$bcc, $subject,$body,NULL,TRUE,TRUE,$headers,$attachment);
					
					
				}
				///////////////////////////////////////////////////////   
 			    
			   $this->_s->message ='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>Query Added Successfuly.</div>';
          $this->_redirect('managequery/addquery');
  			  
			  }
 			  $this->view->params = $this->_getAllParams();

			}
 }

	public function useraddqueryAction()
	{ 
	}

	public function editqueryAction()
	{
		
		$this->view->CountryData=$this->QueryObj->fetchAllCountry();
 		
		$this->view->teamsArr = $this->teamsObj->getAllTeams();
		
		$this->view->tagsArr=$this->tagsObj->getAllCategoryTags($this->userInfo->category);
		$this->view->WebsiteData=$this->QueryObj->fetchAllCategoryWebsite($this->userInfo->category);
		$this->view->serviceData=$this->template->getAllCategoryService($this->userInfo->category);
		 
  	if(isset($this->userInfo->id) and $this->userInfo->user_type=='admin')
		{
		$this->view->ActiveUsers=$this->userObj->fetchActiveUsers();
		
		$this->view->WebsiteData=$this->QueryObj->fetchAllWebsite();
		$this->view->serviceData=$this->template->getAllService();
		$this->view->tagsArr = $this->tagsObj->getAllTags();
		}
		else if(isset($this->userInfo->id) and $this->userInfo->user_type=='sub-admin')
		{
		$this->view->ActiveUsers =$this->userObj->getSubadminAllUsers($this->userInfo->allocated_to); 
		}
		else
		{
		$this->view->ActiveUsers =$this->userObj->getSubadminAllUsers($this->userInfo->id);	
		}
		
	$this->view->priorityArr =$userfetch= $this->followupsettingObj->getAllPriority();
   if($this->getRequest()->getParam('queryid'))
 	    {
   		 $assign_queryid =$this->getRequest()->getParam('queryid');
 		 $this->view->QueryInfo=$QueryInfo = $this->QueryObj->getEditQueryInfo($assign_queryid);
		 //echo "<pre>";
		 //print_r($this->view->QueryInfo);
		 //die;
		 $queryid=$QueryInfo['id'];
 		 $QueryUsers=$this->QueryObj->fetchQueryUsers($queryid); 
		 $this->view->QueryFilesData= $this->QueryObj->fetchQueryFiles($queryid);
		 /*$user_id=array();
		  foreach($QueryUsers as $querydata)
		  {
		   $user_id[]=  $querydata['user_id'];
		  }
		  $this->view->queryUser=$user_id;
		  */
  	    }
 			if($this->getRequest()->isPost('submitBtn') and $this->getRequest()->getParam('submitBtn')!="")
 			{
			  $query_code=$this->getRequest()->getParam('query_code');
  			  $name=$this->getRequest()->getParam('name');
 			  $email_id= $this->getRequest()->getParam('email_id');
 			  $phone=$this->getRequest()->getParam('phone');
 			  $date=$this->getRequest()->getParam('date');
 			  $location=$this->getRequest()->getParam('location');
 			  $city=$this->getRequest()->getParam('city');
 			  
			   
			  $alt_email_id=$this->getRequest()->getParam('alt_email_id');
 			  $alt_contact_no= $this->getRequest()->getParam('alt_contact_no');
 			  $company_name=$this->getRequest()->getParam('company_name');
			  
			  
  			  $website=$this->getRequest()->getParam('website');
			  $other_website=$this->getRequest()->getParam('other_website');
			  $area_of_study=$this->getRequest()->getParam('area_of_study');
 			  $requirement= $this->getRequest()->getParam('requirement');
 			  $other_requirement=$this->getRequest()->getParam('other_requirement');
 			  $allocated_to=$this->getRequest()->getParam('allocated_to');
 			  $profile_id=$this->getRequest()->getParam('profile_id');
  			  $team_id=$this->getRequest()->getParam('team_id');
  			  if($this->getRequest()->getParam('tags'))
			  {
  			  $tags=implode(",",$this->getRequest()->getParam('tags')); 
			  }
			  else
			  {
			  $tags="";  
			  }
  			  $entrytype=$this->getRequest()->getParam('entrytype');
  			   
			  
			  //////////////////////////////////////////////////
			  $priorityArray=explode('|',$this->getRequest()->getParam('priority'));
			  
 			  if($this->getRequest()->getParam('no_day'))
			  {
 			  $priority=$priorityArray[0];
			  $contact_by=$priorityArray[2];
			  $no_day=$this->getRequest()->getParam('no_day');
			  }
			  else
			  {
			  $priority=$priorityArray[0];
			  $no_day=$priorityArray[1]; 
			  $contact_by=$priorityArray[2];
			  }
			  $no_day=explode(',',$no_day);
			  $contact_by=explode(',',$contact_by);
			  ////////////////////////////////////////////////////
			  
			  $deadline=$this->getRequest()->getParam('deadline');
 			  $word_count=$this->getRequest()->getParam('word_count');
			  $academic_level=$this->getRequest()->getParam('academic_level');
      		  $remarks=$this->getRequest()->getParam('remarks');
 			  
 			 
			  
  			  
			  
  		       $Date = date("d-m-Y",strtotime($date));
			   
			   $date=
			   $Date_current = date("d-m-Y",time());
               $follow_up_date= strtotime($Date_current. ' + '.($no_day[0]).' days');
			   
			   
			   
			   /////////////////////////////////////////////////////////////////////
			 /*   $locatie = 'public/UploadFolder/'; 
            $time=time();
  	        $upload_file=array();
  		 	$count= count($_FILES['upload_file']['name']); 
			if ($count!="" && $count > 0) {
			for($i=0;$i<$count;$i++){
				$doc.=$_FILES['upload_file']['name'][$i].',';
				$folder = $locatie.time().$i.'_'.basename(preg_replace("/[^a-zA-Z0-9.]/","",$_FILES['upload_file']['name'][$i])) ; 
 				$base_name=basename($_FILES['upload_file']['name'][$i]);
			if(move_uploaded_file($_FILES['upload_file']['tmp_name'][$i], $folder)){
				$j=$i+1;
 				$upload_file['filename'][]=$base_name;
				$upload_file['file_path'][]=$folder;
 
   			 }
			}
            }*/
			 
			   /////////////////////////////////////////////////////////////////////
			  
    		  $this->view->errors = '';
   			 if($this->view->errors=='')
 			  {				
  			   $array=array('query_code'=>$query_code,'name'=>$name,'email_id'=>$email_id,'phone'=>$phone,'date'=>$date,'location'=>$location,'city'=>$city,'alt_email_id'=>$alt_email_id,'alt_contact_no'=>$alt_contact_no,'company_name'=>$company_name,'area_of_study'=>$area_of_study,'requirement'=>$requirement,'other_requirement'=>$other_requirement,'priority'=>$priority,'academic_level'=>$academic_level,'remarks'=>$remarks,'entrytype'=>$entrytype);	
			    
 			   $insert= $this->QueryObj->updateQuery($array,$queryid);
			   $array2=array('assign_priority'=>$priority,'user_id'=>$allocated_to,'website_id'=>$website,'other_website'=>$other_website,'profile_id'=>$profile_id,'team_id'=>$team_id,'tags'=>$tags);
			   $this->QueryObj->updateExistUserStatusQuery($array2,$assign_queryid);
			   
					
				/*$filename=$upload_file['filename'];
				$file_path=$upload_file['file_path'];
				for($i=0; $i<count($upload_file['filename']); $i++)
				{
				$array_F=array("query_id"=>$queryid,"filename"=>$filename[$i],"file_path"=>$file_path[$i]);
				$insertFile=$this->QueryObj->insertQueryFiles($array_F);
				}
				*/
				
				$arrayAct=array("user_id"=>$this->userInfo->id,'user_name'=>$this->userInfo->name,'message'=>'updated the query','action_date'=>time(),'query_id'=>$queryid,'query_assign_id'=>$assign_queryid);
				 $this->QueryObj->insertActionHistory($arrayAct);
				 
   			///////////////////////////////////////////////////////   
 			  
			   $this->_s->message='<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="icon fa fa-check"></i> Query information has been updated successfully.</div>'; 
			   $this->_redirect('managequery/userquery'); 
         
			  }
 			  $this->view->params = $this->_getAllParams();
 			}
 	}
	
	public function exportAction()
	{
		$this->_helper->layout->disableLayout();
		$checkid=$this->_getParam('checkid');
		$this->view->queryArr =$Queryfetch= $this->QueryObj->getAllCheckedExportQuery($checkid);
		$file="Query_".date("d-m-Y",time()).".xls";
header('Content-Type: text/html');
header("Content-type: application/x-msexcel"); //tried adding  charset='utf-8' into header
header("Content-Disposition: attachment; filename=$file");
 
	}

 public function exportalldataAction()
 {
	$this->_helper->layout->disableLayout();
		
 $whereStr="";
	  $myTeamUserData=array();
	  $tags=array();
         
	      $teamid=$this->getRequest()->getParam('search_team_id');
	      if($teamid)
			{
			$myTeamUserData= $this->userObj->getAllUsersMyTeams($teamid);	
			}
            $this->view->filter_date=$filter_date=$this->getRequest()->getParam('filter_date');
			$dateArray=explode(' - ',$filter_date);
			$fromdate=$dateArray[0]; 
		    $todate=$dateArray[1];
			$todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));
			$this->view->search_keywords=$search_keywords=trim($this->getRequest()->getParam('search_keywords'));
			$this->view->website=$website=$this->getRequest()->getParam('website'); 
			$this->view->update_status=$update_status=$this->getRequest()->getParam('update_status');
			$this->view->transfer_type=$transfer_type=$this->getRequest()->getParam('transfer_type');
			
        if($this->getRequest()->getParam('tags'))
			{
 			 $tags=$this->getRequest()->getParam('tags');
			}			
			
			if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  ass_qr.assign_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " ass_qr.assign_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
			
			if($website)
			{
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.website_id IN (".implode(",",$website).")";
		       }
			 else{
		     $whereStr .= " ass_qr.website_id IN (".implode(",",$website).")";
		         }
		    }
			
			if($update_status)
			{
 		      $keyword = $update_status;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.update_status='".$keyword."'";
		       }
			 else{
		     $whereStr .= " ass_qr.update_status='".$keyword."'";
		         }
		    }
			
			if($transfer_type)
			{
				if($transfer_type=='Fresh')
				{
				$transfer_type=0;	
				}
				
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.transfer_type='".$transfer_type."'";
		       }
			 else{
		     $whereStr .= " ass_qr.transfer_type='".$transfer_type."'";
		         }
		    }
			
			 $this->view->QueryData=$this->QueryObj->FetchAllExportQuery($whereStr,$myTeamUserData,$search_keywords,$tags);
			 
			 $file="Query_".date("d-m-Y",time()).".xls";
header('Content-Type: text/html');
header("Content-type: application/x-msexcel"); //tried adding  charset='utf-8' into header
header("Content-Disposition: attachment; filename=$file");

		
 } 
 public function exportemailcampaigndataAction()
 {
$this->_helper->layout->disableLayout();
		
 $whereStr="";
	  $myTeamUserData=array();
	  $tags=array();
         
	      $teamid=$this->getRequest()->getParam('search_team_id');
	      if($teamid)
			{
			$myTeamUserData= $this->userObj->getAllUsersMyTeams($teamid);	
			}
            $this->view->filter_date=$filter_date=$this->getRequest()->getParam('filter_date');
			$dateArray=explode(' - ',$filter_date);
			$fromdate=$dateArray[0]; 
		    $todate=$dateArray[1];
			$todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));
			$this->view->search_keywords=$search_keywords=trim($this->getRequest()->getParam('search_keywords'));
			$this->view->website=$website=$this->getRequest()->getParam('website'); 
			$this->view->update_status=$update_status=$this->getRequest()->getParam('update_status');
			$this->view->transfer_type=$transfer_type=$this->getRequest()->getParam('transfer_type');
			$this->view->userid=$userid=$this->getRequest()->getParam('user_id');
			
			
        if($this->getRequest()->getParam('tags'))
			{
 			 $tags=$this->getRequest()->getParam('tags');
			}	

         if($userid)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id = '".$userid."'";
		       }
			 else{
		     $whereStr .= "ass_qr.user_id = '".$userid."'";
		         }
 			}			
			
			if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  ass_qr.assign_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " ass_qr.assign_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
			
			if($website)
			{
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.website_id IN (".implode(",",$website).")";
		       }
			 else{
		     $whereStr .= " ass_qr.website_id IN (".implode(",",$website).")";
		         }
		    }
			
			if($update_status)
			{
 		      $keyword = $update_status;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.update_status='".$keyword."'";
		       }
			 else{
		     $whereStr .= " ass_qr.update_status='".$keyword."'";
		         }
		    }
			
			if($transfer_type)
			{
				if($transfer_type=='Fresh')
				{
				$transfer_type=0;	
				}
				
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.transfer_type='".$transfer_type."'";
		       }
			 else{
		     $whereStr .= " ass_qr.transfer_type='".$transfer_type."'";
		         }
		    }
 			 $this->view->QueryData=$this->QueryObj->FetchAllExportQuery($whereStr,$myTeamUserData,$search_keywords,$tags);
			 
$file="Query_Email_Campaign_".date("d-m-Y",time()).".xls";
header('Content-Type: text/html');
header("Content-type: application/x-msexcel"); //tried adding  charset='utf-8' into header
header("Content-Disposition: attachment; filename=$file");
	 
 }
public function queryexportAction()
{
		$this->_helper->layout->disableLayout();
		$checkid=$this->_getParam('checkid');
 		$this->view->queryArr =$Queryfetch= $this->QueryObj->getAllCheckedExportMainQuery($checkid);
		//print_r($Queryfetch);die;
 		$file="Query_".date("d-m-Y",time()).".xls";
header('Content-Type: text/html');
header("Content-type: application/x-msexcel"); //tried adding  charset='utf-8' into header
header("Content-Disposition: attachment; filename=$file");
  
		
}
 
	public function viewdetailsAction()
	{  if($this->getRequest()->getParam('ajaxify')==1)
	{
		 $this->_helper->layout->disableLayout();
	}
      if($this->getRequest()->getParam('queryid'))
 	   {
 		 $queryid =$this->getRequest()->getParam('queryid');
 		 $this->view->QueryInfo=$QueryInfo = $this->QueryObj->getViewQueryId($queryid);
		 
		 if($this->getRequest()->getParam('searchdate'))
		 {
	     $this->view->searchdate=$searchdate=$this->getRequest()->getParam('searchdate');
		 $this->view->CommentInfo = $this->QueryObj->GetSearchComents($queryid,strtotime($searchdate));
		 }
		 else
		 {
		 $this->view->CommentInfo = $this->QueryObj->GetComents($queryid);	 
		 }
  	  }
	 
	/* if($this->getRequest()->getParam('queryid')!="" and $this->getRequest()->getParam('action_taken'))
		{
		 $queryid=$this->getRequest()->getParam('queryid');
 		 $status=$this->getRequest()->getParam('status');
		 if($status==1)
 		 {
 		  $statusvalue=0;	
 		 }
 		 else
 		 {
 		   $statusvalue=1;
 		 }
		 $array=array('action_taken'=>$statusvalue);
 		  $update= $this->QueryObj->updateUserQuery($array,$queryid);
		  
		  $this->_redirect('managequery/index/viewdetails/queryid/'.$queryid); 
		 	
		}
		
		
		if($this->getRequest()->getParam('queryid')!="" and $this->getRequest()->getParam('converted'))
		{
		 $queryid=$this->getRequest()->getParam('queryid');
 		 $status=$this->getRequest()->getParam('status');
		 if($status==1)
 		 {
 		  $statusvalue=0;	
 		 }
 		 else
 		 {
 		   $statusvalue=1;
 		 }
		 $array=array('converted'=>$statusvalue);
 		 $update= $this->QueryObj->updateUserQuery($array,$queryid);
		  
		 $template='Thanks for choosing eMarketz as your success partner.';
		 $sent=$this->smsObj->SentSMS($template,$QueryInfo['phone']);
 		 $this->_redirect('managequery/index/viewdetails/queryid/'.$queryid);
		 	
		}*/
 
 
  
 
		if($this->getRequest()->isPost('updateBtn') and $this->getRequest()->getParam('updateBtn')!="")
		{
			  $action_taken=$this->getRequest()->getParam('action_taken');
			  if($action_taken=="")
			  {
				 $action_taken=0;  
			  }
			  $converted=$this->getRequest()->getParam('converted');
			  if($converted=="")
			  {
				 $converted=0;  
			  }
			  
			   $update_status=$this->getRequest()->getParam('update_status');
			  
			  $array=array('action_taken'=>$action_taken,'converted'=>$converted,'update_status'=>$update_status);
 		     $update= $this->QueryObj->updateUserQuery($array,$queryid);
 			 
		 $comments=$this->getRequest()->getParam('comments');
		 if($comments)
		 {
		 $array=array("ass_query_id"=>$queryid,"query_id"=>$QueryInfo['id'],"user_id"=>$this->userInfo->id,"comments"=>$comments,"date"=>time());
 		 $update= $this->QueryObj->insertComment($array);
		 }
		 $this->_redirect('managequery/index/viewdetails/queryid/'.$queryid); 
		//  $this->_redirect('managequery/allnotification');
		 
		}
	 
 	if($this->getRequest()->getParam('queryid')!="" and $this->getRequest()->getParam('Anoti')==1)
		{
		 $queryid=$this->getRequest()->getParam('queryid');
 		  $array=array('status'=>1);
 		 $update= $this->QueryObj->updateCommentStatus($array,$queryid,$this->userInfo->id);
  		}
		
		if($this->getRequest()->getParam('queryid')!="" and $this->getRequest()->getParam('Unoti')==1)
		{
		 $queryid=$this->getRequest()->getParam('queryid');
 		 $array=array('status'=>1);
 		 $update= $this->QueryObj->updateCommentStatus($array,$queryid,$this->userInfo->id);
  		}
	 
	
	}
	
 
public function userqueryAction()
{   if($this->getRequest()->getParam('ajaxify')==1)
	{
		 $this->_helper->layout->disableLayout();
	}
 
      $myTeamUserData= $this->userObj->getAllUsersMyTeams($this->userInfo->team_id);
	  $this->view->tagsArr=$this->tagsObj->getAllCategoryTags($this->userInfo->category);
	  $this->view->WebsiteData=$this->QueryObj->fetchAllCategoryWebsite($this->userInfo->category);
	  $this->view->serviceData=$this->template->getAllCategoryService($this->userInfo->category);
	  
	  if($this->getRequest()->getParam("tag"))
		 {
			 $this->view->tag=$this->getRequest()->getParam("tag"); 
		 }
      
	  $whereStr="";
      if(isset($this->userInfo->id) and $this->userInfo->user_type=='admin')
      {
		if($this->getRequest()->getParam("userid"))
		 {
		  //$this->view->userid=$userid="/userid/".$this->getRequest()->getParam("userid");
		  $this->view->userid=$this->getRequest()->getParam("userid");
			//$this->view->QueryUsers=$this->QueryObj->FetchUserQuery($this->getRequest()->getParam("userid")); 
		 }
		 else
		 {
		   if($whereStr!="")
			   {
	         $whereStr .= " AND user_type = 'user'";
		       }
			 else{
		     $whereStr .= " user_type = 'user'";
		         }	 
		 
		   
		 }
		 $this->view->userArr =$userfetch= $this->userObj->getAllUsers($whereStr);
		 
		$this->view->WebsiteData=$this->QueryObj->fetchAllWebsite();
		$this->view->serviceData=$this->template->getAllService();
		$this->view->tagsArr = $this->tagsObj->getAllTags();
	 }
	   else
	   { // if($this->userInfo->user_type=='sub-admin' or $this->userInfo->user_type=='Data Manager')
 		 $this->view->userArr =$userfetch= $this->userObj->getSubadminAllUsers(implode(",",$myTeamUserData)); 
        		
 	   }
	   
	   $whereStr="";
		if($this->userInfo->user_type!='admin')
		{
		if($whereStr!="")
			   {
	         $whereStr .= " AND tm.id in (".$this->userInfo->team_id.")";
		       }
			 else{
		     $whereStr .= " tm.id in (".$this->userInfo->team_id.")";
		         }	
		}
		 
	   $this->view->teamsArr = $this->teamsObj->getAllTeams($whereStr);
	   
 	 	 
 		 
		
		if($this->getRequest()->getParam('Action')=="Delete")
	    {
		 $queryid=$this->getRequest()->getParam('checkid');
 		 $delete=$this->QueryObj->DeleteAssignQuery($queryid);
		 $this->_s->message='<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="icon fa fa-check"></i> The query is deleted successfully.</div>'; 
		  $this->_redirect('managequery/userquery');
	    }
		
		
  
}

public function loaduserqueryAction()
{
  $this->_helper->layout->disableLayout();
  $this->view->WebsiteData=$this->QueryObj->fetchAllWebsite();
	  
	  $whereStr="";
	  $myTeamUserData=array();
	  $tags=array();
      if(isset($this->userInfo->id) and $this->userInfo->user_type=='admin')
      { 
		if($this->getRequest()->getParam("userid"))
		 {
		  $this->view->userid=$userid="/userid/".$this->getRequest()->getParam("userid");
			//$this->view->QueryUsers=$this->QueryObj->FetchUserQuery($this->getRequest()->getParam("userid")); 
		 }
		 else
		 {
		 $this->view->userArr =$userfetch= $this->userObj->getAllUsers();
		 }
	  }
	  else if(isset($this->userInfo->id) and ($this->userInfo->user_type=='sub-admin' or $this->userInfo->user_type=='Data Manager'))
	  {
		$myTeamUserData= $this->userObj->getAllUsersMyTeams($this->userInfo->team_id);  
	  }
	      $teamid=$this->getRequest()->getParam('search_team_id');
	      if($teamid)
			{
			$myTeamUserData= $this->userObj->getAllUsersMyTeams($teamid);	
			}
	  
	 
 			  $whereStr ="";
 			$this->view->userid=$userid=$this->getRequest()->getParam('user_id');
			
			$this->view->filter_date=$filter_date=$this->getRequest()->getParam('filter_date');
			$dateArray=explode(' - ',$filter_date);
			$fromdate=$dateArray[0]; 
		    $todate=$dateArray[1];
			
			$todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));
			
			//$this->view->search_type=$search_type=$this->getRequest()->getParam('search_type');
			$this->view->search_keywords=$search_keywords=trim($this->getRequest()->getParam('search_keywords'));
			$this->view->ref_id=$ref_id=trim($this->getRequest()->getParam('ref_id'));
			
			$this->view->website=$website=$this->getRequest()->getParam('website'); 
			$this->view->update_status=$update_status=$this->getRequest()->getParam('update_status');
			$this->view->transfer_type=$transfer_type=$this->getRequest()->getParam('transfer_type');
 			$this->view->flag_mark=$flag_mark=$this->getRequest()->getParam('flag_mark');
			
			if($this->getRequest()->getParam('tags'))
			{
 			 $tags=$this->getRequest()->getParam('tags');
			}
 			if($userid)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id = '".$userid."'";
		       }
			 else{
		     $whereStr .= "ass_qr.user_id = '".$userid."'";
		         }
 			}
			if($ref_id)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.id = '".$ref_id."'";
		       }
			 else{
		     $whereStr .= "ass_qr.id = '".$ref_id."'";
		         }
 			}
			
			
			
			if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  ass_qr.assign_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " ass_qr.assign_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
			
			/*if($search_keywords)
			{
		      $keyword = $search_keywords;
              if($whereStr!="")
			   {
	         $whereStr .= " AND query.name LIKE '%".$keyword."%' or query.email_id LIKE '%".$keyword."%' or query.phone LIKE '%".$keyword."%'";
		       }
			 else{
			$whereStr .= " query.name LIKE '%".$keyword."%' or query.email_id LIKE '%".$keyword."%' or query.phone LIKE '%".$keyword."%'";
		     
		         }
		    }
			*/
		/* if($website)
			{
		      $keyword = $website;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.website_id = '".$keyword."'";
		       }
			 else{
		     $whereStr .= " ass_qr.website_id = '".$keyword."'";
		         }
		    }
			*/
			 if($website)
			{
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.website_id IN (".implode(",",$website).")";
		       }
			 else{
		     $whereStr .= " ass_qr.website_id IN (".implode(",",$website).")";
		         }
		    }
			
			if($update_status)
			{
 		      $keyword = $update_status;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.update_status='".$keyword."'";
		       }
			 else{
		     $whereStr .= " ass_qr.update_status='".$keyword."'";
		         }
		    }
			
			if($transfer_type)
			{
				if($transfer_type=='Fresh')
				{
				$transfer_type=0;	
				}
				
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.transfer_type='".$transfer_type."'";
		       }
			 else{
		     $whereStr .= " ass_qr.transfer_type='".$transfer_type."'";
		         }
		    }
			 if($this->userInfo->user_type=='user' and !$this->getRequest()->getParam('user_id'))
			{
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id='".$this->userInfo->id."'";
		       }
			 else{
		     $whereStr .= " ass_qr.user_id='".$this->userInfo->id."'";
		         }
		    }
		 /*  if($this->userInfo->user_type=='sub-admin' or $this->userInfo->user_type=='Data Manager')
			{
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id IN (".implode(",",$myTeamUserData).")";
		       }
			 else{
		     $whereStr .= " ass_qr.user_id IN (".implode(",",$myTeamUserData).")";
		         }
		    }
			 */
 	 $this->view->QueryUsers=$this->QueryObj->FetchAllUserQuery($whereStr,$myTeamUserData,$search_keywords,$tags);
	  
}
public function allnotificationAction()
{ 
	if($this->getRequest()->getParam('ajaxify')==1)
	{
		 $this->_helper->layout->disableLayout();
	}
	
	$this->view->WebsiteData=$this->QueryObj->fetchAllWebsite();
   $currentDate=strtotime(date("m/d/Y",time())); 
   
       if(isset($this->userInfo->id) and $this->userInfo->user_type=='admin')
      { 
	    $this->view->userArr =$userfetch= $this->userObj->getAllUsers();
		///////////////////////////////////////////////////////////////////////
	if($this->getRequest()->getParam('searchBtn')!="")
	 {
 			$whereStr ="";
 			$this->view->userid=$userid=$this->getRequest()->getParam('user_id');
			$this->view->filter_date=$filter_date=$this->getRequest()->getParam('filter_date');
			$dateArray=explode(' - ',$filter_date);
			$fromdate=$dateArray[0]; 
		    $todate=$dateArray[1];
			$todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));
			$this->view->search_type=$search_type=$this->getRequest()->getParam('search_type');
			$this->view->search_keywords=$search_keywords=$this->getRequest()->getParam('search_keywords');
			$this->view->update_status=$update_status=$this->getRequest()->getParam('update_status'); 
			$this->view->website=$website=$this->getRequest()->getParam('website'); 
  			$this->view->flag_mark=$flag_mark=$this->getRequest()->getParam('flag_mark');
 			if($userid)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id = '".$userid."'";
		       }
			 else{
		     $whereStr .= "ass_qr.user_id = '".$userid."'";
		         }
 			}
			
			
			if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
			
			if($search_type)
			{
		      $keyword = $search_keywords;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ".$search_type." LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= $search_type." LIKE '%".$keyword."%'";
		         }
		    }
			
		 if($website)
			{
		      $keyword = $website;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.website_id LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " ass_qr.website_id LIKE '%".$keyword."%'";
		         }
		    }
			
			if($update_status)
			{
		      $keyword = $update_status;
              if($whereStr!="")
			   {
	         $whereStr .= " AND update_status LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " update_status LIKE '%".$keyword."%'";
		         }
		    }
			 
			if($flag_mark)
			{
		      $keyword = $flag_mark;
              if($whereStr!="")
			   {
	         $whereStr .= " AND flag_mark LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " flag_mark LIKE '%".$keyword."%'";
		         }
		    }
	   }
		///////////////////////////////////////////////////////////////////////
		if($whereStr)
		{
		$this->view->QueryUsers=$this->QueryObj->fetchSearchTodayFollowup($currentDate,$whereStr);	
		}
		else
		{
	    $this->view->QueryUsers=$this->QueryObj->fetchAllTodayFollowup($currentDate);
		}
		
 	  }
	   else if(isset($this->userInfo->id) and $this->userInfo->user_type=='sub-admin')
	   {
		   $this->view->userArr =$userfetch= $this->userObj->getSubadminAllUsers($this->userInfo->allocated_to);
		   
		   
		  ///////////////////////////////////////////////////////////////////////
		if($this->getRequest()->getParam('searchBtn')!="")
	 {
 			  $whereStr ="";
 			$this->view->userid=$userid=$this->getRequest()->getParam('user_id');
			
			$this->view->filter_date=$filter_date=$this->getRequest()->getParam('filter_date');
			$dateArray=explode(' - ',$filter_date);
			$fromdate=$dateArray[0]; 
		    $todate=$dateArray[1];
			
			$todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));
			
			$this->view->search_type=$search_type=$this->getRequest()->getParam('search_type');
			$this->view->search_keywords=$search_keywords=$this->getRequest()->getParam('search_keywords');
			
			$this->view->website=$website=$this->getRequest()->getParam('website'); 
  			$this->view->flag_mark=$flag_mark=$this->getRequest()->getParam('flag_mark');
			
 			if($userid)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id = '".$userid."'";
		       }
			 else{
		     $whereStr .= "ass_qr.user_id = '".$userid."'";
		         }
 			}
			
			
			if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
			
			if($search_type)
			{
		      $keyword = $search_keywords;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ".$search_type." LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= $search_type." LIKE '%".$keyword."%'";
		         }
		    }
			
		 if($website)
			{
		      $keyword = $website;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.website_id LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " ass_qr.website_id LIKE '%".$keyword."%'";
		         }
		    }
			
			if($update_status)
			{
 		      $keyword = $update_status;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.update_status LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " ass_qr.update_status LIKE '%".$keyword."%'";
		         }
		    }
		 
			 
			if($flag_mark)
			{
		      $keyword = $flag_mark;
              if($whereStr!="")
			   {
	         $whereStr .= " AND flag_mark LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " flag_mark LIKE '%".$keyword."%'";
		         }
		    }
	   }
		/////////////////////////////////////////////////////////////////////// 
		   
		 if($whereStr)
		 {
		 $this->view->QueryUsers=$this->QueryObj->fetchSearchSubadminTodayFollowup($currentDate,$this->userInfo->allocated_to,$whereStr);	 
		 }
		 else
		 {
		 $this->view->QueryUsers=$this->QueryObj->fetchAllSubadminTodayFollowup($currentDate,$this->userInfo->allocated_to);	 
		 }  
		   
 		
  	   }
	   else
	   {
		///////////////////////////////////////////////////////////////////////
		if($this->getRequest()->getParam('searchBtn')!="")
	 {
 			  $whereStr ="";
 			
			$this->view->filter_date=$filter_date=$this->getRequest()->getParam('filter_date');
			$dateArray=explode(' - ',$filter_date);
			$fromdate=$dateArray[0]; 
		    $todate=$dateArray[1];
			
			$todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));
			
			$this->view->search_type=$search_type=$this->getRequest()->getParam('search_type');
			$this->view->search_keywords=$search_keywords=$this->getRequest()->getParam('search_keywords');
			
			$this->view->website=$website=$this->getRequest()->getParam('website'); 
  			$this->view->flag_mark=$flag_mark=$this->getRequest()->getParam('flag_mark');
			
 			if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
			
			if($search_type)
			{
		      $keyword = $search_keywords;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ".$search_type." LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= $search_type." LIKE '%".$keyword."%'";
		         }
		    }
			
		 if($website)
			{
		      $keyword = $website;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.website_id LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " ass_qr.website_id LIKE '%".$keyword."%'";
		         }
		    }
			
			if($update_status)
			{
 		      $keyword = $update_status;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.update_status LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " ass_qr.update_status LIKE '%".$keyword."%'";
		         }
		    }
		 
			 
			if($flag_mark)
			{
		      $keyword = $flag_mark;
              if($whereStr!="")
			   {
	         $whereStr .= " AND flag_mark LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " flag_mark LIKE '%".$keyword."%'";
		         }
		    }
	   }
		/////////////////////////////////////////////////////////////////////// 
		if($whereStr)
		{
		$this->view->QueryUsers=$this->QueryObj->fetchSearchTodayUnFollowup($currentDate,$this->userInfo->id,$whereStr);
		}
		else
		{
		$this->view->QueryUsers=$this->QueryObj->fetchTodayUnFollowup($currentDate,$this->userInfo->id);	
		}  
	   
       }
	   
 	 	if($this->getRequest()->getParam('query')!="" and $this->getRequest()->getParam('action_taken'))
		{
		 $queryid=$this->getRequest()->getParam('query');
 		 $status=$this->getRequest()->getParam('status');
		 if($status==1)
 		 {
 		  $statusvalue=0;	
 		 }
 		 else
 		 {
 		   $statusvalue=1;
 		 }
		 $array=array('action_taken'=>$statusvalue);
 		  $update= $this->QueryObj->updateUserQuery($array,$queryid);
		  
		   $this->_redirect('managequery/allnotification');
		 	
		}
		
 		if($this->getRequest()->getParam('query')!="" and $this->getRequest()->getParam('converted'))
		{
		 $queryid=$this->getRequest()->getParam('query');
 		 $status=$this->getRequest()->getParam('status');
		 if($status==1)
 		 {
 		  $statusvalue=0;	
 		 }
 		 else
 		 {
 		   $statusvalue=1;
 		 }
		 $array=array('converted'=>$statusvalue);
 		  $update= $this->QueryObj->updateUserQuery($array,$queryid);
 		   $this->_redirect('managequery/allnotification');
		 	
		}
		
 	
}	

public function loadnotificationqueryAction()
{
$this->_helper->layout->disableLayout();
//$currentDate=strtotime(date("m/d/Y",time()));
 $currentDate=date("m/d/Y",time()); 
 $Date = date("m/d/Y",strtotime($currentDate));
 $currentDate= strtotime($Date. ' - '.(1).' days');
 $currentDate= strtotime($Date. ' + '.(1).' days');
$limit=20;
$whereStr="";
 		if($this->getRequest()->getParam('searchBtn')!="")
		  {
			  $whereStr ="";
			  $limit=$this->getRequest()->getParam('limit');
 			$this->view->userid=$userid=$this->getRequest()->getParam('userid');
			
			$this->view->filter_date=$filter_date=$this->getRequest()->getParam('filter_date');
			$dateArray=explode(' - ',$filter_date);
			$fromdate=$dateArray[0]; 
		    $todate=$dateArray[1];
			
			$todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));
			
			$this->view->search_type=$search_type=$this->getRequest()->getParam('search_type');
			$this->view->search_keywords=$search_keywords=$this->getRequest()->getParam('search_keywords');
  			$this->view->website=$website=$this->getRequest()->getParam('website'); 
			$this->view->requirement=$requirement=$this->getRequest()->getParam('requirement');
 			
			if($userid)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id = '".$userid."'";
		       }
			 else{
		     $whereStr .= "ass_qr.user_id = '".$userid."'";
		         }
 			}
			/*if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}*/
			if($search_type)
			{
		      $keyword = $search_keywords;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ".$search_type." LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= $search_type." LIKE '%".$keyword."%'";
		         }
		    }
			
			if($website)
			{
		      $keyword = $website;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.website_id LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " ass_qr.website_id LIKE '%".$keyword."%'";
		         }
		    }
			if($requirement)
			{
		      $keyword = $requirement;
              if($whereStr!="")
			   {
	         $whereStr .= " AND requirement LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " requirement LIKE '%".$keyword."%'";
		         }
		    }
			
			
		if($this->userInfo->user_type=='sub-admin')
			{
 				$allocated_to=$this->userInfo->allocated_to;
               if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id IN (".$allocated_to.")";
		       }
			 else{
		     $whereStr .= " ass_qr.user_id IN (".$allocated_to.")";
		         }
		    }
 			if($this->userInfo->user_type=='user')
			{
 				$allocated_to=$this->userInfo->id;
               if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id ='".$allocated_to."'";
		       }
			 else{
		     $whereStr .= " ass_qr.user_id ='".$allocated_to."'";
		         }
		    }
  			
		  }
		  
		$whereStr1=$whereStr;
		if($fromdate and $todate)
			{ 
              if($whereStr1!="")
			   {
	         $whereStr1 .= " AND  ass_qr.assign_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr1 .= " ass_qr.assign_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
		///////////////////////////////////////////////All Leads/////////////////////////////////////////////////
 		$this->view->LeadsData=$LeadsData=$this->QueryObj->selectAllNotificationQuery($whereStr1,1,$limit,$currentDate);
		 $this->view->LeadsCount=count($this->QueryObj->selectAllNotificationQuery($whereStr1,1,"",$currentDate));
		 
		 if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  ass_qr.update_status_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " ass_qr.update_status_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
			 
 		///////////////////////////////////////////Contact Made///////////////////////////////////////////////////
 		 $this->view->ContactMadeData=$ContactMadeData=$this->QueryObj->selectAllNotificationQuery($whereStr,2,$limit,$currentDate);
		  $this->view->ContactMadeCount=count($this->QueryObj->selectAllNotificationQuery($whereStr,2,"",$currentDate));
		
		
		///////////////////////////////////////////Quoted///////////////////////////////////////////////////
 		 $this->view->QuotedData=$QuotedData=$this->QueryObj->selectAllNotificationQuery($whereStr,3,$limit,$currentDate);
		 $this->view->QuotedCount=count($this->QueryObj->selectAllNotificationQuery($whereStr,3,"",$currentDate));
		
		///////////////////////////////////////////Negotiating///////////////////////////////////////////////////
 		 $this->view->NegotiatingData=$NegotiatingData=$this->QueryObj->selectAllNotificationQuery($whereStr,4,$limit,$currentDate);
		 $this->view->NegotiatingCount=count($this->QueryObj->selectAllNotificationQuery($whereStr,4,"",$currentDate));
		
		///////////////////////////////////////////Converted///////////////////////////////////////////////////
 		 $this->view->ConvertedData=$ConvertedData=$this->QueryObj->selectAllNotificationQuery($whereStr,5,$limit,$currentDate);
		$this->view->ConvertedCount=count($this->QueryObj->selectAllNotificationQuery($whereStr,5,"",$currentDate));
		///////////////////////////////////////////Client Not Interested///////////////////////////////////////////////////
 		 $this->view->NotInterestedData=$NotInterestedData=$this->QueryObj->selectAllNotificationQuery($whereStr,6,$limit,$currentDate);
		 $this->view->NotInterestedCount=count($this->QueryObj->selectAllNotificationQuery($whereStr,6,"",$currentDate));
		 
		 ///////////////////////////////////////////Delay///////////////////////////////////////////////////
 		$this->view->DelayData=$DelayData=$this->QueryObj->selectAllNotificationQuery($whereStr,7,$limit,$currentDate);
		$this->view->DelayCount=count($this->QueryObj->selectAllNotificationQuery($whereStr,7,"",$currentDate));
 }

public function actiontakenAction() 
{
	if($this->getRequest()->getParam('query')!="")	
	{ 
		 $queryid=$this->getRequest()->getParam('query');
		 $QueryInfo = $this->QueryObj->getViewQueryId($queryid);
  		 $status=$QueryInfo['action_taken'];
		 if($status==1)
 		 {
 		  $statusvalue=0;	
 		 }
 		 else
 		 {
 		   $statusvalue=1;
 		 }
		 $array=array('action_taken'=>$statusvalue);
 		  $update= $this->QueryObj->updateUserQuery($array,$queryid);
		   echo $statusvalue;die; 
	}
 	die;
}

public function convertedAction() 
{
	if($this->getRequest()->getParam('query')!="")	
	{ 
		 $queryid=$this->getRequest()->getParam('query');
		 $statusAction=$this->getRequest()->getParam('statusAction');
		 $QueryInfo = $this->QueryObj->getViewQueryId($queryid);
  		 $status=$QueryInfo['converted'];
		 if($statusAction==2)
		 {
		  $statusvalue=2;	
		  $array=array('converted'=>$statusvalue,'dead_date'=>time()); 
		 }
		 else if($status==1)
 		 {
 		  $statusvalue=0;	
		  $array=array('converted'=>$statusvalue);
 		 }
 		 else
 		 {
 		   $statusvalue=1;
		   $array=array('converted'=>$statusvalue);
		   
		    $template='Thanks for choosing eMarketz as your success partner.';
		    $sent=$this->smsObj->SentSMS($template,$QueryInfo['phone']);
		   
 		 }
		 
 		  $update= $this->QueryObj->updateUserQuery($array,$queryid);
		   echo $statusvalue;die; 
	}
 	die;
}

public function deadqueryAction()
{
	if($this->getRequest()->getParam('ajaxify')==1)
	{
		 $this->_helper->layout->disableLayout();
	}
	
	 $this->view->WebsiteData=$this->QueryObj->fetchAllWebsite();
      if(isset($this->userInfo->id) and $this->userInfo->user_type=='admin')
		  { 
		if($this->getRequest()->getParam("userid"))
		 {
		  $this->view->userid=$userid="/userid/".$this->getRequest()->getParam("userid");
			$this->view->QueryUsers=$this->QueryObj->FetchUserQuery($this->getRequest()->getParam("userid")); 
		 }
		 else
		 {
		 $this->view->userArr =$userfetch= $this->userObj->getAllUsers();
		 
		 }
		 }
	   else if(isset($this->userInfo->id) and $this->userInfo->user_type=='sub-admin')
	   {
 		$this->view->userArr =$userfetch= $this->userObj->getSubadminAllUsers($this->userInfo->allocated_to); 
 	   }
	   
}

public function loaddeadqueryAction()
{
	$this->_helper->layout->disableLayout();
   
      $this->view->WebsiteData=$this->QueryObj->fetchAllWebsite();
      if(isset($this->userInfo->id) and $this->userInfo->user_type=='admin')
      { 
    if($this->getRequest()->getParam("userid"))
	 {
	  $this->view->userid=$userid="/userid/".$this->getRequest()->getParam("userid");
		$this->view->QueryUsers=$this->QueryObj->FetchUserQuery($this->getRequest()->getParam("userid")); 
	 }
	 else
	 {
	 $this->view->userArr =$userfetch= $this->userObj->getAllUsers();
	  
	 
	 if($this->getRequest()->getParam('searchBtn')!="")
	 {
 			  $whereStr ="";
 			$this->view->userid=$userid=$this->getRequest()->getParam('user_id');
			
			$this->view->filter_date=$filter_date=$this->getRequest()->getParam('filter_date');
			$dateArray=explode(' - ',$filter_date);
			$fromdate=$dateArray[0]; 
		    $todate=$dateArray[1];
			
			$todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));
			
			$this->view->search_type=$search_type=$this->getRequest()->getParam('search_type');
			$this->view->search_keywords=$search_keywords=$this->getRequest()->getParam('search_keywords');
			 
 			if($userid)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id = '".$userid."'";
		       }
			 else{
		     $whereStr .= "ass_qr.user_id = '".$userid."'";
		         }
 			}
			
			
			if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
			
			if($search_type)
			{
		      $keyword = $search_keywords;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ".$search_type." LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= $search_type." LIKE '%".$keyword."%'";
		         }
		    }
			
		 
		 
			 
	   }
	   
	   if($whereStr)
 		  {
		 $this->view->QueryUsers=$this->QueryObj->FetchAllUserSearchDeadQuery($whereStr); 
	     }
		 else
		 {
		 $this->view->QueryUsers=$this->QueryObj->FetchAllUserDeadQuery();
		 }
	 }
	 }
	   else if(isset($this->userInfo->id) and $this->userInfo->user_type=='sub-admin')
	   {
		  
		if($this->getRequest()->getParam('searchBtn')!="")
		  {
			   $whereStr ="";
 			$this->view->userid=$userid=$this->getRequest()->getParam('user_id');
			
			$this->view->filter_date=$filter_date=$this->getRequest()->getParam('filter_date');
			$dateArray=explode(' - ',$filter_date);
			$fromdate=$dateArray[0]; 
		    $todate=$dateArray[1];
			
			$todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));
			
			$this->view->search_type=$search_type=$this->getRequest()->getParam('search_type');
			$this->view->search_keywords=$search_keywords=$this->getRequest()->getParam('search_keywords');
			
			$this->view->website=$website=$this->getRequest()->getParam('website'); 
			 
			if($userid)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id = '".$userid."'";
		       }
			 else{
		     $whereStr .= "ass_qr.user_id = '".$userid."'";
		         }
 			}
			if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
			if($search_type)
			{
		      $keyword = $search_keywords;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ".$search_type." LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= $search_type." LIKE '%".$keyword."%'";
		         }
		    }
			
			if($website)
			{
		      $keyword = $website;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.website_id LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " ass_qr.website_id LIKE '%".$keyword."%'";
		         }
		    }
			
			  
		  }
		  
		   if($whereStr)
		   { 
			$this->view->QueryUsers=$this->QueryObj->FetchSearchSubAdminUserDeadQuery($this->userInfo->allocated_to,$whereStr);    
		   }
		   else
		   {
			$this->view->QueryUsers=$this->QueryObj->FetchSubAdminUserDeadQuery($this->userInfo->allocated_to);    
		   }
		   
		$this->view->userArr =$userfetch= $this->userObj->getSubadminAllUsers($this->userInfo->allocated_to); 
        		
	    
	   }
	   else
	   {
		/////////////////////////////////////////////////////////////////////////////////////////////
 		if($this->getRequest()->getParam('searchBtn')!="")
		  {
			   $whereStr ="";
 			$this->view->userid=$userid=$this->getRequest()->getParam('user_id');
			
			$this->view->filter_date=$filter_date=$this->getRequest()->getParam('filter_date');
			$dateArray=explode(' - ',$filter_date);
			$fromdate=$dateArray[0]; 
		    $todate=$dateArray[1];
			
			$todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));
			
			$this->view->search_type=$search_type=$this->getRequest()->getParam('search_type');
			$this->view->search_keywords=$search_keywords=$this->getRequest()->getParam('search_keywords');
			
			$this->view->website=$website=$this->getRequest()->getParam('website'); 
			 
			if($userid)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id = '".$userid."'";
		       }
			 else{
		     $whereStr .= "ass_qr.user_id = '".$userid."'";
		         }
 			}
			if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " query.created_on BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
			if($search_type)
			{
		      $keyword = $search_keywords;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ".$search_type." LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= $search_type." LIKE '%".$keyword."%'";
		         }
		    }
			
			if($website)
			{
		      $keyword = $website;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.website_id LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " ass_qr.website_id LIKE '%".$keyword."%'";
		         }
		    }
			
			  
		  }
		  
		   if($whereStr)
		   { 
			$this->view->QueryUsers=$this->QueryObj->FetchSearchSelfUserDeadQuery($this->userInfo->id,$whereStr);    
		   }
		   else
		   {
			$this->view->QueryUsers=$this->QueryObj->FetchSelfUserDeadQuery($this->userInfo->id);    
		   }
		   
         		
	    
	      
		}
 	 	 
		
		
		if($this->getRequest()->getParam('Action')=="Delete")
	    {
		 $queryid=$this->getRequest()->getParam('checkid');
 		 $delete=$this->QueryObj->DeleteAssignQuery($queryid);
		  $this->_redirect('managequery/deadquery');
	    }
		
		
  
		
}
	
	
public function loadviewquerydetailsAction()
{
$this->_helper->layout->disableLayout();

  if($this->getRequest()->getParam('ajaxify')==1)
	{
		 $this->_helper->layout->disableLayout();
	}
      if($this->getRequest()->getParam('queryid'))
 	   {
 		 $queryid =$this->getRequest()->getParam('queryid');
 		 $this->view->QueryInfo=$QueryInfo = $this->QueryObj->getViewQueryId($queryid);
		 
		 if($this->getRequest()->getParam('searchdate'))
		 {
	     $this->view->searchdate=$searchdate=$this->getRequest()->getParam('searchdate');
		 $this->view->CommentInfo = $this->QueryObj->GetSearchComents($queryid,strtotime($searchdate));
		 }
		 else
		 {
		 $this->view->CommentInfo = $this->QueryObj->GetComents($queryid);	 
		 }
  	  }
	  
  
		if($this->getRequest()->isPost('updateBtn') and $this->getRequest()->getParam('updateBtn')!="")
		{
			  $action_taken=$this->getRequest()->getParam('action_taken');
			  if($action_taken=="")
			  {
				 $action_taken=0;  
			  }
			  $converted=$this->getRequest()->getParam('converted');
			  if($converted=="")
			  {
				 $converted=0;  
			  }
			  
			   $update_status=$this->getRequest()->getParam('update_status');
			  
			  $array=array('action_taken'=>$action_taken,'converted'=>$converted,'update_status'=>$update_status);
 		     $update= $this->QueryObj->updateUserQuery($array,$queryid);
 			 
		 $comments=$this->getRequest()->getParam('comments');
		 if($comments)
		 {
		 $array=array("ass_query_id"=>$queryid,"query_id"=>$QueryInfo['id'],"user_id"=>$this->userInfo->id,"comments"=>$comments,"date"=>time());
 		 $update= $this->QueryObj->insertComment($array);
		 }
		  //$this->_redirect('managequery/index/viewdetails/queryid/'.$queryid); 
		  $this->_redirect('managequery/allnotification');
		 
		}
	 
 	if($this->getRequest()->getParam('queryid')!="" and $this->getRequest()->getParam('Anoti')==1)
		{
		 $queryid=$this->getRequest()->getParam('queryid');
 		  $array=array('status'=>1);
 		 $update= $this->QueryObj->updateCommentStatus($array,$queryid,$this->userInfo->id);
  		}
		
		if($this->getRequest()->getParam('queryid')!="" and $this->getRequest()->getParam('Unoti')==1)
		{
		 $queryid=$this->getRequest()->getParam('queryid');
 		 $array=array('status'=>1);
 		 $update= $this->QueryObj->updateCommentStatus($array,$queryid,$this->userInfo->id);
  		}
 
}

function onlycommentsubmitAction()
{
if($this->getRequest()->getParam('assign_id')!="" and $this->getRequest()->getParam('query_id')!="")
		{
$assign_id=$this->getRequest()->getParam('assign_id');
$query_id=$this->getRequest()->getParam('query_id');
$comments=$this->getRequest()->getParam('comments');
$update_status=$this->getRequest()->getParam('update_status1');
$remainder_date=$this->getRequest()->getParam('remainder_date');



			  if($update_status > 1)
			  {
				 $action_taken=1;  
			  }
			   
			  if($update_status=="5")
			  {
				 $converted=1;
			  }
			  if($update_status=="6")
			  {
				 $converted=2; 
				 $array_dead_date=array('dead_date'=>time());
 		         $update= $this->QueryObj->updateUserQuery($array_dead_date,$assign_id); 
			  }
			    
			 if($this->userInfo->user_type=='admin' or $this->userInfo->user_type=='sub-admin')
		    {
			 $array=array('action_taken'=>$action_taken,'converted'=>$converted,'update_status'=>$update_status,'update_status_date'=>time(),"remainder_date"=>$remainder_date);
 		     $update= $this->QueryObj->updateUserQuery($array,$assign_id);
		    }

if($this->getRequest()->getParam('shift_to_lead')!="")
{
$queryArray=array("open_status"=>1,'update_status'=>1,'assign_date'=>time(),'open_date'=>time(),"open_status"=>0);
$update_status=1;
}
else
{
$queryArray=array("open_status"=>1);	
}

$array=array("ass_query_id"=>$assign_id,
"query_id"=>$query_id,
"user_id"=>$this->userInfo->id,
"comments"=>$comments,
"date"=>time(),
"query_status"=>$update_status,
"comments_type"=>'internal',
"comments_sent_type"=>'user',
);
$insertComments= $this->QueryObj->insertComment($array);
$this->QueryObj->updateUserQuery($queryArray,$assign_id);
echo $insertComments;die;


}			
}

function commentsubmitAction()
{
if($this->getRequest()->getParam('assign_id')!="" and $this->getRequest()->getParam('query_id')!="")
		{
 			$assign_id=$this->getRequest()->getParam('assign_id');
			$query_id=$this->getRequest()->getParam('query_id');
			$update_status=$this->getRequest()->getParam('update_status1');
			$old_status=$this->getRequest()->getParam('old_status');
			$mail_to=$this->getRequest()->getParam('mail_to');
			 
			 
			 $email_body=($this->getRequest()->getParam('email_temp_body'))?$this->getRequest()->getParam('email_temp_body'):"";
			$sms_body=($this->getRequest()->getParam('sms_temp_body'))?$this->getRequest()->getParam('sms_temp_body'):"";
			$call_message=($this->getRequest()->getParam('call_temp_body'))?$this->getRequest()->getParam('call_temp_body'):"";
			$meeting_message=($this->getRequest()->getParam('meeting_temp_body'))?$this->getRequest()->getParam('meeting_temp_body'):"";
			
			
			$send_mail_type=($this->getRequest()->getParam('send_mail_type'))?$this->getRequest()->getParam('send_mail_type'):array();
			$totalHours=$this->getRequest()->getParam('totalHours');
			$totalMinutes=$this->getRequest()->getParam('totalMinutes');
			$mail_subject=$this->getRequest()->getParam('mail_subject');
			$mail_from_email=$this->getRequest()->getParam('mail_from_email');
			$mail_signature=$this->getRequest()->getParam('mail_signature');
			  
			$remainder_date=strtotime($this->getRequest()->getParam('remainder_date'));
			
			$in_time=$this->getRequest()->getParam('in_time');
			
			 $TATScore=0;
			 
			 if($in_time)
			 {
			$interval  = abs(time() - $in_time);
			$totalMinutes   = round($interval / 60);	


           if($totalMinutes < 31)
			{
			$TATScore=10;	
			}
			else if($totalMinutes > 30 and  $totalMinutes < 61)
			{
			$TATScore=9;	
			}
			else if($totalMinutes > 60 and  $totalMinutes < 91)
			{
			$TATScore=8;	
			}
			else if($totalMinutes > 90 and  $totalMinutes < 121)
			{
			$TATScore=7;	
			}
			else if($totalMinutes > 120 and  $totalMinutes < 181)
			{
			$TATScore=6;	
			}
			else if($totalMinutes > 180 and  $totalMinutes < 241)
			{
			$TATScore=5;	
			}
			else if($totalMinutes > 240 and  $totalMinutes < 361)
			{
			$TATScore=3;	
			}
			else if($totalMinutes > 360 and  $totalMinutes < 631)
			{
			$TATScore=1;	
			}
			else
			{
			$TATScore=0;	
			} 
			  
			 }
			 
			 
			$converted=0;
			
			 
			 
			  if($update_status > 1)
			  {
				 $action_taken=1;  
			  }
			   
			  if($update_status=="5")
			  {
				 $converted=1;
                
			 $checkAnotherAss= $this->QueryObj->checkAnotherUserAssign($mail_to,$assign_id);
				 
			  }
			  if($update_status=="6")
			  {
				 $converted=2; 
				 $array_dead_date=array('dead_date'=>time());
 		         $update= $this->QueryObj->updateUserQuery($array_dead_date,$assign_id); 
			  }
			    
			 
			 $array=array('action_taken'=>$action_taken,'converted'=>$converted,'update_status'=>$update_status,'update_status_date'=>time(),"remainder_date"=>$remainder_date);
 		     $update= $this->QueryObj->updateUserQuery($array,$assign_id);
			 
			 
			 /////////////////////////////////
			 if($update_status!=$old_status)
			 {
				 if($update_status==1)
				 {
					$st_text='Lead In'; 
				 }
				 else if($update_status==2)
				 {
					$st_text='Contact Made'; 
				 }
				 else if($update_status==3)
				 {
					$st_text='Quoted'; 
				 }
				 else if($update_status==4)
				 {
					$st_text='Negotiating'; 
				 }
				 else if($update_status==5)
				 {
				$st_text='Converted';	 
				 }
				 else if($update_status==6)
				 {
					$st_text='Client Not Interested'; 
				 }
				 else if($update_status==7)
				 {
					$st_text='Reminder'; 
				 }
			 $arrayAct=array("user_id"=>$this->userInfo->id,'user_name'=>$this->userInfo->name,'message'=>'set status as '.$st_text,'action_date'=>time(),'query_id'=>$query_id,'query_assign_id'=>$assign_id);
				 $this->QueryObj->insertActionHistory($arrayAct);
			 }
			 //////////////////////////////////
 			 
 		 //////////////////////////////////////
		 $locatie = 'public/UploadFolder/'; 
            $time=time();
  	        $attachment=array();
  	        $comments_file=array();
  		 	$count= count($_FILES['comments_file']['name']); 
			if ($count!="" && $count > 0) {
			for($i=0;$i<$count;$i++){
				$doc.=$_FILES['comments_file']['name'][$i].',';
				$folder = $locatie.time().$i.'_'.basename(preg_replace("/[^a-zA-Z0-9.]/","",$_FILES['comments_file']['name'][$i])) ; 
 				$base_name=basename($_FILES['comments_file']['name'][$i]);
			if(move_uploaded_file($_FILES['comments_file']['tmp_name'][$i], $folder)){
				$j=$i+1;
 				$comments_file[]=$folder;
 				
				$attachment[] = PostmarkAttachment::fromFile($folder, str_replace("public/UploadFolder/","",$folder), $_FILES['comments_file']['type'][$i]);
 
   			 }
			}
			
			
			 $comments_file=implode("||",$comments_file);
            }
		
		/* $locatie = 'public/UploadFolder/'; 
            $time=time();
			$comments_file="";
			$attachment="";
				if (isset($_FILES['comments_file']['name'])&& (!empty($_FILES['comments_file']['name']))) {
 				$folder1 = $locatie.$time.'_'.basename($_FILES['comments_file']['name']) ; 
			if(move_uploaded_file($_FILES['comments_file']['tmp_name'], $folder1)){
				 $comments_file=$folder1;
				 $attachment = PostmarkAttachment::fromFile($comments_file, str_replace("public/UploadFolder/","",$comments_file), $_FILES['comments_file']['type']);
			  }
           }
		   */
		   ///////////////////////
		    
		 //if($comments!="" or $comments_file!="")
		 //{
			 if(count($send_mail_type) == 0)
			  {
			 $array=array("ass_query_id"=>$assign_id,"query_id"=>$query_id,"user_id"=>$this->userInfo->id,"comments_file"=>$comments_file,"date"=>time(),"query_status"=>$update_status,"comments_sent_type"=>'user',"FromName"=>$this->userInfo->name); 
			  }
			 else
			 {
				 
				 /////////////////sms////////////
				if(in_array('SMS',$send_mail_type))
				{  
               $sms_body=$sms_body;
				}
				else
				{
				$sms_body="";	
				}
				/////////////////call////////////////
				 if(in_array('Call',$send_mail_type))
				{  
                $call_message=$call_message;
				}
				else
				{
					$call_message="";
				}
				////////////////Meeting///////////////////
				if(in_array('Meeting',$send_mail_type))
				{  
                $meeting_message=$meeting_message;
				}
				else
				{
				 $meeting_message="";
				}
				 
		 $QueryInfo = $this->QueryObj->getViewQueryId($assign_id);		 
				 
				 if(in_array('Email',$send_mail_type))
                 {
					 					
				if($this->userInfo->user_type=='user' and $this->getRequest()->getParam('in_time')!="")
				{
					
				$checkTatScore=$this->QueryObj->checkTatScoreExist($this->userInfo->id,$assign_id,$in_time);
				
				if(count($checkTatScore)==0)
				{
				$tatArray=array("user_id"=>$this->userInfo->id,"ref_id"=>$assign_id,"score"=>$TATScore,"taken_time"=>$totalHours.':'.$totalMinutes,"total_hours"=>$totalHours,"total_minute"=>$totalMinutes,"in_time"=>$in_time,"created_date"=>time());
				$this->QueryObj->insertTATScore($tatArray);
				}
				}	


				if($this->getRequest()->getParam('email_cc'))
				{
				$cc=$this->getRequest()->getParam('email_cc');
				}
				else
				{
					$cc='';
				}
				if($this->getRequest()->getParam('email_bcc'))
				{
				$bcc=$this->getRequest()->getParam('email_bcc');	
				}
				else
				{
					$bcc='';
				}
				$to_email=$QueryInfo['email_id'];				
					 
				 	  
			 $array=array("ass_query_id"=>$assign_id,"query_id"=>$query_id,"user_id"=>$this->userInfo->id,"comments_file"=>$comments_file,"date"=>time(),"query_status"=>$update_status,"email_body"=>$email_body,"sms_body"=>$sms_body,"call_message"=>$call_message,"meeting_message"=>$meeting_message,"TATScore"=>$TATScore,"subject"=>$mail_subject,"from_email"=>$mail_from_email,"signature"=>'',"comments_sent_type"=>'user',"FromName"=>$this->userInfo->name,"to_email"=>$to_email,"cc_email"=>$cc,"bcc_email"=>$bcc);
				 }
				else
				{
				 $array=array("ass_query_id"=>$assign_id,"query_id"=>$query_id,"user_id"=>$this->userInfo->id,"comments_file"=>$comments_file,"date"=>time(),"query_status"=>$update_status,"sms_body"=>$sms_body,"call_message"=>$call_message,"meeting_message"=>$meeting_message,"comments_sent_type"=>'user',"FromName"=>$this->userInfo->name);	
				}				 
			 } 
		  
 		 $insertComments= $this->QueryObj->insertComment($array);
		 $comment_id=$insertComments;
		 //}
		/////////////////Mail And SMS////////////////////////////////// 
		
//$this->postmarkAdminClient->createSenderSignature($QueryInfo['website_email'],'Query Panel');		
		
	
////////////////////////////////////Mail///////////////////////
 /*if(count($send_mail_type) == 0)
  {
 $QueryInfo = $this->QueryObj->getViewQueryId($assign_id);
$commentsInfo = $this->QueryObj->getFirstComments($assign_id);

$body='';
$body.=$comments;

$replyEmail='reply.'.$assign_id.'+'.$this->userInfo->id.'@inbound.phdgurus.com';
$sendResult = $this->postmarkclient->sendEmail("info@inbound.phdgurus.com", $QueryInfo['email_id'],$replyEmail, $commentsInfo['subject'],$body,NULL,true,NULL,NULL,NULL,NULL,[$attachment]);		
 }	*/
 if(count($send_mail_type) > 0)
 {
		
////////////////////////////////////Mail///////////////////////
if(in_array('Email',$send_mail_type))
{
$body='';
$body.=$email_body;
$body.=$mail_signature;


$lastComment = $this->QueryObj->GetLastClientEmail($assign_id);
if($lastComment['message_id'])
{
$body.='<div style="color:#500050;margin:0px 0px 0px 0.8ex;border-left:1px solid rgb(204,204,204);padding-left:1ex;"><p>On '.date("l M d, Y",$lastComment['date']).' at '.date("h:i A",$lastComment['date']).' &nbsp; &lt;'.$lastComment['from_email'].'&gt;  wrote:</p>';
$body.=$lastComment['comments'].$lastComment['email_body'].'</div>';
}
 

$fromMailArray=explode("@",$mail_from_email);
//.$fromMailArray[1]

//$replyEmail='reply.'.$assign_id.'+'.$this->userInfo->id.'@instacrm.rapidcollaborate.com';
$replyEmail=NULL;

$headers['ref_id']=$assign_id;

$sendResult = $this->postmarkclient->sendEmail($mail_from_email, $to_email,$replyEmail,$cc,$bcc, $mail_subject,$body,NULL,TRUE,TRUE,$headers,$attachment);
$messageid=$sendResult->messageid;
 if($messageid and $comment_id)
 {
$this->QueryObj->updateComment(array("message_id"=>$messageid,"email_body"=>$body),$comment_id);
$this->QueryObj->updateUserQuery(array("open_status"=>1),$assign_id);
 }

}
/* $message = array(
'html' => $body,
'subject' => $mail_subject,
'from_email' => $QueryInfo['website_email'],
'from_name' => 'Query Panel',
'to' => array(
array(
	'email'=>$QueryInfo['email_id'],
	'name' => $QueryInfo['name'], 
	'type' => 'to'
)
)
);
$mailsent=SendMandrilMail($message); 
 */
//////////////////Send SMS///////////////////////
 if(in_array('SMS',$send_mail_type))
{  
	if($QueryInfo['phone']!="" and $QueryInfo['sender_id']!="")
	{
$customNumber  = $QueryInfo['phone'];
$msg = urlencode($sms_body);
$get_data = $this->QueryObj->callAPI('GET', 'http://msg.mtalkz.com/V2/http-api.php?apikey=vaBG0xi7VQNM7Xpb&senderid='.$QueryInfo['sender_id'].'&number='.$customNumber.'&message='.$msg.'&format=json', false);
$response = json_decode($get_data, true);
$errors = $response['response']['errors'];
$data = $response['response']['data'][0];
//print_r($data);
	}
}
 
//////////////////Send Call ///////////////////////
/*if(in_array('Call',$send_mail_type))
{

}*/
//////////////////Send Meeting ///////////////////////
/*if(in_array('Meeting',$send_mail_type))
{

}*/
////////////////////////////////////////////////////////////////	
 }			
		///////////////////////////////////////////////////////////////
		 
		 
		  echo 1;die;
		 
		}

die;	
}

public function remainderqueryAction()
{
	if($this->getRequest()->getParam('ajaxify')==1)
		{
			 $this->_helper->layout->disableLayout();
		}
	
	 $this->view->WebsiteData=$this->QueryObj->fetchAllWebsite();
      if(isset($this->userInfo->id) and $this->userInfo->user_type=='admin')
		  { 
		if($this->getRequest()->getParam("userid"))
		 {
		  $this->view->userid=$userid="/userid/".$this->getRequest()->getParam("userid");
			$this->view->QueryUsers=$this->QueryObj->FetchUserQuery($this->getRequest()->getParam("userid")); 
		 }
		 else
		 {
		 $this->view->userArr =$userfetch= $this->userObj->getAllUsers();
		 
		 }
		 }
	   else if(isset($this->userInfo->id) and $this->userInfo->user_type=='sub-admin')
	   {
 		$this->view->userArr =$userfetch= $this->userObj->getSubadminAllUsers($this->userInfo->allocated_to); 
 	   }
}
public function loadremainderqueryAction()
{

	$this->_helper->layout->disableLayout();
    $whereStr ="";
      $this->view->WebsiteData=$this->QueryObj->fetchAllWebsite();
       
	   
	 if($this->getRequest()->getParam('searchBtn')!="")
	 {
 			  $whereStr ="";
 			$this->view->userid=$userid=$this->getRequest()->getParam('user_id');
			
			$this->view->filter_date=$filter_date=$this->getRequest()->getParam('filter_date');
			$dateArray=explode(' - ',$filter_date);
			$fromdate=$dateArray[0]; 
		    $todate=$dateArray[1];
			
			$todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));
			
			$this->view->search_type=$search_type=$this->getRequest()->getParam('search_type');
			$this->view->search_keywords=$search_keywords=$this->getRequest()->getParam('search_keywords');
			
			$this->view->website=$website=$this->getRequest()->getParam('website'); 
			 
			
 			if($userid)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.user_id = '".$userid."'";
		       }
			 else{
		     $whereStr .= "ass_qr.user_id = '".$userid."'";
		         }
 			}
			
			
			if($fromdate and $todate)
			{ 
              if($whereStr!="")
			   {
	         $whereStr .= " AND  ass_qr.update_status_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		       }
			 else{
		     $whereStr .= " ass_qr.update_status_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
		         }
 			}
			
			if($search_type)
			{
		      $keyword = $search_keywords;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ".$search_type." LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= $search_type." LIKE '%".$keyword."%'";
		         }
		    }
			
		 if($website)
			{
		      $keyword = $website;
              if($whereStr!="")
			   {
	         $whereStr .= " AND ass_qr.website_id LIKE '%".$keyword."%'";
		       }
			 else{
		     $whereStr .= " ass_qr.website_id LIKE '%".$keyword."%'";
		         }
		    }
		 
			 
	   }
	 
		
		if(isset($this->userInfo->id) and $this->userInfo->user_type=='admin')
      { 
      $this->view->userArr =$userfetch= $this->userObj->getAllUsers();
	   $this->view->QueryRemainderData=$this->QueryObj->FetchAllUserRemainderQuery($whereStr);
	  }
	   else if(isset($this->userInfo->id) and $this->userInfo->user_type=='sub-admin')
	  {
		$this->view->QueryRemainderData=$this->QueryObj->FetchSubadminRemainderQuery($this->userInfo->allocated_to,$whereStr);
		$this->view->userArr =$userfetch= $this->userObj->getSubadminAllUsers($this->userInfo->allocated_to); 
	  }
	  else{
		 $this->view->QueryRemainderData=$this->QueryObj->FetchUserRemainderQuery($this->userInfo->id,$whereStr); 
	  }
 
		
}

public function multipleshiftqueryAction()
{
$team_id=$this->getRequest()->getParam('team_id');
$allocated_to=$this->getRequest()->getParam('assign_user_id');
$profile_id=$this->getRequest()->getParam('profile_id');
$checkid=$this->getRequest()->getParam('checkid');

if($this->getRequest()->getParam('add_tags'))
{
$tags=implode(",",$this->getRequest()->getParam('add_tags')); 
}
else
{
$tags="";  
}
	
foreach($checkid as $ass_queryid)
{
$QueryAssInfo=$this->QueryObj->getAssignQueryInfo($ass_queryid);
 
$queryid=$QueryAssInfo['id'];
$current_user=$QueryAssInfo['user_id'];
 
$Date_current1 = date("d-m-Y",time());
$follow_up_date1= strtotime($Date_current1);
$this->QueryObj->updateQuery(array("follow_up_date"=>$follow_up_date1),$queryid);	
   
 $QueryUsers=$this->QueryObj->CheckQueryUsers($allocated_to,$queryid); 
 
//$array2=array("user_status"=>0);
			   //$this->QueryObj->updateUserStatusQuery($array2,$queryid);
					if(!count($QueryUsers))
					{

					 //////////////////////////////////////////////////
 			  $date=$QueryAssInfo['date'];
 			  $priority=$QueryAssInfo['priority'];
			  $no_day=$QueryAssInfo['no_day']; 
			  $contact_by=$QueryAssInfo['contact_by'];
  			  ////////////////////////////////////////////////////
  		       $Date = date("d-m-Y",strtotime($date));
			   $date=
			   $Date_current = date("d-m-Y",time());
               $follow_up_date= strtotime($Date_current);
					
			   //////////////////////Insert user for transfer query////////////////////				//$array1=array('query_id'=>$queryid,'user_id'=>$allocated_to,'assign_date'=>time(),'update_status_date'=>time(),'transfer_type'=>1);
				   //$insert1= $this->QueryObj->addUserQuery($array1); 
				   
				   //////////////////////Update user for transfer query////////////////////
				  $array1=array('user_id'=>$allocated_to,'team_id'=>$team_id,'profile_id'=>$profile_id,'assign_date'=>time(),"open_date"=>time(),"open_status"=>0,'transfer_type'=>1,'assign_follow_up_date'=>$follow_up_date,'trans_repli_user'=>$ass_queryid,'update_status_date'=>time(),"tags"=>$tags);
				  
				   $this->QueryObj->updateExistUserStatusQuery($array1,$ass_queryid);	
					//////////////////////////////////////////////////////////////////
					for($i=0;$i< count($no_day);$i++)
					{ 
					if($i==0)
					{
					$status=1;	
					}
					else
					{
					$status=0;	
					}
					 $Date_current1 = date("d-m-Y",time());
                     $follow_up_date1= strtotime($Date_current1. ' + '.($no_day+1).' days');
					
					//$fetchDataCurrentAss= $this->QueryObj->FetchAssignIdByQueryAndUser($allocated_to,$queryid);
					$array2=array('assign_id'=>$ass_queryid,'user_id'=>$allocated_to,'followup_day'=>$follow_up_date1,"query_id"=>$queryid,'contact_by'=>$contact_by[$i],'status'=>$status);	
					$insert2= $this->QueryObj->addUserQueryFolloup($array2);
					}
					////////////////////////////////////////////////////////////////////////////////////////////
					}
					else
					{
					 //$array3=array("user_status"=>1);
					 //$update1= $this->QueryObj->updateQueryUserStatus($array3,$queryid,$allocated_to);	
					}
			   }
			   $userInfo=$this->userObj->getUserId($allocated_to);	
			   
			   $arrayAct=array("user_id"=>$this->userInfo->id,'user_name'=>$this->userInfo->name,'message'=>'Query assigned to '.$QueryAssInfo['user_name'].' on Date ','action_date'=>$QueryAssInfo['assign_date'],'query_id'=>$queryid,'query_assign_id'=>$ass_queryid);
				 $this->QueryObj->insertActionHistory($arrayAct);
			   
			$arrayAct=array("user_id"=>$this->userInfo->id,'user_name'=>$this->userInfo->name,'message'=>'transferred query to '.$userInfo['name'],'action_date'=>time(),'query_id'=>$queryid,'query_assign_id'=>$ass_queryid);
				 $this->QueryObj->insertActionHistory($arrayAct);	
				 
			    $this->_s->message='<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="icon fa fa-check"></i> The query is transferred successfully.</div>'; 
				
				
				
			    $this->_redirect('managequery/userquery');
			  
die;
}

public function multiplereplicatequeryAction()
{
$team_id=$this->getRequest()->getParam('team_id');
$allocated_to=$this->getRequest()->getParam('assign_user_id');
$profile_id=$this->getRequest()->getParam('profile_id');
$checkid=$this->getRequest()->getParam('checkid');

if($this->getRequest()->getParam('add_tags'))
{
$tags=implode(",",$this->getRequest()->getParam('add_tags')); 
}
else
{
$tags="";  
}

$userpInfo=$this->userObj->getUserProfileInfo($profile_id);
 
foreach($checkid as $ass_queryid)
{
$QueryAssInfo=$this->QueryObj->getAssignQueryInfo($ass_queryid);
$queryid=$QueryAssInfo['id'];
$current_user=$QueryAssInfo['user_id'];
$Date_current1 = date("d-m-Y",time());
$follow_up_date1= strtotime($Date_current1);
$this->QueryObj->updateQuery(array("follow_up_date"=>$follow_up_date1),$queryid);		   
$QueryUsers=$this->QueryObj->CheckQueryRepliUsers($allocated_to,$queryid,$profile_id); 
//$array2=array("user_status"=>0);
//$this->QueryObj->updateUserStatusQuery($array2,$queryid);
			  if(!count($QueryUsers))
			  {
		 //////////////////////////////////////////////////
 			  $date=$QueryAssInfo['date'];
 			  $priority=$QueryAssInfo['priority'];
			  $no_day=$QueryAssInfo['no_day']; 
			   $contact_by=$QueryAssInfo['contact_by'];
			 $priorityInfo= $this->followupsettingObj->getPriorityInfo($priority);
			   
  			  ////////////////////////////////////////////////////
  		       $Date = date("d-m-Y",strtotime($date));
			   $date=
			   $Date_current = date("d-m-Y",time());
               $follow_up_date= strtotime($Date_current);
					
 				    $array1=array('query_id'=>$queryid,'user_id'=>$allocated_to,'website_id'=>$userpInfo['website'],'team_id'=>$team_id,'profile_id'=>$profile_id,'assign_follow_up_date'=>$follow_up_date,'assign_date'=>time(),"open_date"=>time(),"open_status"=>0,'update_status_date'=>time(),'trans_repli_user'=>$ass_queryid,'transfer_type'=>2,"assign_priority"=>$priority,"assign_contact_by"=>$contact_by,"tags"=>$tags);
					
				    $insert1= $this->QueryObj->addUserQuery($array1); 
					//////////////////////////////////////////////////////////////////
					$no_day=explode(',',$priorityInfo['follow_up_day']);
					$contact_by=explode(',',$priorityInfo['contact_by']);
					 
					for($i=0;$i< count($no_day);$i++)
					{ 
					if($i==0)
					{
					$status=1;	
					}
					else
					{
					$status=0;	
					}
					 $Date_current1 = date("d-m-Y",time());
                     $follow_up_date1= strtotime($Date_current1. ' + '.($no_day[$i]).' days');
					
					$fetchDataCurrentAss= $this->QueryObj->FetchAssignIdByQueryAndUser($allocated_to,$queryid);
					$array2=array('assign_id'=>$insert1,'user_id'=>$allocated_to,'followup_day'=>$follow_up_date1,"query_id"=>$queryid,'contact_by'=>$contact_by[$i],'status'=>$status);	
					 $insert2= $this->QueryObj->addUserQueryFolloup($array2);
					}
					
					
			$userInfo=$this->userObj->getUserId($allocated_to);		
			$arrayAct=array("user_id"=>$this->userInfo->id,'user_name'=>$this->userInfo->name,'message'=>'replicated query to '.$userInfo['name'],'action_date'=>time(),'query_id'=>$queryid,'query_assign_id'=>$insert1);
			$this->QueryObj->insertActionHistory($arrayAct);
			
			 
					
					
					////////////////////////////////////////////////////////////////////////////////////////////
					}
					
					/*else
					{
					 $array3=array("user_status"=>1);
					 $update1= $this->QueryObj->updateQueryUserStatus($array3,$queryid,$allocated_to);	
					}
					*/
				 	
				 
			   }
			   $this->_s->message='<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="icon fa fa-check"></i> The query is replicated successfully.</div>';
			    $this->_redirect('managequery/userquery');
			  
die;
}

public function updateQueryProfileAction()
{
$assign_id=$this->getRequest()->getParam('assign_id');	
$profile_id=$this->getRequest()->getParam('profile_id');
if($assign_id!="" and $profile_id!="")
{
$array=array('profile_id'=>$profile_id);	
$this->QueryObj->updateExistUserStatusQuery($array,$assign_id);
echo 1;
die;	
}
	
}

public function updateQueryPriorityAction()
{
$query_id=$this->getRequest()->getParam('query_id');	
$priority_id=$this->getRequest()->getParam('priority_id');	
$assign_id=$this->getRequest()->getParam('assign_id');

$QueryInfo = $this->QueryObj->getViewQueryId($assign_id);

 $this->QueryObj->DeleteFllowupAssignQuery($assign_id);
 
$priorityArray=explode('|',$priority_id);

$priority=$priorityArray[0];
$no_day=$priorityArray[1]; 
$contact_by=$priorityArray[2];
	  
$no_day=explode(',',$no_day);
$contact_by=explode(',',$contact_by);

$Date = date("m/d/Y",time());

$follow_up_date= strtotime($Date. ' + '.($no_day[0]).' days');


$array=array('assign_priority'=>$priority,"assign_follow_up_date"=>$follow_up_date,"assign_contact_by"=>$contact_by[0]);
$this->QueryObj->updateExistUserStatusQuery($array,$assign_id);		   

for($i=0;$i< count($no_day);$i++)
{ 
if($i==0)
{
$status=1;	
}
else
{
$status=0;	
}
$Date_current1 = date("d-m-Y",time());
$follow_up_date1= strtotime($Date_current1. ' + '.$no_day[$i].' days');

$array2=array('assign_id'=>$assign_id,'user_id'=>$QueryInfo['user_id'],'followup_day'=>$follow_up_date1,"query_id"=>$query_id,'contact_by'=>$contact_by[$i],'status'=>$status);	
$insert2= $this->QueryObj->addUserQueryFolloup($array2);
}

$arrayAct=array("user_id"=>$this->userInfo->id,'user_name'=>$this->userInfo->name,'message'=>'set priority as '.$priority,'action_date'=>time(),'query_id'=>$query_id,'query_assign_id'=>$assign_id);
$this->QueryObj->insertActionHistory($arrayAct);


echo 1;
	
die;
}

public function updateQueryTagsAction()
{
$assign_qid=$this->getRequest()->getParam('assign_qid');	
$query_id=$this->getRequest()->getParam('query_id');	
$tags=$this->getRequest()->getParam('tags');
if($assign_qid)
{
$array=array('tags'=>$tags);
//print_r($array);die;
$this->QueryObj->updateExistUserStatusQuery($array,$assign_qid);

$arrayAct=array("user_id"=>$this->userInfo->id,'user_name'=>$this->userInfo->name,'message'=>'Tag update to '.$tags,'action_date'=>time(),'query_id'=>$query_id,'query_assign_id'=>$assign_qid);
$this->QueryObj->insertActionHistory($arrayAct);

$tagHtml='';
$arrTag=explode(",",$tags);
foreach($arrTag as $tags)
{
$tagHtml.='<span class="label label-warning" style="padding:2px;">'.$tags.'</span>&nbsp;';
}
 echo $tagHtml;die;
 
 
	
}

die;	
}

public function updateEscalationStatusAction()
{
$assign_id=$this->getRequest()->getParam('assign_id');	
$escalation_mark=$this->getRequest()->getParam('escalation_mark');
$array=array('escalation_mark'=>$escalation_mark);
 if($assign_id)
 {
$this->QueryObj->updateExistUserStatusQuery($array,$assign_id);

if($escalation_mark==1)
{
$this->QuoteObj->insertNotification(array("sender_id"=>$this->userInfo->id,"reciever_id"=>1,"message"=>' Escalation marked',"date"=>time(),"ref_id"=>$assign_id,"noti_tab_type"=>1));



}

 }
 echo 1;
 die;
}



public function clientMailAction()
{
	
if($this->getRequest()->getParam('Action')=="Delete")
{
 $queryid=$this->getRequest()->getParam('checkid');
 $delete=$this->QueryObj->DeleteExternalClientMail($queryid);
  $this->_redirect('managequery/client-mail');
}
	
  
if($this->userInfo->user_type=='sub-admin' or $this->userInfo->user_type=='Data Manager')
{
	$myTeamUserData= $this->userObj->getAllUsersMyTeams($this->userInfo->team_id); 
 	$profileData=$this->userObj->getUserProfilesEmails($this->userInfo->id,$myTeamUserData);
}
else
{
$profileData=$this->userObj->getUserProfilesEmails($this->userInfo->id);	
}
$webEmails=array();
foreach($profileData as $emailVal)
{
$webEmails[]=$emailVal['website_email'];
}

if($this->userInfo->user_type=='admin')
{
$profileData=$this->userObj->getUserProfilesAdminEmails();	
}


 $this->view->profileData=$profileData;

//$this->view->ClientMailExternal=$ClientMailExternal=$this->QueryObj->FetchAllClientMailExternal($webEmails,$this->userInfo->user_type);	
 

}

public function clientMailDetailsAction()
{
$this->_helper->layout->disableLayout();	



$mail_id=$this->getRequest()->getParam('mail_id');	

$this->QueryObj->UpdateClientMailInfo(array("status"=>1),$mail_id);

$this->view->mailInfo=$mailInfo=$this->QueryObj->FetchClientMailInfo($mail_id);

$this->view->clientExistData=$this->QueryObj->getMatchingEmailCleints($mailInfo['FromEmail']);
 
$this->view->totalUnraedClientMail=$this->getTotalUnreadClientMail();

	

}

function readunreadClientmailAction()
{
$mail_id=$this->getRequest()->getParam('mail_id');	
$type=$this->getRequest()->getParam('type');
if($mail_id)
{
$mail_ids=explode(",",$mail_id);
if($type=='unread')
{
$array=array("status"=>0);	
}
else
{
$array=array("status"=>1);	
}
foreach($mail_ids as $ids)
{
$this->QueryObj->UpdateClientMailInfo($array,$ids);	
}

echo count($this->getTotalUnreadClientMail());

}
die;

}

function getTotalUnreadClientMail()
{
if($this->userInfo->user_type=='sub-admin' or $this->userInfo->user_type=='Data Manager')
{
	$myTeamUserData= $this->userObj->getAllUsersMyTeams($this->userInfo->team_id); 
 	$profileData=$this->userObj->getUserProfilesEmails($this->userInfo->id,$myTeamUserData);
}
else
{
$profileData=$this->userObj->getUserProfilesEmails($this->userInfo->id);	
}

$webEmails=array();
foreach($profileData as $emailVal)
{
$webEmails[]=$emailVal['website_email'];
}

return $this->QueryObj->FetchAllUnreadClientMailExternal($webEmails,$this->userInfo->user_type);
die;	
}

function mergeClientMailAction()
{
$ref_id=$this->getRequest()->getParam('ref_id');	
$mail_id=$this->getRequest()->getParam('mail_id');	
if($ref_id!="" and $mail_id!="")
{ 
$QueryInfo = $this->QueryObj->getViewQueryId($ref_id);	
$mailInfo=$this->QueryObj->FetchClientMailInfo($mail_id);

$commentsBody=$mailInfo['TextBody'];
$lastComment = $this->QueryObj->GetLastClientEmail($ref_id);
if($lastComment['message_id'])
{
$commentsBody.='<div style="color:#500050;margin:0px 0px 0px 0.8ex;border-left:1px solid rgb(204,204,204);padding-left:1ex;"><p>On '.date("l M d, Y",$lastComment['date']).' at '.date("h:i A",$lastComment['date']).' &nbsp; &lt;'.$lastComment['from_email'].'&gt;  wrote:</p>';
$commentsBody.=$lastComment['comments'].$lastComment['email_body'].'</div>';
}	

$array=array("ass_query_id"=>$ref_id,
"query_id"=>$QueryInfo['id'],
"user_id"=>$QueryInfo['user_id'],
"comments"=>$commentsBody,
"comments_file"=>$mailInfo['Attachments'],
"date"=>strtotime($mailInfo['Date']),
"query_status"=>$QueryInfo['update_status'],
"comments_sent_type"=>'client',
"subject"=>$mailInfo['Subject'],
"from_email"=>$mailInfo['FromEmail'],
"FromName"=>$mailInfo['FromName'],
"to_email"=>$mailInfo['ToEmail'],
"message_id"=>$mailInfo['MessageID']
);
$insert= $this->QueryObj->insertComment($array);
if($insert)
{
$delete=$this->QueryObj->DeleteExternalClientMail(explode(",",$mail_id));

$arrayAct=array("user_id"=>$this->userInfo->id,'user_name'=>$this->userInfo->name,'message'=>'Merge the query','action_date'=>time(),'query_id'=>$QueryInfo['id'],'query_assign_id'=>$ref_id);
$this->QueryObj->insertActionHistory($arrayAct);
	
}
echo $insert;die;
}
die;
}


function loadclientmailAction()
{
$this->_helper->layout->disableLayout();
$whereStr ="";
if($this->getRequest()->getParam('searchBtn')!="")
{
$whereStr ="";


 
if($this->userInfo->user_type=='sub-admin' or $this->userInfo->user_type=='Data Manager')
{
	$myTeamUserData= $this->userObj->getAllUsersMyTeams($this->userInfo->team_id); 
 	$profileData=$this->userObj->getUserProfilesEmails($this->userInfo->id,$myTeamUserData);
}
else
{
$profileData=$this->userObj->getUserProfilesEmails($this->userInfo->id);	
}
//echo "<pre>";
//print_r($profileData);

$webEmails=array();
foreach($profileData as $emailVal)
{
$webEmails[]=trim($emailVal['website_email']);
}
$from_email_id=$this->getRequest()->getParam('from_email_id');
$date_filter=$this->getRequest()->getParam('date_filter');
$dateArray=explode(' - ',$date_filter);
$fromdate=$dateArray[0]; 
$todate=$dateArray[1];
$todate = date("m/d/Y",strtotime("+1 days", strtotime($todate)));

$whereStrDateFilter="";	
if($fromdate and $todate)
{ 
$whereStrDateFilter = " created_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
 /* if($whereStr!="")
   {
 $whereStr .= " AND  created_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
   }
 else{
 $whereStr .= " created_date BETWEEN '".strtotime($fromdate)."' AND '".strtotime($todate)."'";
	 }*/
}
	
if($from_email_id)
{
  $keyword = $from_email_id;
  if($whereStr!="")
   {
 $whereStr .= " AND ToEmail LIKE '%".trim($keyword)."%' or  cc_email LIKE '%".trim($keyword)."%'";
   }
 else{
 $whereStr .= " ToEmail LIKE '%".trim($keyword)."%' or  cc_email LIKE '%".trim($keyword)."%'";
	 }
}


/*else
{

 if($whereStr!="")
   {
 $whereStr .= " AND ToEmail IN  (".implode(",",$webEmails).") or  cc_email IN (".implode(",",$webEmails).")";
   }
 else{
 $whereStr .= " ToEmail IN  (".implode(",",$webEmails).") or  cc_email IN (".implode(",",$webEmails).")";
	 }	
}
*/
//echo "<pre>";
//print_r($webEmails);

$this->view->ClientMailExternal=$ClientMailExternal=$this->QueryObj->FetchAllClientMailExternal($webEmails,$this->userInfo->user_type,$whereStr, $whereStrDateFilter);	
 

}
	
}

function dismissNotificationAction()
{
if($this->getRequest()->getParam('id'))
{
$noti_id=$this->getRequest()->getParam('id');
$this->QuoteObj->updateQuoteNotification(array("status"=>1),$noti_id);	
}
echo 1;
die;
}

function checkexitemailandwebsiteAction()
{
$email_id=$this->getRequest()->getParam('email_id');
$website=$this->getRequest()->getParam('website');
$array=array();
if($email_id!="" and $website!="")
{
$DataCheck=$this->QueryObj->CheckExistQueryEmailAndWebsite($email_id,$website);
$array=array();
foreach($DataCheck as $row)
{
$array[]=array(
"ref_id"=>$row['id'],
"user_name"=>$row['user_name'],
"client_name"=>$row['client_name'],
"client_email"=>$row['client_email'],
"website"=>$row['website'],
"assign_date"=>date("d M Y",$row['assign_date']),
"status"=>$row['status_name'],
);	
}
	
}
 echo  json_encode(array("array"=>$array,"totalRecord"=>count($array)));
die;	
}

public function showAttachedFilesAction()
{
$this->_helper->layout->disableLayout();
$ref_id=$this->getRequest()->getParam('ref_id');	
$DataFiles=$this->QueryObj->getAllQueryCmmentedFiles($ref_id);
$ViewFilesAarray=array();
foreach($DataFiles as $dfiles)
{
$filesArr=explode("||",$dfiles['comments_file']);
$filepath=($dfiles['comments_sent_type']=='user')?SITEURL:SITEURL.'public/UploadFolder/';
$name=($dfiles['comments_sent_type']=='user')?$dfiles['name']:$dfiles['FromName'];
		foreach($filesArr as $files)
		{
		$userfile_extn = explode(".", strtolower($files));
		$extension = end($userfile_extn);
		$ViewFilesAarray[]=array(
		  "files"=>str_replace("public/UploadFolder/","",$files),
		  "files_url"=>$filepath.$files,
		  "files_date"=>date("d M, Y h:i A",$dfiles['date']),
		  "uploaded_by"=>$name
		);
		}
}
$this->view->commentFiles=$ViewFilesAarray;
}


}

?>