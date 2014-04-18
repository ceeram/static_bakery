Amazon ECS Component
====================

Search, Lookup and Remote Cart management
With this component at hand you can easily do lookups on specific
ASINs or search through the entire catalog. It is easy to build a
shopping application with this thing and the response is always an
easy to access array instead of a XML object.

All you need is a Amazon API Key and, if you like, an Associate Tag.
The XML Parser and HTTPSocket comes from the CakePHP 1.2 core.

Here a quick search and lookup example controller


Controller Class:
`````````````````

::

    <?php 
        var $components = array('Amazon');
    
        function beforeFilter() {
            $this->Amazon->setAccessKey( Configure::read('App.APIs.AmazonKey') );
            $this->Amazon->setAssociateTag( Configure::read('App.APIs.AmazonTag') );
        }
    
        function index() {
            $items = $this->Amazon->itemSearch('DVD', 'Title', 'Small,Offers');
            if (!$items) {
                pr ($this->Amazon->getLastErrors()); // pop some errors
            } else {
                $this->set('items', $items);
            }
        }
    
        function view($asin) {
            $item = $this->Amazon->itemLookup( $asin );
            if (!$item) {
                pr ($this->Amazon->getLastErrors()); // pop some errors
            } else {
                $this->set('item', $item);
            }
        }
    ?>


And this could be the business logic for a shopping cart controller


Controller Class:
`````````````````

::

    <?php 
        var $components = array('Amazon');
    
        function beforeFilter() {
            $this->Amazon->setAccessKey( Configure::read('App.APIs.AmazonKey') );
            $this->Amazon->setAssociateTag( Configure::read('App.APIs.AmazonTag') );
        }
    
        function add() {   
            if ($this->data) {
                $this->Amazon->cartThem(array(
                    array(
                        'quantity' => $this->data['Cart']['quantity'],
                        'offerId' => $this->data['Cart']['offerId']
                    ) // ... could be a larger collection. no prob.
                ));
                $this->redirect(aa('action', 'view'));
            }
        }
        function edit() {   
            if ($this->data) {
                $item_id = $this->data['Cart']['cartItemId'];
                $quantity = $this->data['Cart']['quantity'];
                $this->Amazon->cartUpdate($item_id, $quantity);
                $this->redirect(aa('action', 'view'));
            }
        }
        function view() {   
            $this->set('cart', $this->Amazon->cartGet());
            $this->set('cartHasItems', $this->Amazon->cartHasItems());
            $this->set('cartIsActive', $this->Amazon->cartIsActive());
        }
    
    ?>


Here's the component. Name it "amazon.php" and put it in your
controllers/components directory.


Component Class:
````````````````

