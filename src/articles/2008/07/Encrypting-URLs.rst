Encrypting URLs
===============

by yuri.salame on July 15, 2008

Encrypting URLs is not very SEO friendly but when this is not the aim
you can easily encrypt everything to avoid tampering when normal
checks have been forgotten.
My current situation has forced me to encrypt my URLs, but how to do
that globally and sometimes not do it at all? Desktop applications had
the security of never showing which parameters they were using within
the application but since users can fiddle with the querystring
webapps had to add that extra amount of security.

When a user can't view a certain record you just lock him with
credentials, database queries and such for verification purposes. If
he has permission to access a record like this one :
`http://www.somesite.com/record/44`_ he can easily change the
querystring to `http://www.somesite.com/record/87`_ and access a
different record. You lock him outside record 87 by querying the
database or some other method but when an application gets too large
with many programmers sometimes these checks within a controller might
be forgotten.

A simple change on the webroot/index.php and a couple of
encrypt/decrypt funcions on the bootstrap can resolve this issue.

On the app/webroot/index.php right before the $Dispatcher is instanced
call your decrypting function passing the current url being accessed.

::

    
    $url = do_decrypt($_REQUEST["url"]);
    $Dispatcher = new Dispatcher();
    $Dispatcher->dispatch($url);


On your app/config/bootstrap.php add the decrypting and crypting
functions.

::

    
    function do_crypt($url)
    {
    	return 'url-'.base64_encode($url);
    }	
    
    function do_decrypt($url)
    {
    	if (substr($url , 0 , 4) == 'url-')
    	{
    		$url = substr($url , 4);
    		return base64_decode($url);
    	} else {
    		return $url;
    	}
    }

The Base64encode is used just as an example here but you can do any
kind of crypt here, Blowfish, DES, TripleDES, MD5(db-based), 2-Way,
etc...

Since the URLs that are crypted have the "url-" prefix if an url does
not have this prefix then the original URL is used. This solves the
problem of having just some of the URLs encrypted.




.. _http://www.somesite.com/record/44: http://www.somesite.com/record/44
.. _http://www.somesite.com/record/87: http://www.somesite.com/record/87
.. meta::
    :title: Encrypting URLs
    :description: CakePHP Article related to ,Tutorials
    :keywords: ,Tutorials
    :copyright: Copyright 2008 yuri.salame
    :category: tutorials

