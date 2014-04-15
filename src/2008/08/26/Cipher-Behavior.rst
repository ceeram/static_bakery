Cipher Behavior
===============

by xemle on August 26, 2008

The Cipher Behavior encrypts/decrypts model properties to protect
passwords, emails, etc via symmetric encryption BlowFish. This
behavior is very useful if your data should not be stored in clear
text in the database (e.g. external database connections).
If your want to cipher critical data of your model like passwords or
emails, this cipher behavior handles the symmetric encryption and
decryption on the fly. For the symmetric encryption the cipher
BlowFish is required, but other symmetric encryption algorithms might
be used. This behavior is very useful if your data should not be
stored in clear text in the database (e.g. external database
connections).

The behavior could be configured simple for your needs. By default the
behavior ciphers the password Model property. The code is from the
behavior of the multi-user image gallery phtagr.org.

This behavior is similar to the crypter-component
`http://bakery.cakephp.org/articles/view/crypter-component`_ but
implements an automatic encryption to the model.


The Behavior
~~~~~~~~~~~~

First of all the PEAR package of BlowFish must be installed. This
packages comes with an PHP implementation of BlowFish and does not
required the MCrypt PHP extension. If the MCrypt extension is
installed, it will use it.

::

    pear install Crypt_Blowfish

Download the behavior to your /models/behaviors directory.


