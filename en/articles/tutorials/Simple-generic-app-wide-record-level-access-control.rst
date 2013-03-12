

Simple, generic, app-wide record level access control.
======================================================

by %s on August 02, 2007

Record level access control for all models in your application.

::

    
      /**
       * Defines whether model has record level access control.
       * Set to false in AppModel, but override to true in specific models where required
       *
       * @var boolean
       */
      var $accessControl = false;
    
      /**
       * Identifies the fieldname used in access-controlled models.
       * Normally team_id/territory_id,department_id etc
       * Normally the same in all models that have access control, and the User model
       *
       * @var string
       */
      var $ accessControlField = 'territory_id';
    
      /**
       * Association name of Associated Model that access control field belongs to.
       * If null, assume current model. Could be CreatedByUser for example
       *
       * @var string
       */
      var $ accessControlModel = null;
    
      /**
       * Built-in CakePHP Model Callback method, overwritten here
       *
       * @param array $queryData Automatically supplied to beforeFind() by CakePHP
       * @return boolean Always true
       */
      function beforeFind(&$queryData) {
        parent::beforeFind();
        if($this->accessControl
        && !is_null($this->accessControlField)
        && isset($_SESSION['User'][$this->accessControlField])
        && $_SESSION['User'][$this->accessControlField] > 0) {
          $this->__addAccessControlCondition($queryData);
        }
        return true;
      }
    
      /**
        * Adds conditions to $queryData array before finding records.
        *
        */
      function __addAccessControlCondition(&$queryData) {
    
        // If queryData conditions are array, convert to string.
        if(isset($queryData['conditions'])) {
          if(is_array($queryData['conditions'])) {
            if(!empty($queryData['conditions'])) {
              $db =& ConnectionManager::getDataSource($this->useDbConfig);
              $queryData['conditions'] = implode(' AND ',$db->conditionKeysToString($queryData['conditions']));
            } else {
              $queryData['conditions'] = '1';
          	}
          }
        } else {
          $queryData['conditions'] = '1';
        }
    
        // If accessControlModel is null, use current model
        if(is_null($this->accessControlModel)) {
          $this->accessControlModel = $this->name;
        }
    
        // Get accessControlValue from currently logged in User details in Session
        $accessControlValue = $_SESSION['User'][$this->accessControlField];
    
        // Append accessControl condition to existing conditions including "... OR 0" condition
        $queryData['conditions'] .= " AND (`{$this->accessControlModel}`.`{$this->accessControlField}` = {$accessControlValue} OR `{$this->accessControlModel}`.`{$this->accessControlField}` = 0)";
      }
    
    	function beforeValidate() {
    
    	  // If doing accessControl and accessControlField specified and on current model
    	  // and the accessControlField is not already set in this->data and a new record
    	  // is being inserted
    		if($this->accessControl
    		&& !is_null($this->accessControlField)
    		&& (is_null($this->accessControlModel) || $this->accessControlModel == $this->name)
    		&& isset($_SESSION['User'][$this->accessControlField])
    		&& !isset($this->data[$this->name][$this->accessControlField])
    		&& !isset($this->data[$this->name][$this->primaryKey])
    		&& !$this->id) {
    		  $this->__addAccessControlData();
    		}
    		// Always return true else save won't run.
    	  return true;
    
    	}
    
    	/**
    	 * Adds accessControl key => value to this->data before saving.
    	 *
    	 */
    	function __addAccessControlData() {
    
    		// Get accessControlValue from currently logged in User details in Session
    		$accessControlValue = $_SESSION['User'][$this->accessControlField];
    
    		// Add accessControlField => accessControlValue element to this->data array
        $this->data[$this->name][$this->accessControlField] = $accessControlValue;
    	}


.. meta::
    :title: Simple, generic, app-wide record level access control.
    :description: CakePHP Article related to ,Tutorials
    :keywords: ,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

