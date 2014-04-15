ACL in CakePHP 1.2
==================

by AcidMaX on July 04, 2007

The ACL/Auth components have left many people screaming for help. Here
are some simple basic examples on how to get auth working from
beginning to end. This is not the "definitive" guide to ACL but simply
a primer to get you started with ACL and Auth in CakePHP 1.2.
Getting Started - Database Setup
In order to setup the ACL databases we are going to use the cake shell
scripts throughout this tutorial. I will not be showing you how to use
the cake shell script itself, that is for another tutorial.

Setting up our database we will need to run the following commands.

First - We need to initialize our acl database.

::

    
    cake acl initdb

Second - We need to add a root to the ACO table. This root can be
named anything but we will name this "Controllers"

::

    
    cake acl create aco 0 Controller

Third - We need to add all the controllers we have in our app to the
ACO table.

::

    
    cake acl create aco Controllers Users
    cake acl create aco Controllers Tags
    cake acl create aco Controllers Posts
    cake acl create aco Controllers Comments

Now we need to create the ARO's or what I will call "Roles" for
clarity. We will first create the root ARO and name it Roles. Then we
create two roles named Admin and Member.

::

    
    cake acl create aro 0 Roles
    cake acl create aro Roles Admin
    cake acl create aro Roles Member

Right about now I am sure you are saying. OMG hurry up already I don't
have all day! However, there is still a bit more to do in order to
have ACL setup properly in your application. The next thing we need to
do is create the access entries for the roles for each controller.
There are a few shortcuts we can take for this. Below are some
samples:

Lets say we want to give the admin global access to all controllers.
Instead of assigning the admin role to each controller we created, we
can grant admin rights to the root controller, in this case
"Controllers" with the following command:

::

    
    cake acl grant /Roles/Admin Controllers '*'

This basically says we want to give the Admin role, Create, Read,
Update, Delete access to all controllers in the system (since we
provided Controllers as the aco).

Lets say we wanted to give the Member role access to Created, Read,
Update, Delete comments. We would simply perform the following
command:

::

    
    cake acl grant /Roles/Member  Comments '*'



.. author:: AcidMaX
.. categories:: articles, tutorials
.. tags:: ,Tutorials

