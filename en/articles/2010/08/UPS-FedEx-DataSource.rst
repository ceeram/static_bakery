UPS & FedEx DataSource
======================

by %s on August 23, 2010

A UPS datasource and a FedEx datasource for retrieving shipping rate
estimates.


Note: The shipping APIs for FedEx and UPS have likely been updated so
this article is likely out of date. Use at your discretion.
===========================================================

Looking to get estimated shipping rates from UPS and/or FedEx? Try
these datasources.

These are part of a shipping resources packages I am building.


UPS Model Example
~~~~~~~~~~~~~~~~~

app/models/ups.php
``````````````````

Model Class:
````````````

::

    <?php 
    /**
     * UPS Model
     * Example usage of the UPS datasource.
     * 
     * Must have /app/config/database.php setup with:
     * 	var $ups = array(
     * 		'datasource'	=> 'ups',
     * 		'accessKey'		=> '',
     * 		'userId'		=> '',
     * 		'password'		=> ''
     * 	);
     * 
     * @author Kyle Robinson Young <kyle at kyletyoung.com>
     * @copyright 2010 Kyle Robinson Young
     *
     */
    class Ups extends AppModel {
        var $useDbConfig = 'ups';
        var $useTable = false;
        /**
         * GET RATE
         * Return a single shipping rate estimate.
         * @param $data
         * @return integer
         */
        function getRate($data=array()) {
            $results = $this->find('first', array(
                'conditions' 	=> $data
            ));
            return (!empty($results[$this->name]['rate'])) ? $results[$this->name]['rate'] : -1;
        } // getRate
        /**
         * GET RATES
         * Return multiple shipping rate estimates.
         * @return array | -1
         */
        function getRates() {
            $args = func_get_args();
            $results = $this->find('all', array(
                'conditions' => array(
                    'AND'	=> $args
                ) 
            ));
            if (empty($results)) {
                return array_fill(0, sizeof($args), -1);
            } // empty results
            else {
                return Set::extract('/../'.$this->name.'/rate', $results);
            } // else
        } // getRates
        /**
         * GET RAW RESPONSE
         * Gives you the raw data sent back from UPS.
         * @return array
         */
        function getRawResponse() {
            $ds =& $this->getDataSource();
            return (empty($ds->rawResponse)) ? array() : $ds->rawResponse;
        } // getRawResponse
    } // Ups
    ?>



FedEx Model Example
~~~~~~~~~~~~~~~~~~~

app/models/fedex.php
````````````````````

Model Class:
````````````

::

    <?php 
    /**
     * Fedex Model
     * Example usage of the Fedex datasource.
     * 
     * Must have /app/config/database.php setup with:
     * 	var $fedex = array(
     * 		'datasource'	=> 'fedex',
     * 		'accessKey'		=> '',
     * 		'userId'		=> '',
     * 		'password'		=> ''
     * 	);
     * 
     * @author Kyle Robinson Young <kyle at kyletyoung.com>
     * @copyright 2010 Kyle Robinson Young
     *
     */
    class Fedex extends AppModel {
        var $useDbConfig = 'fedex';
        var $useTable = false;
        /**
         * GET RATE
         * Return a single shipping rate estimate.
         * @param $data
         * @return integer
         */
        function getRate($data=array()) {
            $results = $this->find('first', array(
                'conditions' 	=> $data
            ));
            return (!empty($results[$this->name]['rate'])) ? $results[$this->name]['rate'] : -1;
        } // getRate
        /**
         * GET RATES
         * Return multiple shipping rate estimates.
         * @return array | -1
         */
        function getRates() {
            $args = func_get_args();
            $results = $this->find('all', array(
                'conditions' => array(
                    'AND'	=> $args
                ) 
            ));
            if (empty($results)) {
                return array_fill(0, sizeof($args), -1);
            } // empty results
            else {
                return Set::extract('/../'.$this->name.'/rate', $results);
            } // else
        } // getRates
        /**
         * GET RAW RESPONSE
         * Gives you the raw data sent back from UPS.
         * @return array
         */
        function getRawResponse() {
            $ds =& $this->getDataSource();
            return (empty($ds->rawResponse)) ? array() : $ds->rawResponse;
        } // getRawResponse
    } // Fedex
    ?>



Example Database Config File
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

