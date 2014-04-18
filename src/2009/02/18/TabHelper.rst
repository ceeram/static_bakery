TabHelper
=========

Okay, in the Bakery there is a lot of MenuHelper's and TabHelper's
which tries to produce a nice way to check the controller params etc
and select the active element. This helper does exactly that, just in
an alternative way.
Okay as written in the intro there is about a dusin other helpers that
does exactly this, or close to it. But my needs where a little
different. The other helpers can provide you with more than one
element with the "active" class, if there is more than one match.

An example would be if you have a tab witch is set to be active on the
controlle 'users' and another one where the match should be users
controllers and the action view. When you go to /users/view both of
them would be active.

And to counter this. This helper takes all the params, action,
controller etc and calculates matching points and the match with most
points wins. So i the example above the last rule would win.

Also i needed this Helper to create a link, to another action etc than
the matching one. So i came up with this API.

This is taken from my application, in danish i am afraid but should be
clear enough.

::

    
    <?php
        echo $tab->tabs(array(
                'Forsiden' => array(
                    'match' => array('controller' => 'articles'),
                    'link' => array('controller' => 'articles', 'action' => 'index', 'admin' => false),
                ),
                'Brugere' => array(
                    'match' => array('controller' => 'users'),
                    'link' => array('controller' => 'users', 'action' => 'index', 'admin' => false),
                ),
                'Forum' => array(
                    'match' => array('controller' => 'forums'),
                    'link' => array('controller' => 'forums', 'action' => 'index', 'admin' => false),
                ),
                'VÃ¦ggen' => array(
                    'match' => array('controller' => 'walls'),
                    'link' => array('controller' => 'walls', 'action' => 'index', 'admin' => false),
                ),
            ),
            array('id' => 'navigation')
        );
    ?>

As with all other element generating functions in cakephp the last
array is options, passed into the ul element.

Code:

Helper Class:
`````````````

::

    <?php 
    <?php 
    
    class TabHelper extends AppHelper
    {
        var $helpers = array('Html');
        
        /**
         * Returns a UL list with li and a, soan tags
         *
         * @param array $data
         * array(
         *      'TabName' => array(
         *          'match' => array('controller' => 'Controller', 'action' => 'Action'),
         *          'link'  => array('controller' => 'Controller', 'action' => 'Action', 'Param', 'Param', 'Param'),
         *      ),
         * );
         * @param array $options 
         * @return text
         * @author Henrik
         */
        function tabs($data, $ulOptions = array()) {
            $out = array();
            $points = array();
            $here = Router::parse($this->here);
            $checks = array('controller', 'action');
            
            //normalize urls
            foreach($data as $name => $options) {
                $points[$name] = 0;
                
                if (!isset($options['match'])) {
                    continue;
                }
                
                $url = Router::parse(Router::normalize($options['match']));
                
                foreach($checks as $check) {
                    if ($url[$check] == $here[$check]) {
                        $points[$name]++;
                    } else {
                        continue 2;
                    }
                }
                
                foreach($url['pass'] as $key => $value) {
                    if (isset($here['pass'][$key]) && $value == $here['pass'][$key]) {
                        $points[$name]++;
                    }
                }
            }
            
            arsort($points);
            $activeKey = array_shift(array_flip($points));
            
            foreach($data as $name => $options) {
                $link = $options['link'];
                $out[] = $this->Html->tag('li', $this->Html->link($this->Html->tag('span', $name), $link, array(), null, false), ife($name == $activeKey, array('class' => 'active')));
            }
            
            return $this->Html->tag('ul', join("\n", $out), $ulOptions);
        }
    }
    
    ?>
    ?>



.. author:: Henrik
.. categories:: articles, helpers
.. tags:: helper,menu,tabs,tab,peytz,henrik,Helpers

