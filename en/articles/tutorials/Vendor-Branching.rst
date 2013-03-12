

Vendor Branching
================

by %s on June 11, 2008

Vendor Branching provides a robust method of ensuring your application
repository is always in a stable state with regards to third party
libraries, even if these libraries have unique modifications. When
your application keeps a local copy of all libraries - with the
correct versions, in its own repository, you can confidently implement
any revision to any system as well as expecting stable maintenance and
support.
Each of your projects should be under version control, in their own
repository. This repository should store the entire state of the
application - it should not rely on third party libraries that are not
part of the same repository. When you update your application to
revision n, everything that is needed for that revision to execute
correctly needs to be included.

The prime example of this is the CakePHP core, but also applies to any
other third party libraries that your application relies on (PEAR,
TinyMCE, Gallery2, PHPThumb, Swiftmailer, etc). Priorities of using
these third party repositories in your application are:


#. Your application's repository should store the exact version that
   your application was developed, and tested with.
#. The third party repository will periodically be updated
   (adds/deletes/changes), and you will want to merge these updates into
   your application when you are ready.
#. You may need custom modifications to these libraries, but still
   retain the ability to merge in additional core library changes.

The method to achieve this is called Vendor Branching. There are
numerous tutorials out there on this, such as SvnBook (
`http://svnbook.red-bean.com/en/1.1/ch07s05.html`_ ), and Felix has a
great screencast ( `http://www.debuggable.com/posts/screencast-using-
vendor-branching-and-cakephp:480f4dd6-6cac-44cb-b685-4d6bcbdd56cb`_ ).
This tutorial be a simple step by step guide for implementing Vendor
Branching in your application.



Overview
~~~~~~~~
At its heart, Vendor Branching is performing a merge. A merge between
the old and new revisions of the third party library, against your own
application. To do this, all three targets must be part of the same
repository (your application). This is a limitation of Subversion -
you cannot merge between repositories.

As such, this technique boils down to:

#. Keep a clean, unmodified version of the third party library
   somewhere in your repository.
#. Replace this clean version with the updated third party library,
   whenever you want to update.
#. Merge the changes between these clean versions, into your own
   application.

As your repository will identify all of the changes between 1 and 2
(including adds, deletes, and changes) as a diff, these changes can be
safely merged in 3, regardless of whether your application has unique
changes to these files of its own.



Tutorial: Vendor Branch your CakePHP Core.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Step 1
``````
You should already have a repository for your application set up, with
the following folder structure

c:\YourProject\branches

c:\YourProject\tag

c:\YourProject\trunk

c:\YourProject\trunk\app

c:\YourProject\trunk\cake

c:\YourProject\trunk\docs

c:\YourProject\trunk\vendors
We'll assume you have created this application with the Cake 1.2 Beta
(1.2.0.6311).



Step 2
``````
As the CakePHP used by your application is "dirty" (you will certainly
have changed files in c:\YourProject\trunk\app\config , and possibly
changed files in c:\YourProject\trunk\cake), we need to get a clean
version.

Create the following folders:

c:\YourProject\vendors

c:\YourProject\vendors\cakephp

c:\YourProject\vendors\cakephp\current


Step 3
``````
Put a clean version of the Cake 1.2 Beta (1.2.0.6311) into the
c:\YourProject\vendors\cakephp\current folder - this can either by
from the zip file from the CakePHP site, or you can use a "svn
export". Do not use "svn checkout", as this would make a working
folder from the remote repository.



TortoiseSVN:
;;;;;;;;;;;;
Export from `https://svn.cakephp.org/repo/branches/1.2.x.x`_ ,
revision 6311, into c:\YourProject\vendors\cakephp\current


CLI:
;;;;
svn export -r 6311 --force
`https://svn.cakephp.org/repo/branches/1.2.x.x`_
c:\YourProject\vendors\cakephp\current

You will now have the following folders, with a clean Cake core

c:\YourProject\vendors\cakephp\current\app

c:\YourProject\vendors\cakephp\current\cake

c:\YourProject\vendors\cakephp\current\vendors

c:\YourProject\vendors\cakephp\current\docs


Step 4
``````
Commit the c:\YourProject\vendors to your application repository. Be
sure to add a note with the clean CakePHP revision, otherwise it can
be difficult to remember what the current revision is down the track.



TortoiseSVN:
;;;;;;;;;;;;
Commit the c:\YourProject\vendors, tick the "Show unversioned files"
and "Select all" checkboxes. Use the message "Added clean CakePHP 1.2
r6311"


CLI:
;;;;
svn add c:\YourProject\vendors
svn commit -m "Added clean CakePHP 1.2 r6311" c:\YourProject\vendors
Now your application repository is in a good development state - you
can work with your application however you need.



