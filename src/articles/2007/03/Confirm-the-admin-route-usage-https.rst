Confirm the admin route usage https
===================================

by %s on March 13, 2007

While entering the admin route, needing to be confirmed must use https
to carry on an on-line, in order not to the user neglect, and use the
http on-line, so we have to make the check conversion automatically in
the cakephp.
In app_controller.php

::

    
    function beforeFilter() {
        if(!isset($this->params[CAKE_ADMIN]) && empty(env('HTTPS'))){
            $this->redirect('/'.CAKE_ADMIN);
            exit();
        }
    }


.. meta::
    :title: Confirm the admin route usage https
    :description: CakePHP Article related to admin,https,ssl,Snippets
    :keywords: admin,https,ssl,Snippets
    :copyright: Copyright 2007 
    :category: snippets

