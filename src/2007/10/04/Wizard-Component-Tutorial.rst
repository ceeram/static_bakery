Wizard Component Tutorial
=========================

A tutorial on using my Wizard Component which automates several
aspects of multi-page forms including data persistence, form
preparation, wizard resetting (manual and automatic), and wizard
navigation (including jumping between steps) while maintaining
flexibility with custom validation and completion callbacks.
This is a tutorial for my Wizard Component found here:
`http://bakery.cakephp.org/articles/view/wizard-component`_

The problem
~~~~~~~~~~~
Adding even a second page to a form introduces several new
considerations, such as: persisting data throughout the steps,
preventing users from skipping steps through forged forms or directly
requested urls, being able to handle/validate each page's data set
differently, page refreshing and double submission of data, and even
simple navigation from one page to the next (or previous).


The solution
~~~~~~~~~~~~
The benefits and features of the Wizard Component:


#. Allows easy step definition and order with $step array.
#. Persists data automatically in session. Array is stored in session
   under customizable $sessionKey.
#. Uses redirects rather than renders for navigation (unless data
   validation fails) thus allowing user browser-refreshing without
   reposting data.
#. Requires minimal code in your controller: process() and
   resetWizard() are the only public functions.
#. Automatically resets the wizard if a step request is invalid.
   (optional; default = true)
#. Allows each step to be prepared with its own optional custom
   controller callback.
#. Allows each step to be validated differently with its own custom
   controller callback.
#. Also allows optional controller callbacks for after the wizard
   completion (afterComplete()) and before the receipt is rendered
   (beforeReceipt()).
#. Automatically renders a receipt page after the completion of the
   wizard. (defined by $receiptAction)
#. Automatically resets form data after wizard completion to prevent
   user from going back and resubmitting data.


What it doesn't do:
```````````````````

#. Generate html for your views.
#. Automatically validate your data - controller validation callbacks
   must be present for each step.
#. Easily allow you to "lock down" the form. (ie. no resetting, no
   navigation controls for the user.) (yet - hopefully)
#. Easily allow "plot-branching" navigation. (ie. first step choose
   male/female, varying step afterwards) (also yet)
#. Provide security (yet) from people directly accessing the receipt
   page. I should have this fixed very soon.



How it Works
~~~~~~~~~~~~
The first thing I had to decide when creating the component was the
method of passing the step variable between the view, controller, and
component. I decided against hidden form variables and numerical steps
with an auto-incrementing counter. I decided to just post the current
step as a string in the url.

The wizard has a single point of entry into your controller the
controller action specified in WizardComponent::wizardAction() -
(default is 'wizard'). So your wizard urls would by default be
'/controller/wizard/step' (thanks Adam Johnson). See the optional
stuff to see how to make your actions '/controller/step'.

The step is then passed from the controller's method to the
WizardComponent::process() method. Process() determines: what step has
been called, if the step if valid, if the data is valid, and where to
redirect/render.

There are two steps that are unique: 'reset' and $receiptAction. Reset
resets the wizard by deleting the session and starting the wizard
over. $receiptAction is the view called after the wizard is complete
(defaults to 'receipt'). All other steps are validated.

Validation has two parts: 1. Validate that the step is contained in
the $steps array. 2. Validate that the step is before or exactly the
"expected step" (the step after the last step stored in the session).
This prevents users from skipping steps directly through the url or a
forged form. If a step fails validation, then the wizard is reset (if
autoReset=true) and is redirected back to the beginning. (If
autoReset=false, wizard is redirected to the "expected step".)

If data is present, then it is validated. Otherwise, the step is
rendered (with data loaded from session if present). Data is validated
through a custom controller callback named after the step:
processStepname(). (ie. $step = 'contact', callback =
processContact()). The callback must return a boolean of whether the
data is valid. If false, the step is rendered with the errors. If
true, the data is stored in the session with an array key of
$sessionKey (defaults to Controllername.Wizard).

The last step is to redirect/render the step. If the "Previous" button
was pushed, data validation is skipped and the user is redirected to
the previous step. If any other button was pushed, the wizard is
redirected to the next step. If the current step is the last step in
the $steps array, then optional afterComplete() controller callback is
called and the user is redirected to the $receiptAction.

.. _http://bakery.cakephp.org/articles/view/wizard-component: http://bakery.cakephp.org/articles/view/wizard-component

.. author:: jaredhoyt
.. categories:: articles, tutorials
.. tags:: tutorial,component,Wizard,Tutorials

