CakePHP Query Builder
=====================

by mjamesd on June 09, 2008

This is a basic query builder for the select method. I didn't need to
do insert,update, or delete operations for what I was building it for,
but it would be pretty easy to add that functionality to it.
The controller handles the form and sets the variables. The
'always_column' variable is the fields that you always want displayed
and the 'always_layout' variable defines the space between the
variables. The 'replace_values' variable is used to speed up the
query. The Helper uses a recursive = -1 so it doesn't fetch anything
you don't need, and the 'replace_values' variable gets the related
information that you want.
Remember to include the Search helper in the helpers array.

Controller Class:
`````````````````

::

    <?php 
    class AnyController extends AppController {
    	var $name = 'Anys';
    	var $Inflector;
    	var $helpers = array('Html', 'Form', 'Search');
    
    	function search() {
    		$this->Inflector = new Inflector();
    		$modelName = $this->Inflector->singularize($this->name);
    		$this->set('modelName', $modelName);
    		$this->set('table',strtolower($this->name));
    		if (!empty($this->data)) {
    			$this->set('column_radio', $this->data[$modelName]['column_radio']);
    			$this->set('columns', $this->data[$modelName]['columns']);
    			$this->set('where_fields', $this->data[$modelName]['where_fields']);
    			$this->set('where_conditions', $this->data[$modelName]['where_conditions']);
    			$this->set('where_values', $this->data[$modelName]['where_values']);
    			$this->set('order_by_field', $this->data[$modelName]['order_by_field']);
    			$this->set('order_by_direction', $this->data[$modelName]['order_by_direction']);
    			$this->set('limit', $this->data[$modelName]['limit']);
    			$this->set('always_columns', array('lname',
    							   'title',
    							   'fname',
    							   'mname',
    							   'suffix',
    							  'county_id',
    							   'state_id'
    							)
    			);
    			$this->set('always_layout', array(', ',
    							  ' ',
    							  ' ',
    							  ', ',
    							  '<br />',
    							  ', ',
    							   '<br/>'
    							)
    			);
    			$replace_values = array(
    						array('column' => 'state_id',
    						  'replaceModelName' => 'State',
    						  'replaceModelField' => 'id',
    						  'replaceModelValue' => 'name'
    						  ),
    						array('column' => 'county_id',
    						  'replaceModelName' => 'County',
    						  'replaceModelField' => 'id',
    						  'replaceModelValue' => 'name'
    						 )
    			);
    			$this->set('replace_values', $replace_values);
    		}
    	}
    ?>

Now the View. It just passes the variables to the Element.

View Template:
``````````````

::

    
    // APP/views/anys/search.ctp
    echo $this->renderElement('search', array('modelName' => $modelName,
    
                                              'table' => @$table,
    
                                              'column_radio' => @$column_radio,
    
                                              'columns' => @$columns,
    
                                              'where_fields' => @$where_fields,
    
                                              'where_conditions' => @$where_conditions,
    
                                              'where_values' => @$where_values,
    
                                              'order_by_field' => @$order_by_field,
    
                                              'order_by_direction' => @$order_by_direction,
    
                                              'limit' => @$limit,
    
                                              'always_columns' => @$always_columns,
    
                                              'always_layout' => @$always_layout,
    
                                              'replace_values' => @$replace_values
    
                                             )
    
    );

The Element either creates the form or outputs the results. I'm using
mootools for the effects and the RosSoft Head Helper
(`http://rossoft.wordpress.com/2006/03/28/register-head-tags-from-
helpers-2/`_).

::

    
    <?php
    
    // APP/views/elements/search.ctp
    
    if (empty($column_radio) || empty($where_fields) || empty($where_conditions) || empty($where_values) || empty($order_by_field) || empty($order_by_direction)) {
    
        // print form
    
        $formOptions = $search->formOptions($modelName,$table);
    
        echo $form->create($modelName, array('action' => 'search'));
    
        echo $form->input('column_radio', array('type' => 'radio', 'options' => array('count(*)' => 'Count', '*' => 'All', 'columns' => 'Selected Columns (below)')));
    
        echo $form->input('columns', array('type' => 'select', 'multiple' => 'multiple', 'options' => $formOptions['column']));
    
        echo '<fieldset>';
    
        for ($i = 0; $i < 5; $i++) {
    
            echo $form->input('where_field'.$i, array('type' => 'select', 'name' => 'data['.$modelName.'][where_fields]['.$i.']', 'options' => $formOptions['whereField'], 'empty' => true));
    
            echo $form->input('where_condition'.$i, array('type' => 'select', 'name' => 'data['.$modelName.'][where_conditions]['.$i.']', 'options' => $formOptions['whereCondition'], 'empty' => true));
    
            echo $form->input('where_value'.$i, array('name' => 'data['.$modelName.'][where_values]['.$i.']'));
    
        }
    
        echo '</fieldset>';
    
        echo $form->input('order_by_field', array('type' => 'select', 'options' => $formOptions['orderByField']));
    
        echo $form->input('order_by_direction', array('type' => 'select', 'options' => $formOptions['orderByDirection']));
    
        echo $form->input('limit');
    
        echo $form->end('Build Query');
    
    } else {
    
        $head->register_css('mootools_accordion');
    
        $head->register_jsblock("
    
        window.addEvent('domready', function(){
    
                var accordion = new Accordion('h3.atStart', 'div.atStart', {
    
                        opacity: false,
    
                        onActive: function(toggler, element){
    
                                toggler.setStyle('color', '#ff3300');
    
                        },
    
                        
    
                        onBackground: function(toggler, element){
    
                                toggler.setStyle('color', '#222');
    
                        }
    
                }, $('accordion'));
    
        });"
    
        );
    
        $results = $search->returnSearchResults($modelName,$column_radio,$columns,$where_fields,$where_conditions,$where_values,$order_by_field,$order_by_direction,$limit,$always_columns,$replace_values);
    
        foreach ($results as $result) {
    
    ?>
    
    <div id="accordion">
    
        <h3 class="toggler atStart"><?php
    
        echo $search->returnAlways($result,$always_columns,$always_layout);
    
        ?></h3>
    
        <div class="element atStart">
    
                <table>
    
                        <tr>
    
                            <?php
    
                            foreach ($columns as $column) :
    
                                echo '<th>'.$column.'</th>'."\n";
    
                            endforeach;
    
                            ?>
    
                        </tr>
    
                        <tr>
    
                            <?php
    
                            foreach ($columns as $column) :
    
                                echo '<td>';
    
                                echo (!empty($result[$modelName][$column])) ? $result[$modelName][$column] : '(no value)';
    
                                 echo '</td>'."\n";
    
                            endforeach;
    
                            ?>
    
                        </tr>
    
                </table>
    
        </div>
    
    <?php
    
        }
    
    }
    
    ?>

And finally the Helper. It does all the work.

Helper Class:
`````````````

