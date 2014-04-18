Chartable Behavior
==================

This small behavior is something I made to make models easily work
with the cool OpenFlashChart "vendor" (http://teethgrinder.co.uk/open-
flash-chart/) and the FlashChartHelper
(http://bakery.cakephp.org/articles/view/open-flash-chart-helper-draw-
charts-the-cake-way).

::

    <?php
    /**
     * ChartableBehavior 
     *
     * A generic behavior that's function is to make any model "chartable". In most cases this behavior should be 
     * used in a dynamic way, IE adding the behavior in a controller action instead of having it "always on". 
     * For more info, read here : https://trac.cakephp.org/ticket/4010
     * 
     * The default setup for the behavior will create a graph with the Model::displayField as labels and assume
     * that the model has a field called "count" that it will use in the y-axis.
     * 
     * Example with configuration :
     * 
     * 		// We have a model User with fields 'browser' and 'marketshare' and wish to show this as a piechart:
     * 		$this->User->Behaviors->attach( 'Chartable' , array( 'numberField' => 'marketshare' , 'labelField' => 'browser' , 'type' => 'pie' ) );
     * 		$this->set('data', $this->User->find('all') );
     *
     * @copyright    Copyright (c) 2008, Alexander Morland
     * @version      1.2
     * @created      15/04/2008
     * @modifiedby   LastChangedBy: alkemann 
     * @lastmodified Date: 2008-04-21  
     * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
     *
     */
    class ChartableBehavior extends ModelBehavior 
    {
        var $numberField = '';
        var $labelField = '';
        var $type = '';
    
        /**
         * Constructor that will set up the configurations
         *
         * @param Object $model referance to model, sent automatically
         * @param array $config Array with these options : 'numberField', 'labelField', 'type'
         */
    	function setup(&$model, $config = array()) {
            $this->type =  ( isset($config['type']) ) ? $config['type'] : 'chart';
            $this->numberField = ( isset($config['numberField']) ) ? $config['numberField'] : 'count';
            $this->labelField = ( isset($config['labelField']) ) ? $config['labelField'] : $model->displayField;
    	}
        
    	/**
    	 * As no assocations will be used by a chartable model, we set recursion to -1 for all finds.
    	 *
    	 * @param Object $model
    	 * @param mixed $query
    	 * @return mixed
    	 * @todo Maybe add functionality for using belongsTo associated model fields 
    	 */
    	function beforeFind(&$model, $query) {
            $model->recursive = -1;
            return $query;
        }
        
        /**
         * The function here is to structure the found model data from a cake find into the data
         * structure that ise used by the OpenFlashChart vendor.
         *
         * @param Object $model
         * @param array $results
         * @param boolean $primary
         * @return array
         */
        function afterFind(&$model, $results, $primary = false) {
        
            $ret = array() ;
            if ($this->type == 'pie') {
                foreach ( $results as $id => $row ) {
                    $ret[ $row [$model->alias][$this->labelField] ] = array(
                        'value' => $row[$model->alias][$this->numberField],
                    );            
                }
            } else {
                foreach ( $results as $id => $row ) {
                     $ret['numbers'][$id] = $row [$model->alias][$this->numberField];
                     $ret['labels'][$id] = $row [$model->alias][$this->labelField];
                }    
            }
            return $ret;
        }    
          
    }
    ?>



Model Class:
````````````

::

    <?php class Transport extends AppModel
    
    var $name = 'Transport';
    var $actsAs = array('Chartable' => 
        array ( 'numberField' => 'count' , 'labelField' => 'date' ) );
    ?>


Controller Class:
`````````````````

::

    <?php class TransportsController extends AppController
    
    var $name = 'Transports';
    var $helpers = array('FlashChart');
    var $paginate = array('limit' => 10);
    
    function chart() {
        $this->Transport->recursive = -1;
        $this->set('transports', $this->paginate());
    }
    ?>


View Template:
``````````````

::

    
    <?php
    $flashChart->begin('100%','500');
    
    
    $flashChart->setLabels('x',$data['labels']); 
    
    $flashChart->setData(array(
        'Apples' => array(
            'color' => '#330066',
            'data' => $data['numbers'],
            'graph_style' => 'bar_glass',
        ),
    )); 
    
    $flashChart->configureGrid( array(
        'x_axis' => array( 'legend' => 'Date' ), 
        'y_axis' => array( 'legend' => '#Apples' ) 
    )); 
    
    $flashChart->setRange('y', 0, 1000);
    
    echo $flashChart->render();
    ?>
    <div class="paging">
    	<?php echo $paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
     | 	<?php echo $paginator->numbers();?>
    	<?php echo $paginator->next(__('next', true).' >>', array(), null, array('class'=>'disabled'));?>
    </div>





Behavior Class:
```````````````

