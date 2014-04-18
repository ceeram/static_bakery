Integrate CakePHP with Kcaptcha
===============================

Simple way to integrate CakePHP with Kcaptcha.
KCAPTCHA is a free and open source PHP solution to generate human
validation images (CAPTCHA).

KCAPTCHA is meant to be a very strong protected one but requires no
special hosting featires, only PHP with GD library.

You can download Kcaptcha from here:
`http://captcha.ru/en/kcaptcha/`_. After this, put kcaptcha folder
into vendors directory.

Create Captcha component:

Component Class:
````````````````

::

    <?php 
    class CaptchaComponent extends Object
    {
        function startup(&$controller)
        {
            $this->controller = $controller;
        }
    
        function render()
        {
            vendor('kcaptcha/kcaptcha');
            $kcaptcha = new KCAPTCHA();
            $this->controller->Session->write('captcha', $kcaptcha->getKeyString());
        }
    }
    ?>


Use this component in UsersController:

Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController
    {
        ...
        var $components = array('Session', 'Captcha');
        ...
        function captcha()
        {
            $this->Captcha->render();
        }
        ...
    }
    ?>

Create image tag in view:

View Template:
``````````````

::

    
        <img src="<?php echo $html->url('/users/captcha'); ?>" />

That's all :)

.. _http://captcha.ru/en/kcaptcha/: http://captcha.ru/en/kcaptcha/

.. author:: stephanoff
.. categories:: articles, components
.. tags:: component,captcha,Components

