

Simple Math Question Captcha Component
======================================

by %s on January 20, 2010

I donâ€™t like CAPTCHAs. I donâ€™t know anyone who does.The downfalls
of CAPTCHAs are many - hard to read, annoying, impossible for those
with vision difficulties - and the benefits are slim. So, I came up
with an alternative: a plain text math question to ask the user. This
component generates a random equation and registers the answer as a
session variable. The programmer can then check the form submitterâ€™s
answer against the session-registered answer using the validation
function provided in the component.
UPDATE January 19/2010 - the latest version of this component is now
on Github: `http://github.com/jamienay/math_captcha_component`_

Usage is really simple - Iâ€™ll run through putting it into an equally
simple contact form, which looks a lot like the one done by Jonathan
Snook (`http://snook.ca/archives/cakephp/contact_form_cakephp/`_). The
Contact model looks like this ( app/models/contact.php ):


Model Class:
````````````

::

    <?php 
    class Contact extends AppModel {
        var $name = 'Contact';
        var $useTable = false;
    
        var $_schema = array(
            'name'		=>array('type' => 'string', 'length' => 100),
            'email'		=>array('type' => 'string', 'length' => 255),
            'comments'	=>array('type' => 'text')
        );
    
        var $validate = array(
            'name' => array(
                'rule'=>array('minLength', 1),
                'message'=>'Please enter a name so the Geek know what to call you!' ),
            'email' => array(
                'rule'=>'email',
                'message'=>'Please enter an email address so the Geek knows how to reach you.' ),
            'details' => array(
                'rule'=>array('minLength', 1),
                'message'=> 'Don\'t forget to enter some comments.' )
        );
    }
    ?>

No DB; manual schema; just a placeholder, really.

The Contact controller is set up like this (
app/controllers/contact_controller.php ):


Controller Class:
`````````````````

::

    <?php 
    class ContactController extends AppController {
        var $name = 'Contact';
        var $uses = 'Contact';
        var $components = array('RequestHandler', 'Email', 'Session', 'MathCaptcha');
    
        function index() {
            if ($this->RequestHandler->isPost()) {
                $this->Contact->set($this->data);
                if ($this->MathCaptcha->validates($this->data['Contact']['security_code'])) {
                    if ($this->Contact->validates()) {
                        $this->Email->to = Configure::read('SiteSettings.email_form_address');
                        $this->Email->subject = 'Contact from message from ' . $this->data['Contact']['name'];
                        $this->Email->from = $this->data['Contact']['email']; 
    
                        $this->Email->send($this->data['Contact']['comments']);
                    }
                } else {
                    $this->Session->setFlash(__('Please enter the correct answer to the math question.', true));
                }
            } 
    
            $this->set('mathCaptcha', $this->MathCaptcha->generateEquation());
        }
    }
    ?>

So, weâ€™ve added MathCaptcha to our list of components. There are
various configuration options which you can set when adding
MathCaptcha to the $components array - the config array (with
defaults) looks like this:

::

    
    private $__defaults = array(
            'operand' => '+',
            'minNumber' => 1,
            'maxNumber' => 5,
            'numberOfVariables' => 2
        );

In the index() method, you can see the usage: if weâ€™ve got a POST
request, we call the componentâ€™s validates() method and pass to it
the relevant data from the form - the userâ€™s answer to the question.
If it validates then we continue with the rest of the data validation,
otherwise we give an error message. Youâ€™ll notice that the
generateEquation() method is called regardless; we want a new question
generated each time the page loads.

Finally, we just need one line in the view to grab the
â€™security_codeâ€™. Hereâ€™s the entire contact form (
app/views/contact/index.ctp ):


View Template:
``````````````

::

    
    <?php
    echo $form->create('Contact', array('url' => $this->here));
    echo $form->input('name');
    echo $form->input('email');
    echo $form->input('comments');
    echo $form->input('security_code', array('label' => 'Please Enter the Sum of ' . $mathCaptcha));
    echo $form->end(array('name' => 'Send', 'class' => 'input_btn'));
    ?>

Iâ€™ve called the form field â€™security_codeâ€™, but you can call it
whatever you want.

And thatâ€™s it! A plain text math â€˜captchaâ€™ in almost no time.

Any changes or improvements to this component will be recorded on my
website: `http://jamienay.com/code/math-captcha-component/`_


