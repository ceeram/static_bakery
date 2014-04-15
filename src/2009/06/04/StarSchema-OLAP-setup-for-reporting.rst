StarSchema (OLAP) setup for reporting
=====================================

by eimermusic on June 04, 2009

When you need to gather statistics and reporting data from a large
number of transactions (easily >1'000'000 rows) you need to transfer
that data over to a reporting-friendly format to keep request times
down. One popular database design for this is called a Star Schema.
This is a simple re-usable StarSchema setup for CakePHP.
To make heads and tails of the following it help to have some basic
understanding of star schemas and why anyone would use OLAP. If your
gut-reaction is "Why not just do a find on the transaction data and
format a reporting view from that?" then you should at least give the
following page a look.

`http://ciobriefings.com/Publications/WhitePapers/DesigningtheStarSche
maDatabase/tabid/101/Default.aspx`_

When do I need this?
````````````````````


+ The data you are reporting on is growing and your app is slowing
  down
+ The data is not stored the way you want to "view it" in your reports
+ You need to "view" your data in various different ways in your
  reports



Disclaimer
``````````

This article is to provide an implementation of a star schema setup
for Cake. You still need to know how to DESIGN your schema. It is very
important to plan ahead and create a schema that will accomodate ALL
the ways (dimensions) you want to display your data. It is a bit
tricky to alter the schema afterwards.

This code uses some of ugly messy code that improves performance. The
database goes against some Cake conventions (no pk!). It also contains
some ugly messy code that is the fault of the author (me). Also this
setup has only been run and tested on MySQL and uses a custom dbo that
can put dynamic data into a Model's resultset.

This article is about the star schema implementation. It could be
extended to aggregated tables to improve display performance even
further. How to do this is not explained here.



Pieces to the puzzle
````````````````````


+ Extended DBO
+ Fact Model
+ Dimension Models
+ A Controller example



Extended DBO
~~~~~~~~~~~~

I found this extended DBO a long time ago. Credit should go where
credit is due but I have lost the reference to the original author.
The DBO did more but I picked out the resultSet() method which is what
we need here. To get an extended mysqli driver replace the "mysql_..."
methods with "mysqli_..."

::

    
    <?php
    require_once (LIBS . 'model' . DS . 'datasources' . DS . 'dbo' . DS . 'dbo_mysql.php');
    // app/models/datasources//dbo/dbo_mysql_ex.php
    class DboMysqlEx extends DboMysql {
    	var $description = "MySQL DBO Driver - Extended";
    	
    	// Extended 'resultSet' to allow alias processing
    	// Fields should contain '((something)) AS Model__field'
    	function resultSet(&$results) {
    		$this->results =& $results;
    		$this->map = array();
    		$num_fields = mysql_num_fields($results);
    		$index = 0;
    		$j = 0;
    
    		while ($j < $num_fields) {
    			$column = mysql_fetch_field($results,$j);
    			if (!empty($column->table)) {
    				$this->map[$index++] = array($column->table, $column->name);
    			} else {
    				if (strpos($column->name, '__')) {
    					$parts = explode('__', $column->name);
    					$this->map[$index++] = array($parts[0], $parts[1]);
    				} else {
    					$this->map[$index++] = array(0, $column->name);
    				}
    			}
    			$j++;
    		}
    	}
    }



Fact Model
~~~~~~~~~~

The abstract fact model, the super, the parent, which all concrete
fact models extend.

FactModel implements a few custom methods that are common among fact
models. findFacts() is a custom find method that joins the fact model
with all its dimensions and returns a nice data array with the
requested fact. saveFact() is a custom save method that uses a custom
query to automatically update each row if it exists already. This is
done to improve performance (a lot).



Model Class:
````````````

