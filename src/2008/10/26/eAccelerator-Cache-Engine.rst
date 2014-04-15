eAccelerator Cache Engine
=========================

by stevebest on October 26, 2008

eAccelerator ([url]http://eaccelerator.net/[/url]) is a free open-
source PHP accelerator, optimizer, and dynamic content cache. It also
provides a cache capabilities through the use of shared memory (if
configured and installed properly). Some tests show the andvantage of
this caching engine over APC, XCache and Memcached. If your PHP
installation includes eAccelerator configured using '--with-
eaccelerator-shared-memory', you might want to try this engine.
This class makes use of eAccelerator API and provides a CakePHP cache
engine called 'Eaccelerator'.

::

    
    <?php
    
    /**
     * eAccelerator cache engine for CakePHP
     * 
     * eAccelerator is a free open-source PHP accelerator, optimizer, and dynamic content cache.
     * http://eaccelerator.net/
     * 
     * To make EacceleratorEngine avalable, eAccelerator must be configured using
     * $ ./configure --with-eaccelerator-shared-memory
     */
    class EacceleratorEngine extends CacheEngine {
    
    	/**
    	 * Initialize the cache engine
    	 *
    	 * Called automatically by the cache frontend
    	 *
    	 * @param array $params Associative array of parameters for the engine
    	 * @return boolean true if the engine has been succesfully initialized, false if not
    	 * @access public
    	 */
    	function init($settings = array()) {
    		if (!function_exists('eaccelerator_put')) {
    			return false;
    		}
    		return parent::init($settings);
    	}
    
    	/**
    	 * Garbage collection
    	 *
    	 * Permanently remove all expired and deleted data
    	 *
    	 * @access public
    	 */
    	function gc() {
    		eaccelerator_gc();
    	}
    	
    	/**
    	 * Write value for a key into cache
    	 *
    	 * @param string $key Identifier for the data
    	 * @param mixed $value Data to be cached
    	 * @param mixed $duration How long to cache the data, in seconds
    	 * @return boolean true if the data was succesfully cached, false on failure
    	 * @access public
    	 */
    	function write($key, &$value, $duration) {
    		$data = serialize($value);
    		return eaccelerator_put($key, $data, $duration);
    	}
    	
    	/**
    	 * Read a key from the cache
    	 *
    	 * @param string $key Identifier for the data
    	 * @return mixed the cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
    	 * @access public
    	 */
    	function read($key) {
    		$data = eaccelerator_get($key);
    		if (!$data) {
    			return false;
    		}
    		return unserialize($data);
    	}
    	
    	/**
    	 * Delete a key from the cache
    	 *
    	 * @param string $key Identifier for the data
    	 * @return boolean true if the value was succesfully deleted, false if it didn't exist or couldn't be removed
    	 * @access public
    	 */
    	function delete($key) {
    		return eaccelerator_rm($key);
    	}
    
    	/**
    	 * Delete all keys from the cache
    	 *
    	 * @param boolean $check if true will check expiration, otherwise delete all
    	 * @return boolean true if the cache was succesfully cleared, false otherwise
    	 * @access public
    	 */
    	function clear($check) {
    		if ($check) {
    			return $this->gc();
    		}
    		
    		$result = true;
    		$keys = eaccelerator_list_keys();
    		foreach ($keys as $key) {
    			$key = substr($key['name'], 1);
    			$result = $this->delete($key) && $result;
    		}
    		return $result;
    	}
    
    }
    
    ?>

Save this code as cake/libs/cache/eaccelerator.php and use it in your
app/config/core.php as follows:

::

    
    <?php
    	Cache::config('default', array('engine' => 'Eaccelerator'));
    ?>

This makes use of the eAccelerator as the caching engine for your app.


.. author:: stevebest
.. categories:: articles, snippets
.. tags:: eaccelerator,Snippets

