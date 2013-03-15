

introduction to dAuth v0.3
==========================

by %s on November 11, 2006

This article introduces dAuth V0.3: the authentication (not
authorisation) system with a focus on security, using techniques such
as challenge-response, customizable multiple-stage password hashing,
brute force (hammering) detection etc.


May i introduce... dAuth v0.3
-----------------------------
dAuth is basically plug and play. get all the code that you'll find at
the end of the article, execute the sql and you're all set. You can
optionally tinker with the variables in the component to suit more
specific needs.
You could actually stop reading here and just start playing with it.
But if you're interested in the more in depth workings, read on.

Before you start
----------------
The dAuth system is based on the original "Challengeresponse
authentication with fallback" article. This version will be referred
to as dAuth v0.1 from now on.
`http://bakery.cakephp.org/articles/view/128`_
I received quite some positive feedback about both v0.1 and v0.2 and
it made me feel good. (thumbs up for the big chefs, they make this all
happen!)

If you haven't read the previous article, i suggest you do it now, as
i won't repeat all the necessary information here.

Keep in mind dAuth is only meant for authentication (is someone who he
claims to be?) and not authorization (is someone allowed to do this or
that?)

Also, don't use dAuth v0.1 or v0.2 code, it contains nothing that v0.3
doesn't have :) (apart from a few bugs ;-)


dAuth in depth
--------------
dAuth is a challenge-reponse authentication system.
Here is a diagramn of the transitions the password goes through.


Changes since previous versions
-------------------------------

v0.3 has evolved quite a bit since v0.1.



Genericification (if that's a word)
```````````````````````````````````
Let's start with the most simple change. v0.3 is more generic then
v0.1 because...

#. i tried to organise the code in a way that makes most sense (put
   the auth logic in a component, let the users controller make use of
   the component, have the right functions in the right places (eg moved
   some logic to models memberfunctions)
#. i replaced some view code with calls to cake's helper functions.
   This decreases performance a very little bit, and makes the code a
   tiny little bit more complicated, but the code is more generic, and
   that's what this is all about. (cheers Robert and Andy Dawson for the
   hints)


Detection and handling of brute-force attempts (hammering)
``````````````````````````````````````````````````````````
When someone tries to login, a new entry in the hosts table is
created, with his ip-adress in it, or an already existing one is
retrieved. The behaviour of this host is checked (using the
$hammerRatio variable) and if needed, the host is denied access and
put in a blocked status. It's possible to immediatly die() the
execution if the host is blocked, or hammering is detected. (you
choose this all with $diehammer and $dieblocked)
The blocking is effective for the timespan you define in $blockTime.
When discussing this with Crazylegs (othman ouahbi), he mentioned he
has a similar system, but uses cookies to do additional checking: when
bots/scripts would change their ip-adress, they would still have the
cookie, and the auth system could detect this. Personally i'm not sure
about the benefits of such an approach (as i doubt if scripts/bots
just accept cookies, especially if they are smart enough to change
ip's. i could also imagine several servers that try to enter many
websites and switch their task to the other server when they get
blocked, in this case it wouldn't work too) but it certainly is worth
investigating.

Since i don't like to depend on crontabs, old (unneeded) loginAttempt
records are deleted about every once in 500 http login requests with
the dAuth _cleanUpAttempts() function.

Preventing users transmitting plaintext passes (disabling fallback)
```````````````````````````````````````````````````````````````````
set the $allowClearText variable in the component and you're set! how
easy can it be :)
If $allowClearText = true; then the default form will be working, the
user can fill in his details (username, password) and submit the form.
Upon submit, if he has javascript, a hash will be created of the
password, and the password itself will be replaced by a dummy string.
If he has no javascript, he will transmit the pass in cleartext, and
the system will accept the login if the information is correct.

If $allowClearText = false, however, there will be a form with empty
action and method fields, and with style set to invisible, accompanied
by an error that the user should get javascript support if he wants to
login. If he doesn't have javascript enabled, he will thus only see
the error, and not the form.
However, if he has javascript enabled, the contents of the error will
be emptied, the form method and action will be correctly filled in,
and the form will be set to visible. (all happening immediatly at
page-load) This results in exactly the same form as he would otherwise
get with $allowClearText set to true. This way, the user can login
normally, with the hashing and other fancy features enabled. :-)

Session hijacking detection & handling
``````````````````````````````````````
This is an aspect where cake already handles quite a lot, by providing
a decent session class with issues like fixations and impersonations
already in mind.
I found a great article about security issues arising from using
sessions: `http://phpsec.org/projects/guide/4.html`_ it mentions 4
aspects regarding session hijacking:

#. Session id prediction: This means just guessing the identifier, and
   is a pretty unsuccessfull approach to session hijacking. Id's are
   randomly generated, and consist of many digits, so it's very unlikely
   to guess a valid session identifier. Even if found one, it's probably
   of not much use because you will need to do more to hijack the
   session. (see below) and the chances that your guessed (valid) id is
   actually the one belonging to your target, is also pretty small.
