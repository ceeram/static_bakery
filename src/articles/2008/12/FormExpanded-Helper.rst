FormExpanded Helper
===================

by Warringer on December 29, 2008

I found myself in the need to have multiple buttons in the same form a
few times. Rather than have to add a div and than add the number of
buttons I need, I opted to expand the Form Helper a little.
It's a rather simple Expansion really...

First the Helper form_expanded.php:


Helper Class:
`````````````

::

    <?php 
    class FormExpandedHelper extends FormHelper {
    	
    	var $name = "FormExpanded";
    	
    	function multipleButton($buttons = null) {
    		$return = "";
    		if ($buttons) {
    			$buttons_content = "";
    			foreach ($buttons as $button) {
    				if (!$button['options']) { $button['options'] = array(); } 
    				$buttons_content .= $this->button($button['title'], $button['options']);
    			}
    			$return = $this->Html->div("submit", $buttons_content);
    		}
    		return $return;
    	}
    }
    ?>

Now for the use.

First of all adding it to the Controller of choice, in my case
app_controller.php.


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
    	
    	var $helpers = array('Html', 'Form', 'FormExpanded');
    
    }
    ?>

Next comes how to use it. In my case a Login form login.ctp:


View Template:
``````````````

::

    
    <div class="login form">
    <?php e($form->create('User', array('action' => 'login'))); ?>
    <fieldset>
     		<legend><?php __('Login');?></legend>
    <?php e($form->input('name'));
    	e($form->input('password'));
    	e($form->input('remember_me', array('label' => 'Remember me', 'type' => 'checkbox'))); 
    	$buttons = array(
    		array(
    			'title' => 'Login',
    			'options' => array('type' => 'submit')
    		),
    		array(
    			'title' => 'Register',
    			'options' => array('onClick' => "location.href='register'")
    		)
    	);
    	e($formExpanded->multipleButton($buttons));
    	?>
    </fieldset>
    <?php e($form->end()); ?>
    </div>

Of course its possible to use more than two buttons with this.

.. meta::
    :title: FormExpanded Helper
    :description: CakePHP Article related to ,Helpers
    :keywords: ,Helpers
    :copyright: Copyright 2008 Warringer
    :category: helpers

