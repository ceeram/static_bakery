Wizard Component 1.2 Tutorial
=============================

by %s on September 10, 2010

Automates several aspects of multi-page forms including data
persistence, form preparation and unique data processing, wizard
resetting (manual and automatic), user navigation, and plot-branching
navigation while maintaining flexibility with custom validation and
completion callbacks. This is a tutorial for my Wizard Component 1.2
found here: http://bakery.cakephp.org/articles/view/wizard-
component-1-2-1.
[07/10/10] Update: The code and tutorial has been moved to github and
can be found at `http://github.com/jaredhoyt`_.

If you have ever tried creating a multi-page form, you have probably
realized that adding even a second page introduces several new
considerations, such as: data persistence, user navigation, page
refreshing and double submission, and cleanly handling each page's
data validation and error handling. The wizard component automates the
nitty gritty that comes with multi-page forms and lets you focus on
handling the user's data.


Simple Example
--------------
Lets create a simple example to show how rapidly a multi-page form can
be created with the WizardComponent.

For this example, I am going to be creating a 4 step signup wizard
that includes the following steps:


#. Account Info
#. Mailing Address
#. Billing Info
#. Review

There will also be a "confirmation" page at the end to confirm the
user's signup. This is a very simple example and a wizard like it
probably doesn't have a whole lot of real world usefulness, but I just
want to demonstrate how the component is used and highlight a couple
things as we go along.

It is important to note that though we will using multiple models, the
entire wizard will be contained in one controller. Also, I will be
using the words 'step' and 'page' interchangeably - I'm merely
referring to a page in the multi-page wizard.

So after downloading wizard.php into our project's component folder,
we include it in our controller's $components array just as we would
any other component:

Controller Class:
`````````````````

::

    <?php 
    class SignupController extends AppController {
    	var $components = array('Wizard');
    }
    ?>

Next, we're going to setup our $steps array, which is an ordered list
of steps for the wizard to follow. Each step will have its own view
and will be processed by its own controller callback method. There is
also another optional callback for each step that will be discussed
later.

The steps array is setup in your controller's beforeFilter():

::

    
    	function beforeFilter() {
    		$this->Wizard->steps = array('account', 'address', 'billing', 'review');
    	}

The next step is to create the views used in the signup wizard. The
names of the views correspond to steps names included in $steps
(account.ctp, address.ctp, etc). I'll include the first view
(account.ctp) just to highlight a couple things.

View Template:
``````````````

::

    
    <?=$form->create('Signup',array('id'=>'SignupForm','url'=>$this->here));?>
    	<h2>Step 1: Account Information</h2>
    	<ul>
    		<li><?=$form->input('Client.first_name', array('label'=>'First Name:','size'=>20,'div'=>false));?></li>
    		<li><?=$form->input('Client.last_name', array('label'=>'Last Name:','size'=>20,'div'=>false));?></li>
    		<li><?=$form->input('Client.phone', array('label'=>'Phone Number:','size'=>20,'div'=>false));?></li>
    	</ul>
    	<ul>
    		<li><?=$form->input('User.email', array('label'=>'Email:','size'=>20,'div'=>false));?></li>
    		<li><?=$form->input('User.password',array('label'=>'Password:','size'=>20,'div'=>false,));?></li>
    		<li><?=$form->input('User.confirm',array('label'=>'Confirm:','size'=>20,'div'=>false,'type'=>'password'));?></li>
    	</ul>
    	<div class="submit">
    		<?=$form->submit('Continue', array('div'=>false));?>
    		<?=$form->submit('Cancel', array('name'=>'Cancel','div'=>false));?>
    	</div>
    <?=$form->end();?>

The first thing I want to point out is the url that the form is
submitted to. Rather than submitting to the next step in the wizard,
each step submits to itself , just as a normal form would do. (My
favorite method is above : 'url'=>$this->here.) This is important
because one of my main goals in creating this component was to allow
the wizard to be easily setup and easily modified. This meant keeping
the views divorced, as much as possible, from their inclusion or
position in the steps array. To further this goal, I have created a
WizardHelper that will be published in the bakery soon. In the above
example, "Step 1" would be replaced with the $wizard->stepNumber()
method.

The second thing I wanted to highlight was the component's ability to
handle data for multiple models (the same as single page forms). This
is possible because every step has its own custom callback to process
its data.

Next we are going to setup our controller to handle each of the steps
in the form wizard.

Very important: Rather than creating a separate controller action for
each of the steps in the form, all the steps are tied together through
one action (the default is 'wizard'). This means, for our example, our
urls will look like `http://www.example.com/signup/wizard/account`_
etc. This way, everything is handle by the component and customization
is handled through controller callbacks.

