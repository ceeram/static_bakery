Cryptable Behavior
==================

by %s on August 01, 2009

A behavior that will automatically encrypt/decrypt specified fields in
a model, using your choice of cipher, key, and IV.
I've written the following behavior for a project I recently completed
in Cake, and I thought it would be worth sharing:

::

    
    <?php
    class CryptableBehavior extends ModelBehavior {
    	var $settings = array();
    
    	function setup(&$model, $settings) {
    		if (!isset($this->settings[$model->alias])) {
    			$this->settings[$model->alias] = array(
    				'fields' => array()
    			);
    		}
    
    		$this->settings[$model->alias] = array_merge($this->settings[$model->alias], $settings);
    	}
    
    	function beforeFind(&$model, $queryData) {
    		foreach ($this->settings[$model->alias]['fields'] AS $field) {
    			if (isset($queryData['conditions'][$model->alias.'.'.$field])) {
    				$queryData['conditions'][$model->alias.'.'.$field] = $this->encrypt($queryData['conditions'][$model->alias.'.'.$field]);
    			}
    		}
    		return $queryData;
    	}
    
    	function afterFind(&$model, $results, $primary) {
    		foreach ($this->settings[$model->alias]['fields'] AS $field) {
    			if ($primary) {
    				foreach ($results AS $key => $value) {
    					if (isset($value[$model->alias][$field])) {
    						$results[$key][$model->alias][$field] = $this->decrypt($value[$model->alias][$field]);
    					}
    				}
    			} else {
    				if (isset($results[$field])) {
    					$results[$field] = $this->decrypt($results[$field]);
    				}
    			}
    		}
    
    		return $results;
    	}
    
    	function beforeSave(&$model) {
    		foreach ($this->settings[$model->alias]['fields'] AS $field) {
    			if (isset($model->data[$model->alias][$field])) {
    				$model->data[$model->alias]['cleartext_'.$field] = $model->data[$model->alias][$field];
    				$model->data[$model->alias][$field] = $this->encrypt($model->data[$model->alias][$field]);
    			}
    		}
    		return true;
    	}
    
    	public function encrypt($data) {
    		if ($data !== '') {
    			return base64_encode(mcrypt_encrypt(Configure::read('Cryptable.cipher'), Configure::read('Cryptable.key'), $data, 'cbc', Configure::read('Cryptable.iv')));
    		} else {
    			return '';
    		}
    	}
    
    	public function decrypt($data, $data2 = null) {
    		if (is_object($data)) {
    			unset($data);
    			$data = $data2;
    		}
    
    		if ($data != '') {
    			return trim(mcrypt_decrypt(Configure::read('Cryptable.cipher'), Configure::read('Cryptable.key'), base64_decode($data), 'cbc', Configure::read('Cryptable.iv')));
    		} else {
    			return '';
    		}
    	}
    }

All you need to do is add three lines to your bootstrap, and then load
the behavior in any model you want to use it.

Here are the lines for your bootstrap:

::

    
    <?php
    Configure::write('Cryptable.cipher', 'rijndael-192');
    Configure::write('Cryptable.key','random key string here');
    Configure::write('Cryptable.iv', base64_decode('base64 encoded IV here')); // Create with mcrypt_create_iv with the appropriate size for your cipher

Here's an example of how to load it in your model:

::

    
    <?php
    	var $actsAs = array(
    		'Cryptable' => array(
    			'fields' => array(
    				'password'
    			)
    		)
    	);

If you need to encrypt or decrypt a field outside of the normal find
methods, you can simply call those methods on the model, passing in
the string that needs worked on.

.. meta::
    :title: Cryptable Behavior
    :description: CakePHP Article related to behavior,encryption,Behaviors
    :keywords: behavior,encryption,Behaviors
    :copyright: Copyright 2009 
    :category: behaviors

