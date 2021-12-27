<?php
 class IndexController extends Zend_Controller_Action
 {
    public function init()
    {
        /* Initialize action controller here */

		//parent::init ();

		//$this->_helper->ViewRenderer->setViewSuffix('php');

		//Zend_Session::start();	

		$this->db = Zend_Registry::get('db');
		$this->view->userInfo = $this->userInfo = Zend_Auth::getInstance()->getStorage()->read(); 

    }
    public function indexAction()
    {	
		if(isset($this->userInfo->id)){
			$this->_redirect(SITEURL.'dashboard');	

		}

		$this->_helper->layout->disableLayout();
		$db = $this->_getParam('db');
		$this->view->redirect_url=$redirect_url = $this->_getParam('redirect_url');
		if($this->getRequest()->isPost('submit'))
		{

			 if ($this->getRequest()->getParam('username')!='' && $this->getRequest()->getParam('password')) {			         
					$adapter = new Zend_Auth_Adapter_DbTable(
								$db,

									'tbl_users',

									'username',

									'password'
					);

					$adapter->setIdentity(trim($this->getRequest()->getParam('username')));

					$adapter->setCredential(trim($this->getRequest()->getParam('password')));

					$auth = Zend_Auth::getInstance();

					$result = $auth->authenticate($adapter);
					 
				  


					if ($result->isValid()) {
  						$data = $adapter->getResultRowObject();
						 if($data->status==1)
							 {
							 date_default_timezone_set("Asia/Kolkata");
							 if($data->last_visits)
							 {
							 $_SESSION['last_visits']= $data->last_visits;	
							 }
							 else
							 {
							 $_SESSION['last_visits']= date("d F Y H:i:s",time());
							 } 
							 $today=date("d F Y H:i:s",time());
							 $array=array("last_visits"=>$today);
	 
							 $auth->getStorage()->write($data);
							 session_regenerate_id();
							 if($redirect_url!="")
							 {
								 $this->_redirect($redirect_url);
							 }
							 else
							 {
								$this->_redirect(SITEURL.'dashboard'); 
							 }
							
							//return;
							 }
						 else
						 { 
						 $this->view->errMsg = "Your account has been deactivated. Please contact administrator.";
 						 }

					} else {

						$this->view->errMsg = "Whoops! We didn't recognise your username or password. Please try again.";

					}



				 



			}else{



				$this->view->errMsg = "Whoops! We didn't recognise your username or password. Please try again.";



			}



	 }



		



		



    }



	



	public function logoutAction()



	{



		Zend_Auth::getInstance()->clearIdentity();



        $this->_redirect(SITEURL.'login');



		



	}









}