Behavior Class:
```````````````

::

    <?php 
    require_once("Crypt/Blowfish.php");
    
    class CipherBehavior extends ModelBehavior 
    {
      /** Default values of behavior.
        @key Symmetric key. Default is value of 'Security.salt' configuration.
        @cipher Columns to cipher. Default is 'password'.
        @prefix Prefix of ciphered values. Default is '$E$'.
        @saltLen Length of salt as prefix and suffix. The salt ensures different
        outputs for the same input. Default is 4. 
        @padding Padding of ciphered value. Default is 4.
        @autoDecrypt Decrypt ciphered value automatically. Default is false. */
      var $default = array(
                        'cipher' => 'password', 
                        'prefix' => '$E$', 
                        'saltLen' => 4, 
                        'padding' => 4, 
                        'autoDecrypt' => false,
                        'noEncrypt' => false
                      );
      var $config = array();
    
      function setup(&$model, $config = array()) {
        $this->config[$model->name] = $this->default;
    
        if (isset($config['key']))
          $this->config[$model->name]['key'] = $config['key'];
        else
          $this->config[$model->name]['key'] = Configure::read('Security.salt');
    
        if (isset($config['cipher']))
          $this->config[$model->name]['cipher'] = $config['cipher'];
    
        if (isset($config['prefix']))
          $this->config[$model->name]['prefix'] = $config['prefix'];
    
        if (isset($config['saltLen']))
          $this->config[$model->name]['saltLen'] = $config['saltLen'];
    
        if (isset($config['padding']) && $config['padding'] <= 32)
          $this->config[$model->name]['padding'] = $config['padding'];
    
        if (isset($config['autoDecrypt']))
          $this->config[$model->name]['autoDecrypt'] = $config['autoDecrypt'];
    
        if (isset($config['noEncrypt']))
          $this->config[$model->name]['noEncrypt'] = $config['noEncrypt'];
      }
    
      /** Model hook to encrypt model data 
        @param model Current model */
      function beforeSave(&$model) {
        if (isset($this->config[$model->name]) && !$this->config[$model->name]['noEncrypt']) {
          if (!is_array($this->config[$model->name]['cipher'])) {
            $cipher = array($this->config[$model->name]['cipher']);
          } else {
            $cipher = $this->config[$model->name]['cipher'];
          }
    
          $prefix = $this->config[$model->name]['prefix'];
          $prefixLen = strlen($prefix);
    
          foreach ($cipher as $column) {
            if (!empty($model->data[$model->name][$column]) && 
              substr($model->data[$model->name][$column], 0, $prefixLen) != $prefix) {
              $encrypt = $this->_encryptValue($model->data[$model->name][$column], $this->config[$model->name]);
              if ($encrypt) {
                $model->data[$model->name][$column] = $encrypt;
              } else {
                $this->log(__METHOD__." Could not encrypt {$model->name}::$column: '$model->data[$model->name][$column]'");
              }
            }
          }
        }
      
        return true;
      }
    
      /** Model hook to decrypt model data if auto decipher is turned on in the
        * model behavior configuration. Only primary model data are decrypted. */
      function afterFind(&$model, $result, $primary = false) {
        if (!$result || !isset($this->config[$model->name]['cipher']))
          return $result;
        
        if ($primary && $this->config[$model->name]['autoDecrypt']) {
          // check for single of multiple model
          $keys = array_keys($result);
          if (!is_numeric($keys[0])) {
            $this->decrypt(&$model, &$result);
          } else {
            foreach($keys as $index) {
              $this->decrypt(&$model, &$result[$index]);
            }
          }
        }
        return $result;
      }
    
      /** Decrypt model value
        @param model Current model
        @param data Current model data. If null, the Model::data is used 
        @return Deciphered model data */
      function decrypt(&$model, &$data = null) {
        if ($data === null)
          $data =& $model->data;
        if (isset($this->config[$model->name])) {
          if (!is_array($this->config[$model->name]['cipher'])) {
            $cipher = array($this->config[$model->name]['cipher']);
          } else {
            $cipher = $this->config[$model->name]['cipher'];
          }
    
          $prefix = $this->config[$model->name]['prefix'];
          $prefixLen = strlen($prefix);
          foreach ($cipher as $column) {
            if (!empty($data[$model->name][$column]) && 
              substr($data[$model->name][$column], 0, $prefixLen) == $prefix) {
              $decrypt = $this->_decryptValue($data[$model->name][$column], $this->config[$model->name]);
              if ($decrypt) {
                $data[$model->name][$column] = $decrypt;
              } else {
                $this->log(__METHOD__." Could not decrpyt {$model->name}::$column: '{$data[$model->name][$column]}'");
              }
            }
          }
        }
        return $data;
      }
    
      /** Create salt for cipher's envelope. The salt is an random string which
       * depends on the random generator, the value, the key and on the previous
       * generated character.
        @param value Value to cipher
        @param key Key for encrpytion.
        @param len Length of resulting salt. Default is 4
        @return Randomly generated salt of the given lenth */
      function _generateSalt($value, $key = '9nHPrYcxmvTliA', $len = 4) {
        srand(getMicrotime()*1000);
        $salt = '';
        $lenKey = strlen($key);
        $lenValue = strlen($value);
        $old = rand(0, 255);
        for($i = 0; $i < $len; $i++) {
          $n = ord($key[$i % $lenKey]);
          for ($j = 0; $j < $n; $j++) {
            $toss = rand(0, 255);
          }
          $toss ^= $n;
          $toss ^= ord($value[$i % $lenValue]);
          $toss ^= $old;
          $salt .= chr($toss);
          $old = $toss;
        }
        return $salt;
      }
    
      /** Packs a value with a surrounding salt value. Additionaly the resulting
       * envelope could be aligned
        @param value Value to envelope
        @param salt Salt which builds the prefix and suffix of the envelope
        @param padding Alignment size. Default is 4
        @return Envelope with salt 
        @see _unpackValue() */
      function _packValue($value, $salt, $padding) {
        $l = strlen($value) + 2 * strlen($salt);
        $lp = $l % $padding;
        $pad = '';
        if ($lp) {
          $pad = str_repeat(chr(0), $lp-1).chr($lp);
        }
        return $salt.$value.$pad.$salt;
      }
    
      /** Unpacks an envelope and returns the packed value
        @param envelope
        @return Value or false on an error 
        @see _packValue() */
      function _unpackValue($envelope, $saltLen) {
        $l = strlen($envelope);
        if ($l < 2*$saltLen) {
          $this->log(__METHOD__." Value for unpacking is to short");
          return false;
        }
        $salt = substr($envelope, 0, $saltLen);
        if ($salt != substr($envelope, $l - $saltLen, $saltLen)) {
          $this->log(__METHOD__." Enclosed salt missmatch: '$salt' != '".substr($envelope, $l - $saltLen, $saltLen)."' $l");
          return false;
        }
        $pad = ord(substr($envelope, $l - $saltLen -1, 1));
        if ($pad > 32) 
          $pad = 0;
        $value = substr($envelope, $saltLen, $l - (2 * $saltLen) - $pad);
        return $value;
      }
    
      /** Encrpytes a value using the blowfish cipher. As key the Security.salt
        * value is used 
        @param value Value to cipher
        @return Return of the chiphered value in base64 encoding. To distinguish
        ciphed value, the ciphed value has a prefix of '$E$' i
        @see _decryptValue(), _packValue(), _generateSalt() */  
      function _encryptValue($value, $config) {
        extract($config);
        $bf = new Crypt_Blowfish($key);
    
        $enclose = $this->_packValue($value, $this->_generateSalt($value, $key, $saltLen), $padding);
        $encrypted = $bf->encrypt($enclose);
        if (PEAR::isError($encrypted)) {
          $this->log($encrypted->getMessage());
          return false;
        }
        return $prefix.base64_encode($encrypted);
      }
    
      /** Decrpyted the given base64 string using the blowfish cipher
        @param base64Value Base 64 encoded string.
        @see _encryptValue(), _unpackValue() */
      function _decryptValue($base64Value, $config) {
        extract($config);
        $prefixLen = strlen($prefix);
        if (substr($base64Value, 0, $prefixLen) != $prefix) {
          $this->log(__METHOD__." Security prefix is missing: '$base64Value'");
          return false;
        }
        $encrypted  = base64_decode(substr($base64Value, $prefixLen));
        if ($encrypted === false) {
          $this->log(__METHOD__." Could not decode base64 value '$base64Value'");
          return false;
        }
        $bf = new Crypt_Blowfish($key);
    
        $envelope = trim($bf->decrypt($encrypted), chr(0));
        $value = $this->_unpackValue($envelope, $saltLen);
        if ($value === false) {
          $this->log(__METHOD__." Could not unpack value from '$envelope'");
          return false;
        }
    
        if (PEAR::isError($value)) {
          $this->log($value->getMessage());
          return false;
        }
        return $value;
      }
    
    }
    ?>



Usage
~~~~~

As mentioned above, the behavior ciphers the password property/table
column by default.


Model Class:
````````````

::

    <?php 
    class User extends AppModel
    {
      var $name = 'User';
    
      var $actsAs = array('Cipher' => array());
    
    }
    ?>

Following example saves the User model. Submit your login data via a
formular. The $this->data might look like:

::

    Array
    (
        [User] => Array
            (
                [id] => 1
                [username] => admin
                [password] => MySecret
            )
    )

In the controller you save your submitted data:


Controller Class:
`````````````````

