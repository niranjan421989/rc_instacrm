<?php
    /*
     * class Template_IndexController
     * Date: 15-April-2013
     * Developer: Niranjan Singh
     * Modified By: Niranjan Singh
     * 
     */
 class tags_IndexController extends Zend_Controller_Action {
    /*
     * Date: 15-April-2013
     * Developer: Niranjan Singh
     * Modified By: Niranjan Singh
     * 
     */
    public function init() {
        parent::init();
        $this->view->userInfo = $this->userInfo = Zend_Auth::getInstance()->getStorage()->read();
		//////////////////Flash message session ////////////////
        $this->_s = Zend_Registry::get('session');
        if ($this->_s->message) {
            $this->view->successmessage = $this->_s->message;
            $this->_s->message = NULL;
        }
		/////check User type
         $this->view->leftsection='manageuser';
          $this->tagsObj = new Tags_Model_Tags();
  		   if(!isset($this->userInfo->id)){ 
            $this->_redirect('dashboard/');
         }
		 
		  if($this->getRequest()->getParam('ajaxify')==1)
	   {
		 $this->_helper->layout->disableLayout();
	   }
     }
     /*
     * To list the Templates 
     * Date: 14-OCT-2015
     * Developer: Niranjan Singh
     * Modified By: Niranjan Singh
     * 
     */
    public function indexAction()
 	 { 
	 
	    if(isset($this->userInfo->id) and $this->userInfo->user_type=='admin')
		{
		$this->view->tagsArr =$userfetch= $this->tagsObj->getAllTags();
		}
		else
		{
		$this->view->tagsArr=$this->tagsObj->getAllCategoryTags($this->userInfo->category,$this->userInfo->id);
		}
	
		
		if($this->getRequest()->getParam('Action')=="Delete")
	    {
		 $userid=$this->getRequest()->getParam('checkid');
 		 $delete=$this->tagsObj->DeleteTags($userid);
		 $this->_redirect('tags');
	    }
		 
     }
       /*
      * Date: 15-April-2013
      * Developer: Niranjan Singh
      * Modified By: Niranjan Singh
      * To update temeplate
      */
    public function addAction() 
 	{
		if($this->getRequest()->getParam('id'))
 	    {
 		  $this->view->id=$id =$this->getRequest()->getParam('id');
 		 $this->view->tagInfo = $this->tagsObj->getTagId($id);
	 
  	 }
		
  		if($this->getRequest()->isPost('submitBtn') and $this->getRequest()->getParam('submitBtn')!="")
 			{
 			  $tag_name=$this->getRequest()->getParam('tag_name');
 			  $category=$this->getRequest()->getParam('category');
 			  $tag_type=$this->getRequest()->getParam('tag_type');
 			  $array=array("user_id"=>$this->userInfo->id,"tag_name"=>$tag_name,"category"=>$category,"tag_type"=>$tag_type);
			  
			  if($id)
			  {
			  $update=$this->tagsObj->updateTags($array,$id);  
			  }
			  else
			  {
  			  $insert=$this->tagsObj->addTags($array);
			  }
			  $this->_redirect('tags');
			}	

}

	
 
}