Where should my code go?
========================

by Auzigog on December 29, 2008

This article helps you understand where you should place new code
within the model-view-controller design pattern.
When I started developing my first CakePHP application, I was
flustered every time I want to add new code. I didn't know where it
"belonged" within the existing framework and conventions. As I found
answers to my questions, I started this list.

Before reading this, make sure you `understand the model-view-
controller`_ design pattern.

The "take away" message for each part is highlighted in bold .

Models
~~~~~~

+ Models are designed to hold and manipulate data
+ Models essentially reflect a database table
+ Models (usually) never represent a row in a table, just the table
  itself
+ Models have functions that deal with manipulating data from the
  table
+ Models should be large and should contain the majority of the logic
  for your application-- (remember: fat models, skinny controllers)
+ Anytime you are doing the same thing to a model in multiple places,
  you should refactor that functionality into a method within the model
  (instead of having it in multiple places outside the model).
+ `Cookbook: Understanding Models`_



Behaviors
`````````

+ Behaviors allow you to add the same functionality to multiple models
+ Examples: `tree`_, `soft deletable`_, `increment`_.
+ ` Cookbook: Behaviors`_



Controllers
~~~~~~~~~~~

+ Controllers are designed to separate your site into sections
+ Examples: BlogController, SearchController, GroupsController,
  UserProfileController
+ Contrary to what the `blog tutorial`_ makes you think, there is not
  a single controller for every model (or a single model for every
  controller). It is fairly common that a controller will hold all the
  tasks of a single model, but it is NOT necessary for things to be that
  way.
+ On the same token, avoid mixing controller uses. While there may not
  be a controller for every model, don't put actions that deal with
  model B into model A just to avoid having to create a controller
+ Controllers should be small and NOT contain the majority of the
  logic for your application-- (remember: fat models, skinny
  controllers)
+ The controller isn't there do do everything. It is there to
  determine what needs to be done, and have other people do it
+ Controller actions get data ready to be presented in a view
+ `Cookbook: Introduction to Controllers`_



Components
``````````

+ Components have logic that is shared across multiple controllers
+ Remember, logic should only be in multiple controllers in the first
  place if it is getting data ready for the view. If the logic is just
  manipulating your data, it should be in a model.
+ `Cookbook: Introduction to Components`_



Views
~~~~~

+ Views are individual pages within your site
+ Views are the user interface to your program
+ Each view has a function inside the controller with a matching name.
  These functions are called actions. Each action gets the data ready
  for a specific view.
+ Views present data
+ Views allow you to separate your user interface from the rest of
  your application
+ `Cookbook: Introduction to Views`_



Helpers
```````

+ Just like the name says, there are helper functions that make
  rending things in views more convenient.
+ `Cookbook: Intoduction to Helpers`_



Elements
````````

+ [li] Sections of code that will be used in multiple views
+ [li]`Cookbook: Elements`_



.. _Cookbook: Intoduction to Helpers: http://book.cakephp.org/complete/98/Helpers
.. _soft deletable: http://bakery.cakephp.org/articles/view/soft-delete-behavior
.. _understand the model-view-controller: http://book.cakephp.org/complete/10/Understanding-Model-View-Controller
.. _tree: http://book.cakephp.org/complete/91/Tree
.. _ Cookbook: Behaviors: http://book.cakephp.org/complete/88/Behaviors
.. _increment: http://bakery.cakephp.org/articles/view/increment-behavior
.. _Cookbook: Introduction to Views: http://book.cakephp.org/view/95/View-Templates
.. _blog tutorial: http://book.cakephp.org/view/219/Blog
.. _Cookbook: Understanding Models: http://book.cakephp.org/view/67/Understanding-Models
.. _Cookbook: Introduction to Controllers: http://book.cakephp.org/view/50/Introduction
.. _Cookbook: Introduction to Components: http://book.cakephp.org/view/63/Introduction
.. _Cookbook: Elements: http://book.cakephp.org/view/97/Elements
.. meta::
    :title: Where should my code go?
    :description: CakePHP Article related to guide,mvc,reference,coding,conventions,General Interest
    :keywords: guide,mvc,reference,coding,conventions,General Interest
    :copyright: Copyright 2008 Auzigog
    :category: general_interest