::

    <?php 
    $this->User->save($this->data);
    ?>

Now every time a User is saved, the password will be ciphered. The
behavior only ciphers the properties, if the values do not start with
the ciphered prefix $E$ .

The ciphered data looks now like:

::

    Array
    (
        [User] => Array
            (
                [id] => 1
                [username] => admin
                [password] => $E$fIOGYbF6jQMXOOa5umzgXGWBfo7roAuk
            )
    )

By default the behavior does not decrypt the properties and the
decryption must be called explicitly:


Controller Class:
`````````````````

::

    <?php 
    $user = $this->User->findByUsername($this->data['User']['username']);
    $this->User->decrypt(&$user);
    if ($user['User']['password'] == $this->data['User']['password']) {
      // successful login
    }
    ?>



Configuration
~~~~~~~~~~~~~


Automatic Decryption
````````````````````

If you want to decrpyt all data automatically (might cost some CPU
cycles and slows down your requests), you can configure the cipher
behavior:


Model Class:
````````````

::

    <?php 
    class User extends AppModel
    {
      var $name = 'User';
    
      var $actsAs = array('Cipher' => array('autoDecypt' => true));
    }
    ?>



Model Properties
````````````````

By default, the cipher behavior encrypts and decrypts the model
property (table column) password. Other fields are also possible.


Model Class:
````````````

::

    <?php 
    class User extends AppModel
    {
      var $name = 'User';
    
      var $actsAs = array('Cipher' => array('cipher' => array('password', 'email', 'creditnumber')));
    }
    ?>



Custom Key
``````````

By default, the cipher behavior uses the Security.salt as cipher key.
If you require a custom key, you can set in on the configuration:


Model Class:
````````````

::

    <?php 
    class User extends AppModel
    {
      var $name = 'User';
    
      var $actsAs = array('Cipher' => array('key' => 'MySuperSecureCipherKey'));
    }
    ?>

Note: Since the Security.salt is used from your configuration
config/core.php and cipher key, it is very important to change the
default value of Security.salt! Otherwise the encryption is not
secure!

::

     /**
      * A random string used in security hashing methods.
      */
          Configure::write('Security.salt', 'NewSecureAndUnknownSecuritySaltForCake');



Salt and Padding
````````````````

Before a value is encrypted it will be packed and padded. The clear
text before the value is ciphered is surrounded by a salt and padded
to a specific length block to $salt.$value.$padding.$salt.

The salt is used to avoid same encrypted results of same values. It is
also used to discover the correct decryption. The padding is used to
hide the original value lengths. By default, the salt and padding have
the length of 4. This could be changed in the behavior configuration.

Note: The salt should be at least 2 characters long. Otherwise the
successful decryption could not be detected well (apart of the
diversity of the ciphered value).


Model Class:
````````````

::

    <?php 
    class User extends AppModel
    {
      var $name = 'User';
    
      var $actsAs = array('Cipher' => array('saltLen' => 6, 'padding' => 8));
    }
    ?>



Prefix
``````

To distinguish between ciphered value and a clear text value, the
ciphered value has a prefix. The default prefix is $E$ but could be
change in the configuration.


Model Class:
````````````

::

    <?php 
    class User extends AppModel
    {
      var $name = 'User';
    
      var $actsAs = array('Cipher' => array('prefix' => '$ciphered$'));
    }
    ?>



Debug
`````

