AutoHotkey Guide for CakePHP
============================

Tired of typing "class YourModelsController extends AppController..."
over and over? If so this, is for you.
NOTE: This guide assumes you are using PHPeclipse as your IDE of
choice. If you use another IDE or notepad you will have to adjust the
AutoHotkey script accordingly.

Step 1
------
Download and Install AutoHotkey ( it's free ) -
`http://www.autohotkey.com/`_

Step 2
------
Edit the default script to look like the following.

::

    
    ;-----------------------------------------------------------------------------
    ;	CakePHP
    ;-----------------------------------------------------------------------------
    
    ;model
    :c*?:]cmod::
    InputBox, MyModel, Model Name Prompt, Enter model name ( e.g. MyModel ),,250,125
    if ErrorLevel=0
    {
    	BlockInput On
    	Send, class %MyModel% extends AppModel{enter}{{}{enter}var $name = '%MyModel%';{tab}// required for php4 installs{enter}var $displayField = 'id';{enter}
    	BlockInput Off
    }
    return
    
    ;controller
    :c*?:]ccon::
    InputBox, MyModels, Models Controller Prompt, Enter controller name ( e.g. MyModels ),,250,125
    if ErrorLevel=0
    {
    	BlockInput On
    	Send, class %MyModels%Controller extends AppController{enter}{{}{enter}var $name = '%MyModels%';{tab}// required for php4 installs{enter}var $scaffold;{enter}
    	BlockInput Off
    }
    return
    



Step 3
------
Fire up PHPeclipse and create a model php file.

Reload the script by right-clicking the taskbar icon and reload.

type ]cmod

It should prompt you for the model's name, so type your models name.

For controllers type ]ccon

.. _http://www.autohotkey.com/: http://www.autohotkey.com/

.. author:: Tr0y
.. categories:: articles, tutorials
.. tags:: code-generation,autohotkey,phpeclipse,automation,Tutorials

