Minify helper for cakephp
=========================

by _k10_ on January 17, 2009

A minify helper for js and css assets.
Lately, I have been working on a pretty large scale website involving
cakephp. With all the feature requests in place, it was time for
optimization. I usually follow Yahoo's Developer Network's best
practices - `http://developer.yahoo.com/performance/rules.html`_. It
has got fantastic set of rules in there ( a must read for every web
developer).

It is the same set of rules I implemented for my website - Property
Jungle - `http://www.propertyjungle.in`_.

As I didn't want re-invent any wheels, I zeroed down to project minify
found here - `http://code.google.com/p/minify/`_. It provides the
following best practices out-of-box.

1. Minimise HTTP request - Since it unifies javascript (js) and css
assets, only one call for each should be issued by a page
2. Adds an Expires or a Cache-control header: You don't want your
server to issue the same js and css assets for every page load for a
given user session.
3. Gzip components : Does that!
4. Minify js and css: Does that too!
5. Configure Etags: Phew, does that too!

Pretty good number of performance improvements. Ok enough of the
introduction. Lets dive in.

Here are the sets to follow to get things working.

1. Dump the min folder from the minify source in your webroot
directory - It should be accessible by http://server-name/min
2. Dump my minify helper into the helpers folder --> /views/helpers.

Helper Class:
`````````````

::

    <?php 
    /***
     * Cakephp view helper to interface with http://code.google.com/p/minify/ project.
     * Minify: Combines, minifies, and caches JavaScript and CSS files on demand to speed up page loads.
     * @author: Ketan Shah - ketan.shah@gmail.com - http://www.innovatechnologies.in
     * Requirements: An entry in core.php - "MinifyAsset" - value of which is either set 'true' or 'false'. False would be usually set during development and/or debugging. True should be set in production mode.
     */
    
    Class MinifyHelper extends AppHelper{
            
            var $helpers = array('Javascript','Html'); //used for seamless degradation when MinifyAsset is set to false;
            
            function js($assets){
                if(Configure::read('MinifyAsset')){
                   e(sprintf("<script type='text/javascript' src='%s'></script>",$this->_path($assets, 'js')));
                }
                else{
                    e($this->Javascript->link($assets));
                }
            }
            
            
            function css($assets){
                if(Configure::read('MinifyAsset')){
                    e(sprintf("<link type='text/css' rel='stylesheet' href='%s' />",$this->_path($assets, 'css')));
                }
                else{
                    e($this->Html->css($assets));
                }
            }
            
            function _path($assets, $ext){
                $path = $this->webroot . "min/b=$ext&f=";
                foreach($assets as $asset){
                    $path .= ($asset . ".$ext,");
                }
                return substr($path, 0, count($path)-2);
            }
        }
    
    ?>

3. Add an entry 'Minify' in the $helpers array of the controller which
will serve your page request.
4. Use $minify->js() and $minify->css() functions in the layout. Both
functions require an array of asset names without their extensions.
5. Add an entry Configure::write('MinifyAsset',true) in your core.php
file. Setting it false during debugging or development phase would be
a good idea.

The helper code is pretty self-explanatory. It basically generates the
list and script tags for css and js respectively with the 'src'
attribute pointing to the 'min' folder in pre-specified format (when
the 'MinifyAsset' is set to true in the core). Setting it false,
fetches the assets from js and css folders.

Hope this helps you in getting up to speed quick and make a pretty
noticeable performance improvement to your website. Any feedback or
constructive criticism would be appreciated.

-Ketan.
`http://www.innovatechnologies.in`_ ketan _d0t_ shah _At_
innovatechnologies.in

.. _http://www.propertyjungle.in: http://www.propertyjungle.in/
.. _http://www.innovatechnologies.in: http://www.innovatechnologies.in/
.. _http://code.google.com/p/minify/: http://code.google.com/p/minify/
.. _http://developer.yahoo.com/performance/rules.html: http://developer.yahoo.com/performance/rules.html

.. author:: _k10_
.. categories:: articles, helpers
.. tags:: minify,cachecontrol,etags,gzip,Helpers

