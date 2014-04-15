How we built Twittermail in 48 hours
====================================

by LennieZ on February 05, 2009

We started Twittermail in june 2007, last week we launched our brand-
new website Twittermail.com, this time we built it in CakePHP 1.2, in
exactly 48 hours.
Okay, for those who don't know Twittermail, Twittermail is an openidea
by Boris Veldhuijzen van Zanten, he thought that it would be nice if
people could e-mail their tweets to Twitter.com.

This is very useful for people who use mobile phones. Of course you
can go to the mobile webpage of Twitter, but sending an e-mail is much
more easier. Some older phones do not even have a browser and only
have e-mail functionality. Also a lot of businesses block Twitter.com
on their corporate network but with Twittermail you can always update
your Twitter account even from your business e-mail address.

Those who register at Twittermail, get a secret unique e-mail address
like 1234abcde[AT] twittermail com. When you send an e-mail to this
secret e-mail address, it will get posted instantly to Twitter.com
through the Twitter API.



The Enviorement
~~~~~~~~~~~~~~~
Twittermail is a small project so basically we only need a sign-up
form, a settings page and we need to show some recent activity. Since
we handle loads of visitors and more than 3000 e-mails a day this
project needed to be very scalable and thin. We read a lot about the
benchmarking results 1 . We managed to do this project in exactly 48
hours, thanks to CakePHP (and Coca Cola).



Custom Validation
~~~~~~~~~~~~~~~~~
Users supply us their Twitter credentials so that we can create an
unique e-mail address. Because we need to check if the information
they supply is valid, we created a custom validation which checks the
Twitter API whether or not everything is valid. In our model we
created a custom function called 'isValidTwitter', in this function we
make a CURL call to the Twitter API, we parse the output from JSON to
an array and we get back the user information or an error.


Model Class:
````````````

::

    <?php 
    class Twitter extends AppModel
    {
        var $name = 'Twitter';
    
        var $validate = array(
            'twitter_username' => array(
                'rule' => array('isValidTwitter'),
                'message' => __('The credentials you supplied are not valid! Please try again')
            )
        );
    ?>



Model Class:
````````````

::

    <?php 
        /**
         * Valid Checker: Is Valid Twitter Account
         *
         */
        function isValidTwitter($value, $params = array())
        {
            $curl_twitter = curl_init();
            curl_setopt($curl_twitter, CURLOPT_URL, 'http://twitter.com/account/verify_credentials.json');
            curl_setopt($curl_twitter, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl_twitter, CURLOPT_HEADER, false);
            curl_setopt($curl_twitter, CURLOPT_USERPWD, $this->data[$this->name]['twitter_username'].":".$this->data[$this->name]['twitter_password']);
            curl_setopt($curl_twitter, CURLOPT_POST, true);
            curl_setopt($curl_twitter, CURLOPT_RETURNTRANSFER, true);
            $curl_result = curl_exec($curl_twitter);
            curl_close($curl_twitter);
    
            $twitterResponse = json_decode($curl_result, true);
    ?>

From now on you can do whatever you want with the results. In our
project we encode the password, fetch some Twitter information and
save it to the database.

Because we don't need a very complex User Authentication, we didn't
use ACL.


Localization & Internationalization, l10n and i18n
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
At the moment our website is English-only, but we already made it
multi-language proof by using the __('Login') function. For those who
never worked with localization, in the cake console you can simply
generate a language file by using the command 'cake I18n' here you get
the option to extract all __('') strings to POI files.
You can read more about localization at the wonderful Cookbook at
`http://book.cakephp.org/view/162/Localizing-Your-Application`_


Scaling
~~~~~~~


Caching
```````
Because we're serving more than 18,000 users and more than 800,000
e-mails have been sent, scaling is a important subject. First of all,
use Cache! You don't need to get all data over and over again from the
database. For example in the sidebar we have 'Newest Twittermailers'.
This is cached for a year (yes, a year), everytime when someone is
registering, we're resetting the cache. Cache isn't a thing you should
forget, and for those who never worked with it please read the
Cookbook.

::

    
    // Reset / Delete the cache, so next time the newest Twittermailers list will be re-generated.
    Cache::delete('sidebar_newesttwitters');



Containable Behavior
````````````````````
This is a very big improvement in the Cake 1.2 series, in the past I
always used for my CakePHP projects 'expect' (For 1.1 users:
`http://bakery.cakephp.org/articles/view/keeping-bindmodel-and-
unbindmodel-out-of-your-controllers`_).
This model behavior allows you to filter and limit model find
operations. Using Containable will help you cut down on needless wear
and tear on your database, increasing the speed and overall
performance of your application. The class will also help you search
and filter your data for your users in a clean and consistent way.
It helps you selecting only the data you really want, it's recommended
to read the full recept about this at
`http://book.cakephp.org/view/474/Containable`_


Think about security
~~~~~~~~~~~~~~~~~~~~
Often developers forget to think about security, but security is a
very important subject when you're developing web-applications. Use
the validations and double check them with the sanitize object built-
in CakePHP.

::

    
    App::import('Sanitize');

Put this code in your AppController and AppModel to achieve a higher
lever of security, read about it at `http://book.cakephp.org/view/153
/Data-Sanitization/`_
For example:

::

    
    echo Sanitize::html($untrustedString, true);

And remember: NEVER trust the input of your users!

Then there's the Model::save function, that lacks a good description
in the cakebook, but in my opinion the $fieldList method is very
important.
Like I said before, never trust the input of your users, supply
$fieldList with an array of all the fields you're supposed to fill.

For example:

::

    
    $this->Twitter->save($this->data, true, array('username', 'password', 'email'));



How we did it in 48 hours
~~~~~~~~~~~~~~~~~~~~~~~~~
Of course this is a (very) small project, but because of Cake we were
able to manage this project in 48 hours. It's important to stick to
the CakePHP 'rules', just use the validation methods, use the helpers,
use the build-in components, it really helps you with keeping your
code clean and compact.



Some respect
~~~~~~~~~~~~
Show the world you're happy with CakePHP, put the 'cakephp - power'
button on your website, be proud you're using CakePHP. Also don't
forget to donate, the cake developers team are working day-and-night
to supply us a better CakePHP, the only thing we could do is using
CakePHP and show our satisfaction, at the end of 2009 we will donate
the Cake Foundation 10% of Twittermail's profit.

Happy baking,

Bram Kok
Lennaert Ekelmans
`http://www.twittermail.com`_

.. _http://bakery.cakephp.org/articles/view/keeping-bindmodel-and-unbindmodel-out-of-your-controllers: http://bakery.cakephp.org/articles/view/keeping-bindmodel-and-unbindmodel-out-of-your-controllers
.. _http://book.cakephp.org/view/474/Containable: http://book.cakephp.org/view/474/Containable
.. _http://www.twittermail.com: http://www.twittermail.com/
.. _http://book.cakephp.org/view/162/Localizing-Your-Application: http://book.cakephp.org/view/162/Localizing-Your-Application
.. _http://book.cakephp.org/view/153/Data-Sanitization/: http://book.cakephp.org/view/153/Data-Sanitization/

.. author:: LennieZ
.. categories:: articles, case_studies
.. tags:: twitter,case study,twittermail,Case Studies

