reCaptcha Plugin - 0.8.0
========================

by %s on January 05, 2010

reCaptcha is a plugin to allow the easy use of reCaptcha.net's captcha
library.


Requirements
~~~~~~~~~~~~

+ CakePHP >= 1.2
+ PHP >= 5.2
+ PECL json >= 1.2.0 (to use reCaptcha configuration options)
+ Keys from `http://recaptcha.net`_



Usage
~~~~~

Controller Class:
`````````````````

::

    <?php 
    var $components = array(array('Recaptcha.Captcha' => array(
    				'private_key' => PRIVATE_KEY_FROM_RECAPTCHA_DOT_NET, 
    				'public_key' => PUBLIC_KEY_FROM_RECAPTCHA_DOT_NET)));
    var $helpers = array('Recaptcha.CaptchaTool');
    
    ...
    
    function save() {
        if ($this->Captcha->validate()) {
            // Do something that requires validation
        }
    }
    ?>



View Template:
``````````````

::

    
    <?php echo $captchaTool->show(); ?>



Download
~~~~~~~~
`http://www.holostek.net/system/files/recaptcha-0.8.0.zip`_

.. _http://www.holostek.net/system/files/recaptcha-0.8.0.zip: http://www.holostek.net/system/files/recaptcha-0.8.0.zip
.. _http://recaptcha.net: http://recaptcha.net/
.. meta::
    :title: reCaptcha Plugin - 0.8.0
    :description: CakePHP Article related to captcha,plugin,recaptcha,Plugins
    :keywords: captcha,plugin,recaptcha,Plugins
    :copyright: Copyright 2010 
    :category: plugins