#. Session id capturing: session id's are transferred via GET
   variables or cookies, data which is sent in cleartext over the
   network, and could be sniffed by bad guys, or retrieved via bugs or
   potentially other methods. I guess it could even be done serverside if
   you're on a shared host: a crewmember could retrieve your session
   files/entries in the database. I should look into this issue further,
   but keep in mind that getting the valid session id (with this method,
   or the id prediction method or the fixation method below) is not the
   only step towards a successful hijacking. read on...
#. Session fixation: The key here is that a malicious user passes you
   a url with a (randomly chosen) session id already in it, if you keep
   that session alive by browsing the site and logging in, the malicious
   user knows a valid session id by which he could hijack your session.
   The article recommends generating a new random id when the user logs
   in. CakePHP is our buddy here because it systematically generates a
   new session id each http request, totally crushing fixation attempts!
   This also has the side-effect of making a successfully retrieved
   session id only valid for a very short timespan: the time between
   successive http requests or in worst case, the maximum session valid
   time in case the "last" session id is retrieved. This valid time is
   defined in the config file. see
   `http://manual.cakephp.org/chapter/configuration`_ (it is also used to
   delete the serverside tmp session files after expiring)
#. Target impersonation: So, if the malicious user retrieved your
   session id one way or another, he should not be able to hijack your
   session. How can be checked if a request with a valid session id is
   coming from a good guy are a bad one? By checking not only the session
   id, but also additional parameters like the user agent. Again, cake
   spoils the fun and takes away any need to program this in the
   application code ( ;-) ) It has hijack-protection built-in by checking
   the user agent. However, personally i think this could be improved,
   because it's isn't very hard for a malicious user to try some known
   user agents, especially since some are very popular (most targets
   probably use MSIE6 for example, and all the frequently used user
   agents are not only known (they are everywhere on the internet), they
   are also a select few.) A system that would block users that had more
   then one user agent in a short timespan would be of use here i think.
   (or let them auth again). Even more, i would just use the ip-adress as
   additional criteria to check your user against. (this is not
   recommended by the article, but personally i've never seen one user
   having more then one ip-adress in successive http requests. But even
   if this would be the case, letting the user login again is just a
   small sacrifice. On the other hand, the chance that the good guy and
   the bad guy share the same ip is very small, and faking an other ip
   adress is an extra step that makes it even harder to take-over a
   session (maybe something to think about for later cake versions,
   activating ip-check when security is set to high?)


Small changes
`````````````

#. I've changed the name of the encrypt() function to hash(), because
   strictly speaking, that function isn't really encryption because by
   definition, in that case decryption should be possible. It's rather
   hashing, which is irreversible (which doesn't mean that one can't find
   passwords for a given hash, see previous article. But this can take
   very long, especially if you make this function more complicated then
   using plain md5's or sha's)
#. You'll see that hash() is no longer just a sha1 hash. To protect
   against password-retrieval efforts i added a salt. And not just a
   static salt, since the salt is the first letter of the password
   itself, the salt is customized for each password which makes using
   password-cracking tools harder :-)
#. I improved the error reporting mechanism a bit. Different errors
   while processing the user input in the business layer will result in
   warning messages that are directly linked to the specific problem.
#. and more... (see for yourself :)

These were all changes from v0.1 to v0.2
There are also quite a few specific additions specific to v0.3, you'll
see those at the specific pages mentioned below.



Installation
------------

Option 1
````````
Here are the pages with the code:

#. views, usersController and javascripts
   `http://bakery.cakephp.org/articles/view/152`_
#. component `http://bakery.cakephp.org/articles/view/153`_
#. helper `http://bakery.cakephp.org/articles/view/154`_
#. models `http://bakery.cakephp.org/articles/view/155`_


Option 2
````````
you could also just grab the code from these urls:

#. views/users/login.thtml:
   `http://bakery.cakephp.org/articles/download_code/152/block/1`_
#. views/users/register.thtml:
   `http://bakery.cakephp.org/articles/download_code/152/block/2`_
#. views/elements/userinfo.thtml:
   `http://bakery.cakephp.org/articles/download_code/152/block/3`_
#. views/users/change_password.thtml:
   `http://bakery.cakephp.org/articles/download_code/152/block/4`_
#. controllers/users_controller.php:
   `http://bakery.cakephp.org/articles/download_code/152/block/5`_
#. webroot/js/d_auth.js:
   `http://bakery.cakephp.org/articles/download_code/152/block/6`_
#. webroot/js/sha1.js:
   `http://bakery.cakephp.org/articles/download_code/152/block/7`_
#. controllers/components/d_auth.php:
   `http://bakery.cakephp.org/articles/download_code/153/block/1`_
#. views/helpers/d_auth.php:
   `http://bakery.cakephp.org/articles/download_code/154/block/1`_
#. models/user.php:
   `http://bakery.cakephp.org/articles/download_code/155/block/1`_
#. models/host.php:
   `http://bakery.cakephp.org/articles/download_code/155/block/2`_
#. models/login_attempt.php:
   `http://bakery.cakephp.org/articles/download_code/155/block/3`_
#. SQL code:
   `http://bakery.cakephp.org/articles/download_code/155/block/4`_

Don't forget you need to follow cake's
conventions!`http://manual.cakephp.org/appendix/conventions`_

Configuration
-------------
Take a look at the variables in the component, change to your likings
and you're all set!


Final words
-----------
You'll see that i've added a little bonus, a basic (no captcha or
other fancy stuff yet) /users/register and /users/changePassword
form/action, and a userinfo element which strictly speaking can't be
part of an auth-system, but since it they integrate so nicely with the
auth system and share some logic i just have to do it ;-). Even if you
don't want any visitor to register on your site, you can limit this
action to yourself to easily create new users. (the right component's
stage1Hash() function is called for you so the perfect hash is put in
the database) Ironicly enough, when filling in the register or change
password form, the passwords can not be hashed using stage2Hash, since
this hashing manner changes all the time and the server would not be
able to know the original stage1 hash that is the "source" of the
stage 2 hash. And the database needs to store the stage1 hash so... If
anyone would sniff the network, and if they are smart enough, they
could enter the sniffed hash in the form, modify the javascript code a
bit to start from the stage1 hash (which would be created anyway)
instead of the original password, in order to generate valid responses
for the challenge/response system. Https would certainly be in its
place here, or otherwise a customized encryption system so that the
server could decrypt the password.



