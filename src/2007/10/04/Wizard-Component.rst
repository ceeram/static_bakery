Wizard Component
================

Automates several aspects of multi-page forms including data
persistence, form preparation, wizard resetting (manual and
automatic), and wizard navigation (including jumping between steps)
while maintaining flexibility with custom validation and completion
callbacks.
Tutorial for this component found here:
`http://bakery.cakephp.org/articles/view/wizard-component-tutorial`_

Component Class:
````````````````

::

    <?php 
    /**
     * Wizard component by jaredhoyt.
     *
     * Handles multi-step form navigation, persistence, and validation callbacks.
     *
     * PHP versions 4 and 5
     *
     * Comments and bug reports welcome at jaredhoyt AT gmail DOT com
     *
     * Licensed under The MIT License
     *
     * @writtenby		jaredhoyt
     * @lastmodified	Date: September 29, 2007 12:43PM
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */ 
    class WizardComponent extends Object {
    /**
     * Option to automatically reset if the wizard does not follow "normal"
     * operation. (ie. manual url changing, navigation away and returning, etc.)
     * Set this to false if you want the Wizard to "remember" the steps
     * completed even if "normal" operation isn't followed.
     *
     * @var boolean
     * @access public
     */
    	var $autoReset = true;
    /**
     * View to be rendered after the wizard is complete. 
     * Option callback - Controller::beforeReceipt().
     *
     * @var string
     * @access public
     */
    	var $receiptAction = 'receipt';
    /**
     * Internal step tracking.
     *
     * @var string
     * @access private
     */
    	var $__currentStep = null;
    /**
     * List of steps, in order, that are in the Wizard.
     *
     * example: $steps = array('contact','payment','confirm')
     *
     * @var array
     * @access public
     */
    	var $steps = array();
    /**
     * Session key that all data from the Wizard is stored in. 
     *
     * Default is ControllerName.Wizard (ie. SignupWizard).
     *
     * @var string
     * @access public
     */
    	var $sessionKey = null;
    /**
     * Controller action that processes your step (if not setup by router). 
     *
     * Default is blank - this is for controllers 'dedicated' to the wizard with
     *  a route similar to Router::connect('/your_controller/*', array('controller' => 'your_controller', 'action' => 'index'));
     *
     * @var string
     * @access public
     */
    	var $wizardAction = 'wizard';
    /**
     * Other components used.
     *
     * @var array
     * @access public
     */
    	var $components = array('Session');
    	
    /**
     * Component startup method.
     *
     * @param unknown_type $controller
     * @access public
     */	
    	function startup(&$controller) {
    		$this->Controller = &$controller;
    		$this->data = $this->Controller->data;
    		$this->params = $this->Controller->params;
    		$this->__currentStep = current($this->steps);
    		
    		if(empty($this->sessionKey)) {
    			$this->sessionKey = $this->Controller->name.'Wizard';
    		}
    		if(!empty($this->wizardAction)) {
    			$this->wizardAction .= '/';
    		}
    	}
    
    /**
     * Main Component method.
     *
     * @param $step Name of step associated in $this->steps to be processed.
     * @access public
     */		
    	function process($step) { 
    		if($step == 'reset') {
    			$this->resetWizard();
    		} elseif($step == $this->receiptAction) {
    			if(method_exists($this->Controller,'beforeReceipt')) {
    				$this->Controller->beforeReceipt();
    			}
    			return $this->Controller->render($this->receiptAction);
    		} elseif(!is_null($step)) {
    			if($this->__validStep($step)) {
    				$this->__setCurrentStep($step);
    				
    				if(!empty($this->data) && empty($this->params['form']['Previous'])) {
    					$processCallback = Inflector::variable('process_'.$this->__currentStep);
    					if(method_exists($this->Controller,$processCallback)) {
    						if($this->Controller->$processCallback()) {
    							$this->Session->write("$this->sessionKey.$step",$this->data);
    							
    							if(next($this->steps)) {
    								$this->Controller->redirect($this->wizardAction.current($this->steps),null,true);
    							} else {
    								if(method_exists($this->Controller,'afterComplete')) {
    									$this->Controller->afterComplete();
    								}
    								$this->resetWizard();
    								$this->Controller->redirect($this->wizardAction.$this->receiptAction,null,true);
    							}
    						}
    					} else {
    						trigger_error(__('Process Callback not found. Please create Controller::'.$processCallback, true), E_USER_WARNING);
    					}
    				} elseif(!empty($this->params['form']['Previous'])) { 
    					$this->Controller->redirect($this->wizardAction.prev($this->steps));
    				}
    				
    				$prepareCallback = Inflector::variable('prepare_'.$this->__currentStep);
    				if(method_exists($this->Controller,$prepareCallback)) {
    					$this->Controller->$prepareCallback();
    				}
    				
    				if($this->Session->check("$this->sessionKey.$this->__currentStep")) {
    					$this->Controller->data = $this->Session->read("$this->sessionKey.$this->__currentStep");
    				}
    				
    				return $this->Controller->render($this->__currentStep);
    			} else {
    				trigger_error(__('Step validation: '.$step.' is not a valid step.', true), E_USER_WARNING);
    			}
    		}
    		
    		if($step != 'reset' && $this->autoReset) {
    			$this->resetWizard();
    		}
    		
    		$this->Controller->redirect($this->wizardAction.$this->__getExpectedStep());
    	}
    /**
     * Finds the last completed step stored in the session and returns 
     * the next step from $this->steps array. If no session is stored, 
     * it returns the first step.
     *
     * @access private
     * @return string $step
     */	
    	function __getExpectedStep() {
    		foreach($this->steps as $step) {
    			if(!$this->Session->check($this->sessionKey.'.'.$step)) {
    				return $step;
    			}
    		}
    	}
    /**
     * Moves internal array pointer of $this->steps to $step and sets $this->__currentStep.
     *
     * @param $step Step to point to.
     * @access private
     */		
    	function __setCurrentStep($step) {
    		reset($this->steps);
    		
    		while(current($this->steps) != $step) {
    			$this->__currentStep = next($this->steps);
    		}
    	}
    /**
     * Resets the wizard by deleting the wizard session.
     *
     * @access public
     */	
    	function resetWizard() {
    		$this->Session->del($this->sessionKey);
    	}
    /**
     * Validates the $step in two ways:
     *   1. Validates that the step exists in $this->steps array.
     *   2. Validates that the step is either before or exactly the expected step.
     *
     * @param $step Step to validate.
     * @access private
     */		
    	function __validStep($step) {
    		if(in_array($step,$this->steps)) {
    			return (array_search($step,$this->steps) <= array_search($this->__getExpectedStep(),$this->steps));
    		}
    		
    		return false;
    	}
    }
    ?>



.. _http://bakery.cakephp.org/articles/view/wizard-component-tutorial: http://bakery.cakephp.org/articles/view/wizard-component-tutorial

.. author:: jaredhoyt
.. categories:: articles, components
.. tags:: forms,component,Wizard,Components