::

    <?php 
    /*
     *	Abstract super-model for Facts
     */
    class FactModel extends AppModel {
    
    	var $name = 'FactModel';
    		
    	// I prefer using findFacts() directly but like this you could quickly make a find type of the whole thing
    	function find($type, $options = array()) {
    		switch ($type) {
    			case 'facts':
    				return $this->findFacts($options['fact'],$options);
    			default:
    				return parent::find($type, $options);
    		}
    	}
    
    	// this method will always be specific to each fact model
    	function gather( $start_time = null ) {
    		debug($this->alias.' must implement gather()');
    		return false;
    	}
    
    	//-- 'mapped' re-arranges the results in an array-hierarchy according to the group parameter.
    	//-- E.G. grouping by weekday might return array keys mon,tue... instead of 0,1...
    	function findFacts($fact, $options) {
    		$defaults = array(
    			'conditions' =>'',
    			'fields' =>array(),
    			'order' =>'',
    			'group' =>'',
    			'mapped'=>false
    		);
    		$options = array_merge($defaults,$options);
    		
    		$dimensions = $this->getAssociated('belongsTo');
    		
    		$joins = $this->useTable.' AS '.$this->alias;
    		$this_name = $this->alias;
    		foreach ( $dimensions as $k => $dim ) {
    			$dimension = $this->$dim->useTable.' AS '.$dim;
    			$fk = $this->belongsTo[$dim]['foreignKey'];
    			$joins = "($dimension INNER JOIN $joins ON $dim.id = $this_name.$fk)";
    		}
    		$fields = array_merge($options['fields'],array($fact.' AS '.$this->alias.'__fact',$options['group'],$options['order']));
    		$fields = array_unique($fields);
    		
    		$db =& ConnectionManager::getDataSource($this->useDbConfig);
    		$query = $db->renderStatement('select', array(
    			'conditions' => $db->conditions($options['conditions'], true, true, $this),
    			'fields' => join(', ', $fields),
    			'table' => '',
    			'alias' => '',
    			'order' => $db->order($options['order']),
    			'limit' => '',
    			'joins' => $joins,
    			'group' => $db->group($options['group'])
    		));				
    		$raw_facts = $this->query($query);
    		
    		if ( $options['mapped'] ) {
    			$group_str = str_replace(' ','',$options['group']);
    			$groups = explode(',',$group_str);
    			$last_group = array_pop($groups);
    			
    			$mapped_facts = array();
    			foreach ( $raw_facts as $key => $val ) {
    				$domain =& $mapped_facts;
    				foreach ( $groups as $group ) {
    					$gKey = Set::extract($raw_facts,$key.'.'.$group);
    					if ( !isset($domain[ $gKey ]) ) {
    						$domain[ $gKey ] = array();
    					}
    					$domain =& $domain[ $gKey ];
    				}
    				$gKey = Set::extract($raw_facts,$key.'.'.$last_group);
    				$domain[$gKey] = $val;
    			}
    
    			return $mapped_facts;
    		} else {
    			return $raw_facts;
    		}
    	}
    	
    	
    	function saveFact($fact) {
    		
    		$keys = array_keys($fact[$this->alias]);
    		$values = array_values($fact[$this->alias]);
    		
    		$fields = $this->_getFactFields();
    		$update = '';
    		foreach ( $fields as $field ) {
    			$update .= ' `'.$field.'` = '.$fact[$this->alias][$field].',';
    		}
    				
    		$query = 'INSERT INTO `'.$this->useTable.'` (`'. implode('`,`', $keys) .'`) VALUES ('. implode(',', $values) .') ON DUPLICATE KEY UPDATE'.substr($update,0,-1);
    		$this->query($query);
    	}
    	
    	function _getFactFields() {
    		$fields = array();
    		foreach ( $this->_schema as $field => $params ) {
    			if ( !isset($params['key']) ) {
    				$fields[] = $field;
    			}
    		}
    		return $fields;
    	}
    }
    ?>



Example FactSentSms
~~~~~~~~~~~~~~~~~~~

A simple example of a concrete model extending FactModel. This one
tracks outgoing SMS messages for a few dimensions. More on dimensions
shortly.

In this example the gather() method is quite simple. It could contain
any number of complex calculations or pulling of associated data. This
method does all the heavy lifting of the data and an initial gathering
of an existing transaction table can take a long time.