Component Class:
````````````````

::

    <?php 
    /**
     * Math Captcha Component class.
     *
     * Generates a simple, plain text math equation as an alternative to image-based CAPTCHAs.
     *
     * @filesource
     * @author			Jamie Nay
     * @copyright       Jamie Nay
     * @license	    http://www.opensource.org/licenses/mit-license.php The MIT License
     * @link            http://jamienay.com/code/math-captcha-component
     */
    class MathCaptchaComponent extends Object {
    
        /**
         * Other components needed by this component
         *
         * @access public
         * @var array
         */
        public $components = array('Session');
    
        /**
    	 * component settings
    	 *
    	 * @access public
    	 * @var array
    	 */
    	public $settings = array();
    
        /**
    	 * Default values for settings.
    	 * - operand: the operand used in the math equation
    	 * - minNumber: the minimum number used to generate the random variables.
    	 * - maxNumber: the corresponding maximum number.
    	 * - numberOfVariables: the number of variables to include in the equation.
    	 *
    	 * @access private
    	 * @var array
    	 */
        private $__defaults = array(
            'operand' => '+',
            'minNumber' => 1,
            'maxNumber' => 5,
            'numberOfVariables' => 2
        );
    
        /**
         * The variables used in the equation.
         *
         * @access public
         * @var array
         */
        public $variables = array();
    
        /*
         * The math equation.
         *
         * @access public
         * @var string
         */
        public $equation = null;
    
        /**
         * Configuration method.
         *
         * @access public
         * @param object $model
         * @param array $settings
         */
        public function initialize(&$controller, $settings = array()) {
            $this->settings = array_merge($this->__defaults, $settings);
        }
    
        /*
         * Method that generates a math equation based on the component settings. It also calls
         * a secondary function, registerAnswer(), which determines the answer to the equation
         * and sets it as a session variable.
         *
         * @access public
         * @return string
         *
         */
        public function generateEquation() {
            // Loop through our range of variables and set a random number for each one.
            foreach (range(1, $this->settings['numberOfVariables']) as $variable) {
                $this->variables[] = rand($this->settings['minNumber'], $this->settings['maxNumber']);
            }
    
           // debug($this->settings); debug($this->variables);
            $this->equation = implode(' ' . $this->settings['operand'] . ' ', $this->variables);
            //debug($this->equation);
            // This function determines the answer to the equation and stores it as a session variable.
            $this->registerAnswer();
    
            return $this->equation;
        }
    
        /*
         * Determines the answer to the math question from the variables set in generateEquation()
         * and registers it as a session variable.
         *
         * @access public
         * @return integer
         */
        public function registerAnswer() {
            // The eval() function gives us the $answer variable.
            eval("\$answer = ".$this->equation.";");
    
            $this->Session->write('MathCaptcha.answer', $answer);
    
            return $answer;
        }
    
        /*
         * Compares the given data to the registered equation answer.
         *
         * @access public
         * @return boolean
         */
        public function validates($data) {
            return $data == $this->Session->read('MathCaptcha.answer');
        }
    
    }
    ?>

`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _http://jamienay.com/code/math-captcha-component/: http://jamienay.com/code/math-captcha-component/
.. _http://github.com/jamienay/math_captcha_component: http://github.com/jamienay/math_captcha_component
.. _Page 1: :///articles/view/4caea0e5-fdf8-47ee-8624-4e1182f0cb67/lang:eng#page-1
.. _http://snook.ca/archives/cakephp/contact_form_cakephp/: http://snook.ca/archives/cakephp/contact_form_cakephp/
.. _Page 2: :///articles/view/4caea0e5-fdf8-47ee-8624-4e1182f0cb67/lang:eng#page-2
.. meta::
    :title: Simple Math Question Captcha Component
    :description: CakePHP Article related to forms,captcha,spam,spam protection,contact form,Tutorials
    :keywords: forms,captcha,spam,spam protection,contact form,Tutorials
    :copyright: Copyright 2010 
    :category: tutorials

