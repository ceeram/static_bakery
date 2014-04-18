Dynamic GridHelper Using Cakephp
================================

In this post I will describe how to integrate dynamic grid helper with
CakePHP application. In every project, we need to develop tabular data
grid to display MySQL table data such as Member List, Order List, etc.
It is often a monotonous work for developers to create grid views and
work on them. So we created a generalized, flexible and dynamic
tabular Data Grid Helper which can be useful for displaying any MySQL
table data. The following are the important features and benefits of
this helper: 1. generates dynamic grid using different columns 2.
reusability of code 3. a time saving tool 4. display or hide a part of
your grid 5. simple and easy to configure 6. options to filter data
grid 7. dynamic filter elements 8. Ajax based filtering 9.
single/multiple checkboxes actions as per requirements
Primary Configuration
Make sure prototype.js is included in your layout.

+ Here I am working with â€œadminâ€ section so my URLs are like this:
  `http://www.yourdomain.com/admin/controller_name/action_name`_
+ Note :

    + Make sure when you are working with admin section your admin routing
      will be enabled in your â€œapp/config/core.phpâ€ as given below:
      Configure::write('Routing.admin', 'admin');
    + And in your â€œapp/config/routes.phpâ€ put the given code.
      Router::connect('/admin', array('controller' => 'users', 'action'
      =>â€™ usermanagementâ€™, 'admin' => 1));



Create your database and run following sql.