Notice that the fact table contains a field referencing the original
primary key from the transaction table. This is to ensure that we can
update the data without accidentally overwriting rows or creating
duplicates. The table definition makes a unique key of the composite
of all dimensions and this id. There is no primary key, by design.

::

    
    CREATE TABLE `fact_sent_smses` (
      `dimension_time_id` int(11) unsigned NOT NULL,
      `dimension_client_id` int(11) unsigned NOT NULL,
      `dimension_type_id` int(11) unsigned NOT NULL,
      `dimension_module_id` int(11) unsigned NOT NULL,
      `outgoing_id` int(11) unsigned NOT NULL,
      `num_smses` int(11) default '0',
      `response_code` int(11) default '0',
      `tarif_total` int(11) default '0',
      UNIQUE KEY `dimension_time_id` (`dimension_time_id`,`dimension_client_id`,`dimension_type_id`,`outgoing_id`,`dimension_module_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_binary


Model Class:
````````````

::

    <?php 
    /*
     *	Tracks outgoing messages with daily grain.
     */
    App::import('Model','FactModel');
    class FactSentSms extends FactModel {
    	var $name = 'FactSentSms';
    	var $useTable = 'fact_sent_smses';
    	
    	// dimensions are specified as belongsTo accosiations.
    	var $belongsTo = array('DimensionTime','DimensionClient','DimensionType','DimensionModule');
    
    
    	// gather new facts from transation model, run periodically from cron shell
    	// this method will always be specific to each fact model
    	function gather( $start_time = null ) {
    		if ( empty($start_time) ) {
    			$start_time = strtotime( '-1 hour', time() );
    		}
    		$start_date = date('Y-m-d H:i:s', $start_time);
    
    		$OutgoingSms =& ClassRegistry::init('OutgoingSms');
    		$page = 1;
    		while ( $all = $OutgoingSms->find('all', array(
    			'fields'=>array(
    				'*',
    				'DATE(OutgoingSms.created) AS OutgoingSms__date',
    				'TIME(OutgoingSms.created) AS OutgoingSms__time',
    				'DAYOFWEEK(OutgoingSms.created) AS OutgoingSms__day_of_week',
    				'DAYOFMONTH(OutgoingSms.created) AS OutgoingSms__day_of_month',
    				'DAYOFYEAR(OutgoingSms.created) AS OutgoingSms__day_of_year',
    				'MONTH(OutgoingSms.created) AS OutgoingSms__month',
    				'QUARTER(OutgoingSms.created) AS OutgoingSms__quarter',
    				'YEAR(OutgoingSms.created) AS OutgoingSms__year'
    			),
    			'conditions'=>array(
    				'OutgoingSms.created >' => $start_date
    			),
    			'recursive'=>'0',
    			'order'=>'OutgoingSms.created ASC',
    			'limit'=>'5000',
    			'page' => $page++
    		)) ) {
    			foreach ( $all as $one ) {
    				// Associate this fact with a record from each dimension
    				$fact['FactSentSms']['dimension_time_id'] = $this->DimensionTime->getDimensionFor($one['OutgoingSms']);
    				$fact['FactSentSms']['dimension_type_id'] = $this->DimensionType->getDimensionFor($one['OutgoingSms']);
    				$fact['FactSentSms']['dimension_client_id'] = $this->DimensionClient->getDimensionFor($one['OutgoingSms']);
    				$fact['FactSentSms']['dimension_module_id'] = $this->DimensionModule->getDimensionFor($one['OutgoingSms']);
    				
    				// Simple facts tracked
    				$fact['FactSentSms']['response_code'] = $one['OutgoingSms']['response_code'];
    				$fact['FactSentSms']['tarif_total'] = $one['OutgoingSms']['data']['tariffClass'];
    				$fact['FactSentSms']['outgoing_id'] = $one['OutgoingSms']['id']; // copy original id so that we can do updates reliably
    				$fact['FactSentSms']['num_smses'] = count(split_sms($one['OutgoingSms']['data']['userData'])); //WARNING. this line will fail unless you also have a function to split SMS messages
    
    				$this->saveFact($fact);
    			}
    		}// end big while
    	}
    }
    ?>



Dimension Models
~~~~~~~~~~~~~~~~

The dimension models are often simple. They only implement one custom
method (also often simple): getDimensionFor(). This method takes a
single record from the transaction model and figures out which
dimension it belongs to. E.G. It can look at the created field and
find the right time dimension to match that timestamp. It can check
the a status code and simply map to a record representing that code.

The following example is a time dimension with a daily grain. I.E. the
smallest increment in time is a day. I chose this dimension as the
example since it is one that can be re-used, often without
modification. If you look at gather() (above) you can see that I let
MySQL do all the heavy calculations to pick out the date components.
The method tries to find an existing dimension record and failing that
it creates a new record. Simple pimple!

This example also implements the most basic form of caching. Since the
dimensions usually contain a very limited number of records caching
each one in memory will dramatically increase the performance of the
gathering process. Say you have the price dimension with 10 prices
(that is 10 records) and 800'000 transactions to map. Evenly
distributed, each price record will be queried 80'000 times. 79'999
times to many. So by caching the queries you save your database
799'990 redundant queries just for a single dimension. My simple cache
is a lot faster than using cacheQueries, probably because it is so
very basic. When it is all that is needed I will gladly take the
performance boost.


::

    
    CREATE TABLE `dimension_time` (
      `id` int(11) unsigned NOT NULL auto_increment,
      `day_of_week` int(11) default NULL,
      `day_of_month` int(11) default NULL,
      `day_of_year` int(11) default NULL,
      `month` int(11) default NULL,
      `quarter` int(11) default NULL,
      `year` int(11) default NULL,
      `holiday` int(1) default '0',
      `weekend` int(1) default '0',
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COLLATE=utf8_binary



Model Class:
````````````

::

    <?php 
    /*
     *	Time dimension with daily grain
     */
    class DimensionTime extends AppModel {
    	var $name = 'DimensionTime';
    	var $useTable = 'dimension_time';
    	//var $cacheQueries = true;
    	var $dim_cache; // lesson learned: Do your own caching.
    
    	function getDimensionFor($one) {
    		if ( empty($one['created']) ) {
    			$one['created'] = '2000-01-01 00:00:00';			
    			$one['day_of_week'] = '1';
    			$one['day_of_month'] = '1';
    			$one['day_of_year'] = '1';
    			$one['month'] = '1';
    			$one['quarter'] = '1';
    			$one['year'] = '2000';
    		}
    
    		$dim_time = null;
    		//-> add cache search here
    		if ( !empty( $this->dim_cache[ $one['year'].$one['day_of_year'] ] ) ) {
    			$dim_time = $this->dim_cache[ $one['year'].$one['day_of_year'] ];
    		}
    
    		if ( empty($dim_time) ) {
    			$dim_time = $this->find('first',array(
    				'conditions' => array(
    					'DimensionTime.day_of_year' => $one['day_of_year'],
    					'DimensionTime.year' => $one['year']
    				)
    			));
    		}
    		if ( empty($dim_time) ) {
    			$dim_time['DimensionTime'] = array(
    				'day_of_week'=> $one['day_of_week'],
    				'day_of_month'=> $one['day_of_month'],
    				'day_of_year'=> $one['day_of_year'],
    				'month'=> $one['month'],
    				'quarter'=> $one['quarter'],
    				'year'=> $one['year'],
    				'holiday'=> -1,
    				'weekend'=> ($one['day_of_week']>5) ? 1: 0,
    			);
    			$this->create($dim_time);
    			$this->save($dim_time);
    			$dim_time = $this->read();
    		}
    		//-> save to cache here		
    		$this->dim_cache[ $one['year'].$one['day_of_year'] ] = $dim_time;
    
    		return $dim_time['DimensionTime']['id'];
    	}
    	
    }
    ?>

Then just rinse and repeat. Each dimension is very similar in its
design. You just have to figure out what key values you need to store
to define the desired "grain". Often this is a simple as using the
categories a product can be in or the different status codes returned
for a message.



A ReportsController example
~~~~~~~~~~~~~~~~~~~~~~~~~~~

This is a simple ReportsController that uses the model FactSentSms to
get statistics for pretty charts in the view. (Charts will not be
covered here.) The first example method below is used to load up data
for stats showing how many messages have been sent per weekday. The
fact you find is usually a SUM(), COUNT() or AVG() or some other SQL
function.

It looks a lot like a normal find. You can use order and conditions
like normal. In the first example we only way to count records where
the response code is a successful transaction (you need to know our
system to know exactly why they are this way). but for and error
report we want the opposite.

Group takes on a slightly special meaning here. It is used to define
the dimension(s) from which to view the data. In the first example
simply the day of week from the time dimension. You can add more
groupings to get your data returned as a multi-dimensional array, as
in the modules_and_types() method.

Mapped is a nifty little thing that replaces numerical array keys
(0-n) with meaningful keys like: monday-sunday or 1-7 for day of week
and 1-31 for day of month. It is a bit like findList() in that it
prepares your data for the table or graph in the view.


Controller Class:
`````````````````

::

    <?php 
    class ReportsController extends AppController {
    	var $name = 'Reports';
    	var $uses = array('FactSentSms');
    	var $billableResponseCodes = array('0','15'); // only these response codes result in a transaction.
    
    	// show successful transactions per weekday
    	function weekdays() {
    		$weekly_smses = $this->FactSentSms->findFacts('Sum(FactSentSms.num_smses)',array(
    			'group'=>'DimensionTime.day_of_week',
    			'order'=>'DimensionTime.day_of_week',
    			'conditions'=>array(
    				'(FactSentSms.response_code IN ('.implode(',',$this->billableResponseCodes).') )'
    			),
    			'mapped'=>true
    		));
    		$this->set('weekly_smses',$weekly_smses);		
    	}
    	
    	// show which modules (=parts of the system) have sent which types of messages
    	function modules_and_types() {
    		$all_modules = $this->FactSentSms->findFacts($sum,array(
    			'fields' => array('Sum(FactSentSms.tarif_total) AS FactSentSms__tarif_total'), // additional fields can be defined
    			'group'=>'DimensionModule.name, DimensionType.type', // multiple dimensions are possible
    			'order'=>'DimensionModule.name',
    			'conditions'=>array(
    				'(FactSentSms.response_code IN ('.implode(',',$this->billableResponseCodes).') )'
    			),
    			'mapped'=>true
    		));
    		$this->set('all_modules',$all_modules);
    	}
    	
    	// show error types for this month
    	function errors_this_month() {
    		$responses = $this->FactSentSms->findFacts($sum,array(
    			'group'=>'FactSentSms.response_code',
    			'order'=>'FactSentSms.response_code',
    			'conditions'=>array(
    				'(FactSentSms.response_code NOT IN ('.implode(',',$this->billableResponseCodes).') )',
    				'DimensionTime.month'=>date('m'),
    				'DimensionTime.year'=>date('Y')
    			),
    			'mapped'=>true
    		));				
    		$this->set('responses',$responses);
    	}
    }
    ?>



Conclusion
~~~~~~~~~~

I hope you can start to see the benefits. Once the schema is setup we
can write any number of reporting queries and "look at" the data from
many different directions very quickly. For example, doing the weekly
example on â‰ˆ220'000 records on our system is just over a second for
the full Cake request cycle including rendering of the html. That is
pretty dang good for a single (2 year old) rack server if you ask me.

[p] If you design your dimensions cleverly they can be re-used for
many fact tables. The time dimension is a prime candidate here. I can
use that for all reports of all types of facts since it check for the
generic created field.

[p] I will try to remember to check back here from time to time if
anyone has any questions. Enjoy.




.. _http://ciobriefings.com/Publications/WhitePapers/DesigningtheStarSchemaDatabase/tabid/101/Default.aspx: http://ciobriefings.com/Publications/WhitePapers/DesigningtheStarSchemaDatabase/tabid/101/Default.aspx

.. author:: eimermusic
.. categories:: articles, tutorials
.. tags:: model,schema,olap,statistics,report,star,Tutorials

