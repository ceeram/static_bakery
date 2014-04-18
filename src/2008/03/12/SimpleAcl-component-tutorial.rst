SimpleAcl component tutorial
============================

This component follows the traditional way of acl only slightly
modifying it. In example there's an inheritance feature, which will
help tremendously the access controlling because you don't have to
specify every action in aros_acos-table.
Ok, here comes the component, from component you will find useful
information of usage so I'm not going to re-explain them here.

And of course is there a working site using SimpleAcl where you can
see SimpleAcl-running? Yeah I have is using it, but the deprecated
version. I have done major improvements to SimpleAcl since I build
that site.
And notice it's a small site I don't know how this component will
scale to larger ones. So in that site is a deprecated version but at
least it's also working.


Component Class:
````````````````

::

    <?php 
    /**
     *	SimpleAcl is using a well known acl-mechanics and slightly modifying 
     *	it to more inheritable way. Which means that if you have access to house
     *	then you have access to its rooms as well unless you specify the rules. 	 
     *	
     *	In general:
     *		I'm using a controllers names on url as they are in app.
     *		So Users in url is Users not users.
     *		
     *		Aros:  
     *			I didn't find any sensible use of the "foreign_key" in aros-table so I gave it job to couch 
     *			aros-type.
     *			ie. 
     *				 0 => users_maingroup
     *				 1 => group
     *				 2 => user 
     *			    		
     *  	 	this is used only in presentation on admin_index so its kinda unnecessary field.
     *			And it could be safely removed for not having unwanted data on queries.
     *	
     *      Acos:
     *      	Here the same case too I used that "object_id" as a type indicator
     *      	ie.
     *      		0 => layer
     *      		1 => Controller
     *      		2 => action
     *      	It could be safely removed.      
     *      	
     *		Aros_cos:
     *			They are handled normally, no any tricks are used.
     *   
     *	Specified:  
     *		About Acos:
     *	   
     *			It is possible to create more layers in acos where all permissions will
     *			inherit from a mother-node, then you put the bottom one in the check() and 
     *			SimpleAcl finds its way to top.
     *	
     *			ie. main > layer#1 > layer#2 > /Controller > /Controller/action
     *	
     *			in check array("/Controller/action", "/Controller", "layer#2")
     *			Notice if you put instead layer#1 in check then layer#2 will be pass by layer#1 and layer#2's rules are not
     *			taken into account.        
     *	
     *			Its even posible to create separate paths till you put the branch node
     *			as the bottom one in that array
     *	
     *			ie. main > layer#1#1 > layer#1#2 > /controller > /controller/action       
     *	  		   layer#2#1 > layer#2#2 > /controller > /controller/action <-- you want to test against this
     *	  		   
     *  		in check array("/Controller/action", "/Controller", "layer#2#2") 
     *  
     *			However this makes no sense, since you have to dublicate same 
     *			controllers/actions in different paths. So avoid this approach.    
     *		
     *		About Aros:
     *			
     *			As I said there's no limit for groups.
     *			And notice only a user can ask permission for certain areas never group.	  
     *	
     *  @author 		unigue__    
     *	@version		1.1
     *	@compability 	Developed for Cake 1.1.15.5144, dont know does it work on 1.2X	
     *	@see 			Cakes own acls on http://manual.cakephp.org/chapter/acl
     *	@see			Example of usage in checkAccessA-function 
     */   
    class SimpleAclComponent extends Object 
    {
    
    	// {{{ Public
    	
    	/**
    	 *	Class's name, ugly due to PHP4 issues on caps or smt
    	 *		 
    	 *	@var 	string	 
    	 */	 	
        var $name = "SimpleAcl";
        
        // }}}
        // {{{ Private
        
        /**
    	 *	Prefix of tables
    	 *		 
    	 *	@var 	string	
    	 *	@access private
    	 */	 
        var $table_prefix__;
        
        /**
    	 *	acos tables name with prepends and appends	
    	 *		 
    	 *	@var 	string	
    	 *	@access private
    	 */	 
        var $acos_table__;
    	
    	/**
    	 *	acor tables name with prepends and appends
    	 *		 
    	 *	@var 	string
    	 *	@access private
    	 */	 
    	var $aros_table__;
    	
    	/**
    	 *	axos table's name with prepends and appends
    	 *	!!! Not implemented yet !!!	 *		 
    	 *		 
    	 *	@var 	string
    	 *	@access private
    	 */	 
    	var $axos_table__;
    	
    	/**
    	 *	aros_acos tables name with prepends and appends	
    	 *		 
    	 *	@var 	string
    	 *	@access private
    	 */	 
    	var $arosacos_table__;
    	
    	/**
    	 *	Settings for in general, keep this array in 1 dimensional in case of you want 
    	 *	override existing values because array_merge can't merge deeper values. 
    	 *		 
    	 *	@var 	array	
    	 *	@access private
    	 */	 
    	var $settings__ = array(	
    		"case_sensitive"	=> true, 	// If false all acos and aros are handled in case insensitive way
    										// when they are strlowered before regex-checkes
    		"acos_table"		=> "acos", 
    		"aros_table"		=> "aros",
    		"axos_table"		=> "axos",
    		"arosacos_table"	=> "aros_acos",
    		"main_aco"			=> "main",
    		"main_aro"			=> "users_maingroup",
    		"security_level"	=> 1,
    		"cut_admin_off"		=> true
    	);
    	
    	/**
    	 *	If accessed from controller then it's link goes here
    	 *		 
    	 *	@var 	string	
    	 *	@access private
    	 */	 
    	var $controller__;
    	
    	/**
    	 *	A database link via SimpleAcl is able to execute queries 
    	 *	this is only clean way to get data from tables since other solutions would be too messy
    	 *	in practical point of view. Notice that we're only fetching data so no other operations are executed.
    	 *	So this link-method can be considered as safe.	 	
    	 *		  	 	
    	 *	@var 	object
    	 *	@access private
    	 */	 
    	var $db_link__;
    	
    	/**
    	 *	This will map undenified cruds to a known cruds
    	 *	I'm using normal names for a cruds but if you do baking then you need to keep later commented things along.
    	 *		 	 
    	 *	@var 	array
    	 *	@access private
    	 */	
    	var $cruds__ = array(
    		'create' 	=> '_create', 
    		'read'		=> '_read',
    		'update' 	=> '_update', 
    		'delete' 	=> '_delete', 
    		
    		// Additionals, add with care !!!
    		// basically this rips out the first prefix of the action 
    		// ie. /Users/create => create, /Users/show_me_my_posts => show
    		// So make sure that crud is front of the action and not in the middle or somewhere 
    		'add' 		=> '_create', 	// If baked this is the crud	
    		
    		'edit' 		=> '_update',	// If baked 	
    		
    		'delete' 	=> '_delete',	// If baked 	
    		
    		'read'		=> '_read', 	
    		'examples'	=> '_read',
    		'view'		=> '_read',		// If baked 
    	);	
    	
    	/**
    	 *	Important: for security reasons set this to false.	
    	 *	If the requested action is not in $this->cruds__ array then overwrite the crud to read.
    	 *	If false remember to put all action-prefixes in the $this->cruds__ array	
    	 *		   		 
    	 *	@var 	bool
    	 *	@access private
    	 */	 	
    	var $override_to_read__= true; 
    
    	/**
    	 *	Just a name of current controller	
    	 *		 
    	 *	@var 	string
    	 *	@access private
    	 */	
    	var	$controller_name__ = "";			
    	
    	/**
    	 *	Just a name of current action
    	 *		 	
    	 *	@var 	string
    	 *	@access private
    	 */
    	var $action_name__ = "";
    	
    	/**
    	 *	Name of admin in your app	
    	 *		 
    	 *	@var 	string
    	 *	@access private
    	 */
    	var $cake_admin__ = "admin";
    	
    	/**
    	 *	Possibly one of the key in the cruds table, basically this is ripped off the action name
    	 *	ie. 1. /Users/create --> $check_aco__ = create
    	 *		2. /Products/add_products_in_basket = add and so on
    	 *			 	  
    	 *	@var 	string
    	 *	@access private
    	 */
    	var $check_aco__ = "";
    	
    	/**
    	 *	Checkin cruds initially denied of course
    	 *		 	
    	 *	@var 	string
    	 *	@access private
    	 */
    	var $access_rules__ = array( 
    		"_create" 	=> 0,
    		"_read" 	=> 0,
    		"_update" 	=> 0,
    		"_delete" 	=> 0
    	);
    	
    	/**
    	 *	What's on top of the aco tree. Basically this is for helping 
    	 *	access controlling and reducing data in aros_acos data.
    	 *	But few principles
    	 *	main
    	 *		> Controller
    	 *					> actions
    	 *					
    	 *	And if I give admin a full rights for the main-aco then all the rest acos will inherit those
    	 *	rules and no more aros_acos data is needed. 
    	 *	If we want to deny admins access to some delicate ares that will be done by adding
    	 *	few rules on those acos.	 	  	 	   	 	 	 	 	 	 
    	 *		 
    	 *	@var 	string
    	 *	@access private
    	 */
    	var $main_aco__	= "main";
    	
    	/**
    	 *	This will allow to cut the admin prefix off the url ie.
    	 *	if this is false 
    	 *		in url "/Users/admin_index" controller is "/Users" and action "/admin_index"
    	 *		Notice thus the aco must be in form /Users/admin_index in table. 
    	 *		this is what SimpleAcl presumes	 
    	 *	if this is true	 
    	 *		the url will mod to "/admin/Users/index" where controller is "/admin/Users" and action is "/index"
    	 *		This is must better rule because the urls are saved in acos-table as they appear
    	 *		on browser's address-field	 	 	 
    	 *		 	 	
    	 *	@var 	string
    	 *	@access private
    	 */
    	var $cut_admin_off__ = true;
    	
    	/**
    	 *	Top of the aros hierarchy	
    	 *	Remeber there's no limits of groups in aros table or at least SimpleAcl is not bigoted about it.
    	 *	 	 	
    	 *	@var 	string
    	 *	@access private
    	 */
    	var $main_aro__ = "users_maingroup";
    	
    	/**
    	 *	Controls the security level of SimpleAcl
    	 *	 	 	
    	 *	@var 	integer	
    	 *		0 is highest
    	 *		1 is lowest	 
    	 *	@access private
    	 */
    	var $security_level__ = 1;
    
    	// }}}
    	// {{{ Functions	
    	
     	/**
     	 *	Setup for controller Cakes stuff
     	 */	   	
        function startup(&$controller) 
    	{	
    		$this->controller__ = $controller;	
        }	
    
    	/**
    	 *	Initialize SimpleAcls vars
    	 *	NOTICE! If your tables has different prefixes, leave $table_prefix empty and 
    	 *	write full table names for each table	 
    	 *		 
    	 *	@param	array	settings 
    	 *	@param	object 	db-link	 	 
    	 *	@param	array 	params		 
    	 */	 	 	 	 	 	
        function init($settings, $db_link, $params) 
    	{
    
    		$this->settings__			= array_merge($this->settings__, $settings);
    	
    		$this->table_prefix__		= $this->settings__["table_prefix"];        
    		$this->acos_table__ 		= $this->settings__["table_prefix"].$this->settings__["acos_table"];
    	    $this->aros_table__ 		= $this->settings__["table_prefix"].$this->settings__["aros_table"];
    	    $this->axos_table__ 		= $this->settings__["table_prefix"].$this->settings__["axos_table"];
    		$this->arosacos_table__ 	= $this->settings__["table_prefix"].$this->settings__["arosacos_table"];
    		$this->security_level__ 	= $this->settings__["security_level"];
    		$this->cut_admin_off__ 		= $this->settings__["cut_admin_off"];
    		$this->main_aco__ 			= $this->settings__["main_aco"];
    		$this->main_aro__ 			= $this->settings__["main_aro"];		
    		$this->db_link__			= $db_link;				
    
    		$this->controller_name__ 	= $params['controller'];			
    		$this->action_name__ 		= $params['action'];
    	
    		// Make sure that admin is correct
    		if(defined('CAKE_ADMIN')) {
    			$this->cake_admin__ = CAKE_ADMIN;
    		}			
        }
    	
    	/**
    	 *		(*1) Results are returned in form:
    	 *				
    	 *	 		array( 
    	 *				Number => array(
    	 *					[parent] => array(
    	 *						[aro_id] 		=> Number,
    	 *					)
    	 *				), 
    	 *				...
    	 *			);	
    	 *	
    	 *	@param	string 	A Calling aro usually users name
    	 *	@return	array	See (1*)	 
    	 */
    	function getAroTree__($aro) 
    	{	
    
    		if(!$this->settings__["case_sensitive"]) {
    			$aro = strtolower($aro);
    		}
    		
            $aro_access_query	= "
    			SELECT parent.id as aro_id, parent.alias
    			FROM {$this->aros_table__} AS node,
    			{$this->aros_table__} AS parent
    			WHERE node.lft BETWEEN parent.lft AND parent.rght
    			AND node.alias = '{$aro}'										
    			ORDER BY parent.lft;";
    						
    		$aro_tree = $this->db_link__->query(($aro_access_query));
    		
    		return $aro_tree;	
    	}	
    	
    	/**
    	 *	Get leaf aco tree
    	 *	
    	 *	Results are in form 
    	 *	(*1)
    	 *		Array(	
    	 *			[Number] => Array
    	 *	        	(
    	 *	            [parent] => Array
    	 *	                (
    	 *	                    [aco_id] => Number
    	 *	                )
    	 *    			), 
    	 *    ...
    	 *    );				
    	 *
    	 *	@param	array	array( most bottom, ... , top one ) ie. array(  'controller/action', 'controller' )
    	 *	@return	array	See (*1) Aco-tree if wild_card_acos is empty null is returned
    	 */
    	function getAcoTree__($wild_card_acos) 
    	{	
    		// Checking that aco really exist
    		$aro_exist_query 	= "";
    
    		// Start from which exist
    		$exist_aco 			= null;
    		$aco_tree 			= null;
    			
    		if(empty($wild_card_acos) || !is_array($wild_card_acos)) {
    			return null;
    		}
    		else {				
    			/**
    			 *	So it starts traversing from the bottom to the top and when it finds an exist one aco it 
    			 *	register it to var and breaks the loop		 
    			 */		 		 		
    			foreach($wild_card_acos as $wild_card_aco) {
    			
    				// If there's typoes caps in the acos 
    				if(!$this->settings__["case_sensitive"]) {
    					$wild_card_aco = low($wild_card_aco);
    				}
    			
    				$aco_exist_query 	= "SELECT * FROM {$this->acos_table__} as aco WHERE aco.alias = '{$wild_card_aco}'";
    				$does_aco_exist  	= $this->db_link__->query($aco_exist_query);
    				
    				if(!empty($does_aco_exist)) {				
    					$exist_aco = $wild_card_aco;
    					break;
    				}
    			}
    	
    			// Aco exists, then take the tree based on that
    			if($exist_aco != null) {
    				$aco_access_query	= "
    					SELECT parent.id as aco_id, parent.alias
    					FROM {$this->acos_table__} AS node,
    					{$this->acos_table__} AS parent
    					WHERE node.lft BETWEEN parent.lft AND parent.rght
    					AND	node.alias = '{$exist_aco}'										
    					ORDER BY parent.lft;";
    									
    				$aco_tree = $this->db_link__->query($aco_access_query);		
    			}
    			
    			return $aco_tree;	
    		}								
    	}
    
    	/**
    	 *	!!! Not implemented yet !!!	
    
    	 *	Gets all defined axos	
    	 *	$aro_tree = 
    	 *		array( 
    	 *				Number => array(
    	 *					[parent] => array(
    	 *						[aro_id] 		=> Number,
    	 *					)
    	 *				), 
    	 *				...
    	 *			);	
    	 */
    	function getAxos__($aro_tree) 
    	{										
    	}
    
    	/**
    	 *	Get aros_acos-tree for given acos. So you get rules for acos that you have 
    	 *	putted in check array
    	 *	
    	 *	(*1) Results are in form:
    	 *	
    	 *		Array (
    	 *			    [Number] => Array
    	 *			        (
    	 *			            [aro_aco] => Array
    	 *			                (
    	 *			                    [id] => Number
    	 *			                    [aro_id] => Number
    	 *			                    [aco_id] => Number
    	 *			                    [_create] => Number
    	 *			                    [_read] => Number
    	 *			                    [_update] => Number
    	 *			                    [_delete] => Number
    	 *			                )
    	 *			
    	 *			        ),
    	 *				...
    	 *			);			
    	 *
    	 *	@param	array	ids in form [NUMBER][parent][aro_id]
    	 *	@param	array	ids in form [NUMBER][parent][aco_id]		
    	 *	@return	array	see (*1) Aco-tree, in case of in wild_card_acos is empty then null is returned
    	 */
    	function getArosAcos__($aro_tree, $aco_tree) 
    	{
    		$aros_acos_ids = array();
    	
    		if(empty($aro_tree) || empty($aco_tree)) {
    			return null;
    		}
    	
    		/**
    		 *	This will give an aro_id and aco_id's in array separated by "AND"
    		 *			
    		 *	If you have 3 wildcard-acos and 3 aros then this will loop 9 times and 
    		 *	the bigger number you give the more this would work. But this is an cpu
    		 *	not traffic-issue		 	 		 
    		 */		 		 		
    		foreach( $aro_tree as $aro_alias ) {
    			foreach($aco_tree as $aco_alias) {
    				$aros_acos_ids[] = " aro_id = ".$aro_alias["parent"]["aro_id"]." 
    					AND aco_id = ".$aco_alias["parent"]["aco_id"];
    			}
    		}
    
    		/**
    		 *	Next get all aros_acos that are associated 
    		 *	to earlier mentioned aro_id and aco_id's group 
    		 */		 		 		
    		$aros_acos_query	= "SELECT * FROM {$this->arosacos_table__} as aro_aco 
    								WHERE ".implode( " OR ", $aros_acos_ids );
    		$aros_acos_rows		= $this->db_link__->query( $aros_acos_query );
    		
    		return $aros_acos_rows;	
    	}
    
    	/**
    	 *	This is a motor of this component, it gathers aro/aco-trees and get an aros_acos by
    	 *	using them and returns a rule whether access or not	
    	 *
    	 *	@param	string	aro usually a user
    	 *	@param	array	array( most bottom, ... , top one ) ie. array(  'controller/action', 'controller' )
    	 *	@param	array	Settings for future usage, not yet implemented				
    	 *	@return	bool	whether user has access or not - 1/0
    	 */
    	function check($aro, $settings=array()) 
    	{		
    		/**
    		 *	Put admin on front of the controller		
    		 *	Because this is how they are saved in acl-tables,
    		 *	Otherwise do your own logic here
    		 */			 		
    		if($this->cut_admin_off__ && eregi("^".$this->cake_admin__."_", $this->action_name__)) {
    		
    			$this->controller_name__ = "{$this->cake_admin__}/{$this->controller_name__}";
    			
    			/** 
    			 * 	Take cake_admin prefix off of the action
    			 * 	Because the urls are saved in the db in form "admin/posts/create"
    			 */					
    			$this->action_name__ = eregi_replace("^".$this->cake_admin__."_", "", $this->action_name__);			
    		}	
    
    		list($check_crud) 	= split('[_]', $this->action_name__);
    		$this->check_aco__	= $check_crud;
    
    		$wild_card_acos = array(
    			"/{$this->controller_name__}/{$this->action_name__}", 	// ie. /Users/login
    			"/{$this->controller_name__}", 	// ie. /login
    			$this->main_aco__ // Whatever you have on top in act-tbl
    		);						
    		
    		// Highest, test only against current url
    		if($this->security_level__ == 0) {
    			$wild_card_acos = array("/{$this->controller_name__}/{$this->action_name__}"); 
    		}
    						
    		$aro_tree = $this->getAroTree__($aro);		
    		$aco_tree = $this->getAcoTree__($wild_card_acos);
    		
    		// Sanitaze, return 0 as no access if one of is empty
    		if(empty($aco_tree) || empty($aro_tree)) {
    			return 0;
    		}
    				
    		$aros_acos = $this->getArosAcos__($aro_tree, $aco_tree);
    		
    		// We have to start traversing from the top because child nodes inherit rules and specifies them.		
    		if(!empty($aros_acos)) {
    		
    			/*
    				[0] => Array
    			        (
    			            [parent] => Array
    			                (
    			                    [aro_id] => 1
    			                    [alias] => users_maingroup
    			                )
    			
    			        )
    			*/
    			// Aros 
    			foreach($aro_tree as $aro) {
    			
    				/*
    				[0] => Array
    			        (
    			            [parent] => Array
    			                (
    			                    [aco_id] => 7
    			                    [alias] => main
    			                )
    			
    			        )
    				*/
    				// Acos
    				foreach($aco_tree as $aco) {
    				
    					/*
    						[0] => Array
    					        (
    					            [aro_aco] => Array
    					                (
    					                    [id] => 25
    					                    [aro_id] => 37
    					                    [aco_id] => 8
    					                    [_create] => 0
    					                    [_read] => 1
    					                    [_update] => 0
    					                    [_delete] => 0
    					                )
    					
    					        )
    					*/
    					// Aros_acos
    					foreach($aros_acos as $aro_aco) {
    						
    						if($aro["parent"]["aro_id"] == $aro_aco["aro_aco"]["aro_id"]
    								&& $aco["parent"]["aco_id"] == $aro_aco["aro_aco"]["aco_id"]) {
    							$this->access_rules__["_create"] 	= $aro_aco[ "aro_aco" ]["_create"];
    							$this->access_rules__["_read"] 		= $aro_aco[ "aro_aco" ]["_read"];
    							$this->access_rules__["_update"] 	= $aro_aco[ "aro_aco" ]["_update"];
    							$this->access_rules__["_delete"] 	= $aro_aco[ "aro_aco" ]["_delete"];
    						}
    					}					
    				}		
    			}		
    		}
    
    		/**
    		  *	1. 	1 	0	allow to overwrite to read if not in array 
    		 *	2. 	0	0 	not allowed to overwrite and not in the array return 0 
    		 *	These will pass by without taken care:	 
    		 *	3-4.	*	1	in array		 		 	 		 
    		 */		 		 		
    		if($this->security_level__ != 0 && $this->override_to_read__ 
    				&& !in_array($this->check_aco__, array_keys($this->cruds__))) {
    			$this->check_aco__ = "read";
    		}
    		else if($this->security_level__ == 0 || !$this->override_to_read__ 
    				&& !in_array($this->check_aco__, array_keys($this->cruds__))) {
    			// Tight rules not allowed
    			return 0;
    		}
    		
    		// 	$this->check_aco__  	= "read/create/update/delete/view/add/..."
    		//  $this->cruds__ 			= "read/create/update/delete/view/add/..."	=> "_read/_create/_update/_delete" 
    		// 	$this->access_rules__	= "_read/_create/_update/_delete" => 0/1  		
    		return $this->access_rules__[$this->cruds__[$this->check_aco__]];		
    	}
    	
    	// }}}
    
    }
    ?>

