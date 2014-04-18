How to make CakePHP and 1&1 linux hosting bake together
=======================================================

In late 2003, [url=http://www.1and1.com]1&1 Internet, Inc.[/url]
stormed the country with their US debut by offering free professional
package hosting for 3 years. In 2004, an independent research firm
reported that 1&1 was the world's fastest-growing web host, activating
400,000 accounts and seeing 21.9 percent overall growth. Today, I'm
going to look at how quick and easy it is to get your CakePHP
application up and running on a 1and1.com linux hosting package.
Before I begin, I imagine that many users will find themselves here
hoping to find an answer to the question "Why do I keep getting '500 -
Internal Server Errors' with CakePHP on 1and1.com servers?" I'll
answer this first and then proceed with a tutorial for less advanced
users hoping for a walk-through. To fix your 500 errors, simply add
the following to your .htaccess files after 'RewriteEngine on':

::

        RewriteBase    /

Now on to the tutorial. If we take a look at the system requirements
from the `CakePHP Manual`_'s chapter on `installation`_, all of
1and1.com's Linux hosting packages will meet the requirements for a
CakePHP application. For only $4.00 per month, the 1&1 beginner
package offers 10GB of web space, PHP scripting, and ten 100MB MySQL
databases with a 90-day money back guarantee: more than enough
resources to get a CakePHP application up and baking. (No, I don't
work for 1&1)

The first step is to put the cake libraries somewhere on your server.
I use ~/cake. However you upload the Cake libraries is up to you. You
can FTP upload the tar.gz file, you can SSH into your account and
upload that way, you can use 'wget' to have the 1and1.com server do
the downloading for you, or advanced users can even use subversion to
download the absolute latest code (note, 1and1 at this time doesn't
have subversion installed, only CVS, so you'll have to download the
latest subversion, compile it, and add its 'bin' directory to your
path to use it). I'll walk you through the 'wget' method because it's
classy.



.. _installation: http://manual.cakephp.org/chapter/installing
.. _CakePHP Manual: http://manual.cakephp.org/

.. author:: glite
.. categories:: articles, tutorials
.. tags:: ,Tutorials

