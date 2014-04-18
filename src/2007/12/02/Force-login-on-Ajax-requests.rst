Force login on Ajax requests
============================

Users often encounter situations when they want to perform an action
on a web site and get redirected to login page. Applicationâ€™s task
is to take care of performing the requested action right after
userâ€™s successful log in. It is easy job, unless the action is
requested via ajax.
Below I present a simple solution to this problem. Let me stress the
word ’simple’, because I’m not 100% satisfied with this. It works
fine, however, and I didn’t have time to think about something
prettier.

Here’s users_controller.php file which defines login and ajax_login
actions. The former one is supposed to take care of logging the user
in the system, the latter only redirects to login page via ajax
response.

Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController {
        var $name = 'Users';      
    
        function login() {
            //… check login credentials
            // let's assume they were ok, then perform the action requested by the user before logging in
            if ($this->Session->check('loginRedirectUrl')) {
                if ($this->Session->check('loginRedirectParams')) {
                    $params = $this->Session->read('loginRedirectParams');
                    $params[] = 'return';
                    $this->Session->del('loginRedirectParams');
                    $this->requestAction($params['url']['url'], $params);
                }
                $url = $this->Session->read('loginRedirectUrl');
                $this->Session->del('loginRedirectUrl');
                $this->redirect($url);
            }
        }      
    
        function ajax_login() {
            $this->render('ajax_login', 'ajax');
        }      
    
        /**
         * action that is accessed through ajax request
         */
        function ajax_action() {
            $this->checkSession();
            // do something creative
        }
    }
    ?>

Now create view file: ajax_login.ctp. It will contain JavaScript code
that will make the browser load login page. That is something I don’t
feel fully comfortable with, because you must handle ajax response in
your view files to place this code in page body from where the browser
can read it and execute. Since most ajax requests handles their
responses, this will work fine, but probably there are some that
don’t. You should try something else on such occasions.


View Template:
``````````````

::

    
    <script type="text/javascript">
    window.location = '<?php echo 'http://'.$_SERVER['HTTP_HOST'].$html->url('users/login').'"'; ?>';
    </script>;

The last piece of equation is app_controller.php file where we place a
method that checks wheter the user is logged in and redirects him to
login page if he's not. Call this method at the beginning of every
action/method that is restricted to registered users only.


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
        var $components = array('Session', 'RequestHandler');      
    
        function checkSession() {
            if (!$this->Session->check('User')) { // when the user is not logged in
                if ($this->RequestHandler->isAjax()) {
                    $this->Session->write('loginRedirectUrl', $this->referer());
                    $this->Session->write('loginRedirectParams', $this->params);
                    $this->requestAction('/users/ajax_login');
                } else {
                    $this->Session->write('loginRedirectUrl', '/'.$this->params['url']['url']);
                    $this->redirect('/users/login');
                }
            }
        }
    }
    ?>

That's it. It works both for ajax and usual requests, CakePHP 1.1 and
1.2. I just can't figure out how to avoid this JS trick, if you have
any ideas please let me know.


.. author:: michal
.. categories:: articles, snippets
.. tags:: redirect,login,session,controller,Snippets