These ones in app_controller

Copy only the necessary parts, don't replace your own with that.


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller 
    {
    	var $beforeFilter 			= array('checkAccessA');
    	var $helpers 				= array('Session');
        var $components 			= array('SimpleAcl'); 
    	var $uses 					= array('User');
    	var $table_prefix			= "psc_";
    
    	/**
    	 *	Used with SimpleAcl-component, basically this handles acl's in your app
    	 *	You can customize its actions when unauthorized things happen at 
    	 *	the last lines of this function.	 	 
    	 *	
    	 *	Usage: 	In app_controller --> var $beforeFilter	= array('checkAccessA');	
    	 *	 	 
    	 *	@author			unigue__
    	 *	@version		1.0
    	 *	@compability 	Developed for Cake 1.1.15.5144, dont know does it work on 1.2	 		 	 
    	 *	@see			SimpleAcl-component
    	 *	@see			Cakes acl-tutorial on http://manual.cakephp.org/chapter/acl	 	 
    	 */	 	 	
        function checkAccessA() 
    	{      	
    		if(empty($this->params['controller']) || empty($this->params['action'])) {
    			return;
    		}	
    		else if (!empty($this->params) && !empty($this->User)) {
    			
    			// Setting ups for SimpleAcl
    			$settings = array(
    				"table_prefix" 		=> $this->table_prefix, // Needed
    				"case_sensitive"	=> true, // Unnecessary others are already in SimpleAcl 	
    				"acos_table"		=> "acos", 
    				"aros_table"		=> "aros",
    				"axos_table"		=> "axos",
    				"arosacos_table"	=> "aros_acos",
    				"main_aco"			=> "main",
    				"main_aro"			=> "users_maingroup",
    				"security_level"	=> 1,
    				"cut_admin_off"		=> true
    			);
    						
    			// Initialization
    			$this->SimpleAcl->init($settings, $this->User, $this->params);
    			// Finally access or not			
    			$access = $this->SimpleAcl->check($this->Session->read('User.user_name'));			
    
    			/**
    			 * 	If no access, redirect to login or whatever
    			 * 	Normally user cant get 0 if he's following showed links properly,
    			 * 	otherwise he's hijacking for a weak spots or smt.			 	 
    			 *	Again put your own logic here
    			 */			 			 		
    			if(!$access) {
    				$this->flash("You need to login first.", "/Users/login", 1);
    				exit;
    			}			
    		}
    		else {
    			$this->flash("You need to login first.", "/Users/login", 1);
    			exit;
    		}	
    	}
    }
    ?>

