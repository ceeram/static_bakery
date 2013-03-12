

MySQL collation behavior
========================

by %s on April 12, 2008

A simple behavior that sets proper collation for order by SQL
statements based on the current localization.
Whenever you write multilangual apps you have to deal with many many
problems. One of them is setting a proper collation for order by SQL
statements so that results are ordered properly. The following
behavior adds a proper collation to every SQL statement based on
current localization.
Included behavior includes yet only polish collation. However you can
include more collations very quickly.

Usage :

Model Class:
````````````

::

    <?php 
    class ArticleModel extends AppModel {
      $actsAs = array ('Collation'=>array('title','contents'));
    }
    ?>

Code :

Behavior Class:
```````````````

::

    <?php 
    /**
    * MySQL collation Behavior class file.
    *
    * Model Behavior to support Polish typography.
    *
    * @filesource
    * @package	app
    * @subpackage	models.behaviors
    */
    
    /**
    * Add MySQL collation behavior
    *
    * @author	Pawel Gasiorowski <p.gasiorowski@axent.pl>
    * @package	app
    * @subpackage 	models.behaviors
    */
    class CollationBehavior extends ModelBehavior {
    	
    	/**
    	* Initiate behaviour for the model using specified settings.
    	*
    	* @param object $model		Model using the behaviour
    	* @param array $settings	Settings to override for model.
    	*
    	* @access public
    	*/
    	function setup(&$model, $settings) {
    		#
    		#	get current language
    		#
    		$i18n = &I18n::getInstance();
    		$language = $i18n->l10n->map($i18n->l10n->lang);
    		
    		#
    		#	get collation by language
    		#
    		switch ($language) {
    			case 'pl':
    			$collation = 'utf8_polish_ci';
    			default:
    			$collation = 'utf8_general_ci';
    			break;
    		}
    		
    		#
    		#	read settings
    		#
    		if (!isset($this->settings[$model->name]['fields'])) {
    			$this->settings[$model->name]['fields'] = array();
    		}
    		$this->settings[$model->name]['fields'] = array_unique(array_merge($this->settings[$model->name]['fields'], ife(is_array($settings), $settings, array())));
    		$this->settings[$model->name]['search'] = array();
    		$this->settings[$model->name]['replace'] = array();
    		
    		#
    		#	setup replacements for columns
    		#
    		foreach ($this->settings[$model->name]['fields'] as &$field) {
    			if (strpos($field,".") === false) {
    				$this->settings[$model->name]['search'][] = "/({$model->name}.{$field})/";
    			} else {
    				$this->settings[$model->name]['search'][] = "/({$field})/";
    			}
    			$this->settings[$model->name]['replace'][] = '$1'." COLLATE {$collation}";
    		}
    	}
    	
    	/**
    	* Run before model is queried
    	*
    	* @param object $model		Model
    	* @param array $query		Model query data
    	*
    	* @access public
    	* @since 1.0
    	*/
    	function beforeFind (&$model, &$query) {
    		if (!empty($query['order'])) {
    			if (is_string($query['order'])) {
    				$query['order'] = preg_replace($this->settings[$model->name]['search'],$this->settings[$model->name]['replace'],$query['order']);
    			} else if (is_array($query['order'])) {
    				foreach ($query['order'] as $i => $order) {
    					if (is_array($order)) {
    						$_order = "";
    						foreach ($order as $field => $direction) {
    							$_order .= " {$field} {$direction}";
    						}
    						$order = trim ($_order);
    					}
    					$order = preg_replace($this->settings[$model->name]['search'],$this->settings[$model->name]['replace'],$order);
    					$query['order'][$i] = $order;
    				}
    				$query['order'] = implode(" ",$query['order']);
    			}
    		}
    		
    		return $query;
    	}
    }
    ?>


.. meta::
    :title: MySQL collation behavior
    :description: CakePHP Article related to i18n,mysql,behavior,l10n,collation,Behaviors
    :keywords: i18n,mysql,behavior,l10n,collation,Behaviors
    :copyright: Copyright 2008 
    :category: behaviors

