Transaction behavior
====================

by %s on December 19, 2010

A little behavior that enables you to use (nested) transactions in
1.2+ with MySQL.


Example use
```````````
Sample model - note the $actsAs

To change default settings do:

::

    
    var $actsAs = array('Transactional');


Model Class:
````````````

::

    <?php 
    class Order extends AppModel
    {
        var $name	 = 'Order';
        var $actsAs   = array('Transactional');
        var $hasMany  = array('OrderDetail');
    ?>



Controller Class:
`````````````````

::

    <?php 
    class OrderController extends AppController {
        var $name = 'Order';
        var $uses = array('Order');
    
        function checkout() {
            if(!empty($this->data)) {
                $this->Order->begin(); // Start our transaction
                if( $this->Order->save( $this->data ) ) {
                    if( $this->Order->OrderDetail->save( $this->data ) ) {
                        $this->Order->commit(); // Persist the data
                        $this->redirect('/shop/order/thanks');
                        exit;    
                    } else { 
    // Couldnt save the products to the order
    // since the products to the order couldnt be stored, dont store anything at all
    // rollback all changes to the database sine 'begin' was called
                        $this->Order->rollback(); 
                        $this->redirect('/shop/order/error');
                        exit;
                    }
                } else { 
                    // Didnt save
                }
            }
        }
    }
    ?>



Model Class:
````````````

::

    <?php 
    <?php
    /**
     * Transactional Behavior
     *
     * @author Christian Winther <cwin@expressional.com>
     * @version 1.0
     * @since 19.12.2010
     */
    class TransactionalBehavior extends ModelBehavior {
    
    	protected $savepoints = array();
    
    	/**
    	 * Begin a transaction
    	 *
    	 * @param Model $Model
    	 * @return boolean
    	 */
    	public function begin(Model $Model) {
    		if (!$this->inTransaction($Model)) {
    			$DataSource = ConnectionManager::getDataSource($Model->useDbConfig);
    			$DataSource->begin($Model);
    		} else {
    			$Model->query(sprintf('SAVEPOINT %s_%d', $Model->useDbConfig, $this->getNextSavepoint($Model)));
    		}
    
    		$this->inTransaction($Model, true);
    		return true;
    	}
    
    	/**
    	 * Commit a transaction
    	 *
    	 * @param Model $Model
    	 * @return boolean
    	 */
    	public function commit(Model $Model) {
    		if (!$this->inTransaction($Model)) {
    			return false;
    		}
    
    		if (!$this->hasSavepoint($Model)) {
    			$DataSource = ConnectionManager::getDataSource($Model->useDbConfig);
    			$DataSource->commit($Model);
    			$this->inTransaction($Model, false);
    		} else {
    			$Model->query(sprintf('RELEASE SAVEPOINT %s_%d', $Model->useDbConfig, $this->getCurrentSavepoint($Model)));
    			$this->getPreviousSavepoint($Model);
    		}
    
    		return true;
    	}
    
    	/**
    	 * Rollback a transaction
    	 *
    	 * @param Model $Model
    	 * @return boolean
    	 */
    	public function rollback(Model $Model) {
    		if (!$this->inTransaction($Model)) {
    			return false;
    		}
    
    		if (!$this->hasSavepoint($Model)) {
    			$DataSource = ConnectionManager::getDataSource($Model->useDbConfig);
    			$DataSource->rollback($Model);
    			$this->inTransaction($Model, false);
    		} else {
    			$Model->query(sprintf('ROLLBACK TO SAVEPOINT %s_%d', $Model->useDbConfig, $this->getPreviousSavepoint($Model)));
    		}
    
    		return true;
    	}
    
    	/**
    	 * Check if we are in transaction
    	 *
    	 * @param Model $Model
    	 * @return integer
    	 */
    	public function inTransaction(Model $Model, $bool = null) {
    		$key = sprintf('Model.%s.InTransaction', $Model->useDbConfig);
    
    		if (!is_null($bool)) {
    			return Configure::write($key, $bool);
    		}
    
    		return Configure::read($key);
    	}
    
    	/**
    	 * Get next savepoint identifier
    	 *
    	 * @param Model $Model
    	 * @return integer
    	 */
    	protected function getNextSavepoint(Model $Model) {
    		if (!array_key_exists($Model->useDbConfig, $this->savepoints)) {
    			return $this->savepoints[$Model->useDbConfig] = 0;
    		}
    		return ++$this->savepoints[$Model->useDbConfig];
    	}
    
    	/**
    	 * Get the previous savepoint identifier
    	 *
    	 * @param Model $Model
    	 * @return integer
    	 */
    	protected function getPreviousSavepoint(Model $Model) {
    		if (!array_key_exists($Model->useDbConfig, $this->savepoints)) {
    			throw new Exception(sprintf('Database connection %s does not have any savepoints', $Model->useDbConfig));
    		}
    		return --$this->savepoints[$Model->useDbConfig];
    	}
    
    	/**
    	 * Check if the database connection has any active savepoints
    	 *
    	 * @param Model $Model
    	 * @return boolean
    	 */
    	protected function hasSavepoint(Model $Model) {
    		if (!array_key_exists($Model->useDbConfig, $this->savepoints)) {
    			return false;
    		}
    		return $this->savepoints[$Model->useDbConfig] > 0;
    	}
    
    	/**
    	 * Get the current savepoint identifier
    	 *
    	 * @param Model $Model
    	 * @return integer
    	 */
    	protected function getCurrentSavepoint(Model $Model) {
    		if (!array_key_exists($Model->useDbConfig, $this->savepoints)) {
    			throw new Exception(sprintf('Database connection %s does not have any savepoints', $Model->useDbConfig));
    		}
    		return $this->savepoints[$Model->useDbConfig];
    	}
    }
    ?>


.. meta::
    :title: Transaction behavior
    :description: CakePHP Article related to actsas,transaction,behavior,rollback,unlock,transaction behavior,commit,lock,Behaviors
    :keywords: actsas,transaction,behavior,rollback,unlock,transaction behavior,commit,lock,Behaviors
    :copyright: Copyright 2007 
    :category: behaviors