app/config/database.php
```````````````````````

::

    
    class DATABASE_CONFIG {
    	var $ups = array(
    	    'datasource'	    => 'ups',
    	    'accessKey'		    => '',
    	    'userId'		    => '',
    	    'password'		    => ''
    	);
    	var $fedex = array(
    	    'datasource'	    => 'fedex',
    	    'accountNumber'		=> '',
    	    'meterNumber'		=> ''
    	);
    }


Now onto the datasource code...



UPS DataSource
~~~~~~~~~~~~~~

app/models/datasources/ups_source.php
`````````````````````````````````````

Model Class:
````````````

::

    <?php 
    /**
     * UPS DataSource v0.2
     * Used for estimating shipping rates from UPS, through models.
     * 
     * Copyright (C) 2010 Kyle Robinson Young
     * 
     * Permission is hereby granted, free of charge, to any person
     * obtaining a copy of this software and associated documentation
     * files (the "Software"), to deal in the Software without
     * restriction, including without limitation the rights to use,
     * copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the
     * Software is furnished to do so, subject to the following
     * conditions:
     * 
     * The above copyright notice and this permission notice shall be
     * included in all copies or substantial portions of the Software.
     * 
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
     * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
     * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
     * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
     * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
     * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
     * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
     * OTHER DEALINGS IN THE SOFTWARE.
     * 
     * @author Kyle Robinson Young <kyle at kyletyoung.com>
     * @copyright 2010 Kyle Robinson Young
     * @license http://www.opensource.org/licenses/mit-license.php The MIT License
     * @version 0.2
     * @link http://www.kyletyoung.com/code/cakephp_shipping
     * 
     * UPS Developer & Documentation
     * 	https://www.ups.com/upsdeveloperkit
     * 
     * USAGE:
     * 	As of this build, you can query data like so:
     * 	$results = $this->Ups->find('first', array(
     * 		'conditions'	=> array(
     * 			'weight'	=> 25,
     * 			'service'	=> '03'
     * 		)
     * 	));
     * 	$results = $this->Ups->findByWeight(25);
     * 	$results = $this->Ups->find("weight = 25, service = '02'");
     * 
     * TODO:
     * 	Make request XML dynamic.
     * 	Setup auto validate for read.
     * 	Handle response errors.
     * 
     */
    App::import('Core', array('HttpSocket', 'Xml', 'Set'));
    class UpsSource extends DataSource {
        /**
         * _CONFIG
         * Defaults coming in from config/database.php
         * @var array
         */
        var $_config = array(
    	    'accessKey'		=> '',
    	    'userId'		=> '',
    	    'password'		=> '',
    	    'apiUrl'		=> 'https://www.ups.com/ups.app/xml/Rate',
            'autoValidate'	=> true,
        
            // DEFAULT VALUES REQUIRED
        	'shipper_zip'        => '94901',
        	'shipper_country'    => 'US',
        	'ship_from_zip'      => '94901',
        	'ship_from_country'  => 'US',
        	'ship_to_zip'        => '94901',
        	'ship_to_country'    => 'US',
            'shipper_number'     => '1234',
        	'pickup_type'        => '01',
            'packaging_type'     => '02',
            'dimensions_unit'    => 'IN',
            'dimensions_length'  => 8,
        	'dimensions_height'  => 8,
        	'dimensions_width'   => 8,
            'weight_unit'        => 'LBS',
            'weight'             => 1,
            'service'            => '03'
    	);
    	/**
    	 * _SCHEMA
    	 * @var array
    	 */
    	var $_schema = array(
    	    'ups'	=> array(
    	        'rate'	=> array(
    	            'type'	    => 'integer',
    	            'null'	    => true,
    	            'primary'	=> false,
    	            'length'	=> 11
    	        ),
    	        'currency'	=> array(
    	            'type'	    => 'string',
    	            'null'	    => true,
    	            'primary'	=> false,
    	            'length'	=> 3
    	        ),
    	        'status'	=> array(
    	            'type'	    => 'string',
    	            'null'	    => true,
    	            'primary'	=> false,
    	            'length'	=> 255
    	        ),
    	        'error_code'	=> array(
    	            'type'	    => 'integer',
    	            'null'	    => true,
    	            'primary'	=> false,
    	            'length'	=> 11
    	        ),
    	        'error_description'	=> array(
    	            'type'	    => 'string',
    	            'null'	    => true,
    	            'primary'	=> false,
    	            'length'	=> 255
    	        ),
    	    )
    	);
    	/**
    	 * _VALIDATE
    	 * Use validate rules to check input data.
    	 * @var array
    	 */
    	var $_validate = array(
    	    'weight' => array(
    	        'rule' => array('comparison', '>=', .1),
    	        'message' => 'Weight must be over 0.1'
    	    ),
    	);
    	/**
    	 * RAW RESPONSE
    	 * The last raw response.
    	 * @var array
    	 */
    	var $rawResponse = array();
    	/**
    	 * CONSTRUCTOR
    	 * Init config and setup connection.
    	 * @param array $config
    	 */
    	function __construct($config) {
    	    $this->_config = array_merge($this->_config, (array)$config);
    	    $this->connection = new HttpSocket();
    		parent::__construct($config);
    	} // __construct
    	/**
    	 * READ
    	 * Posts to UPS and returns response.
    	 * @param object $model
    	 * @param array $queryData
    	 */
    	function read(&$model, $queryData=array()) {
    	    
    	    // IF VALIDATE INPUT
    	    $this->_autoValidate($model);
    	    
    	    // FORMAT CONDITIONS
    	    $conditions = $this->_prepareConditions($queryData['conditions']);
    	    
    	    $out = array();
    	    $this->rawResponse = array();
    	    foreach ($conditions as $data) {
        	    // BUILD XML
    	        $xml = $this->_buildXml($data);
    	    
        	    // POST XML
    	        $response = $this->connection->post($this->_config['apiUrl'], $xml);
    	    
        	    // FORMAT RESPONSE
    	        $response = new Xml($response);
    	        $response = $response->toArray();
    	        $this->rawResponse[] = (array)$response;
    	        
    	        // GRAB FIELDS FROM RESPONSE
        	    $rate = current(Set::extract('/RatingServiceSelectionResponse/RatedShipment/TotalCharges/MonetaryValue', $response));
        	    $currency = current(Set::extract('/RatingServiceSelectionResponse/RatedShipment/TotalCharges/CurrencyCode', $response));
        	    $status = current(Set::extract('/RatingServiceSelectionResponse/Response/ResponseStatusDescription', $response));
        	    $error_code = current(Set::extract('/RatingServiceSelectionResponse/Response/Error/ErrorCode', $response));
        	    $error_description = current(Set::extract('/RatingServiceSelectionResponse/Response/Error/ErrorDescription', $response));
        	    
        	    $out[] = array(
        	        $model->name	=> array(
        	            'rate'              => $rate,
        	            'currency'			=> $currency,
        	            'status'	        => $status,
        	            'error_code'	    => $error_code,
        	            'error_description'	=> $error_description,
        	        )
                );
    	    } // foreach
    	    return $out;
    	} // read
    	/**
         * DataSource Query abstraction
         * Copied from cake/libs/model/datasources/dbo_source.php
         *
         * @return resource Result resource identifier.
         * @access public
         */
    	function query() {
    	    $args	  = func_get_args();
    		$fields	  = null;
    		$order	  = null;
    		$limit	  = null;
    		$page	  = null;
    		$recursive = null;
    
    		if (count($args) == 1) {
    			return $this->fetchAll($args[0]);
    
    		} elseif (count($args) > 1 && (strpos(strtolower($args[0]), 'findby') === 0 || strpos(strtolower($args[0]), 'findallby') === 0)) {
    			$params = $args[1];
    
    			if (strpos(strtolower($args[0]), 'findby') === 0) {
    				$all  = false;
    				$field = Inflector::underscore(preg_replace('/^findBy/i', '', $args[0]));
    			} else {
    				$all  = true;
    				$field = Inflector::underscore(preg_replace('/^findAllBy/i', '', $args[0]));
    			}
    
    			$or = (strpos($field, '_or_') !== false);
    			if ($or) {
    				$field = explode('_or_', $field);
    			} else {
    				$field = explode('_and_', $field);
    			}
    			$off = count($field) - 1;
    
    			if (isset($params[1 + $off])) {
    				$fields = $params[1 + $off];
    			}
    
    			if (isset($params[2 + $off])) {
    				$order = $params[2 + $off];
    			}
    
    			if (!array_key_exists(0, $params)) {
    				return false;
    			}
    
    			$c = 0;
    			$conditions = array();
    
    			foreach ($field as $f) {
    				$conditions[$args[2]->alias . '.' . $f] = $params[$c];
    				$c++;
    			}
    
    			if ($or) {
    				$conditions = array('OR' => $conditions);
    			}
    
    			if ($all) {
    				if (isset($params[3 + $off])) {
    					$limit = $params[3 + $off];
    				}
    
    				if (isset($params[4 + $off])) {
    					$page = $params[4 + $off];
    				}
    
    				if (isset($params[5 + $off])) {
    					$recursive = $params[5 + $off];
    				}
    				return $args[2]->find('all', compact('conditions', 'fields', 'order', 'limit', 'page', 'recursive'));
    			} else {
    				if (isset($params[3 + $off])) {
    					$recursive = $params[3 + $off];
    				}
    				return $args[2]->find('first', compact('conditions', 'fields', 'order', 'recursive'));
    			}
    		} else {
    			if (isset($args[1]) && $args[1] === true) {
    				return $this->fetchAll($args[0], true);
    			} else if (isset($args[1]) && !is_array($args[1]) ) {
    				return $this->fetchAll($args[0], false);
    			} else if (isset($args[1]) && is_array($args[1])) {
    				$offset = 0;
    				if (isset($args[2])) {
    					$cache = $args[2];
    				} else {
    					$cache = true;
    				}
    				$args[1] = array_map(array(&$this, 'value'), $args[1]);
    				return $this->fetchAll(String::insert($args[0], $args[1]), $cache);
    			}
    		}
    	} // query
    	/**
    	 * LIST SOURCES
    	 * @return array
    	 */
    	function listSources() {
    		return array('ups');
    	} // listSources
    	/**
    	 * DESCRIBE
    	 * @param object $model
    	 */
    	function describe($model) {
    		return $this->_schema['ups'];
    	} // describe
    	/**
    	 * _AUTO VALIDATE
    	 * @param object $model
    	 * @access private
    	 */
    	function _autoValidate($model) {
    	    if ($this->_config['autoValidate']) {
    	        $model->validate = $this->_validate;
    	        //$model->save();
    	    } // autoValidate
    	} // autoValidate
    	/**
    	 * _PREPARE CONDITIONS
    	 * @param mixed $conditions
    	 * @return array
    	 * @access private
    	 */
    	function _prepareConditions($conditions=array()) {
    	    // IF LIKE SQL WHERE QUERY
    	    if (is_string($conditions)) {
    	        $tmp = array();
    	        $conditions = explode(",", $conditions);
    	        foreach ($conditions as $val)
    	        {
    	            $e = explode("=", $val);
    	            if (empty($e[0])) continue;
    	            $tmp[trim($e[0])] = trim($e[1], " '\"");
    	        } // foreach
    	        $conditions = $tmp;
    	    } // is_string
    	    // IF A SINGLE REQUEST
    	    if (!isset($conditions['AND'])) {
    	        $conditions = array('AND' => array($conditions));
    	    } // !empty
    	    $out = array();
    	    if (empty($conditions['AND'])) return array();
    	    foreach ($conditions['AND'] as $arr) {
    	        $tmp = array();
    	        // PARSE OUT MODEL
    	        if (empty($arr)) continue;
        	    foreach ($arr as $key => $val) {
                    $tmp[end(explode(".", $key))] = $val;
                } // foreach
                //debug($tmp);
                // MERGE WITH DEFAULTS
                $out[] = array_merge(
                    (array)$this->_config,
                    (array)$tmp
                );
    	    } // foreach
    	    return $out;
    	} // _prepareConditions
    	function _buildXml($data=array()) {
    	    $data = $this->_formatData($data);
    	    return "<?xml version=\"1.0\"?>  
    		<AccessRequest xml:lang=\"en-US\">  
    		    <AccessLicenseNumber>".$data['accessKey']."</AccessLicenseNumber>  
    		    <UserId>".$data['userId']."</UserId>  
    		    <Password>".$data['password']."</Password>  
    		</AccessRequest>  
    		<?xml version=\"1.0\"?>  
    		<RatingServiceSelectionRequest xml:lang=\"en-US\">  
    		    <Request>  
    			<TransactionReference>  
    			    <CustomerContext>Bare Bones Rate Request</CustomerContext>  
    			    <XpciVersion>1.0001</XpciVersion>  
    			</TransactionReference>  
    			<RequestAction>Rate</RequestAction>  
    			<RequestOption>Rate</RequestOption>  
    		    </Request>  
    		<PickupType>  
    		    <Code>".$data['pickup_type']."</Code>  
    		</PickupType>  
    		<Shipment>  
    		    <Shipper>  
    			<Address>  
    			    <PostalCode>".$data['shipper_zip']."</PostalCode>  
    			    <CountryCode>".$data['shipper_country']."</CountryCode>  
    			</Address>  
    		    <ShipperNumber>".$data['shipper_number']."</ShipperNumber>  
    		    </Shipper>  
    		    <ShipTo>  
    			<Address>  
    			    <PostalCode>".$data['ship_to_zip']."</PostalCode>  
    			    <CountryCode>".$data['ship_to_country']."</CountryCode>  
    			<ResidentialAddressIndicator/>  
    			</Address>  
    		    </ShipTo>  
    		    <ShipFrom>  
    			<Address>  
    			    <PostalCode>".$data['ship_from_zip']."</PostalCode>  
    			    <CountryCode>".$data['ship_from_country']."</CountryCode>  
    			</Address>  
    		    </ShipFrom>  
    		    <Service>  
    			<Code>".$data['service']."</Code>  
    		    </Service>  
    		    <Package>  
    			<PackagingType>  
    			    <Code>".$data['packaging_type']."</Code>  
    			</PackagingType>  
    			<Dimensions>  
    			    <UnitOfMeasurement>  
    				<Code>".$data['dimensions_unit']."</Code>  
    			    </UnitOfMeasurement>  
    			    <Length>".$data['dimensions_length']."</Length>  
    			    <Width>".$data['dimensions_width']."</Width>  
    			    <Height>".$data['dimensions_height']."</Height>  
    			</Dimensions>  
    			<PackageWeight>  
    			    <UnitOfMeasurement>  
    				<Code>".$data['weight_unit']."</Code>  
    			    </UnitOfMeasurement>  
    			    <Weight>".$data['weight']."</Weight>  
    			</PackageWeight>  
    		    </Package>  
    		</Shipment>  
    		</RatingServiceSelectionRequest>";
    	} // buildXml
    	/**
    	 * _FORMAT DATA
    	 * @param array $data
    	 */
    	function _formatData($data=array()) {
    	    $data['weight'] = number_format($data['weight'], 1, '.', '');
    	    return $data;
    	} // _formatData
    	/**
    	 * _ERROR CHECK
    	 * @param array $data
    	 * 
    	 * TODO: Build this.
    	 */
    	function _errorCheck($data=null)
    	{
    	} // _errorCheck
    } // UpsSource
    ?>