::

    <?php 
    /**
     * Amazon E-Commerce Service Component 
     *    Search, lookup and remote cart management.
     *
     * @version 0.2
     * @author Kjell Bublitz <m3nt0r.de@gmail.com>
     * @copyright (c) 2008, Kjell Bublitz
     * @link http://www.m3nt0r.de Authors Weblog
     * @link http://github.com/m3nt0r/cake-bits Components Repository
     * @license http://www.opensource.org/licenses/mit-license.php The MIT License
     * @package app
     * @subpackage app.app.controllers.components
     */
    App::import('Core', array('Xml', 'HttpSocket'));
    
    /**
     * AmazonComponent
     *
     * @author Kjell Bublitz <m3nt0r.de@gmail.com>
     * @package app
     * @subpackage app.app.controllers.components
     */
    class AmazonComponent extends Object 
    {
    	/**
    	 * Component version for Request Header
    	 * @var string
    	 */
    	var $version = '0.2';
    
    	/**
    	 * This should be your public Amazon API-Key
    	 * @access protected
    	 * @var string
    	 */
    	var $_accessKey = '';
    
    	/**
    	 * This should be your Associate Tag (name).
    	 * @access protected
    	 * @var string
    	 */
    	var $_associateTag = '';
    
    	/**
    	 * The Session Key used for cart tracking
    	 * @access protected
    	 * @var string
    	 */
    	var $_sessionKey = '_AmaCart';
    
    	/**
    	 * Used Components 
    	 * @var array
    	 * @access public
    	 */
    	var $components = array('Session');
    		
    	/**
    	 * Default Parameters
    	 * @var array
    	 * @access public
    	 */
    	var $defaultParams = array(
    		'Service' => 'AWSECommerceService',
    		'Version' => '2006-09-11',	
    	);
    		
    	/**
    	 * Contains the last cart response
    	 * @var object
    	 * @access private
    	 */
    	var $__lastCart = null;
    	
    	/**
    	 * Contains the last error messages, indexed by method
    	 * @var array
    	 * @access private
    	 */
    	var $__lastErrors = array();
    	
    	/** 
    	 * This is the endpoint for all API requests.
    	 * @var string
    	 */
    	var $servicePoint = 'http://ecs.amazonaws.com/onca/xml';
    	
    	/**
    	 * Initalize the default parameters
    	 *
    	 * @access protected
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
    	 * @return void
    	 */
    	function _initDefaultParams() {
    		$this->defaultParams = am($this->defaultParams, array(
    			'AWSAccessKeyId' => $this->_accessKey,
    			'AssociateTag' => $this->_associateTag
    		));
    	}
    	
    	/**
    	 * Set your Amazon API Key
    	 *
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
    	 * @return object AmazonComponent
    	 */
    	function setAccessKey($key) {
    		$this->_accessKey = $key;
    		$this->_initDefaultParams();
    		return $this;
    	}
    	
    	/**
    	 * Set your Amazon Associate Tag
    	 *
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
    	 * @return object AmazonComponent
    	 */
    	function setAssociateTag($tag) {
    		$this->_associateTag = $tag;
    		$this->_initDefaultParams();
    		return $this;
    	}
    	
    	/**
    	 * Get an array with errors that came up after a request returned false.
    	 * Optional you can provide the name of the method to only get theirs, if any..
    	 *
    	 * @todo Need to add error catching to cart methods
    	 *
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
    	 * @return array  Always returns a array
    	 */
    	function getLastErrors($method = null) {
    		if ($method) {
    			return ife(isset($this->__lastErrors[$method]), $this->__lastErrors[$method], array());
    		}
    		return $this->__lastErrors;
    	}
    		
    	/**
    	 * Performs an ItemSearch (retrieve many items)
    	 * 
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
    	 * @param string $searchIndex A key like Books, Music, DVD...
    	 * @param string $queryString The query string: something from an input field, etc..
    	 * @param string $responseGroup (optional) Set of response groups separated with comma
    	 * @param string $page (optional) Amazon sends 10 hits per page
    	 * @return mixed Response or FALSE on error 
    	 */
    	function itemSearch($searchIndex, $queryString, $responseGroup = 'Small', $page = 1) {
    		$params = am($this->defaultParams, array(
    			'Operation' => 'ItemSearch',
    			'SearchIndex' => $searchIndex,
    			'ResponseGroup' => $responseGroup,
    			'Keywords' => $queryString,
    			'ItemPage' => $page,
    		));
    		$response = array_shift($this->__query($params));
    		if (isset($response['Items']['Request']['Errors'])) {
    			foreach ($response['Items']['Request']['Errors'] as $error) {
    				$this->__lastErrors['itemSearch'][] = $error;
    			}
    			return false;
    		}
    		return $response;
    	}
    	
    	/**
    	 * Look up a specific ASIN  (retrieves a single item)
    	 *
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
    	 * @param string $itemId ASIN - Amazon article number
    	 * @param string $responseGroup (optional) Set of response groups separated with comma
    	 * @return mixed Response or FALSE on error 
    	 */
    	function itemLookup($itemId, $responseGroup = 'Medium') {
    		$params = am($this->defaultParams, array(
    			'Operation' => 'ItemLookup',
    			'ResponseGroup' => $responseGroup,
    			'ItemId' => $itemId
    		));
    		$response = array_shift($this->__query($params));
    		if (isset($response['Items']['Request']['Errors'])) {
    			foreach ($response['Items']['Request']['Errors'] as $error) {
    				$this->__lastErrors['itemLookup'][] = $error;
    			}
    			return false;
    		}		
    		return $response;
    	}
    	
    	/**
    	 * Convenience method to bulk submit a couple items, or just one single item. This will create a cart if necessary.
    	 * 
    	 *  Example: $this->Amazon->cartThem(array(array('offerId' => 'asdasd...', 'quantity' => 3), array(...)));
     	 *
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>	
    	 * @param array $selectedItems A array with offerIds and quantity keys.
    	 * @return mixed Response or FALSE if nothing to do or bad input
    	 */
    	function cartThem($selectedItems) {
    		$result = false;
    		if (!empty($selectedItems) && is_array($selectedItems)) {
    			if (!$this->Session->check($this->_sessionKey)) { // new cart
    				$firstItem = array_shift($selectedItems);
    				$result = $this->cartCreate($firstItem['offerId'], $firstItem['quantity']);
    			}
    			if (count($selectedItems)) { // add 
    				foreach ($selectedItems as $item) {
    					$result = $this->cartAdd($item['offerId'], $item['quantity']);
    				}
    			}
    		}
    		return $result;
    	}
    	
    	/** 
    	 * Creates a new Remote Cart. A new cart is initialized once you add at least 1 item. The HMAC and CartID 
    	 * is used in all further communications. BEFORE YOU CAN USE THE CART, YOU HAVE TO ADD 1 ITEM AT LEAST!
    	 *
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>	
    	 * @param array $offerListingId An OfferListing->OfferListingId from Lookup or Search. You'll need "Offer" response group!
    	 * @param integer $quantity The amount the user wants from this item.
    	 * @return array
    	 */
    	function cartCreate($offerListingId, $quantity = 1) {		
    		$params = am($this->defaultParams, array(
    			'Operation' => 'CartCreate',
    			'Item.1.OfferListingId' => $offerListingId,
    			'Item.1.Quantity' => $quantity
    		));
    		$response = $this->__query($params);
    		$response = $response['CartCreateResponse'];
    		
    		// save the result in the session
    		$this->Session->write($this->_sessionKey, array(
    			'HMAC' => $response['Cart']['HMAC'],
    			'cartId' => $response['Cart']['CartId'],
    			'PurchaseUrl' => $response['Cart']['PurchaseURL'],
    		));
    		
    		return $this->__formatCartItems($response['Cart']);
    	}
    	
    	/**
    	 * Adds a new Item with given quantity to the remote cart.
    	 * 
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>	
    	 * @param string $offerListingId An ItemID from Lookup or Search Offer
    	 * @param integer $quantity As the name says.. 
    	 * @param string $HMAC (optional) HMAC If empty, uses session.
    	 * @param string $cartId (optional) Remote cart ID. If empty, uses session.
    	 * @return mixed Response or FALSE on missing HMAC/ID 
    	 */
    	function cartAdd($offerListingId, $quantity = 1, $HMAC = null, $cartId = null) {
    		if (!$HMAC) {
    		 	$HMAC = $this->Session->read($this->_sessionKey.'.HMAC');
    		}
    		if (!$cartId) {
    			$cartId = $this->Session->read($this->_sessionKey.'.cartId');
    		}
    		if (!$HMAC || !$cartId) {
    			return false;
    		}
    		
    		$params = am($this->defaultParams, array(
    			'Operation' => 'CartAdd',
    			'CartId' => $cartId,
    			'HMAC' => $HMAC,
    			'Item.1.OfferListingId' => $offerListingId,
    			'Item.1.Quantity' => $quantity
    		));
    		$response = $this->__query($params);
    		return $this->__formatCartItems($response['CartAddResponse']['Cart']);
    	}
    
    	/**
    	 * Update the Quantity of a CartItem
    	 * 
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>	
    	 * @param string $cartItemId As the name says.. [CartItem][CartItemId]
    	 * @param integer $quantity As the name says.. 
    	 * @param string $HMAC (optional) HMAC which was returned with cartCreate. If empty, uses session.
    	 * @param string $cartId (optional) The ID of the remote cart. If empty, uses session.
    	 * @return mixed Response or FALSE on missing HMAC/ID 
    	 */
    	function cartUpdate($cartItemId, $quantity, $HMAC = null, $cartId = null) {
    		if (!$HMAC) {
    		 	$HMAC = $this->Session->read($this->_sessionKey.'.HMAC');
    		}
    		if (!$cartId) {
    			$cartId = $this->Session->read($this->_sessionKey.'.cartId');
    		}
    		if (!$HMAC || !$cartId) {
    			return false;
    		}
    		
    		$params = am($this->defaultParams, array(
    			'Operation' => 'CartModify',
    			'CartId' => $cartId,
    			'HMAC' => $HMAC,
    			'Item.1.CartItemId' => $cartItemId,
    			'Item.1.Quantity' => $quantity
    		));
    		$response = $this->__query($params);
    		return $this->__formatCartItems($response['CartModifyResponse']['Cart']);
    	}
    	
    	/**
    	 * Deletes the CartItem from the remote cart.
    	 * 
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>	
    	 * @param string $cartItemId As the name says.. [CartItem][CartItemId]
    	 * @param string $HMAC (optional) HMAC which was returned with cartCreate. If empty, uses session.
    	 * @param string $cartId (optional) The ID of the remote cart. If empty, uses session.
    	 * @return mixed Response or FALSE on missing HMAC/ID 
    	 */
    	function cartRemove($cartItemId, $HMAC = null, $cartId = null) {
    		if (!$HMAC) {
    		 	$HMAC = $this->Session->read($this->_sessionKey.'.HMAC');
    		}
    		if (!$cartId) {
    			$cartId = $this->Session->read($this->_sessionKey.'.cartId');
    		}
    		if (!$HMAC || !$cartId) {
    			return false;
    		}
    		
    		$params = am($this->defaultParams, array(
    			'Operation' => 'CartModify',
    			'CartId' => $cartId,
    			'HMAC' => $HMAC,
    			'Item.1.CartItemId' => $cartItemId,
    			'Item.1.Quantity' => 0
    		));
    		$response = $this->__query($params);
    		return $this->__formatCartItems($response['CartModifyResponse']['Cart']);
    	}
    	
    	/**
    	 * Gets the current remote cart contents
    	 * 
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
    	 * @param string $HMAC (optional) HMAC which was returned with cartCreate. If empty, uses session.
    	 * @param string $cartId (optional) The ID of the remote cart. If empty, uses session.
    	 * @return mixed Response or FALSE on missing HMAC/ID 
    	 */
    	function cartGet($HMAC = null, $cartId = null) {
    		if (!$HMAC) {
    		 	$HMAC = $this->Session->read($this->_sessionKey.'.HMAC');
    		}
    		if (!$cartId) {
    			$cartId = $this->Session->read($this->_sessionKey.'.cartId');
    		}
    		if (!$HMAC || !$cartId) {
    			return false;
    		}		
    		
    		$params = am($this->defaultParams, array(
    			'Operation' => 'CartGet',
    			'CartId' => $cartId,
    			'HMAC' => $HMAC
    		));
    		$response = $this->__query($params);
    		return $this->__formatCartItems($response['CartGetResponse']['Cart']);
    	}
    	
    	/**
    	 * Check if an remote cart is available based on last/given response
    	 *
    	 * @access public
     	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
    	 * @param array $cart A cart response
    	 * @return boolean
    	 */	
    	function cartIsActive($cart = null) {
    		if (!$cart) {
    			$cart = $this->__lastCart;
    		}	
    		return ($cart && isset($cart['CartId']));
    	}
    	
    	/**
    	 * Check if Cart-Response has any Items
    	 *
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
    	 * @param array $cart A cart response
    	 * @return boolean
    	 */
    	function cartHasItems($cart = null) {
    		if (!$cart) {
    			$cart = $this->__lastCart;
    		}
    		return ($cart && isset($cart['CartItems']));
    	}
    	
    	/**
    	 * Remove Cart from Session.
    	 *
    	 * @access public
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
    	 * @return boolean
    	 */
    	function cartKill() {
    		return $this->Session->del($this->_sessionKey);
    	}
    	
    	/**
    	 * Makes sure that CartItem is always a single dim array.
    	 *
    	 * @access private
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
    	 * @param array $cart Cart Response
    	 * @return array Cart Response
    	 */
    	function __formatCartItems($cart) {
    		unset($cart['Request']);
    		if (isset($cart['CartItems'])) {
    			$_cartItem = $cart['CartItems']['CartItem'];
    			$items = array_keys($_cartItem);
    			if (!is_numeric(array_shift($items))) {
    				$cart['CartItems']['CartItem'] = array($_cartItem);
    			}
    		}
    		$this->__lastCart = $cart; // for easier working with helper methods
    		return $cart;
    	}
    
    	
    	/**
    	 * Does the acutal request using Http_Socket and Xml
    	 *
    	 * @access private
    	 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
    	 * @param array $params An parameter array
    	 * @return array Response Array
    	 */
    	function __query($params) {
    		if (!$this->_accessKey) {
    			trigger_error("AmazonComponent: Missing Amazon API Key - use 'setAccessKey();' in 'beforeFilter();'", E_USER_WARNING); 
    			exit;
    		}
    		$socket = new HttpSocket();
    		$header = aa('header', aa('User-Agent', 'CakePHP AmazonComponent v'.$this->version));
    		$response = $socket->get($this->servicePoint, $params, $header);	
    		return Set::reverse(new Xml($response));
    		
    	}
    	
    }
    ?>


I can't give you more examples because the API is huge. There are
several ResponseGroups and the response has many, many attributes
where most of them depend on the Groups chosen. So i leave it up to
you to dig through the bunch of arrays -- pr() will be your new friend
:)

However, this component is well documented and you should get your
desired result very quickly. Check out the following link for a list
of possible ResponseGroups:

`http://docs.amazonwebservices.com/AWSEcommerceService/2006-09-13/`_
PS: yes, this component was built 2006. I recently found it in my
project files and thought it's nice to make it public. This also means
that the API used is versioned: 2006-09-11. In order to use a newer
version just change date in the $defaultParams. At the moment i will
not promise that using a newer API version won't break the component.
See for yourself. I am to 80% sure that there won't be any problem.
But who knows.. :)

Enjoy!

.. _http://docs.amazonwebservices.com/AWSEcommerceService/2006-09-13/: http://docs.amazonwebservices.com/AWSEcommerceService/2006-09-13/

.. author:: m3nt0r
.. categories:: articles, components
.. tags:: xml,webservice,ecommerce,httpsocket,amazon,Components

