Bake ROT13 Encoded "mailto:" Links
==================================

by debuggeddesigns on November 04, 2008

Why would I want to use encoded "mailto:" anchor tags? To obfuscate
e-mail addresses from spam harvesters. The helper lets you easily
encode an entire anchor tag using ROT13 Encryption. The ROT13 encoding
simply shifts every letter by 13 places in the alphabet while leaving
non-alpha characters untouched. At run-time, javascript is used to
decode the ROT13 encryption. If javascript is disabled, then the
e-mail address is safely shown by reversing the e-mail address using
PHP and re-reversing (versing?) it at run-time using CSS.


Step 1: Create the Mailto helper
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
This helper's function accepts an e-mail address and link content as
it's parameters and returns javascript code containing the encrypted
anchor tag.


Filename: /app/views/helpers/mailto.php



Helper Class:
`````````````

::

    <?php 
    class MailtoHelper extends Helper {
    	
    	function createLink($addr, $link_content) {
    
    		//build the mailto link
    		$unencrypted_link = '<a href="mailto:'.$addr.'">'.$link_content.'</a>';
    		//build this for people with js turned off
    		$noscript_link = '<noscript><span style="unicode-bidi:bidi-override;direction:rtl;">'.strrev($link_content.' > '.$addr.' <').'</span></noscript>';
    		//put them together and encrypt
    		$encrypted_link = '<script type="text/javascript">Rot13.write(\''.str_rot13($unencrypted_link).'\');</script>'.$noscript_link;
    
    		return $encrypted_link;
    	}
    }
    ?>



Step 2: Include the helper inside your controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


Filename: /app/controllers/tests_controller.php
```````````````````````````````````````````````

Controller Class:
`````````````````

::

    <?php 
    class TestsController extends AppController {
        var $name = 'Tests';
        var $helpers = array('Mailto');
    
        function mailto() { }
    }
    ?>



Step 3: Create the javascript
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
The javascript decodes the anchor tag that was encoded in the helper
above.

Download this file: `http://scott.yang.id.au/file/js/rot13.js`_
Save the file here: /app/webroot/js/rot13.js



Step 4: Include rot13.js in your layout view
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

[h4]Filename: /app/views/layouts/default.thtml
``````````````````````````````````````````````

View Template:
``````````````

::

    <script type="text/javascript" src="<?php echo $this->webroot; ?>js/rot13.js"></script>




Step 5: Use the helper inside a view
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

[h4]Filename: /app/views/tests/mailto.thtml
```````````````````````````````````````````

View Template:
``````````````

::

    <?php echo $mailto->createLink('spam@debuggeddesigns.com','Debugged Interactive Designs'); ?>




Step 6: View the source and be amazed
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
To test it out, visit the page `www.yourdomain.com/tests/mailto`_

This is the ROT13 encoded anchor tag found in the source code:

::

    
    <script type="text/javascript">Rot13.write('<n uers="znvygb:fcnz@qrohttrqqrfvtaf.pbz">Qrohttrq Vagrenpgvir Qrfvtaf</n>');</script><noscript><span style="unicode-bidi:bidi-override;direction:rtl;">< moc.sngiseddeggubed@maps > sngiseD evitcaretnI deggubeD</span></noscript>

I got this idea from a presentation by Mark Rosenthal at a BostonPHP
meeting that explained this technique. He admitted that there are many
different techniques for tricking spam bots, and he might not be the
first to think of this one. Well, after some google searching, he
wasn't: `http://scott.yang.id.au/2003/06/obfuscate-email-address-with-
javascript-rot13/`_. Believe it or not, that javascript file above was
written by Scott Yang in 2003 for this exact purpose. Please leave any
alternate techniques you might use in the comments below.

Update: Since creating this helper, I found the article "Nine Ways To
Obfuscate E-mail Addresses Compared" at
`http://techblog.tilllate.com/2008/07/20/ten-methods-to-obfuscate-e
-mail-addresses-compared/`_, which says ROT13 encoding and changing
the code direction with css "...are absolutely rock-solid and keep
your addresses safe from the harvesters."

.. _http://techblog.tilllate.com/2008/07/20/ten-methods-to-obfuscate-e-mail-addresses-compared/: http://techblog.tilllate.com/2008/07/20/ten-methods-to-obfuscate-e-mail-addresses-compared/
.. _www.yourdomain.com/tests/mailto: http://www.yourdomain.com/tests/mailto
.. _http://scott.yang.id.au/2003/06/obfuscate-email-address-with-javascript-rot13/: http://scott.yang.id.au/2003/06/obfuscate-email-address-with-javascript-rot13/
.. _http://scott.yang.id.au/file/js/rot13.js: http://scott.yang.id.au/file/js/rot13.js

.. author:: debuggeddesigns
.. categories:: articles, tutorials
.. tags:: js,mailto,spam,strrot,anchor,encoding,rot,Tutorials