FedEx DataSource
~~~~~~~~~~~~~~~~

app/models/datasources/fedex_source.php
```````````````````````````````````````

Model Class:
````````````

::

    <?php 
    /**
     * Fedex DataSource v0.1
     * Used for estimating shipping rates from Fedex, through models.
     * 
     * Copyright (C) 2010 Kyle Robinson Young
     * 
     * Permission is hereby granted, free of charge, to any person
     * obtaining a copy of this software and associated documentation
     * files (the "Software"), to deal in the Software without
     * restriction, including without limitation the rights to use,
     * copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the
     * Software is furnished to do so, subject to the following
     * conditions:
     * 
     * The above copyright notice and this permission notice shall be
     * included in all copies or substantial portions of the Software.
     * 
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
     * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
     * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
     * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
     * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
     * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
     * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
     * OTHER DEALINGS IN THE SOFTWARE.
     * 
     * @author Kyle Robinson Young <kyle at kyletyoung.com>
     * @copyright 2010 Kyle Robinson Young
     * @license http://www.opensource.org/licenses/mit-license.php The MIT License
     * @version 0.1
     * @link http://www.kyletyoung.com/code/cakephp_shipping
     * 
     * FedEx Developer & Documentation
     * 	http://fedex.com/us/developer/
     * 
     * USAGE:
     * 	As of this build, you can query data like so:
     * 	$results = $this->Fedex->find('first', array(
     * 		'conditions'	=> array(
     * 			'weight'	=> 25
     * 		)
     * 	));
     * 	$results = $this->Fedex->findByWeight(25);
     * 	$results = $this->Fedex->find("weight = 25");
     * 
     * TODO:
     * 	Make request XML dynamic.
     * 	Setup auto validate for read.
     * 	Handle response errors.
     * 
     */
    App::import('Core', array('HttpSocket', 'Xml', 'Set'));
    class FedexSource extends DataSource {
        /**
         * _CONFIG
         * Defaults coming in from config/database.php
         * @var array
         */
        var $_config = array(
    	    'accountNumber'		=> '',
    	    'meterNumber'		=> '',
    	    'apiUrl'		    => 'https://gatewaybeta.fedex.com/GatewayDC',
            'autoValidate'	    => true,
        
            // DEFAULT VALUES REQUIRED
            'ship_from_state'      => 'CA',
        	'ship_from_zip'        => '95451',
        	'ship_from_country'    => 'US',
        	'ship_to_state'        => 'CA',
        	'ship_to_zip'          => '95451',
        	'ship_to_country'      => 'US',
        	'carrier_code'         => 'FDXG',
            'dropoff_type'         => 'REGULARPICKUP',
        	'service'              => 'GROUNDHOMEDELIVERY',
            'packaging'            => 'YOURPACKAGING',
            'weight_unit'          => 'LBS',
            'weight'			   => 1.0,
            'package_count'		   => 1
    	);
    	/**
    	 * _SCHEMA
    	 * @var array
    	 */
    	var $_schema = array(
    	    'fedex'	=> array(
    	        'rate'	=> array(
    	            'type'	    => 'integer',
    	            'null'	    => true,
    	            'primary'	=> false,
    	            'length'	=> 11
    	        ),
    	        'currency'	=> array(
    	            'type'	    => 'string',
    	            'null'	    => true,
    	            'primary'	=> false,
    	            'length'	=> 3
    	        ),
    	        'status'	=> array(
    	            'type'	    => 'string',
    	            'null'	    => true,
    	            'primary'	=> false,
    	            'length'	=> 255
    	        ),
    	        'error_code'	=> array(
    	            'type'	    => 'integer',
    	            'null'	    => true,
    	            'primary'	=> false,
    	            'length'	=> 11
    	        ),
    	        'error_description'	=> array(
    	            'type'	    => 'string',
    	            'null'	    => true,
    	            'primary'	=> false,
    	            'length'	=> 255
    	        ),
    	    )
    	);
    	/**
    	 * _VALIDATE
    	 * Use validate rules to check input data.
    	 * @var array
    	 */
    	var $_validate = array(
    	    'weight' => array(
    	        'rule' => array('comparison', '>=', .1),
    	        'message' => 'Weight must be over 0.1'
    	    ),
    	);
    	/**
    	 * RAW RESPONSE
    	 * The last raw response.
    	 * @var array
    	 */
    	var $rawResponse = array();
    	/**
    	 * CONSTRUCTOR
    	 * Init config and setup connection.
    	 * @param array $config
    	 */
    	function __construct($config) {
    	    $this->_config = array_merge($this->_config, (array)$config);
    	    $this->connection = new HttpSocket();
    		parent::__construct($config);
    	} // __construct
    	/**
    	 * READ
    	 * Posts to Fedex and returns response.
    	 * @param object $model
    	 * @param array $queryData
    	 */
    	function read(&$model, $queryData=array()) {
    	    
    	    // IF VALIDATE INPUT
    	    $this->_autoValidate($model);
    	    
    	    // FORMAT CONDITIONS
    	    $conditions = $this->_prepareConditions($queryData['conditions']);
    	    
    	    $out = array();
    	    $this->rawResponse = array();
    	    foreach ($conditions as $data) {
    	        if (empty($data)) continue;
    	        
        	    // BUILD XML
    	        $xml = $this->_buildXml($data);
    	    
        	    // POST XML
    	        $response = $this->connection->post($this->_config['apiUrl'], $xml);
    	    
        	    // FORMAT RESPONSE
    	        $response = new Xml($response);
    	        $response = $response->toArray();
    	        $this->rawResponse[] = (array)$response;
    	        
    	        // GRAB FIELDS FROM RESPONSE
        	    $rate = current(Set::extract('/FDXRateReply/EstimatedCharges/DiscountedCharges/NetCharge', $response));
        	    $currency = current(Set::extract('/FDXRateReply/EstimatedCharges/CurrencyCode', $response));
        	    
        	    $status = '';
        	    $error_code = current(Set::extract('/FDXRateReply/Error/Code', $response));
        	    $error_description = current(Set::extract('/FDXRateReply/Error/Message', $response));
        	    
        	    $out[] = array(
        	        $model->name	=> array(
        	            'rate'              => $rate,
        	            'currency'			=> $currency,
        	            'status'	        => $status,
        	            'error_code'	    => $error_code,
        	            'error_description'	=> $error_description,
        	        )
                );
    	    } // foreach
    	    return $out;
    	} // read
    	/**
         * DataSource Query abstraction
         * Copied from cake/libs/model/datasources/dbo_source.php
         *
         * @return resource Result resource identifier.
         * @access public
         */
    	function query() {
    	    $args	  = func_get_args();
    		$fields	  = null;
    		$order	  = null;
    		$limit	  = null;
    		$page	  = null;
    		$recursive = null;
    
    		if (count($args) == 1) {
    			return $this->fetchAll($args[0]);
    
    		} elseif (count($args) > 1 && (strpos(strtolower($args[0]), 'findby') === 0 || strpos(strtolower($args[0]), 'findallby') === 0)) {
    			$params = $args[1];
    
    			if (strpos(strtolower($args[0]), 'findby') === 0) {
    				$all  = false;
    				$field = Inflector::underscore(preg_replace('/^findBy/i', '', $args[0]));
    			} else {
    				$all  = true;
    				$field = Inflector::underscore(preg_replace('/^findAllBy/i', '', $args[0]));
    			}
    
    			$or = (strpos($field, '_or_') !== false);
    			if ($or) {
    				$field = explode('_or_', $field);
    			} else {
    				$field = explode('_and_', $field);
    			}
    			$off = count($field) - 1;
    
    			if (isset($params[1 + $off])) {
    				$fields = $params[1 + $off];
    			}
    
    			if (isset($params[2 + $off])) {
    				$order = $params[2 + $off];
    			}
    
    			if (!array_key_exists(0, $params)) {
    				return false;
    			}
    
    			$c = 0;
    			$conditions = array();
    
    			foreach ($field as $f) {
    				$conditions[$args[2]->alias . '.' . $f] = $params[$c];
    				$c++;
    			}
    
    			if ($or) {
    				$conditions = array('OR' => $conditions);
    			}
    
    			if ($all) {
    				if (isset($params[3 + $off])) {
    					$limit = $params[3 + $off];
    				}
    
    				if (isset($params[4 + $off])) {
    					$page = $params[4 + $off];
    				}
    
    				if (isset($params[5 + $off])) {
    					$recursive = $params[5 + $off];
    				}
    				return $args[2]->find('all', compact('conditions', 'fields', 'order', 'limit', 'page', 'recursive'));
    			} else {
    				if (isset($params[3 + $off])) {
    					$recursive = $params[3 + $off];
    				}
    				return $args[2]->find('first', compact('conditions', 'fields', 'order', 'recursive'));
    			}
    		} else {
    			if (isset($args[1]) && $args[1] === true) {
    				return $this->fetchAll($args[0], true);
    			} else if (isset($args[1]) && !is_array($args[1]) ) {
    				return $this->fetchAll($args[0], false);
    			} else if (isset($args[1]) && is_array($args[1])) {
    				$offset = 0;
    				if (isset($args[2])) {
    					$cache = $args[2];
    				} else {
    					$cache = true;
    				}
    				$args[1] = array_map(array(&$this, 'value'), $args[1]);
    				return $this->fetchAll(String::insert($args[0], $args[1]), $cache);
    			}
    		}
    	} // query
    	/**
    	 * LIST SOURCES
    	 * @return array
    	 */
    	function listSources() {
    		return array('fedex');
    	} // listSources
    	/**
    	 * DESCRIBE
    	 * @param object $model
    	 */
    	function describe($model) {
    		return $this->_schema['fedex'];
    	} // describe
    	/**
    	 * _AUTO VALIDATE
    	 * @param object $model
    	 * @access private
    	 */
    	function _autoValidate($model) {
    	    if ($this->_config['autoValidate']) {
    	        $model->validate = $this->_validate;
    	        //$model->save();
    	    } // autoValidate
    	} // autoValidate
    	/**
    	 * _PREPARE CONDITIONS
    	 * @param mixed $conditions
    	 * @return array
    	 * @access private
    	 */
    	function _prepareConditions($conditions=array()) {
    	    // IF LIKE SQL WHERE QUERY
    	    if (is_string($conditions)) {
    	        $tmp = array();
    	        $conditions = explode(",", $conditions);
    	        foreach ($conditions as $val)
    	        {
    	            $e = explode("=", $val);
    	            if (empty($e[0])) continue;
    	            $tmp[trim($e[0])] = trim($e[1], " '\"");
    	        } // foreach
    	        $conditions = $tmp;
    	    } // is_string
    	    // IF A SINGLE REQUEST
    	    if (!isset($conditions['AND'])) {
    	        $conditions = array('AND' => array($conditions));
    	    } // !empty
    	    $out = array();
    	    if (empty($conditions['AND'])) return array();
    	    foreach ($conditions['AND'] as $arr) {
    	        $tmp = array();
    	        // PARSE OUT MODEL
    	        if (empty($arr)) continue;
        	    foreach ($arr as $key => $val) {
                    $tmp[end(explode(".", $key))] = $val;
                } // foreach
                //debug($tmp);
                // MERGE WITH DEFAULTS
                $out[] = array_merge(
                    (array)$this->_config,
                    (array)$tmp
                );
    	    } // foreach
    	    return $out;
    	} // _prepareConditions
    	function _buildXml($data=array()) {
    	    $data = $this->_formatData($data);
    	    return '<?xml version="1.0" encoding="UTF-8" ?>
            	<FDXRateRequest xmlns:api="http://www.fedex.com/fsmapi" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="FDXRateRequest.xsd">
                	<RequestHeader>
            			<CustomerTransactionIdentifier>Express Rate</CustomerTransactionIdentifier>
                        <AccountNumber>'.$data['accountNumber'].'</AccountNumber>
                        <MeterNumber>'.$data['meterNumber'].'</MeterNumber>
                        <CarrierCode>'.$data['carrier_code'].'</CarrierCode>
                    </RequestHeader>
                    <DropoffType>'.$data['dropoff_type'].'</DropoffType>
                    <Service>'.$data['service'].'</Service>
                    <Packaging>'.$data['packaging'].'</Packaging>
                    <WeightUnits>'.$data['weight_unit'].'</WeightUnits>
                    <Weight>'.$data['weight'].'</Weight>
                    <OriginAddress>
                        <StateOrProvinceCode>'.$data['ship_from_state'].'</StateOrProvinceCode>
                        <PostalCode>'.$data['ship_from_zip'].'</PostalCode>
                        <CountryCode>'.$data['ship_from_country'].'</CountryCode>
                    </OriginAddress>
                    <DestinationAddress>
                        <StateOrProvinceCode>'.$data['ship_to_state'].'</StateOrProvinceCode>
                        <PostalCode>'.$data['ship_to_zip'].'</PostalCode>
                        <CountryCode>'.$data['ship_to_country'].'</CountryCode>
                    </DestinationAddress>
                    <Payment>
                        <PayorType>SENDER</PayorType>
                    </Payment>
                    <PackageCount>'.$data['package_count'].'</PackageCount>
                </FDXRateRequest>';
    	} // buildXml
    	/**
    	 * _FORMAT DATA
    	 * @param array $data
    	 */
    	function _formatData($data=array()) {
    	    $data['weight'] = number_format($data['weight'], 1, '.', '');
    	    $data['package_count'] = number_format($data['package_count'], 0, '.', '');
    	    return $data;
    	} // _formatData
    	/**
    	 * _ERROR CHECK
    	 * @param array $data
    	 * 
    	 * TODO: Build this.
    	 */
    	function _errorCheck($data=null)
    	{
    	} // _errorCheck
    } // FedexSource
    ?>

`1`_|`2`_|`3`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_

.. _Page 3: :///articles/view/4caea0e7-4330-4780-9023-485282f0cb67/lang:eng#page-3
.. _Page 2: :///articles/view/4caea0e7-4330-4780-9023-485282f0cb67/lang:eng#page-2
.. _Page 1: :///articles/view/4caea0e7-4330-4780-9023-485282f0cb67/lang:eng#page-1
.. meta::
    :title: UPS & FedEx DataSource
    :description: CakePHP Article related to datasource,ups,fedex,Models
    :keywords: datasource,ups,fedex,Models
    :copyright: Copyright 2010 
    :category: models