Because of this, the wizard action itself can be very basic. It merely
needs to pass the step requested to the component's main method -
process():

Controller Class:
`````````````````

::

    <?php 
    class SignupController extends AppController {
    	var $components = array('Wizard');
    
    	function beforeFilter() {
    		$this->Wizard->steps = array('account', 'address', 'billing', 'review');
    	}
    
    	function wizard($step = null) {
    		$this->Wizard->process($step);
    	}
    }
    ?>

Something to consider if your wizard is the controller's main feature
(as it would be in our example), is to route the default action for
the controller to the wizard action. This would allow prettier links
such as `http://www.example.com/signup`_ to be handled by
SignupController::wizard(), which would then redirect to
/signup/wizard/account (or the first incomplete step in the wizard.)

::

    Router::connect('/signup', array('controller' => 'signup', 'action' => 'wizard'));

Next, we are going to create controller callbacks to handle each step.
Each step has two controller callbacks: prepare and process.

The prepare callback is optional and occurs before the step's view is
loaded. This is a good place to set any data or variables that you
want available for the view. The name of the callback is
prepareStepName. So for our example, our prepare callbacks would be
prepareAccount(), prepareAddress(), etc.

The process callback is required and occurs after data has been
posted. This is where data validation should be handled. The process
callback must return either true or false. If true, the wizard will
continue to the next step; if false, the user will remain on the step
and any validation errors will be presented. The name of the callback
is processStepName. So for our example, our process callbacks would be
processAccount(), processAddress(), etc. You do not have to worry
about retaining data as this is handled automatically by the
component. Data retrieval will be discussed later in the tutorial.

It's very important to note that every step in the wizard must contain
a form with a field. The only way for the wizard to continue to the
next step is for the process callback to return true. And the process
callback is only called if $this->data is not empty.