Step 5 Updating to a newer library.
```````````````````````````````````
Once a newer library is available you may want to merge it into your
application. To do this you need to replace the clean "current"
version in your repository with the newer library. When replacing you
need to retain all of the existing ".svn" folders in the "current"
folder, so that your repository stays sane.

Remove all of the existing files from the "current" folder.


Windows GUI:
;;;;;;;;;;;;
Search for *.* in c:\YourProject\vendors\cakephp\current, then select
all of the files (not folders) and delete


Windows CLI:
;;;;;;;;;;;;
del /s /q /a:-H-R c:\YourProject\vendors\cakephp\current\*.*


MacOSX Finder:
;;;;;;;;;;;;;;
Open the "current" folder.
Hit Cmd+F to initiate a Search.
Make sure the "current" folder is selected in the "Search:" bar.
Hold the Option key and click on the '+' to add a condition (adds a
condition group).
Select "[None] of the following are true" for the group condition.
Select "[Kind] is [Folders]" for the condition.

This will find all files - but should ignore the hidden ".svn" files.
So select all of these, and delete.

Export a clean version of the latest Cake 1.2 into this same folder


TortoiseSVN:
;;;;;;;;;;;;
Export the HEAD revision of
`https://svn.cakephp.org/repo/branches/1.2.x.x`_ to
c:\YourProject\vendors\cakephp\current


CLI:
;;;;
svn export --force `https://svn.cakephp.org/repo/branches/1.2.x.x`_
c:\YourProject\vendors\cakephp\current

Make sure to make a note of the revision you checked out - at the time
of writing this was r6788

Commit this updated vendor to your repository (including all
adds/deletes/changes), with a note indicating the revision


TortoiseSVN:
;;;;;;;;;;;;
Commit the c:\YourProject\vendors\cakephp\current, tick the "Show
unversioned files" and "Select all" checkboxes. Use the message
"Updated CakePHP vendor to 1.2 r6788"


Windows CLI:
;;;;;;;;;;;;
#add the new files
svn add --force c:\YourProject\vendors\cakephp\current

#remove all missing files - by (tediously) manually running
svn delete FILENAMEHERE
#for each file that appears in
svn status c:\YourProject\vendors\cakephp\current
#with a ! (indicating the file has been removed from the working
copy).

#commit the lot
svn commit -m "Updated CakePHP vendor to 1.2 r6788"
c:\YourProject\vendors\cakephp\current


Linux CLI:
;;;;;;;;;;
#add the new files
svn add --force /YourProject/vendors/cakephp/current
#remove all missing files
svn status | grep '\!' | awk '{print $2;}' | xargs svn rm
#commit the lot
svn commit -m "Updated CakePHP vendor to 1.2 r6788"
/YourProject/vendors/cakephp/current


Step 6
``````
Now that your repository has both clean versions of the library, you
can merge these into your application.
First you need to know which revisions of your own repository your are
merging between. Check the log of your
c:\YourProject\vendors\cakephp\current folder to see what revisions
are between the two vendor versions.



TortoiseSVN:
;;;;;;;;;;;;
Log on c:\YourProject\vendors\cakephp\current, make note of the
revisions of the two vendor versions


CLI:
;;;;
svn log c:\YourProject\vendors\cakephp\current
#---------------------------------------------------------------------
---
#r50 | username | 2008-05-10 16:34:10 +1000 (Sat, 10 May 2008) | 1
line
#
#Updated CakePHP vendor to 1.2 r6788
#---------------------------------------------------------------------
---
#r49 | username | 2008-05-10 15:10:13 +1000 (Sat, 10 May 2008) | 1
line
#
#Added clean CakePHP 1.2 r6311
#---------------------------------------------------------------------
---
As for this tutorial there was no application activity in between, the
two revisions to merge between are r49 and r50

Now that we have the repository revisions, merge your main project
folder (trunk) against these clean revisions


TortoiseSVN:
;;;;;;;;;;;;
Merge on c:\YourProject\trunk, against
svn://YourRepository/YourProject/vendors/cakephp/current From:
revision 49, To: revision 50


CLI:
;;;;
svn merge -r 49:50
svn://YourRepository/YourProject/vendors/cakephp/current
c:\YourProject\trunk

This has now updated your CakePHP core in your application working
copy. As it has just merged in the diff of the core, any of your own
local modifications to the CakePHP core will still be there (although
watch for conflicts in this case). This will include any changes to
the CakePHP /app/config/* files, and other files in the "app" folder.

You should test your application with this new core, and fix any
issues that may have arisen. When you are happy with this new core,
you can commit your trunk. Alternatively, you can always revert your
trunk to undo this merge (leaving your application core un-updated).



Step 7: Rinse and Repeat
````````````````````````
Repeat this from Step 5 whenever you wish to update your third party
library.



Not just the CakePHP core
~~~~~~~~~~~~~~~~~~~~~~~~~
You should use Vendor Branching for all of your third party libraries,
anything where there is a chance that the third party library will be
updated (and that you might want to implement this update), and where
you may possibly make local changes to those libraries. This includes
any of your application vendor files (PHP libraries, Javascript
frameworks etc), and even your own utility scripts (if you have common
Components/Helpers etc that are used in multiple applications).

.. _http://svnbook.red-bean.com/en/1.1/ch07s05.html: http://svnbook.red-bean.com/en/1.1/ch07s05.html
.. _http://www.debuggable.com/posts/screencast-using-vendor-branching-and-cakephp:480f4dd6-6cac-44cb-b685-4d6bcbdd56cb: http://www.debuggable.com/posts/screencast-using-vendor-branching-and-cakephp:480f4dd6-6cac-44cb-b685-4d6bcbdd56cb
.. _https://svn.cakephp.org/repo/branches/1.2.x.x: https://svn.cakephp.org/repo/branches/1.2.x.x
.. meta::
    :title: Vendor Branching
    :description: CakePHP Article related to ,Tutorials
    :keywords: ,Tutorials
    :copyright: Copyright 2008 
    :category: tutorials