::

    <?php 
    /**
     * This behavior lets you order items in a very similar way to the tree
     * behavior, only there is only 1 level. You can however have many 
     * independent lists in one table. Usually you use a foreign key to
     * set / see what list you are in (see example bellow) or if you have
     * just one list (or several lists, but no association) you can just 
     * use a field called "order_id" and set it manually.
     * 
     * What it does:
     * 
     * It manages the creation and updating of the order field. It 
     * also sets the models order property to this field. When adding new
     * nodes or deleting old ones, this behavior will do the necisary changes
     * to keep the list working properly. It is build to be completely
     * automagic after the initial configuration by letting it know 
     * your foreign_key and weight fields.
     * 
     * Usage example :
     * 
     * Lets say you have books with pages and want the pages ordered
     * by page number (obviously a book sorted alphabetically would be 
     * silly). So you have these models:
     * 
     * Book hasMany Page
     * Page belongsTo Book
     * 
     * The Page model has fields : 
     * 
     * id
     * content
     * book_id 
     * page_number
     * 
     * To set up this behavior we add this property to the Page model :
     * 
     * var $actsAs = array('Ordered' => array(
     * 			'field' 		=> 'page_number',
     * 			'foreign_key' 	=> 'book_id'
     * 		));
     * 
     * Now when you save a new page (no changes needed to action or view,
     * but leave page_number out of the form), it will be added to the end 
     * of the book.
     * 
     * Now lets say the last two pages to be created got made in the wrong 
     * order, so you want to move the last page "up" one space. With the 
     * a simple controller call to the model like this that can be achieved:
     * 
     * // in a controller action :
     * $this->Page->moveup($id);
     * // the id here is the id of the newest page
     * 
     * You find that the first page you made is suppose to be the 5 pages later:
     * 
     * // in a controller action :
     * $this->Page->movedown($id, 5);
     * 
     * Also you discovered that in the first page got put in the middle. This 
     * can easily be moved first by doing this :
     * 
     * // in a controller action :
     * $this->Page->moveup($id,true);
     * // true will move it to the extre in that direction
     * 
     * You can also use actions 
     * 
     *  - isfirst($id)
     *  - islast($id)
     * 
     * to find out if the node is first or last page.
     * 
     */
    class OrderedBehavior extends ModelBehavior {
    	var $name = 'Ordered';
    	
    	/**
    	 * field : (string) The field to be ordered by. 
    	 * 
    	 * foreign_key : (string) The field to identify one SET by. each set has their own 
    	 *               order (ie they start at 1)
    	 */
    	var $_defaults = array(
    		'field' => 'weight',
    		'foreign_key' => 'order_id'
    	);
    	
    	function setup(&$Model, $config = array()) {
    		if (!is_array($config)) {
    			$config = array();
    		}
    		$this->settings = array_merge($this->_defaults, $config);
    		$Model->order = $Model->alias.'.'.$this->settings['field']. ' ASC';
    	}
    
    	function beforeDelete(&$Model) {
    		// What was the weight of the deleted model?		
    		$old_weight = $Model->data[$Model->alias][$this->settings['field']];
    		// update the weight of all models of higher weight by 
    		// decreasing them by 1
    		$f = $this->settings['field'];
    		$fk = $this->settings['foreign_key'];
    		$fk_id = $Model->data[$Model->alias][$fk];
    		$t = $Model->table;
    		$o = $old_weight;
    		$query = "UPDATE $t SET $f = $f - 1 WHERE $f > $o AND $fk = '$fk_id';";
    		$Model->query($query);
    		return TRUE;
    	}
    	
    	/**
    	 * Sets the weight for new items so they end up at end
    	 *
    	 * @todo add new model with weight. clean up after
    	 * @param Model $Model
    	 */
    	function beforeSave(&$Model) {
    	    //	Check if weight id is set. If not add to end, if set update all
    	    // rows from ID and up
    		if (!isset($Model->data[$Model->alias]['id'])) {
    			// get highest current row
    			$highest = $this->_highest($Model);
    			// set new weight to model as last by using current highest one + 1
    			$Model->data[$Model->alias][$this->settings['field']] 
    			 = $highest[$Model->alias][$this->settings['field']] + 1;
    		}
    		return TRUE;
    	}
    
    	/**
    	 * Moving a node to specific weight, it will shift the rest of the table to make room.
    	 *
    	 * @param Object $Model
    	 * @param int $id The id of the node to move
    	 * @param int $new_weight the new weight of the node
    	 * @return boolean True of move successful
    	 */	
    	function moveTo(&$Model, $id = null, $new_weight = null) {
    		if (!$id || !$new_weight) {
    			return FALSE;
    		}
    		// fetch the model and its old weight
    		$old_weight = $this->_read($Model,$id);
    		// give Model new weight	
    		$Model->data[$Model->alias][$this->settings['field']] = $new_weight;
    		$f = $this->settings['field'];
    		$fk = $this->settings['foreign_key'];
    		$fk_id = $Model->data[$Model->alias][$fk];
    		$t = $Model->table;
    		$n = $new_weight;
    		$o = $old_weight;
    		if ($new_weight == $old_weight) {
    			// move to same location?
    			return FALSE;
    		} elseif ($new_weight > $old_weight) {			
    			// move all nodes that have weight > old_weight AND <= new_weight up one (-1)
    			$query = "UPDATE $t SET $f = $f - 1 WHERE $f <= $n  AND $f > $o AND $fk = '$fk_id';";
    			$Model->query($query);		
    			// save new weight	
    			return $Model->save(null, FALSE);
    		} else { // $new_weight < $old_weight
    			// move all where weight >= new_weight AND < old_weight down one (+1)			
    			$query = "UPDATE $t SET $f = $f + 1 WHERE $f >= $n  AND $f < $o AND $fk = '$fk_id';";
    			$Model->query($query);	
    			// save new weight	
    			return $Model->save(null, FALSE);			
    		}
    	}	
    	/**
    	 * Take in an order array and sorts the list based on that order specification
    	 * and creates new weights for it. If no foreign key is supplied, all lists
    	 * will be sorted.
    	 *
    	 * @todo foreign key independent
    	 * @param Object $Model
    	 * @param array $order
    	 * @param mixed $foreign_key
    	 * $returns boolean TRUE if successfull
    	 */
    	function sortBy(&$Model, $order, $foreign_key) {
    		$conditions = array($this->settings['foreign_key'] => $foreign_key);
    		$Model->recursive = -1;
    		$all = $Model->find('all', array(
    			'fields' => array('id', $this->settings['field'], $this->settings['foreign_key']),
    			'conditions' => $conditions,
    			'order' => $order
    		));
    		$i = 1;
    		foreach ($all as $key => $one) {
    			$all[$key][$Model->alias][$this->settings['field']] = $i++;
    		}
    		return $Model->saveAll($all);
    	}
    
    	/**
    	 * Reorder the node, by moving it $number spaces up. Defaults to 1
    	 *
    	 * If the node is the first node (or less then $number spaces from first)
    	 * this method will return false.
    	 * 
    	 * @param AppModel $Model
    	 * @param mixed $id The ID of the record to move
    	 * @param mixed $number how many places to move the node or true to move to last position
    	 * @return boolean true on success, false on failure
    	 * @access public
    	 */	
    	function moveup(&$Model, $id = null, $number = 1) {	
    		$old_weight = $this->_read($Model, $id);
    		if (is_numeric($number)) {	
    			if ($number == 1) { // move 1 space
    				$previous = $this->_previous($Model);
    				if (!$previous) {
    					return FALSE;
    				}				
    				$Model->data[$Model->alias][$this->settings['field']] 
    					= $previous[$Model->alias][$this->settings['field']];
    						
    				$previous[$Model->alias][$this->settings['field']] = $old_weight;
    				
    				$data[0] = $Model->data;
    				$data[1] = $previous;
    				
    				return $Model->saveAll($data,array('validate'=>FALSE));
    				
    			} elseif ($number < 1) { // cant move 0 or negative spaces
    				return FALSE;
    			} else { // move Model up N spaces UP
    				
    				// find the one occupying new space and its weight
    				$new_weight = $Model->data[$Model->alias][$this->settings['field']] - $number;
    				// check if new weight is possible. else move last
    				if (! $this->_findByWeight($Model, $new_weight)) {
    					return FALSE;
    				}
    				// increase weight of all where weight > new weight and id != Model.id				
    				$f = $this->settings['field'];
    				$fk = $this->settings['foreign_key'];
    				$fk_id = $Model->data[$Model->alias][$fk];
    				$t = $Model->table;
    				$n = $new_weight;
    				$o = $old_weight;
    				$query = "UPDATE $t SET $f = $f + 1 WHERE $f >= $n  AND $f < $o AND $fk = '$fk_id';";
    				$Model->query($query);
    				
    				// set Model weight to new weight and save it
    				$Model->data[$Model->alias][$this->settings['field']] = $new_weight;
    				return $Model->save(NULL, FALSE);
    			}
    		} elseif (is_bool($number)) { // move Model FIRST;
    			
    			// set Model weight to 0
    			$Model->data[$Model->alias][$this->settings['field']] = 0;
    			
    			// increase weight of all where weight < old_weight by 1
    			$f = $this->settings['field'];
    			$fk = $this->settings['foreign_key'];
    			$fk_id = $Model->data[$Model->alias][$fk];
    			$t = $Model->table;
    			$o = $old_weight;
    
    			$Model->save(null,FALSE);
    
    			$query = "UPDATE $t SET $f = $f + 1 WHERE $f < $o AND $fk = '$fk_id';";
    			$Model->query($query);
    			return TRUE;
    		} else { // $number is neither a number nor a bool
    			return FALSE;
    		}		
    	}
    	
    	/**
    	 * Reorder the node, by moving it $number spaces down. Defaults to 1
    	 *
    	 * If the node is the last node (or less then $number spaces from last)
    	 * this method will return false.
    	 *
    	 * @param AppModel $Model
    	 * @param mixed $id The ID of the record to move
    	 * @param mixed $number how many places to move the node or true to move to last position
    	 * @return boolean true on success, false on failure
    	 * @access public
    	 */	
    	function movedown(&$Model, $id = null, $number = 1) {		
    		$old_weight = $this->_read($Model, $id);
    		if (is_numeric($number)) {
    			if ($number == 1) { // move node 1 space down
    				$next = $this->_next($Model);
    				if (!$next) { // it is the last node
    					return FALSE;
    				}			
    				// switch the node's weight around		
    				$Model->data[$Model->alias][$this->settings['field']] 
    					= $next[$Model->alias][$this->settings['field']];
    			
    				$next[$Model->alias][$this->settings['field']] = $old_weight;
    				
    				// create an array of the two nodes and save them
    				$data[0] = $Model->data;
    				$data[1] = $next;				
    				return $Model->saveAll($data,array('validate'=>FALSE));	
    							
    			} elseif ($number < 1) { // cant move 0 or negative number of spaces
    				return FALSE;
    			} else { // move Model up N spaces DWN
    				
    				// find the one occupying new space and its weight
    				$new_weight = $Model->data[$Model->alias][$this->settings['field']] + $number;
    				// check if new weight is possible. else move last
    				if (! $this->_findByWeight($Model, $new_weight)) {
    					return FALSE;
    				}
    				// increase weight of all where weight > new weight and id != Model.id				
    				$f = $this->settings['field'];
    				$fk = $this->settings['foreign_key'];
    				$fk_id = $Model->data[$Model->alias][$fk];
    				$t = $Model->table;
    				$n = $new_weight;
    				$o = $old_weight;
    				$query = "UPDATE $t SET $f = $f - 1 WHERE $f <= $n  AND $f > $o AND $fk = '$fk_id';";
    				
    				$Model->query($query);
    				// set Model weight to new weight and save it
    				$Model->data[$Model->alias][$this->settings['field']] = $new_weight;
    				return $Model->save(NULL, FALSE);
    			}
    
    		} elseif (is_bool($number)) { // move Model LAST;
    			
    			// get highest weighted row
    			$highest = $this->_highest($Model);
    			// check of Model is allready highest
    			if ($highest[$Model->alias]['id'] == $Model->data[$Model->alias]['id']) {
    				return FALSE;
    			}
    			// set Model to highest weight + 1 and save that 
    			$Model->data[$Model->alias][$this->settings['field']] 
    				= $highest[$Model->alias][$this->settings['field']] + 1;
    			$Model->save(NULL, FALSE);
    			
    			// decrease weight for all with weight > old weight
    			$f = $this->settings['field'];
    			$fk = $this->settings['foreign_key'];
    			$fk_id = $Model->data[$Model->alias][$this->settings['foreign_key']];
    			$t = $Model->table;
    			$o = $old_weight;
    			$query = "UPDATE $t SET $f = $f - 1 WHERE $f > $o AND $fk = '$fk_id';";
    			$Model->query($query);
    			return TRUE;
    		} else {  // $number is neither a number nor a bool
    			return FALSE;
    		}	
    	}
    	
    	/**
    	 * Returns true if the specified item is the first item 
    	 *
    	 * @param Model $Model
    	 * @param Int $id
    	 * @return Boolean, true if it is the first item, false if not
    	 */
    	function isfirst(&$Model, $id = null){
    		$first = $this->_read($Model, $id);
    		if ($Model->data[$Model->alias][$this->settings['field']] == 1) {
    			return TRUE;
    		} else {
    			return FALSE;
    		}
    	}
    	
    	/**
    	 * Returns true if the specified item is the last item 
    	 *
    	 * @param Model $Model
    	 * @param Int $id
    	 * @return Boolean, true if it is the last item, false if not
    	 */
    	function islast(&$Model, $id = null){
    		$last = $this->_highest($Model);
    		if ($last[$Model->alias]['id'] == $id) {
    			return TRUE;
    		} else {
    			return FALSE;
    		}		
    	}
    	
    	function _findByWeight(&$Model, $weight) {	
    		return $Model->find('first', array(
    			'conditions' => array(
    				$this->settings['foreign_key'] => $Model->data[$Model->alias][$this->settings['foreign_key']],
    			    $this->settings['field'] => $weight
    			),
    			'order' => array($this->settings['field'].' DESC'),
    			'fields' => array('id',$this->settings['field'],$this->settings['foreign_key'])			
    		));
    	}
    	
    	function _highest(&$Model) {
    		return $Model->find('first', array(
    			'conditions' => array(
    				$this->settings['foreign_key'] => $Model->data[$Model->alias][$this->settings['foreign_key']]
    			),
    			'order' => array($this->settings['field'].' DESC'),
    			'fields' => array('id',$this->settings['field'],$this->settings['foreign_key'])			
    		));
    	}
    	
    	function _previous(&$Model) {
    		return $Model->find('first', array(
    			'conditions' => array(
    				$this->settings['field'] => $Model->data[$Model->alias][$this->settings['field']]-1,
    				$this->settings['foreign_key'] => $Model->data[$Model->alias][$this->settings['foreign_key']]
    			),
    			'fields' => array('id',$this->settings['field'],$this->settings['foreign_key'])			
    		));
    	}	
    	
    	function _next(&$Model) {
    		return $Model->find('first', array(
    			'conditions' => array(
    				$this->settings['field'] => $Model->data[$Model->alias][$this->settings['field']]+1,
    				$this->settings['foreign_key'] => $Model->data[$Model->alias][$this->settings['foreign_key']]
    			),
    			'fields' => array('id',$this->settings['field'],$this->settings['foreign_key'])			
    		));
    	}
    	
    	function _all(&$Model) {
    		return $Model->find('all', array(
    			'conditions' => array($this->settings['foreign_key'] => $Model->data[$Model->alias][$this->settings['foreign_key']]),
    			'fields' => array('id',$this->settings['field'],$this->settings['foreign_key']),
    			'order' => array($this->settings['field'].' DESC')
    		));
    	}
    	
    	function _read(&$Model,$id) {
    		$Model->id = $id;
    		$Model->recursive = -1;
    		$Model->read(array('id',$this->settings['field'],$this->settings['foreign_key']));
    		return $Model->data[$Model->alias][$this->settings['field']];
    	}
    }
    ?>



List of features and changes to come
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

+ Adding a new node with custom weight (ie not always at end)
+ Making the foreign_key usage optional

If you have any other suggestions. please leave a comment.
`1`_|`2`_|`3`_|`4`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_
+ `Page 4`_

.. _Page 2: :///articles/view/4caea0e1-7d58-4997-b256-4bbd82f0cb67/lang:eng#page-2
.. _Page 1: :///articles/view/4caea0e1-7d58-4997-b256-4bbd82f0cb67/lang:eng#page-1
.. _Page 4: :///articles/view/4caea0e1-7d58-4997-b256-4bbd82f0cb67/lang:eng#page-4
.. _Page 3: :///articles/view/4caea0e1-7d58-4997-b256-4bbd82f0cb67/lang:eng#page-3

.. author:: alkemann
.. categories:: articles, behaviors
.. tags::
behavior,chart,openflashchart,FlashChartHelper,alkemann,Behaviors