Also, keep in mind that i'm just human. Humans make errors, especially
humans like me! Take a look at the code, try some stuff out, and give
us some feedback, thanks! (especially the (timing stuff in) the host
behaviour checking could use some attention)

To-do
-----

#. Look into session id capturing, how it can be done, if cake does
   something about it, or if i should do it
#. Consider using cookies as add-on for hammering detection (with
   possibly denying users if they disable cookies, or using a fallback
   for these users, with the same security issue remaining)
#. Find out how i can request the SSL layer if it's available
#. [li]Find out how can encrypt and decrypt efficiently for using the
   register and password-change forms.



.. _http://bakery.cakephp.org/articles/download_code/155/block/4: http://bakery.cakephp.org/articles/download_code/155/block/4
.. _http://bakery.cakephp.org/articles/download_code/155/block/1: http://bakery.cakephp.org/articles/download_code/155/block/1
.. _http://bakery.cakephp.org/articles/download_code/155/block/2: http://bakery.cakephp.org/articles/download_code/155/block/2
.. _http://bakery.cakephp.org/articles/download_code/155/block/3: http://bakery.cakephp.org/articles/download_code/155/block/3
.. _http://bakery.cakephp.org/articles/download_code/152/block/7: http://bakery.cakephp.org/articles/download_code/152/block/7
.. _http://bakery.cakephp.org/articles/download_code/152/block/6: http://bakery.cakephp.org/articles/download_code/152/block/6
.. _http://bakery.cakephp.org/articles/download_code/152/block/5: http://bakery.cakephp.org/articles/download_code/152/block/5
.. _http://bakery.cakephp.org/articles/download_code/152/block/4: http://bakery.cakephp.org/articles/download_code/152/block/4
.. _http://bakery.cakephp.org/articles/download_code/152/block/3: http://bakery.cakephp.org/articles/download_code/152/block/3
.. _http://bakery.cakephp.org/articles/download_code/152/block/2: http://bakery.cakephp.org/articles/download_code/152/block/2
.. _http://bakery.cakephp.org/articles/download_code/152/block/1: http://bakery.cakephp.org/articles/download_code/152/block/1
.. _http://bakery.cakephp.org/articles/view/128: http://bakery.cakephp.org/articles/view/128
.. _http://bakery.cakephp.org/articles/download_code/154/block/1: http://bakery.cakephp.org/articles/download_code/154/block/1
.. _http://manual.cakephp.org/appendix/conventions: http://manual.cakephp.org/appendix/conventions
.. _http://bakery.cakephp.org/articles/view/154: http://bakery.cakephp.org/articles/view/154
.. _http://bakery.cakephp.org/articles/view/155: http://bakery.cakephp.org/articles/view/155
.. _http://bakery.cakephp.org/articles/view/152: http://bakery.cakephp.org/articles/view/152
.. _http://bakery.cakephp.org/articles/view/153: http://bakery.cakephp.org/articles/view/153
.. _http://phpsec.org/projects/guide/4.html: http://phpsec.org/projects/guide/4.html
.. _http://manual.cakephp.org/chapter/configuration: http://manual.cakephp.org/chapter/configuration
.. _http://bakery.cakephp.org/articles/download_code/153/block/1: http://bakery.cakephp.org/articles/download_code/153/block/1
.. meta::
    :title: introduction to dAuth v0.3
    :description: CakePHP Article related to login,dauth,challenge response,secure,auth dAuth challenge,General Interest
    :keywords: login,dauth,challenge response,secure,auth dAuth challenge,General Interest
    :copyright: Copyright 2006 
    :category: general_interest

