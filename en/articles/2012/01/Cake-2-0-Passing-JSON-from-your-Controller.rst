

Cake 2.0: Passing JSON from your Controller
===========================================

by %s on January 17, 2013

With CakePHP 2.0, it's easy to pass JSON encoded data back from you
Controller without having to invoke your View.

Say you have a Controller "Users" that has an "Index" Action that
looks like:

<?php class UsersController extends AppController {

::

    public $paginate = array(
        'limit' => 25,
        'order' => array(
            'User.name' => 'asc'
        )
    );
    
    public function index() {
        $users = $this->paginate('User');
        $this->set('users', $users);
    }

}

The "index" method will return an array of users (name $users) to your
view. But say you want to return JSON whenever this action was called
via an Ajax call. The response would be the rendered HTML page.

Alter your "index" method to so that it looks like the following:

public function index() { $users = $this->paginate('User'); if
($this->request->is('ajax')) return new CakeResponse(array('body' =>
json_encode($this->paginate()))); $this->set('users', $users); }

Now your index method returns JSON when the request is Ajax, and a
normal view otherwise. So how


.. meta::
    :title: Cake 2.0: Passing JSON from your Controller
    :description: CakePHP Article related to AJAX,json,cakephp 2.0,cakeresponse,Articles
    :keywords: AJAX,json,cakephp 2.0,cakeresponse,Articles
    :copyright: Copyright 2012 
    :category: articles