::

    <?php 
    <?php
    // APP/views/helpers/search.php
    
    class SearchHelper extends Helper {
    
        
    
        private $Model;
    
        protected $modelName = 'Person';
    
        
    
        protected $results;
    
        protected $columns = array();
    
        protected $where;
    
        protected $orderBy;
    
        protected $limit;
    
        
    
        private function loadModel($modelName = null) {
    
            if (empty($modelName)) {
    
                $modelName = $this->modelName;
    
            }
    
            $this->modelName = $modelName;
    
            $this->Model =& ClassRegistry::getObject($modelName, 'Model');
    
    	$this->Model->recursive = -1;
    
        }
    
        
    
        private function addColumns($column_radio, $columns, $always_columns) {
    
            if ($column_radio == 'count(*)' || $column_radio == '*') {
    
                $this->addColumn($column_radio);
    
            } else {
    
                if (!is_array($columns)) {
    
                    $columns = array($columns);
    
                }
    
                if (!empty($always_columns) && !is_array($always_columns)) {
    
                    $always_columns = array($always_columns);
    
                }
    
                foreach ($always_columns as $always_column) {
    
                    if (!in_array($always_column,$columns)) {
    
                        $this->addColumn($always_column);
    
                    }
    
                }
    
                foreach ($columns as $column) {
    
                    $this->addColumn($column);
    
                }
    
            }
    
        }
    
        
    
        private function addColumn($column) {
    
    	$this->columns[] = $column;
    
        }
    
        
    
        private function setWhere($where_fields,$where_conditions,$where_values) {
    
            $where = '';
    
            for($i = 0; $i < count($where_values); $i++) {
    
                if (!empty($where_values[$i])) {
    
                    $where .= '`'.$this->modelName.'`.`'.$where_fields[$i].'` '.$where_conditions[$i].' "'.$where_values[$i].'" && ';
    
                }
    
            }
    
            $where = substr($where,0,strlen($where)-4);
    
    	$this->where = $where;
    
        }
    
        
    
        private function setOrderBy($order_by_field = 'id',$order_by_direction = 'asc') {
    
            $orderBy = '`'.$this->modelName.'`.`'.$order_by_field.'` '.$order_by_direction;
    
    	$this->orderBy = $orderBy;
    
        }
    
        
    
        private function setLimit($limit) {
    
    	$this->limit = $limit;
    
        }
    
        
    
        private function setResults() {
    
    	$this->results = $this->Model->findAll($this->where,$this->columns,$this->orderBy,$this->limit);
    
        }
    
        
    
        private function replaceValues($replace_value) {
    
            if (!is_array($replace_value)) {
    
                $replace_value = array($replace_value);
    
            }
    
            if (count($replace_value) != 4) {
    
                return false;
    
            }
    
            $replaceModel =& ClassRegistry::getObject($replace_value['replaceModelName'], 'Model');
    
            $replaceModel->recursive = -1;
    
            $replaces = $replaceModel->findAll();
    
            $replaces_new = array();
    
            foreach ($replaces as $replace) {
    
                $replaces_new[$replace[$replace_value['replaceModelName']][$replace_value['replaceModelField']]] = $replace[$replace_value['replaceModelName']][$replace_value['replaceModelValue']];
    
            }
    
            $replaces = $replaces_new;
    
            foreach ($this->results as &$result) {
    
                $result[$this->modelName][$replace_value['column']] = $replaces[$result[$this->modelName][$replace_value['column']]];
    
            }
    
        }
    
        
    
        public function returnSearchResults($modelName,$column_radio,$columns,$where_fields,$where_conditions,$where_values,$order_by_field,$order_by_direction,$limit,$always_columns = null) {
    
            $this->loadModel($modelName);
    
            $this->addColumns($column_radio,$columns,$always_columns);
    
            $this->setWhere($where_fields,$where_conditions,$where_values);
    
            $this->setOrderby($order_by_field,$order_by_direction);
    
            $this->setLimit($limit);
    
            $this->setResults();
    
            foreach ($replace_values as $replace_value) {
    
                $this->replaceValues($replace_value);
    
            }
    
            return $this->results;
    
        }
    
        
    
        public function formOptions($modelName,$table) {
    
            // return options for column, whereField, whereCondition,orderByField, orderByDirection
    
            $this->loadModel($modelName);
    
            $fields = $this->Model->query('show fields in `'.$table.'`');
    
            $fieldOptions = array();
    
            foreach ($fields as $field) {
    
                $fieldOptions[$field['COLUMNS']['Field']] = $field['COLUMNS']['Field'];
    
            }
    
            $columnOptions = $fieldOptions;
    
            $whereFieldOptions = $fieldOptions;
    
            $whereConditionOptions = array('=' => '=',
    
                                           '<=' => '<=',
    
                                           '<' => '<',
    
                                           '>' => '>',
    
                                           '>=' => '>=',
    
                                           'like' => 'like',
    
                                           '!=' => '!='
    
            );
    
            $orderByFieldOptions = $fieldOptions;
    
            $orderByDirectionOptions = array('asc' => 'asc',
    
                                             'desc' => 'desc'
    
            );
    
            return array('column' => $columnOptions,
    
                         'whereField' => $whereFieldOptions,
    
                         'whereCondition' => $whereConditionOptions,
    
                         'orderByField' => $orderByFieldOptions,
    
                         'orderByDirection' => $orderByDirectionOptions
    
                        );
    
        }
    
        
    
        public function returnAlways($result,$always_columns,$always_layout) {
    
            $always = '';
    
            for ($i = 0; $i < count($always_columns); $i++) {
    
                $always .= $result[$this->modelName][$always_columns[$i]].$always_layout[$i];
    
            }
    
            return $always;
    
        }
    
    
    
    }
    
    ?>
    ?>

I hope it's all self-explanatory. Maybe I'll update this later or post
comments on this article explaining it more if people need further
explanation.

.. _http://rossoft.wordpress.com/2006/03/28/register-head-tags-from-helpers-2/: http://rossoft.wordpress.com/2006/03/28/register-head-tags-from-helpers-2/
.. meta::
    :title: CakePHP Query Builder
    :description: CakePHP Article related to 1.2,query,custom,Helpers
    :keywords: 1.2,query,custom,Helpers
    :copyright: Copyright 2008 mjamesd
    :category: helpers

