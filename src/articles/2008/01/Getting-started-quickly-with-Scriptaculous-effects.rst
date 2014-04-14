Getting started quickly with Scriptaculous effects
==================================================

by janb on January 03, 2008

CakePHP's AJAX helper offers you direct access to the Sciptaculous
library. If you have little AJAX experience, the usage of this helper
might seem overwhelming. It is not, the largest part of the job is
creating good views.


A real world example: combined index/view page
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

I assume that you know all the basics of CakePHP and that you have
setup a model, view and controller using the Bake command-line
application (version 1.2.x). Note, if you use version 1.1.x, you have
to add manually the AJAX helper to the controller. I have used a
simple customer data example for this tutorial.

The index view contains a table with all data from the database. The
last column contains text links which enable you to load a detailed
view. In this tutorial we're going to incorporate this detailed view
into the index view, using effects from the Scriptaculous library.

Preparing the views
~~~~~~~~~~~~~~~~~~~

The main idea is that we change the view.ctp file, so that the
detailed view will be rendered in an element. This element can then be
used in the index view.

View.ctp
````````

This view is only used if you do a direct request. The AJAX-request
only used the element customerdata .

View Template:
``````````````

::

    <div class="customer">
    <h2><?php  __('Customer');?></h2>
    <?php echo $this->renderElement('customerdata');?>
    </div>
    <div class="actions">
    	<ul>
    		<li><?php echo $html->link(sprintf(__('Edit %s', true), __('Customer', true)), array('action'=>'edit', $customer['Customer']['id'])); ?> </li>
    		<li><?php echo $html->link(sprintf(__('Delete %s', true), __('Customer', true)), array('action'=>'delete', $customer['Customer']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $customer['Customer']['id'])); ?> </li>
    		<li><?php echo $html->link(sprintf(__('List %s', true), __('Customers', true)), array('action'=>'index')); ?> </li>
    		<li><?php echo $html->link(sprintf(__('New %s', true), __('Customer', true)), array('action'=>'add')); ?> </li>
    	</ul>

The actual data is rendered from the file customerdata.ctp which
resides in the subdirectory elements :

View Template:
``````````````

::

    <dl>
    	<dt class="altrow"><?php __('Id') ?></dt>
    	<dd class="altrow">
    		<?php echo $customer['Customer']['id'] ?>
    		 
    	</dd>
    	<dt><?php __('Firstname') ?></dt>
    	<dd>
    		<?php echo $customer['Customer']['firstname'] ?>
    		 
    	</dd>
    	<dt class="altrow"><?php __('Lastname') ?></dt>
    	<dd class="altrow">
    		<?php echo $customer['Customer']['lastname'] ?>
    		 
    	</dd>
    	<dt><?php __('Address') ?></dt>
    	<dd>
    		<?php echo $customer['Customer']['address'] ?>
    		 
    	</dd>
    	<dt class="altrow"><?php __('City') ?></dt>
    	<dd class="altrow">
    		<?php echo $customer['Customer']['city'] ?>
    		 
    	</dd>
    	<dt><?php __('Email') ?></dt>
    	<dd>
    		<?php echo $customer['Customer']['email'] ?>
    		 
    	</dd>
    </dl>


index.ctp
`````````

The index view should have at least one div-tag where the data can be
loaded (ID: CustomerData). We add a div-tag (ID: LoadingDiv) for a
dynamic loading image as well (try Google Image to find such an
image). The div-tag with ID CustomerPaging is created by the Bake
command-line application.

View Template:
``````````````

::

    <h2><?php __('Customers');?></h2>
    
    <div id="LoadingDiv" style="display: none;">
    	<?php echo $html->image('ajax-loader.gif'); ?>
    </div>
    
    <div id="CustomerPaging">
    	<?php echo $this->renderElement('paging'); ?>
    </div>
    
    <div id="CustomerData">
    </div>


Adding Scriptaculous effects
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The Scriptaculous effects are added to the paging.ctp element. This
element was pre-baked with the command-line interface. We only add an
AJAX link in the last column of the table.

View Template:
``````````````

::

    <?php echo $ajax->link(__('Ajax View', true), array('action'=>'view', $customer['Customer']['id']), array('update' => 'CustomerData', 'loading' => 'Element.show(\'LoadingDiv\'); Effect.BlindDown(\'CustomerData\')', 'complete' => 'Element.hide(\'LoadingDiv\')', 'before' => 'Element.hide(\'CustomerData\')'));?>

The link helper takes 3 parameters:

+ the link text is 'Ajax View'
+ the request to be made is the action view from the current
  controller, with the parameter id
+ the div-tag that should be updated is listed in the option array, as
  well as the Scriptaculous effects for loading, complete and before.


Explanation of the effects
``````````````````````````

The before effect is used to clear the existing data in the div-tag
(actually, we make it invisible). This helps the end-user to notice
that the data has changed upon the new request. The loading effect is
used to show the LoadingDiv (which shows an animation) and to show the
CustomerData div-tag. Finally the complete effect is used to hide the
LoadingDiv (otherwise the loading animation would continue forever).

The link-method from the AJAX helper might seem complicated, but it is
not. You can just copy-paste it and adapt the camelcased variables to
match your div-tags. Changing effects should not be difficult as well.

References
~~~~~~~~~~

+ This article is based on a blog entry from ReverseFolds:
  `http://www.reversefolds.com/articles/show/ajax`_
+ You can download Scriptaculous from:
  `http://script.aculo.us/downloads`_
+ The manual (with more effects) can be found at:
  `http://wiki.script.aculo.us/scriptaculous/`_
+ The manual page on helpers has more information on the options of
  the link-method from the AJAX helper:
  `http://manual.cakephp.org/chapter/helpers`_.



.. _http://manual.cakephp.org/chapter/helpers: http://manual.cakephp.org/chapter/helpers
.. _http://script.aculo.us/downloads: http://script.aculo.us/downloads
.. _http://wiki.script.aculo.us/scriptaculous/: http://wiki.script.aculo.us/scriptaculous/
.. _http://www.reversefolds.com/articles/show/ajax: http://www.reversefolds.com/articles/show/ajax
.. meta::
    :title: Getting started quickly with Scriptaculous effects
    :description: CakePHP Article related to scriptaculous,Tutorials
    :keywords: scriptaculous,Tutorials
    :copyright: Copyright 2008 janb
    :category: tutorials