â€¢ CREATE TABLE `userdetails` (
`id` int(11) NOT NULL auto_increment,
`user_id` int(11) NOT NULL,
`firstname` varchar(30) NOT NULL,
`lastname` varchar(30) NOT NULL,
`birthdate` date NOT NULL,
`sex` enum('Male','Female') NOT NULL,
`email` varchar(75) NOT NULL,
`created` datetime NOT NULL,
`modified` datetime NOT NULL,
PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1
AUTO_INCREMENT=9 ;

INSERT INTO `userdetails` (`id`, `user_id`, `firstname`, `lastname`,
`birthdate`, `sex`, `email`, `created`, `modified`) VALUES
(1, 3, 'Minesh ', 'Shah', '2009-10-30', 'Male',
'minesh@rightwaysolution.com', '2009-10-30 15:03:36', '0000-00-00
00:00:00'),
(2, 4, 'Bharat', 'Maheria', '2009-10-30', 'Male',
'bharat@rightwaysolution.com', '2009-10-30 15:04:33', '0000-00-00
00:00:00'),
(3, 5, 'rightway', 'solution', '2009-11-08', 'Male',
'admin@rightwaysolution.com', '2009-11-09 17:09:53', '0000-00-00
00:00:00'),
(4, 6, 'rws', '', '2009-11-09', 'Male', 'amin@rws.com', '2009-11-09
17:10:43', '0000-00-00 00:00:00'),
(5, 7, 'rose', 'tailor', '2009-11-04', 'Male', 'rose@gmail.com',
'2009-11-09 17:14:12', '0000-00-00 00:00:00'),
(6, 8, 'jack', 'bristal', '2009-11-05', 'Male', 'jack@yahoo.com',
'2009-11-09 17:14:49', '0000-00-00 00:00:00'),
(7, 9, 'graham', 'black', '2009-11-05', 'Male', 'graham@hotmail.com',
'2009-11-04 17:16:49', '0000-00-00 00:00:00'),
(8, 10, 'jennifer', 'white', '2009-11-03', 'Female',
'jennifer@bolgspot.com', '2009-11-04 17:17:55', '0000-00-00
00:00:00');

â€¢ CREATE TABLE `user_types` (
`id` int(11) NOT NULL auto_increment,
`name` varchar(30) NOT NULL,
`created` datetime NOT NULL,
`status` tinyint(1) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

INSERT INTO `user_types` (`id`, `name`, `created`, `status`) VALUES
(1, 'SUPERADMIN', '2009-10-28 13:14:34', 1),
(2, 'ADMIN', '2009-10-28 13:14:46', 1),
(3, 'Free', '2009-10-30 15:18:20', 1),
(4, 'VIP', '2009-10-30 15:18:22', 1);

â€¢ CREATE TABLE `users` (
`id` int(11) NOT NULL auto_increment,
`user_type_id` int(11) NOT NULL,
`username` varchar(30) NOT NULL,
`password` varchar(100) NOT NULL,
`status` tinyint(1) NOT NULL,
`modified` datetime NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

INSERT INTO `users` (`id`, `user_type_id`, `username`, `password`,
`status`, `modified`) VALUES
(1, 1, 'superadmin', 'c765ba760b9677c7db6636a64b310ac1024b0dfd', 1,
'2009-10-29 06:50:18'),
(2, 2, 'admin', '1a6fcd9e97f700fd0794748af955360389fc525c', 1,
'2009-11-05 09:26:20'),
(3, 3, 'minesh', 'minesh', 1, '0000-00-00 00:00:00'),
(4, 4, 'bharat', 'bharat', 1, '0000-00-00 00:00:00'),
(5, 3, 'rightwaysolution', 'rightwaysolution', 1, '0000-00-00
00:00:00'),
(6, 3, 'rws', 'rws', 1, '0000-00-00 00:00:00'),
(7, 4, 'rose', 'rose', 1, '0000-00-00 00:00:00'),
(8, 5, 'jack', 'jack', 1, '0000-00-00 00:00:00'),
(9, 3, 'graham', 'graham', 1, '0000-00-00 00:00:00'),
(10, 4, 'jennifer', 'jennifer', 1, '0000-00-00 00:00:00');

Note: Make sure that you have created and configured your database
with your cakephp application.



Step 1 (Create Grid Helper: â€œgrid.phpâ€)
Create /app/views/helpers/grid.php


Helper Class:
`````````````

::

    <?php 
    
    class GridHelper extends AppHelper
    {
         
       /**
        * Dependent on the following helpers
        *
        * @var     array
        * @access  public
        */
       var $helpers = array('Html', 'Number','Time',"Session","Ajax");
       
       
       /*  	Used to set form action of Grid 
       		@var String 
       		Example: var $formAction="/admin/users/generategrid";  
       */
    	var $formAction="";		  
    	
    	
    /* Used to set checkbox name of "Check All" column  
       @var String 
    	   Example: var $checkboxName="data[User][chkitem][]";
    */
    	var $checkboxName="";
    	
    	
       
    	/* Used to set visibility of text actions such as "Delete","Block", etc.. 
    	   @var Bool (either 0 or 1)
    	   		Set 1 => Text actions are visible in grid. 
    			Set 0 => Text actions does not visible in grid. 
    	   Example:	var $setTextActionsFlag=1;
    	*/
    	var $setTextActionsFlag=1;
    	
    	
    		
    	/* Used to set Text Actions that are applicable on Multiple checkboxes
    	   @var     array
    	   Example:	var $setTextActions=array("Delete","Block","Active");
    		
    	*/
    	var $setTextActions=array();	
    	
    	
    		
    	/* Used to set visibility of filter combobox. 
    	   @var Bool (either 0 or 1) 
    	   			Set 1 => filter combobox is visible in grid. 
    				Set 0 => filter combobox does not visible in grid. 
    	   Example:	var $filterComboFlag=1;
    	*/
    	var $filterComboFlag=1;
    	
    	
    	/* Used to set filter Combobox's values  
    	   @var     array
    	   Example:	var $filterComboValues=array("Block"=>"Block","1"=>"Active","0"=>"In-Active");
    	*/
    	var $filterComboValues=array();
    	
    	
    	
    	/* Used to set filter Combobox's "onChange" action 
    	   @var     array
    	   Example:	var $filterComboAction=array('update' => 'update_gridcontent',
    											  'url'    => '/admin/users/filter',
    											  'frequency' => 2.5);
    										
    	  Note: The array is same as when you work with Ajax's "observeField" and passed the Options.
    	*/
    	var $filterComboAction=array();
    	
    	
    	/*
    	   Used to set action for single row such as deleting single record, block single record etc..
    	   @var     array
    	   Example:	var $performSingleAction=array('update' => 'update_gridcontent',
    											  'url'    => '/admin/users/callaction',
    											  'frequency' => 2.5);
    										
    	*/
    	var $performSingleAction="";
    	
    	
    	
    	/*
    		Usered as a array index of your Dataset.
    		Note: Do not change its value.
    	*/
    	var $recordCounter=0;
    	
    	
    	/* Used to set the Dataset on which the grid is build.  
    	   @var     array
    	   Example:	var $data=[your dataset array variable];
    	   
    	   Note: your dataset variable must be in form of cakephp standard.	
    	*/  
    	  var $data=array();
    	  
    	  
    	  /* Used to set dynamic headers for grid.
    	  	 @var     array
    		 Example: var $headers=array("Name"=>array("Userdetail.firstname"),
    								 "Profile Photo"=>array("Userdetail.profile_photo","type"=>"image","path"=>"/minesh/"),
    								 "Registration Date"=>array("Userdetail.created"));
    		
    		Note: your headers must be in form of:
    		var $headers=array("your-grid-header-caption"=>array("Model.Fieldname"));
    	   */
    	  var $headers=array();
       
       
      	 /*
       		Used to generate to grid.
    		return String as Grid.
           */
       	function create()
       	{
       		$captions=$this->headers;
    		$records=$this->data;
    		$col_span=count($captions);
    		
    		
    		$str='';
    		$str.='<div><form name="gridform" id="gridform" action="'.$this->Html->url($this->formAction).'" method="post">';	
       		$str.='<table width="100%" border="0" cellpadding="0" cellspacing="1" class="tabledata">';
    		
    		
    		if($this->filterComboFlag==1 or $this->setTextActionsFlag==1)
    			$str.= $this->generateTextActions($this->setTextActions,$col_span,array_keys($captions),array_values($captions),$this->filterComboValues);
    		
    		if($this->setTextActionsFlag)
    			$str.= '<input type="hidden" name="data[Action][type]" id="action" value="" />';
    			
    		if($this->filterComboFlag)
    			$str.= '<input type="hidden" name="data[Filter][type]" id="filter" value="" />';
    			
    			$str.= '<input type="hidden" name="data[Action][value]" id="action_value" value="" />';
    			
    		$str.=$this->generateHeaders((array)$this->headers);
       			   	
      		$rows = '';
    		$rows .= $str; 
    		if(!count($records))
    			$rows .='<tr><td colspan='.$col_span.' align="center" style="color:red;"> No Records Found. </td></tr>';
    	  	else
      		{
    			 foreach ($records as $record)
       	 			$rows .= $this->Html->tableCells(array_values($this->_format($record,array_keys($captions),array_values($captions))),array("class"=>"one"),array("class"=>"two"),true);
    		}
    		$rows.="</form>";
    	
    		$view_obj = ClassRegistry::init("View");
    		$rows .= $view_obj->renderElement("admin/paging");
    		$rows .="</td></tr></table></div>";
      	
    		$rows .=$this->Ajax->observeField('action_value', $this->performSingleAction);
    
    	  return $rows;
    	} 
    	
    	
    	/**  Generates TextActions which are applicable to Multiple Records such as "Delete","Block", etc..
    	 @$actions array  
    	 @$span Mixed
    	 @$captions array
    	 @$header_values array
    	 return String;
    	 */ 
    
    
            function generateTextActions($actions,$span,$captions,$header_values,$filterComboValues=null)
    	{
    		$str='<tr><td colspan="'.$span.'" class="Pagination">';
    	
    		if(is_array($filterComboValues) and $this->filterComboFlag)
    		{
    		
    			$str.='<select name="data[Filter][type]" id="filter_combo" class="selecttop" >';
    			$options="";
    			
    			(array_key_exists("selected",$filterComboValues))?$selected_index=$filterComboValues['selected']:$selected_index='';
    			
    			foreach($filterComboValues as $k=>$v):
    				if($k==="selected")	
    					continue;
    				if($k===$selected_index)
    					$options .='<option value="'.$k.'" selected >'.$v.'</option>';
    				else
    					$options .='<option value="'.$k.'" >'.$v.'</option>';
    			endforeach;
    			
    			$str  .=$options."</select>";	
    		
    			$str.=$this->Ajax->observeField('filter_combo', $this->filterComboAction);
    		
    	}
    	
    	$action_str='';
    	if($this->setTextActionsFlag)
    	{	
    		
    		$x=array_search("CheckAll",$captions);
    		(array_key_exists($x,$captions))?$field_name=$header_values[$x]['name']:$field_name="";
    			
    		foreach($actions as $k=>$v):
    			$action_str .= " | ".$this->Html->link($v,"javascript:void(0);",array("onclick"=>"return validate_remove(\"".$field_name."\",\"".$v."\");"),null,false)." |"; 
    		endforeach;			
    	}
    	return $str.$action_str."</td></tr>";
    	
    }
    
    /**  Formats values of recordset into one row and returns the row
    	 @$cols array  
    	 @$captions array 
    	 @$header_values array
    	 return array
    	  */ 
    function _format($cols,$captions,$header_values) { 
    		$arr=array();
    		
    		foreach($header_values as $k=>$v):
    		
    			if($captions[$k]=="CheckAll")
    			{
    				if($this->setTextActionsFlag==0) continue;
    				$x=explode(".",$v['value']);
    				$arr[]='<input type="checkbox" name="'.$v['name'].'" value="'.$cols[$x[0]][$x[1]].'" id="checkbox2" class="checkbox" />';
    				
    			}
    			else
    			{
    				if(count($v)>0 and $captions[$k]!="Actions")
    				{
    					$x=explode(".",$v[0]);
    					if(count($x)==2)
    					{
    						if(array_key_exists("type",$v) and $v['type']=="image")
    							$arr[]=$this->generateActions(array("IMAGE"=>$v['path']),$cols[$x[0]][$x[1]]); 
    						else
    							$arr[]=$cols[$x[0]][$x[1]];
    					}
    					else
    						$arr[]=$x[0];
    				}
    				else
    				{
    					if(is_array($v))
    						$arr[]=$this->generateActions($v); 
    				}
    			}
    		endforeach;
    		$this->recordCounter++;
    	  return $arr;
    } 
    
    /* Generates Single Row Action such as "Delete","Block" etc.. 
    	@$actions array  
    	@$img_name image_name 
    	@return array i.e. (actions)
    	*/ 
    
    function generateActions($actions,$img_name=null)
    {
    	$action_str='';
    	$i=0;
    	foreach($actions as $k=>$v):
    	
    	
    		if($k!="IMAGE")
    		{
    			$x=explode(".",$v[0]);
    			$action=array("onclick"=>"return call_single_action('".$k."','".$this->data[$this->recordCounter][$x[0]][$x[1]]."');");
    		}
    		
    		switch($k)
    		{
    		
    		case "Delete":
    						
    		$action_str .= $this->Html->link($k,"javascript:void(0);",$action,null,false)." | "; 	
    		break;
    		case "Block":
    		$action_str .= $this->Html->link($k,"javascript:void(0);",$action,null,false)." | "; 	
    		break;
    			
    		case "TrustedMember":
    		$action_str .= $this->Html->link($k,"javascript:void(0);",$action,null,false)." | "; 	
    		break;
    			
    		case "MakeAsAdmin":
    		$action_str .= $this->Html->link($k,"javascript:void(0);",$action,null,false)." | "; 	
    		break;
    			
    		case "Approve":
    		$action_str .= $this->Html->link($k,"javascript:void(0);",$action,null,false)." | "; 	
    		break;
    			
    		case "Suspended":
    		$action_str .= $this->Html->link($k,"javascript:void(0);",$action,null,false)." | "; 	
    		break;
    			
    		case "View":
    		$action_str .= $this->Html->link($k,$v,false,null,false)." | "; 	
    		break;
    			
    		case "Edit":
    		$action_str .= $this->Html->link($k,$v,false,null,false)." | "; 	
    		break;
    		case "IMAGE":
    		$action_str .= $this->Html->link($this->Html->image($v.$img_name,array('width'=>80,'border'=>'0','height'=>54)),"#",false,null,false); 	
    		break;
    							
    		}
    		
    	endforeach;
    	return $action_str;
    	
    }
    
    /* Transforms keys into Headers  
    	@param array  
    	@return array 
    	@access private */ 
    function generateHeaders($keys) { 
    	
    		$header_str='';
    		$header_str.="<tr>";
    		foreach($keys as $k=>$v):
    			if($k==="CheckAll")
    			{
    				if($this->setTextActionsFlag==0) continue;
    				
    				$header_str.='<td class="Tabhead"><a href="javascript:void(0);" "class"="checkbox" id="chkall"  onclick="javascript:checkall(\''.$v["name"].'\');" >Check All</a></td>';
    			}
    			else
    				$header_str.="<td class='Tabhead'>".$k."</td>";
    		endforeach;
    		$header_str.="</tr>";
    	
    	return $header_str; 
    } 
    }
    
    
    ?>



Step 2 (Create Controller: â€œusers_controller.phpâ€)
Create â€œapp/controllers/users_controller.phpâ€


Controller Class:
`````````````````

::

    <?php 
    
    class UsersController extends AppController {
    	var $name = 'Users';
    	var $helpers = array('Html','Ajax','Form','Javascript',"Grid");
    	var $uses = array('User',"Userdetail","UserType");
    	var $components = array('RequestHandler', 'Session');
          /* Note:   if you are not working with admin section just remove prefix "admin_" from all the below funtion. I am at admin section so that i have used for example "admin_callaction". */
    	function admin_callaction()
    	{
    		Configure::write("debug",0);	
    		if($this->RequestHandler->isAjax())
    		{
    			if(array_key_exists("Action",(array)$this->data))
    			{
    				$x=array();
    				$x=explode(",",$this->data['Action']['value']);
    				$this->data['Action']['type']=$x[0];
    				$ids=$x[1];
    			}
    		}	
    		else
    		{
    			$this->layout='default_admin';
    			if(array_key_exists("User",(array)$this->data))
    				$ids=$this->data['User']['chkitem'];
    		}
    		switch($this->data['Action']['type'])
    		{
    		case "Delete":	$this->User->deleteAll(array("User.id"=>$ids)); break;
    		case "Block": 			$this->User->updateAll(array("is_blocked"=>1),array("User.id"=>$ids)); 	break;
    		case "TrustedMember": 	$this->User->updateAll(array("is_trusted_member"=>1),array("User.id"=>$ids)); break;	
    		case "MakeAsAdmin":		$this->User->updateAll(array("user_type_id"=>2),array("User.id"=>$ids)); break;	
    		case "Approve":			$this->User->updateAll(array("is_verify"=>1),array("User.id"=>$ids)); break;				
    		case "Suspended":		//$this->User->updateAll(array("is_verify"=>1),array("User.id"=>$ids)); 
    			break;
    		}		
    		if($this->RequestHandler->isAjax())
    		{
    			$conditions='User.user_type_id="3" or User.user_type_id="4"';
    			$this->paginate = array(
    				//'limit' => ADMIN_PGLIMIT, 
    				'limit' => 2, 
    				'recursive' => 1,
    				'conditions' => $conditions ,
    				'fields' => '',
    				'order' => array('User.id'=>'DESC'));
    			$userlisting = $this->paginate('User');
    			$this->set('userlisting',$userlisting);	
    			$this->viewPath = 'elements'.DS.'admin';
    		    $this->render('grid');
    			$this->Session->setFlash('<p class="success-message">'.count($ids).' Members Successfully '.$this->data['Action']['type'].' </p>');
    		}
    		else
    		{
    			$this->Session->setFlash('<p class="success-message">'.count($ids).' Members Successfully '.$this->data['Action']['type'].' </p>');
    			$this->redirect("/admin/users/usermanagement");
    		}
    	}
    	function admin_usermanagement()
    	{
    		Configure::write("debug",0);
    		$this->layout='default_admin';
    		$conditions='User.user_type_id="3" or User.user_type_id="4"';
    		$this->paginate = array(
    			//'limit' => ADMIN_PGLIMIT, 
    			'limit' => 2, 
    			'recursive' => 1,
    			//'conditions' => $conditions ,
    			'fields' => '',
    			'order' => array('User.id'=>'DESC'));
    		$userlisting = $this->paginate('User',$conditions);
    		$this->set('userlisting',$userlisting);	
    	}
    	function admin_filter()
    	{
    		Configure::write('debug',0);
    		$filterValue = '';
    		if(!empty($this->data['Filter']['type']) and $this->data['Filter']['type']!="All") {
    			$filterValue = $this->data['Filter']['type'];
    			$user_type=$this->User->find("upper(UserType.name)='".strtoupper($filterValue)."'",array("UserType.id"));
    			$condition="User.user_type_id=".$user_type['UserType']['id'];
    			$order="";
    		}
    		else
    		{
    			$condition='User.user_type_id="3" or User.user_type_id="4"';
    			$order="User.id DESC";
    		}
    		$this->paginate = array(
    			//'limit' => 1,
    			'recursive' => 1,
    			'conditions' =>$condition,
    			'fields' => '',
    			'order' => ''
    		);
    		$userlisting = $this->paginate('User');
    		$this->set('userlisting',$userlisting);
    		//Calling Ajax Listing From Element
    		if($this->RequestHandler->isAjax())
    		{	
    			$this->set("selected_cmb_value",$this->data['Filter']['type']);
    			$this->viewPath = 'elements'.DS.'admin';
    		    $this->render('grid');
    		}	
        }	
    
    }       
    
    
    ?>



Step 3 (Create Element: â€œgrid.ctpâ€)
Note: To configure the grid you should go through the
â€œ/app/views/helpers/grid.phpâ€ file where all significance of all
the configuration parameters for dynamic grid explained with example.

Create â€œ/app/views/elements/admin/grid.ctpâ€



View Template:
``````````````

::

    
    <?php	
    	 	/* setting form action */
    		 $grid->formAction="/admin/users/callaction/";	
    		 
    		 /* setting up visibility of filterComboBox */					
    		$grid->filterComboFlag=1;
    		
    		/* setting up visibility of Header TextActions */	
    		$grid->setTextActionsFlag=1;
    		
    			
    		/* setting filter values for combo*/
    		$grid->filterComboValues=array("0"=>"Sort By","Free"=>"Free","VIP"=>"VIP","All"=>"All");	
    		
    		/* used to display selected filter combo value */
    		if(isset($selected_cmb_value))
    			$grid->filterComboValues=array("0"=>"Sort By","Free"=>"Free","VIP"=>"VIP","All"=>"All","selected"=>$selected_cmb_value);
    		
    		/* setting up the  filterComboAction */
    		$grid->filterComboAction=array('update' => 'update_gridcontent',
    						   'url'    => '/admin/users/filter',
    						   'frequency' => 2.5);
    		
    		/* setting up the  performSingleAction */
    		$grid->performSingleAction=array('update' => 'update_gridcontent',
    						      'url'    => '/admin/users/callaction',
    						   'frequency' => 2.5);
    							  
    		/* setting up the Header text actions */
    		$grid->setTextActions=array("Delete","Block","TrustedMember","MakeAsAdmin");
    		
    	      /* setting header captions and assoctiate its db field */
                     $grid->headers=array('CheckAll'=>array("name"=>"data[User][chkitem][]","value"=>"User.id"),"First Name"=>array("Userdetail.firstname"),
    			"Last Name"=>array("Userdetail.lastname"),
                            "Sex"=>array("Userdetail.sex"),
                            "E-Mail"=>array("Userdetail. email"),
    			"User Type"=>array("UserType.name"),
                            "Registration Date"=>array("Userdetail.created"),
    			"Login"=>array("<a href='#'> Login </a>"),
    "Actions"=>array("Delete"=>array("User.id"),"TrustedMember"=>array("User.id"),"Block"=>array("User.id"),"MakeAsAdmin"=>array("User.id")));
    							
    			/* setting up the dataset for which the grid to be generated */	  	
    				$grid->data=$userlisting; 	?>
    					 <div id="update_gridcontent">
    					  <table width="100%">
    					 	<tr>
    				<td align="center" style="color:green;">
    				<?php ($session->check('Message.flash'))?$session->flash():""; ?>
    				</td>
    						</tr>
    					</table>
    					<?php	
    						
    						/* generates your dynamic grid */
    						echo $grid->create(); 	
    					?>
    				 	</div>
    



Step 4 (Create View: â€œadmin_usermanagement.ctpâ€)
Create â€œ/app/views/users/ admin_usermanagement.ctpâ€



View Template:
``````````````

::

    
    <script language="javascript">
    function checkall(id)
    {
    	items = document.getElementsByName(id);
    	for(i=0;i<items.length;i++)
    	{
    		if(document.getElementById('chkall').innerHTML=="Un-Check")
    			items.item(i).checked = false;
    		else	
    			items.item(i).checked = true;
    	}
    	if(document.getElementById('chkall').innerHTML=="Un-Check")
    		document.getElementById('chkall').innerHTML="Check All";
    	else
    		document.getElementById('chkall').innerHTML="Un-Check";
    }
    function validate_remove(id,action)
    {	items = document.getElementsByName(id);
    	found = false;
    	x=document.getElementsByName('User');
    	for(i=0;i<items.length;i++)
    	{
    		if(items.item(i).checked)
    		{
    			document.getElementById('action').value=action;
    			document.getElementById("gridform").submit();
    			return true;
    		}
    	}		
    	if(!found)
    		alert('Please Select At-Least One Checkbox.');
    	return false;
    }
    function select_onchange(value)
    {
    	document.getElementById('filter').value=value;
    	document.getElementById("gridform").submit();
    }
    function call_single_action(action,value)
    {
    	var x=new Array();
    	if(confirm("Are You Sure You Want To Perform "+action+" On This Record?"))
    	{
    		x[0]=action;
    		x[1]=value;
    		document.getElementById('action_value').value=x;
    	}
    }
    </script>
                    <div class="content borders">
                    	<h1 id="user">User Management</h1>
    				       <div id="ContentPart">
    	 <?php 	echo $this->renderElement("admin/grid",array("userlisting"=>$userlisting)); 	?>
                    </div>
           	  </div>
    



Step 5 (Create Model: â€œuser.phpâ€)
Create /app/models/user.php



Model Class:
````````````

::

    <?php 
    
    class User extends AppModel {
    	
    	var $name = 'User';
    	var $hasOne=array('Userdetail' =>array('className' => 'Userdetail',
    						'foreignKey' => 'user_id',
    						'conditions' => '',
    						'fields' => '',
    						'order' => '',
    						'counterCache' => ''
    					),
    					'UserType' =>array('className' => 'UserType',
    						'foreignKey' => 'id',
    						'conditions' => '',
    						'fields' => '',
    						'order' => '',
    						'counterCache' => ''));
    	}
    
    ?>




Step 6 (Create Model: â€œuserdetail.phpâ€)
Create /app/models/userdetail.php



Model Class:
````````````

::

    <?php  
    
    class Userdetail extends AppModel {
    	
    	var $name = 'Userdetail';
    	
    	var $belongsTo=array('User' =>
    				array('className' => 'User',
    						'foreignKey' => 'user_id',
    						'conditions' => '',
    						'fields' => '',
    						'order' => '',
    						'counterCache' => ''));
    	}
    
    
    ?>



Step 7 (Create Model: â€œuser_type.phpâ€)

Create /app/models/user_type.php



Model Class:
````````````

::

    <?php  
    
    class UserType extends AppModel {
    	
    	var $name = 'UserType';
    	
    	
    	var $belongsTo=array('User' =>
    				array('className' => 'User',
    						'foreignKey' => 'user_type_id',
    						'conditions' => '',
    						'fields' => '',
    						'order' => '',
    						'counterCache' => ''));
    }
    
    ?>




Step 8 (Create Layout: â€œdefault_admin.ctpâ€)

Create â€œ/app/views/layouts/default_admin.ctpâ€


::

    
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Welcome to Moar Videos - Administration Section </title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">  
    <?php  echo $javascript->link("prototype"); ?>
    <?php  echo $javascript->link("scriptaculous"); ?>
    <?php  echo $html->css("admin/admin-orange.css"); ?>
    
    </head>
    <body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
    
    
    <div id="outer">
    	       		
            <!--RightPannel starts here-->
                <?php echo $content_for_layout; ?>
            <!--RightPannel ends here-->
    </div>
    
    </body>
    </html>



Step 9 (Create Paging Element: â€œpaging.ctpâ€)
Crate â€œapp/views/elements/admin/paging.ctpâ€



::

    
    <?php echo $paginator->options(array('url'=>$paginator->params['pass'])); ?>
    <tr>
    	<td colspan="7" align="right" class="Pagination">
    	<?php if($paginator->hasPrev()){
    		  	echo $paginator->prev("Previous ",array('escape'=>false), null, null);
    		 } 
    		echo $paginator->numbers(array('separator'=>' '));
    		 if($paginator->hasNext()){
    		 	echo $paginator->next(" Next",array('escape'=>false), null,null);	
    		 } 
    	?>	
    </td></tr>
    



Step 10 (Create CSS File: â€œadmin-orange.cssâ€)
Crate â€œapp/webroot/css/admin/admin-orange.cssâ€


::

    
    html,body,form,
    h1,h2,h3,h4,h5,h6,p {margin:0px;padding:0px;}
    * { margin:0; padding:0; list-style:none}
    img{ border:none}
    body{	background:#f3f3f3 ;	font-size:0.8em;	color:#777;	margin:0 auto;	font-family:Arial, Helvetica, sans-serif;}
    .contentwidth{	width:1003px;	margin:0 auto;}
    p, h1, h2, h3, h4, h5, h6{	padding:4px 0;	font-weight:normal;}
    a{color:#11B7ED;}
    a:hover{color:#393b32;}
    a img{	border:0px;}
    .content {	background:#fff url(../../img/contentbg.gif) repeat-x;	padding:4px 10px;	margin-bottom:15px;}
    .borders{ border:1px solid #cfccc9;}
    .textbox-small, 
    .textbox, 
    .textbox-large, 
    .textarea-small,
    .textbox-small2,
    .textbox-small3,
    .textarea,
    .textarea-large,
    select {	padding:2px;}
    .textbox-small {width:170px;}
    .select-small {width:178px;}
    .textbox-small2 {width:250px;}
    .textbox-small3 {width:65px;}
    .textbox {	width:350px;}
    .textbox-large {	width:450px;}
    .textarea-small {	width:250px;	height:100px;}
    .textarea {	width:350px;	height:100px;}
    .textarea-large {	width:450px;	height:100px;}
    .button-bold, .button-subdued, .calender {	font-weight:bold;	color:#fff;	padding:2px; cursor:pointer}
    .button-bold {	border:1px solid #dddddd;	background:#80E0F8 ; cursor:pointer}
    form .button-subdued {	border:1px solid #ccc;	background:#ccc; cursor:pointer;}
    p.success, p.error {	line-height:2em;	margin:8px 0;	color:#fff;	font-weight:bold;	padding:0 10px; text-align:left;}
    p.success {	background:#86ca5d;	border:1px solid #5cb327;}
    p.success a, p.error a {	color:#fff;}
    p.error {	background:#d44937;	border:2px solid #aa2b1a;}
    label.error { color:#990000;}
    table.trackreport { font-size:12px;}
    .tabledata td.Trone { background:#eef5ff}
    .tabledata tr.vipline { background:#eff6f8}
    .tabledata {border:1px solid #ccc; margin:10px 0;}
    .tabledata th {	font-weight:bold;	background:#ccc;	color:#fff;	/*text-align:left*/ }
    .tabledata td.Tabhead {	font-weight:bold;	background:#ccc;	color:#333;	}
    .tabledata td.Tabhead a { font-weight:normal; color:#000}
    .tabledata td { padding:5px}
    .tabledata img { /*padding:2px; border:#ddd solid 1px*/}
    .tabledata td.Pagination { background:#f4f4f4; color:#333}
    .tabledata td.Pagination a { color:#000; margin:0 3px}
    .tabledata div.left { display:block; float:left; margin-right:2px}
    .tabledata .shaded {	background:#eee;}
    /* clearfix start */ 
    .clearfix:after {     content: ".";    display: block;    clear: both;    visibility: hidden;    line-height: 0;    height: 0;}
    .clearfix {    display: inline-block;}
    html[xmlns] .clearfix {    display: block;}
    * html .clearfix {    height: 1%;}
    .TxtLink { text-align:right}
    .fltrit{ float:right}
    .none { display:none}
    .Message { display:block; border:#ec9d26 solid 1px; border-top:#ec9d26 solid 3px; margin-left:50px; margin-right:50px; padding:7px; color:#f79101}
    .border-new{border:1px solid #999; padding:2px;}
    /* clearfix end */ 
    .tabletop{ margin-top:10px}
    .checkbox{ vertical-align:middle}
    .sidemargin{ margin-left:15px}
    .welcome{margin-top:10px;}
    .welcome li{display:block;
    float:left;
    font-size:12px;
    margin:0 0 0px 0px;
    width:350px;
    line-height:80px;
    font-size:15px; 
    font-weight:bold; 
    margin-top:15px; 
    text-decoration:none;
    
    }
    
    .date{ font-weight:normal; font-size:11px; }
    .selecttop { float:right; width:110px; }
    p.replybtn { margin-bottom:10px; font-size:11px;}
    .reply { width:500px; display:none; clear:both; margin:0 auto 10px auto; background:#e8f5f8; padding:10px; text-align:center; border:#ddd solid 1px; overflow:hidden;}
    .reply input { float:right;}
    .reply .textarea { width:98%;}
    /* added by minesh shah */ 
    .failure-message
    {  
    	text-align:center;
    	color: #FF0000;	
    	font-weight: bold; 
    	font-size:12px;
    	font-family:Verdana, Arial, Helvetica, sans-serif;
    }
    .one {background:#fff}
    .two {background:#dff8ff}
    
    tr.one:hover
    {
    background:#A6CAF0;
    font-family:Arial, Helvetica, sans-serif;
    cursor:pointer;
    color:black;
    }
    tr.two:hover
    {
    background:#A6CAF0;
    font-family:Arial, Helvetica, sans-serif;
    cursor:pointer;
    color:black;
    }
    




Now all the configurations done so now just you have to run your
application.

For me its: `http://www.mydomain.com/admin/users/usermanagement`_



.. _http://www.yourdomain.com/admin/controller_name/action_name: http://www.yourdomain.com/admin/controller_name/action_name
.. _http://www.mydomain.com/admin/users/usermanagement: http://www.mydomain.com/admin/users/usermanagement

.. author:: rws123
.. categories:: articles, helpers
.. tags:: gridview cakephp,gridhelper cakephp,grid cakephp,dynamic
grid cakephp,grid in cakephp,data grid cakephp,Helpers