The behavior dumps log message to the standard log if something goes
wrong. Please watch these entries while developing with the cipher
behavior.


Changing Security.salt
``````````````````````

If you using this behavior and some data is already ciphered but have
to change the Security.salt, you need to decrypt all the data with the
old Security.salt, save the clear text and encrypt all values with the
new Security.salt.

Decrypt all values with the old Security.salt value:


Model Class:
````````````

::

    <?php 
    class User extends AppModel
    {
      var $name = 'User';
    
      var $actsAs = array('Cipher' => array('noEncrypt' => true, 'autoDecrypt' => true));
    
      function clearCipher() {
        $users = $this->findAll();
        foreach ($users as $user) {
          $this->id = $user['User']['id'];
          $this->save($user);
        }
      }
    }
    ?>



Controller Class:
`````````````````

::

    <?php 
    $this->User->clearCipher();
    ?>

Encrypt now all values with the new Security.salt.


Model Class:
````````````

::

    <?php 
    class User extends AppModel
    {
      var $name = 'User';
    
      var $actsAs = array('Cipher' => array());
    
      function cipherAll() {
        $users = $this->findAll();
        foreach ($users as $user) {
          $this->id = $user['User']['id'];
          $this->save($user);
        }
      }
    }
    ?>



Controller Class:
`````````````````

::

    <?php 
    $this->User->cipherAll();
    ?>



.. _http://bakery.cakephp.org/articles/view/crypter-component: http://bakery.cakephp.org/articles/view/crypter-component

.. author:: xemle
.. categories:: articles, behaviors
.. tags:: database,pear,behavior,cryptography,decryption,encryption,bl
owfish,cipher,Behaviors