So lets create some basic process callbacks. Real world examples would
most likely be more complicated, but this should give you the basic
idea (don't forget to add any needed models):

Controller Class:
`````````````````

::

    <?php 
    class SignupController extends AppController {
    	var $uses = array('Client', 'User', 'Billing');
    	var $components = array('Wizard');
    
    	function beforeFilter() {
    		$this->Wizard->steps = array('account', 'address', 'billing', 'review');
    	}
    
    	function wizard($step = null) {
    		$this->Wizard->process($step);
    	}
    /**
     * [Wizard Process Callbacks]
     */
    	function processAccount() {
    		$this->Client->set($this->data);
    		$this->User->set($this->data);
    
    		if($this->Client->validates() && $this->User->validates()) {
    			return true;
    		}
    		return false;
    	}
    
    	function processAddress() {
    		$this->Client->set($this->data);
    
    		if($this->Client->validates()) {
    			return true;
    		}
    		return false;
    	}
    
    	function processBilling() {
    		$this->Billing->set($this->data);
    
    		if($this->Billing->validates()) {
    			return true;
    		}
    		return false;
    	}
    
    	function processReview() {
    		return true;
    	}
    }
    ?>


At this point in the tutorial, your wizard should have of four steps -
each consisting of a view and process callback (plus any optional
prepare callbacks). Also, the wizard should be automatically handling
data persistence and navigation between the steps. The next question
is how to retrieve the data stored by the component and what happens
at the completion of the wizard.


Data Retrieval
``````````````
Retrieving data from the component is possible at any point in the
wizard. While our example will not manipulate or store the data
permanently until the completion of the wizard, it's also reasonable
that some applications may need to store data before the end of the
wizard. For example, a job application may not be completed in one
session but rather over a period of time. The progress, then, would
need to be kept up with between sessions, rather than
manipulated/stored all at once during the wizard completion.

Wizard data is stored with the following path:
sessionKey.stepName.modelName.fieldName. The sessionKey will be
explained in the Wizard Completion section below. The component method
for retrieving data is read($key = null) which works pretty much like
SessionComponent::read() except that the sessionKey is handled
automatically by the WizardComponent and doesn't need to be passed
into read(). Passing null into read() returns all Wizard data.

So, for example, if we wanted to do something with the client's email
address (which was obtained in the account step) while processing the
review step, we would use the following code:

::

        function processReview() {
            $email = $this->Wizard->read('account.User.email');
            /* do something with the $email here */
    
            return true;
        }

An example showing how to retrieve all the current data with read()
will be given below.


Wizard Completion
`````````````````
One of my goals when writing this component was to prevent double
submission of user data. One of the ways I accomplished this was by
using the process callbacks for each step and redirecting to rather
than rendering the next step.

The second way was including an extra redirect and callback during the
wizard completion process that creates a sort of "no man's land" for
the wizard data. The way this works is, after the process callback for
the last step is completed, the wizard data is moved to a new location
in the session (Wizard.complete), the wizard redirects to a null step
and another callback is called - afterComplete().

afterComplete() is an optional callback and is the ideal place to
manipulate/store data after the wizard has been completed by the user.
The callback does not need to return anything and the component
automatically redirects to the $completeUrl (default '/') after the
callback is finished.

It's important to note that immediately after the afterComplete()
callback and before the user is redirected to $completeUrl, the wizard
is reset completely (all data is flushed from the session). If you
need to redirect manually from afterComplete(), be sure to call
Wizard->resetWizard() manually.

So, to complete our tutorial example, we will pull all the data out of
the wizard, store it in our database, and redirect the user to a
confirmation page.


Controller Class:
`````````````````

::

    <?php 
    class SignupController extends AppController {
    	var $uses = array('Client', 'User', 'Billing');
    	var $components = array('Wizard');
    
    	function beforeFilter() {
    		$this->Wizard->steps = array('account', 'address', 'billing', 'review');
    		$this->Wizard->completeUrl = '/signup/confirm';
    	}
    
    	function confirm() {
    	}
    
    	function wizard($step = null) {
    		$this->Wizard->process($step);
    	}
    /**
     * [Wizard Process Callbacks]
     */
    	function processAccount() {
    		$this->Client->set($this->data);
    		$this->User->set($this->data);
    
    		if($this->Client->validates() && $this->User->validates()) {
    			return true;
    		}
    		return false;
    	}
    
    	function processAddress() {
    		$this->Client->set($this->data);
    
    		if($this->Client->validates()) {
    			return true;
    		}
    		return false;
    	}
    
    	function processBilling() {
    		$this->Billing->set($this->data);
    
    		if($this->Billing->validates()) {
    			return true;
    		}
    		return false;
    	}
    
    	function processReview() {
    		return true;
    	}
    /**
     * [Wizard Completion Callback]
     */
    	function afterComplete() {
    		$wizardData = $this->Wizard->read();
    		extract($wizardData);
    
    		$this->Client->save($account['Client'], false, array('first_name', 'last_name', 'phone'));
    		$this->User->save($account['User'], false, array('email', 'password'));
    		
    		... etc ...
    	}
    }
    ?>

Please note the addition to beforeFilter() and the new confirm()
method. You would also need to create a view file (confirm.ctp) with
something like "Congrats, your sign-up was successful!" etc. It would
also be good to create some sort of token during the afterComplete()
callback and have it checked for in the confirm() method, but that's
outside the scope of this tutorial.

A new addition to the WizardComponent 1.2 is plot-branching navigation
(pbn). If you ever read a book as a child in which you interacted with
the plot - i.e. If the knight slays the dragon, turn to page 64, if
the knight runs for safety, turn to page 82. - then you've experienced
pbn. In some applications, the steps in a wizard may not be a simple
linear path, but might instead require the ability to "change course"
based on user input.

For example, a survey that has varying questions for men or women
might ask gender on the first page and would then need to navigate to
different pages depending on the answer. While this is a simple
example, some wizards can become very complicated when all the
different options occur at different points in the wizard and "paths"
begin to cross.

In some instances, it may not be a different path altogether, but
merely a step being skipped over. Integrating Paypal Pro, for
instance, requires the application allow the user to either enter
their billing information on the site, or hop over to Paypal, login to
their account and "skip" the billing page on the original site.


Advanced $steps Array
`````````````````````
When using pbn, the $steps array becomes a bit more complex. Instead
of adding/removing steps on the fly, all the steps are included into
the array like they normally would. Then, "branches" are selected or
skipped using the component methods. The trick to understanding the
WizardComponent's pbn implementation is understanding the $steps array
- the rest is pretty simple.

A simple $steps array is a single-tiered structure with each element
corresponding to a step in the wizard. The array is ordered and the
steps are handled sequentially.

An advanced $steps array setup for pbn is a multi-tiered structure
consisting of simple $steps arrays separated by branch arrays (or
branch groups). The branch arrays are associative arrays with branch
names as indexes and simple $steps arrays as elements.

For example, lets say we had six steps: step1, step2, gender, step3,
step4, and step5. The gender step would determine the user's gender
and the subsequent steps would vary accordingly. If male, step3 and
step4 would be used; if female, step4 and step5 would be used. So lets
setup our $steps array:

::

    function beforeFilter() {
        $this->Wizard->steps = array('step1', 'step2', 'gender', array('male' => array('step3', 'step4'), 'female' => array('step4', 'step5')));
    } 

It's important to understand that there is almost always more than one
way to accomplish the same effect with different $steps arrays. For
example, I could have instead, setup a 'male' branch that used step3,
included step4 for both, and then another branch for 'female' that
would include step5.

::

    function beforeFilter() {
        $this->Wizard->steps = array('step1', 'step2', 'gender', array('male' => array('step3')), 'step4', array('female' => array('step5')));
    } 

Also, although these examples are simple, I should point out that the
$steps array is not limited to a three-tiered array. As long as the
pattern is followed - array(stepName, array(branchName =>
array(stepName, etc...))) - the steps array can be as complex as
resources allow for.

After the the $steps array is setup, the question becomes, "How does
the component navigate through all the branches?" This is done be
selecting which branch will be used in a "branch group". By default,
the first branch in a group is always used (unless it has been
"skipped" - more on that later). You can turn this feature off by
setting Wizard->defaultBranch = false.

So, lets look at our two previous examples:

::

    Example 1:
        $this->Wizard->steps = array('step1', 'step2', 'gender', array('male' => array('step3', 'step4'), 'female' => array('step4', 'step5')));
    
    Example 2:
        $this->Wizard->steps = array('step1', 'step2', 'gender', array('male' => array('step3')), 'step4', array('female' => array('step5')));

In example 1, 'male' and 'female' are two branches in the same branch
group. Therefore, without any interference, the component would
automatically use the 'male' branch and 'female' would be skipped. The
steps would occur: step1, step2, gender, step3, step4. If
$defaultBranch = false, both would be skipped and the steps would
occur: step1, step2, gender.

In example 2, 'male' and 'female' are in separate branch groups.
Therefore, without any interference, both branches would be used since
they are the first branch in their respective groups. The steps would
occur: step1, step2, gender, step3, step4, step5. If $defaultBranch =
false, both would be skipped and the steps would occur: step1, step2,
gender, step4.


branch() and unbranch()
```````````````````````
In order to specify to the component which branches should be used,
you must use the branch() and unbranch() methods. The branch() method
includes a branch (specified by its name) in the session and
unbranch() removes a branch from the session. branch() also has an
extra parameter that allows branches to be easily skipped - more on
that below.

So lets assume "female" was selected on the gender step. During the
"processGender" callback, we could specify the "female" branch to be
included:

::

        function processGender() {
            $this->Client->set($this->data);
    
            if($this->Client->validates()) {
                if($this->data['Client']['gender'] == 'female') {
                     $this->Wizard->branch('female');
                } else {
                     $this->Wizard->branch('male');
                }
                return true;
            }
            return false;
        } 

In example 1, the 'female' branch would be used instead of the 'male'
branch and the steps would occur: step1, step2, gender, step4, step5.
However, in example 2, unless $defaultBranch = false, the 'male'
branch would also be used since it is not in the same branch group as
'female'.

Important: The first branch that has been included in the session will
be used. In other words, if you were to do branch('male') and
branch('female') for example 1, 'male' would be used since it occurs
before 'female'. If 'male' was branched previously and you later
wanted 'female' to be used, you would need to use unbranch('male').

In addition to including a branch to be used, branch() can also
specify branches to be "skipped" by setting the second parameter to
'true'. If, for example, we used Wizard->branch('male', true) in the
previous examples, 'male' would be skipped and 'female' would be used.
The steps would occur: step1, step2, gender, step4, step5 - the same
as using branch('female') with $defaultBranch = true!

The last thing I want to mention about pbn is that branch names do not
necessarily have to be unique. In fact, I'd imagine some complex pbn
wizards could be solved with some creative branch naming schemes in
which identical branch names would be used only one branch() would
have to be called to alter multiple branch groups. For example, using
branch('male') with the following $steps array would select the 'male'
branches in both the first and second branch groups.

::

    $steps = array('step1', array('male' => ..., 'female' => ...), 'step2', array('cyborg' => ..., 'male' => ..., 'alien' => ...)); 

Also, (the other last thing I want to mention), the $steps array that
each branch name points to can be treated exactly the same as the main
$steps array - i.e. branch groups can be nested and branches are
selected with branch() and $defaultBranch.
`1`_|`2`_|`3`_|`4`_|`5`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_
+ `Page 4`_
+ `Page 5`_

.. _http://github.com/jaredhoyt: http://github.com/jaredhoyt
.. _Page 4: :///articles/view/4caea0e2-1704-4831-926c-43a882f0cb67/lang:eng#page-4
.. _Page 5: :///articles/view/4caea0e2-1704-4831-926c-43a882f0cb67/lang:eng#page-5
.. _http://www.example.com/signup/wizard/account: http://www.example.com/signup/wizard/account
.. _Page 1: :///articles/view/4caea0e2-1704-4831-926c-43a882f0cb67/lang:eng#page-1
.. _Page 2: :///articles/view/4caea0e2-1704-4831-926c-43a882f0cb67/lang:eng#page-2
.. _Page 3: :///articles/view/4caea0e2-1704-4831-926c-43a882f0cb67/lang:eng#page-3
.. _http://www.example.com/signup: http://www.example.com/signup
.. meta::
    :title: Wizard Component 1.2 Tutorial
    :description: CakePHP Article related to forms,wizard component,Wizard,multistep,multipage,Tutorials
    :keywords: forms,wizard component,Wizard,multistep,multipage,Tutorials
    :copyright: Copyright 2010 
    :category: tutorials