Acos-table with sample data

::

    
    CREATE TABLE `psc_acos` (
      `id` int(11) NOT NULL auto_increment,
      `object_id` int(11) default NULL,
      `alias` varchar(255) NOT NULL default '',
      `lft` int(11) default NULL,
      `rght` int(11) default NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
    
    -- 
    -- Dumping data for table `psc_acos`
    -- 
    
    INSERT INTO `psc_acos` (`id`, `object_id`, `alias`, `lft`, `rght`) VALUES 
    (7, 0, 'main', 1, 6),
    (15, 1, '/Users', 2, 3),
    (16, 1, '/Search', 4, 5),

Aros-table with sample data

::

    
    CREATE TABLE `psc_aros` (
      `id` int(11) NOT NULL auto_increment,
      `foreign_key` int(11) default NULL,
      `alias` varchar(255) NOT NULL default '',
      `lft` int(11) default NULL,
      `rght` int(11) default NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
    
    -- 
    -- Dumping data for table `psc_aros`
    -- 
    
    INSERT INTO `psc_aros` (`id`, `foreign_key`, `alias`, `lft`, `rght`) VALUES 
    (1, 0, 'users_maingroup', 1, 22),
    (20, 1, 'customers_group', 2, 5),
    (39, 2, 'test_user', 3, 4),
    (21, 1, 'admins_group', 6, 9),
    (52, 2, 'test_admin', 7, 8),
    (37, 1, 'anonymous_group', 10, 13),
    (38, 2, 'visitor', 11, 12);

Aros_aco-table

::

    
    CREATE TABLE `psc_aros_acos` (
      `id` int(11) NOT NULL auto_increment,
      `aro_id` int(11) default NULL,
      `aco_id` int(11) default NULL,
      `_create` int(11) NOT NULL default '0',
      `_read` int(11) NOT NULL default '0',
      `_update` int(11) NOT NULL default '0',
      `_delete` int(11) NOT NULL default '0',
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


Ok what this component do?

It control the access to a certain areas by asking does Aro(User-Tim)
has access to
Aco(url-/Users/view/3) if it does then SimpleAcl will return a true as
a granted access otherwise the permission is
denied.

What this component does not do?
Within it you can't handle your permission data, it is designed to
answer to one simple question does someone
has access to somewhere that's it. My methods on this handling area
are not polished yet so
I can't give any mature enough code for this so it's your job to make
it work.

Basic knowledge of SimpleAcl
Lets assume you have a "main > Controllers > actions " aco-tree then
you give
a rule 1111(_create, _read, _update, _delete) for admin-Joe to main-
aco.
Now Joe goes to url /admin/Carts/view which is not in acos-table so
now
SimpleAcl first lookup for the "/admin/Carts/view"-aco, so it didn't
exists.
next "/admin/Carts"-aco it does not either
and the last hope "main"-aco here SimpleAcl finds rules 1111 and
access is granted.
I'm not going to give anymore examples of that because web is filled
up with acl-info.

Give me examples

This line "$access =
$this->SimpleAcl->check($this->Session->read('User.user_name'));"
is all you need, here Aro(User) is asking for a permission to certain
page.

Settings

Here is all necessary settings, they are pretty self explanatory so
I'll comment a couple of them

"main_aco" => "main",
This is a mother of all acos

"main_aro" => "users_maingroup",
this is a mother of all aros

"table_prefix" => $this->table_prefix, // Needed
"case_sensitive" => true, // Unnecessary others are already in
SimpleAcl
"acos_table" => "acos",
"aros_table" => "aros",
"axos_table" => "axos",
"arosacos_table" => "aros_acos",
"main_aco" => "main",
"main_aro" => "users_maingroup",
"security_level" => 1,
"cut_admin_off" => true

Things to notice
How this component handles the acos on which access is asked for? In
component you'll find a "wild_card_acos" variable which contains
array("main_layer", "/Controller", "/Controller/action") so if one is
granted to "/Profiles/view"-aco, could he see other osers profiles as
well? SimpleAcl doesn't take care of that, in your Profiles-controller
action's "view" you of course fetch data by user.id(which is stored
into session) so it impossible to get other users delicate data.

It is possible to add a fourth layer(fine grained aco) in acos-table
ie.
1. main - mother node of all
2. /Controllers - just a group of controllers
3. /Controllers/actions
4. /Controller/action/params
that fourth layer would be params["url"]["url"] and it has to be added
at the bottom in "wild_card_acos" variable.
However I didn't find this useful, but that's up to you.

And that's it, ask if you have some questions, proposals or feature
requests.


.. author:: unigue_
.. categories:: articles, components
.. tags:: component,access control,SimpleAcl,Components

