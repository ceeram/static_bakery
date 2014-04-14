AJAX star rating plugin
=======================

by schneimi on August 19, 2010

In the days of social networks, you often want to give users the
possibility to rate things and to show the average rating. The most
common user interface implementation is a star rating system. This
plugin offers you an easy, customizable way to enable your users to
star rate any CakePHP model you want.


Features
~~~~~~~~

+ Multi user, multi model rating
+ Guest rating
+ Just one element in your views
+ Seamless integration with AJAX
+ Prototype and jQuery support
+ Cross browser compatibility
+ Fallback for disabled javascript
+ Various configurations



Requirements
~~~~~~~~~~~~

+ CakePHP 1.2 or 1.3
+ Prototype or jQuery framework
+ User id stored in session for secure rating



Demonstration
~~~~~~~~~~~~~
A demo can be tested and downloaded at
`http://ratingdemo.schneimi.spacequadrat.de/`_



Download
~~~~~~~~
Latest version for CakePHP 1.2:
`http://www.wuala.com/mystic11/public/rating2.3.zip`_

Latest version for CakePHP 1.3:
`http://www.wuala.com/mystic11/public/rating2.4.zip`_



Installation and Use
~~~~~~~~~~~~~~~~~~~~

1) Make sure you meet the requirements above. For the download and
integration of a javascript framework, please visit
`http://www.prototypejs.org/`_ for Prototype and `http://jquery.com/`_
for jQuery.

2) Extract the plugin, including the subfolder rating, to your app
plugins folder app/plugins.

3) Copy the rating/config/plugin_rating.php to your app configs folder
app/config and change the settings to your desire. It is recommended
to let Rating.showHelp set to true until everything works.

4) Apply the install.sql to your database to create the ratings table.

::

    
    CREATE TABLE `ratings` (
      `id` int(11) unsigned NOT NULL auto_increment,
      `user_id` char(36) NOT NULL default '',
      `model_id` char(36) NOT NULL default '',  
      `model` varchar(100) NOT NULL default '',
      `rating` tinyint(2) unsigned NOT NULL default '0',
      `name` varchar(100) default '',
      `created` datetime default NULL,
      `modified` datetime default NULL,
      PRIMARY KEY (`id`),
      KEY `rating` (`model_id`,`model`,`rating`,`name`)
    );


5) Load the plugin javascript and css files into your layout. Replace
[your_framework] with prototype_min or jquery_min depending on the
framework you use.

View Template:
``````````````

::

    
    <?php
      echo $javascript->link('/rating/js/rating_[your_framework]');
      echo $html->css('/rating/css/rating');
    ?>



6) For full model integration in your app, apply the following
relation to your models. (replace [name_of_your_model])

Model Class:
````````````

::

    <?php 
    var $hasMany = array('Rating' => 
                         array('className'   => 'Rating',
                               'foreignKey'  => 'model_id',
                               'conditions' => array('Rating.model' => '[name_of_your_model]'),
                               'dependent'   => true,
                               'exclusive'   => true
                   )
    );
    ?>



7) If you set Rating.saveToModel to true, then add the defined
Rating.modelAverageField and Rating.modelVotesField to all models you
want to rate. To do that you can use the following SQL statements
(replace [your_table], [Rating.modelAverageField],
[Rating.modelVotesField]).

::

    
    ALTER TABLE [your_table] ADD (`[Rating.modelAverageField]` decimal(3,1) unsigned default '0.0');
    ALTER TABLE [your_table] ADD (`[Rating.modelVotesField]` int(11) unsigned default '0');

If the plugin shows the fields are still missing, try to clear the
model cache of your app at app/tmp/cache/models.

8) You can change the styles of the rating element in the css file
rating/vendors/css/rating.css.

9) Finally you can place the rating element in your views as follows.
(replace [name_of_your_model] and [id_of_your_model])


Default rating element for one model id
+++++++++++++++++++++++++++++++++++++++

View Template:
``````````````

::

    
    <?php
    echo $this->element('rating', array('plugin' => 'rating',
                                        'model' => '[name_of_your_model]',
                                        'id' => [id_of_your_model]));
    ?>



Default rating element for one model id
+++++++++++++++++++++++++++++++++++++++
If you want to have different ratings for one model id like sound and
picture of a movie, you can use the additional name parameter.


View Template:
``````````````

::

    
    <?php
      echo $this->element('rating', array('plugin' => 'rating',
                                          'model' => '[name_of_your_model]',
                                          'id' => [id_of_your_model],
                                          'name' => 'sound'));
    
      echo $this->element('rating', array('plugin' => 'rating',
                                          'model' => '[name_of_your_model]',
                                          'id' => [id_of_your_model],
                                          'name' => 'picture'));
    ?>



Individual configuration of a rating element
++++++++++++++++++++++++++++++++++++++++++++
Sometimes you want to use more than one style of rating elements in
your app. That can be reached with the 'config' parameter and
different config files in 'app/config'. Just clone the default
'plugin_rating.php' and give it a different name, which you then pass
to the element.

View Template:
``````````````

::

    
      // uses 'plugin_rating.php' in 'app/config'
      echo $this->element('rating', array('plugin' => 'rating', 
                                          'model' => '[name_of_your_model]',
                                          'id' => [id_of_your_model]));
      
      // uses 'plugin_rating_style1.php' in 'app/config'
      echo $this->element('rating', array('plugin' => 'rating',
                                          'model' => '[name_of_your_model]',
                                          'id' => [id_of_your_model],
                                          'config' => 'plugin_rating_style1'));


rating/config/plugin_rating.php

::

    
    <?php
    /**
     * Config file for the AJAX star rating plugin.
     *
     * @author Michael Schneidt <michael.schneidt@arcor.de>
     * @copyright Copyright 2009, Michael Schneidt
     * @license http://www.opensource.org/licenses/mit-license.php
     * @link http://bakery.cakephp.org/articles/view/ajax-star-rating-plugin-1
     * @version 2.4
     */
    
    /**
     * Disable the user rating.
     */
    $config['Rating.disable'] = false;
    
    /**
     * Show errors and warnings that should help to setup the plugin.
     */
    $config['Rating.showHelp'] = true;
    
    /**
     * CakePHP app root.
     * 
     * If you access your app like http://yourdomain/mycake then /mycake/ is your app root.
     */
    $config['Rating.appRoot'] = '';
    
    /**
     * Show a flash message after rating.
     * 
     * (displays 'Rating.flashMessage')
     */
    $config['Rating.flash'] = false;
    
    /**
     * Message shown on flash.
     * 
     * (depends on 'Rating.flash')
     */
    $config['Rating.flashMessage'] = __('Your rating has been saved.', true);
    
    /**
     * Enable fallback for disabled javascript.
     * 
     * (this inserts additional html code)
     */
    $config['Rating.fallback'] = true;
    
    /**
     * Show flash message on fallback save redirect.
     * 
     * (displays 'Rating.flashMessage')
     */
    $config['Rating.fallbackFlash'] = false;
    
    /**
     * User id location in the session data.
     */
    $config['Rating.sessionUserId'] = 'User.id';
    
    /**
     * Enable Guest rating. (ignores 'Rating.sessionUserId')
     * 
     * Guest access is stored in cookie to prevent multiple ratings (not secure!)
     */
    $config['Rating.guest'] = false;
    
    /**
     * Guest cookie duration time. (interpreted with strtotime())
     */
    $config['Rating.guestDuration'] = '1 week';
    
    /**
     * Maximum rating.
     */
    $config['Rating.maxRating'] = 5;
    
    /**
     * Location of the full star image.
     */
    $config['Rating.starFullImageLocation'] = 'rating/img/star-full.png';
    
    /**
     * Location of the empty star image.
     */
    $config['Rating.starEmptyImageLocation'] = 'rating/img/star-empty.png';
    
    /**
     * Location of the half star image.
     */
    $config['Rating.starHalfImageLocation'] = 'rating/img/star-half.png';
    
    /**
     * Save the average rating and vote count to the rated model.
     * 
     * This may speed up loading, because the values must not be
     * calculated from the ratings on every access. This is also 
     * helpful if you want to sort the model by rating data, e.g. 
     * using pagination sort.
     * 
     * This config only works, if you use no more than one rating 
     * element (name parameter) for each model id and no different 
     * config files (config parameter) with same field names set.
     * 
     * If set to true, you have to add the 'Rating.modelAverageField' 
     * and 'Rating.modelVotesField' to your rated models.
     */
    $config['Rating.saveToModel'] = false;
    
    /**
     * Field name in models for the average rating.
     * 
     * SQL: ALTER TABLE <model_table> ADD <Rating.modelAverageField> decimal(3,1) unsigned default '0.0';
     * 
     * (depends on 'Rating.saveToModel')
     */
    $config['Rating.modelAverageField'] = 'rating';
    
    /**
     * Field name in models for the rating votes.
     * 
     * SQL: ALTER TABLE <model_table> ADD <Rating.modelVotesField> int(11) unsigned default '0';
     * 
     * (depends on 'Rating.saveToModel')
     */
    $config['Rating.modelVotesField'] = 'votes';
    
    /**
     * Allow users to change their ratings.
     */
    $config['Rating.allowChange'] = true;
    
    /**
     * Allow users to delete their ratings by 
     * deselecting the current rating.
     * 
     * (depends on 'Rating.allowChange')
     */
    $config['Rating.allowDelete'] = true;
    
    /**
     * Display the user rating in stars instead of the average rating.
     */
    $config['Rating.showUserRatingStars'] = false;
    
    /**
     * Show a mark to indicate the user rating.
     *  
     * (change mark in /vendors/css/rating.css .rating-user)
     */
    $config['Rating.showUserRatingMark'] = true;
    
    /**
     * Define the text beside the stars.
     * 
     * %AVG% Average rating
     * %MAX% Maximum rating
     * %VOTES% Number of votes
     * %RATING% User rating
     */
    $config['Rating.statusText'] = '%AVG% / %MAX%  (%VOTES%)';
    
    /**
     * Show 'Rating.mouseOverMessages' on mouseover.
     */
    $config['Rating.showMouseOverMessages'] = true;
    
    /**
     * Messages that are showing on mouseover.
     *  
     * If you want to put links into the messages like for login, you have
     * to do that manually, because the CakePHP helpers don't work here yet.
     * 
     * 'login' this message appears if the user is not signed in.
     * 'rated' this message appears if the user rated already.
     * 'delete' this message appears if the user mouseovers his rating and 'Rating.allowDelete' is set true.
     * '1' to 'Rating.maxRating' represent the different rating values.
     * 
     * (depends on 'Rating.showMouseOverMessages')
     */
    $config['Rating.mouseOverMessages'] = array('login' => __('Please login to rate', true),
                                                'rated' => __('Thanks for your rating', true),
                                                'delete' => __('Click to remove your rating', true),
                                                '1' => __('Really bad', true),
                                                '2' => __('Bad', true),
                                                '3' => __('Average', true),
                                                '4' => __('Good', true),
                                                '5' => __('Really good', true));
    ?>


rating/models/ratings.php

Model Class:
````````````

::

    <?php 
    /**
     * Model for the AJAX star rating plugin.
     *
     * @author Michael Schneidt <michael.schneidt@arcor.de>
     * @copyright Copyright 2009, Michael Schneidt
     * @license http://www.opensource.org/licenses/mit-license.php
     * @link http://bakery.cakephp.org/articles/view/ajax-star-rating-plugin-1
     * @version 2.4
     */
    class Rating extends Model {
      var $name = 'Rating';
      
      var $validate = array('user_id' => array('rule' => array('maxLength', 36),
                                               'required' => true),
                            'model_id' => array('rule' => array('maxLength', 36),
                                                'required' => true),
                            'model' => array('rule' => 'alphaNumeric',
                                             'required' => true));
    }
    ?>


rating/views/ratings/view.ctp

View Template:
``````````````

::

    
    <?php
    /**
     * View for the AJAX star rating plugin.
     *
     * @author Michael Schneidt <michael.schneidt@arcor.de>
     * @copyright Copyright 2009, Michael Schneidt
     * @license http://www.opensource.org/licenses/mit-license.php
     * @link http://bakery.cakephp.org/articles/view/ajax-star-rating-plugin-1
     * @version 2.4
     */
    ?>
     
    <?php
      // decision to enable or disable the rating
      $enable = ($session->check(Configure::read('Rating.sessionUserId')) // logged in user or guest
                   || (Configure::read('Rating.guest') && $session->check('Rating.guest_id')))
                 && !Configure::read('Rating.disable') // plugin is enabled
                 && (Configure::read('Rating.allowChange') // changing is allowed or it's the first rating
                     || (!Configure::read('Rating.allowChange') && $data['%RATING%'] == 0));
    
      // the images are displayed here before js initialization to avoid flickering.
      echo $rating->stars($model, $id, $data, $options, $enable);
      
      // format the statusText and write it back
      $text = $rating->format(Configure::read('Rating.statusText'), $data);
      Configure::write('Rating.statusText', $text);
    ?>
    
    <div id="<?php echo $model.'_rating_'.$options['name'].'_'.$id.'_text'; ?>" class="<?php echo !empty($text) ? 'rating-text' : 'rating-notext'; ?>">
      <?php
        echo $text;
      ?>
    </div>
    
    <?php
      // initialize the rating element
      if (!Configure::read('Rating.disable')) {
        echo $javascript->codeBlock("ratingInit('".$model.'_rating_'.$options['name'].'_'.$id."', "
                                               ."'".addslashes(json_encode($data))."'," 
                                               ."'".addslashes(json_encode(Configure::read('Rating')))."',"
                                               ."'".addslashes(json_encode($options))."',"
                                               .intval($enable).");");
      }
    ?>
    
    <?php if (Configure::read('Rating.fallback')): ?>
    <noscript>
      <div class="fallback">
        <?php
          if ($enable) {
            // show fallback form
            echo $form->create('Rating', 
                               array('type' => 'get',
                                     'url' => array('action' => 'save')));
            echo $form->radio('value',
                              $rating->options(), 
                              array('legend' => false,
                                    'id' => $model.'_rating_'.$options['name'].'_'.$id,
                                    'value' => $data['%RATING%']));
            echo $form->hidden('model', array('value' => $model));
            echo $form->hidden('rating', array('value' => $id));
            echo $form->hidden('name', array('value' => $options['name']));
            echo $form->hidden('config', array('value' => $options['config']));
            echo $form->hidden('fallback', array('value' => true));
            echo $form->submit(__('Vote', true),
                               array('div' => false,
                                     'title' => __('Vote', true)));
            
            echo $form->end();
          }
        ?>
      </div>
      
      <?php
        // get mouseover messages for showing
        $mouseOverMessages = Configure::read('Rating.mouseOverMessages');
      ?>
      
      <?php // show login message
            if (!$enable && Configure::read('Rating.showMouseOverMessages')
                && !empty($mouseOverMessages['login'])
                && !Configure::read('Rating.disable')
                && $data['%RATING%'] == 0): ?>
        <div id="<?php echo $model.'_rating_'.$options['name'].'_'.$id.'_text'; ?>" class="<?php echo !empty($text) ? 'rating-text' : 'rating-notext'; ?>">
          <?php
            echo $mouseOverMessages['login'];
          ?>
        </div>
      <?php endif; ?>
      
      <?php // show rated message
            if (!$enable && Configure::read('Rating.showMouseOverMessages')
                && !empty($mouseOverMessages['rated'])
                && $data['%RATING%'] > 0): ?>
        <div id="<?php echo $model.'_rating_'.$options['name'].'_'.$id.'_text'; ?>" class="<?php echo !empty($text) ? 'rating-text' : 'rating-notext'; ?>">
          <?php
            echo $mouseOverMessages['rated'];
          ?>
        </div>
      <?php endif; ?>
    </noscript>
    <?php endif; ?>
    
    <?php
      // show flash message
      if (Configure::read('Rating.flash')) {
        $session->flash('rating');
      }
      
      // debug sql dump
      echo $this->element('sql_dump');
    ?>

rating/views/elements/rating.ctp

View Template:
``````````````

::

    
    <?php
    /**
     * Element for the AJAX star rating plugin.
     *
     * @author Michael Schneidt <michael.schneidt@arcor.de>
     * @copyright Copyright 2009, Michael Schneidt
     * @license http://www.opensource.org/licenses/mit-license.php
     * @link http://bakery.cakephp.org/articles/view/ajax-star-rating-plugin-1
     * @version 2.4
     */
    ?>
    
    <?php
      // default name
      if (empty($name)) {
        $name = 'default';
      }
      
      // default config
      if (empty($config)) {
        $config = 'plugin_rating';
      }
    ?>
    
    <div id="<?php echo $model.'_rating_'.$name.'_'.$id; ?>" class="rating">
      <?php
        echo $this->requestAction('rating/ratings/view/'.$model.'/'.$id.'/'.base64_encode(json_encode(array('name' => $name, 'config' => $config))), array('return'));
      ?>
    </div>

rating/views/helpers/rating.php

::

    
    <?php
    /**
     * Helper for the AJAX star rating plugin.
     *
     * @author Michael Schneidt <michael.schneidt@arcor.de>
     * @copyright Copyright 2009, Michael Schneidt
     * @license http://www.opensource.org/licenses/mit-license.php
     * @link http://bakery.cakephp.org/articles/view/ajax-star-rating-plugin-1
     * @version 2.4
     */
    class RatingHelper extends AppHelper {
      var $helpers = array('Html', 'Form', 'Session');
    
      /**
       * Creates the stars for a rating.
       *
       * @param string $model Model name
       * @param integer $id Model id
       * @param array $data Rating data
       * @param array $options Options
       * @param boolean $enable Enable element
       * @return Stars as HTML images
       */
      function stars($model, $id, $data, $options, $enable) {
        $output = '';
        $starImage = Configure::read('Rating.starEmptyImageLocation');
        
        if (Configure::read('Rating.showUserRatingStars')) {
          $stars = $data['%RATING%'];
        } else {
          $stars = $data['%AVG%'];
        }
        
        for ($i = 1; $i <= $data['%MAX%']; $i++) {
          if ($i <= floor($stars)) {
            $starImage = Configure::read('Rating.starFullImageLocation');
          } else if ($i == floor($stars) + 1 && preg_match('/[0-9]\.[5-9]/', $stars)) {
            $starImage = Configure::read('Rating.starHalfImageLocation');
          } else {
            $starImage = Configure::read('Rating.starEmptyImageLocation');
          }
          
          if (Configure::read('Rating.showUserRatingMark') && $i <= $data['%RATING%']) {
            $class = 'rating-user';
          } else {
            $class = 'rating';
          }
          
          if (!$enable) {
            $class .= '-disabled';
          }
          
          $htmlImage = $this->Html->image('/'.$starImage, 
                                          array('class' => $class,
                                                'id' => $model.'_rating_'.$options['name'].'_'.$id.'_'.$i,
                                                'alt' => __('Rate it with ', true).$i));
    
          if (Configure::read('Rating.fallback')) {
            $output .= $this->Form->label($model.'.rating', 
                                          $htmlImage, 
                                          array('for' => $model.'Rating'.ucfirst($options['name']).$id.$i,
                                                'class' => 'fallback'));
          } else {
            $output .= $htmlImage;
          }
        }
    
        return $output;
      }
      
      /**
       * Formats a text in replacing data wildcards.
       *
       * @param string $text
       * @param array $data
       * @return Formatted text
       */
      function format($text, $data) {
        foreach ($data as $wildcard => $value) {
          $text = str_replace($wildcard, $value, $text);
        }
        
        // fix lost blanks in js (excluding blanks between html tags)
        $text = preg_replace('/(?!(?:[^<]+>|[^>]+<\/(.*)>))( )/', ' ', $text);
        
        return $text;
      }
      
      /**
       * Creates options for fallback radio buttons.
       * 
       * @return Radio options
       */
      function options() {
        $options = array();
        
        if (Configure::read('Rating.showMouseOverMessages')) {
          $options = Configure::read('Rating.mouseOverMessages');
          unset($options['login'], $options['rated'], $options['delete']);
        } else {
          $options = range(0, Configure::read('Rating.maxRating'));
          unset($options[0]);
        }
        
        return $options;
      }
    }
    ?>


rating/controllers/ratings_controller.php

Controller Class:
`````````````````

::

    <?php 
    /**
     * Controller for the AJAX star rating plugin.
     *
     * @author Michael Schneidt <michael.schneidt@arcor.de>
     * @copyright Copyright 2009, Michael Schneidt
     * @license http://www.opensource.org/licenses/mit-license.php
     * @link http://bakery.cakephp.org/articles/view/ajax-star-rating-plugin-1
     * @version 2.4
     */
    class RatingsController extends RatingAppController {
      /**
       * Renders the content for the rating element.
       *
       * @param string $model Name of the model
       * @param integer $id Id of the model
       * @param string $options JSON/BASE64 encoded options
       */
      function view($model = '', $id = 0, $options = '') {
        $this->layout = null;
        
        $userRating = null;
        $avgRating = null;
        $votes = null;
        $modelInstance = ClassRegistry::init($model);
        $optionsData = json_decode(base64_decode($options), true);
        
        $name = $optionsData['name'];
        $config = $optionsData['config'];    
        
        // load the config file
        $this->__loadConfig($config);
        
        // setup guest access
        if (Configure::read('Rating.guest') 
            && !$this->Session->check(Configure::read('Rating.sessionUserId'))) {
          $this->__setupGuest();
        }
        
        // check if user id exists in session
        if (Configure::read('Rating.showHelp') 
            && !Configure::read('Rating.guest') 
            && (!$this->Session->check(Configure::read('Rating.sessionUserId')) 
                || !$this->Session->read(Configure::read('Rating.sessionUserId')) > 0)) {
          echo '<p>Warning: No valid user id was found at "'.Configure::read('Rating.sessionUserId').'" in the session.</p>';
        }
        
        // check if model id exists
        $modelInstance->id = $id;
        
        if (Configure::read('Rating.showHelp') && !$modelInstance->exists(true)) {
          echo '<p>Error: The model_id "'.$id.'" of "'.$model.'" does not exist.</p>';
        }
    
        // choose between user id and guest id
        if (!$this->Session->read(Configure::read('Rating.sessionUserId')) 
            && (Configure::read('Rating.guest') && $this->Session->read('Rating.guest_id'))) {
          $userId = $this->Session->read('Rating.guest_id');
        } else {
          $userId = $this->Session->read(Configure::read('Rating.sessionUserId'));
        }
    
        if (!empty($userId)) {
          $userRating = $this->Rating->field('rating',
                                             array('model' => $model, 
                                                   'model_id' => $id, 
                                                   'user_id' => $userId,
                                                   'name' => $name));
        }
    
        if (empty($userRating)) {
          $userRating = 0;
        }
        
        // retrieve rating values from model or calculate them
        if (Configure::read('Rating.saveToModel')) {
          if (Configure::read('Rating.showHelp') 
              && !$modelInstance->hasField(Configure::read('Rating.modelAverageField'))) {
            echo '<p>Error: The average field "'.Configure::read('Rating.modelAverageField').'" in the model "'.$model.'" does not exist.</p>';
          }
          
          if (Configure::read('Rating.showHelp') 
              && !$modelInstance->hasField(Configure::read('Rating.modelVotesField'))) {
            echo '<p>Error: The votes field "'.Configure::read('Rating.modelVotesField').'" in the model "'.$model.'" does not exist.</p>';
          }
          
          $values = $modelInstance->find(array($modelInstance->name.".".$modelInstance->primaryKey => $id),
                                         array(Configure::read('Rating.modelAverageField'), 
                                               Configure::read('Rating.modelVotesField')),
                                         null,
                                         -1);
          
          $avgRating = $values[$modelInstance->name][Configure::read('Rating.modelAverageField')];
          $votes = $values[$modelInstance->name][Configure::read('Rating.modelVotesField')];
        } else {
          $values = $this->Rating->find(array('model' => $model,
                                              'model_id' => $id,
                                              'name' => $name),
                                        array('AVG(Rating.rating)', 'COUNT(*)'));
          
          $avgRating = round($values[0]['AVG(`Rating`.`rating`)'], 1);
          $votes = $values[0]['COUNT(*)'];
        }
        
        if (empty($votes)) {
          $votes = 0;
        }
        
        if ($avgRating && !strpos($avgRating, '.')) {
          $avgRating = $avgRating.'.0';
        } else if (!$avgRating) {
          $avgRating = '0.0';
        }
    
        $this->set('id', $id);
        $this->set('model', $model);
        $this->set('config', $config);
        $this->set('options', $optionsData);
        $this->set('data', array('%VOTES%' => $votes.' '.__n('vote', 'votes', $votes, true), 
                                 '%RATING%' => $userRating, 
                                 '%AVG%' => $avgRating,
                                 '%MAX%' => Configure::read('Rating.maxRating')));
        $this->render('view');
      }
      
      /**
       * Saves the user selected rating value. Depending on the plugin 
       * configuration, it also updates or deletes the rating.
       *
       * @param string $model Name of the model
       * @param integer $id Id of the model
       * @param integer $value User rating value
       */
      function save($model = '', $id = 0, $value = 0) {
        $this->layout = null;
        $saved = false;
        $fallback = false;
        $referer = Controller::referer();
        
        $name = $this->params['url']['name'];
        $config = $this->params['url']['config'];
        
        // load the config file
        $this->__loadConfig($config);
        
        // data from fallback form
        if (isset($this->params['url']['fallback']) 
            && $this->params['url']['fallback']) {
          $fallback = true;
          
          $model = $this->params['url']['model'];
          $id = $this->params['url']['rating'];
          $value = $this->params['url']['value'];
        }
    
        // check if model id exists
        $modelInstance = ClassRegistry::init($model);
        $modelInstance->id = $id;
        
        if (!$modelInstance->exists(true)) {
          if (!$fallback) {
            $this->view($model, $id, base64_encode(json_encode(array('name' => $name, 'config' => $config))));
          } else {
            $this->redirect($referer);
          }
          
          return;
        }
        
        // choose between user and guest id
        if (Configure::read('Rating.guest') && $this->Session->read('Rating.guest_id')) {
          $userId = $this->Session->read('Rating.guest_id');
        } else {
          $userId = $this->Session->read(Configure::read('Rating.sessionUserId'));
        }
        
        // check if a rating already exists 
        $userRating = $this->Rating->find(array('model' => $model, 
                                                'model_id' => $id, 
                                                'user_id' => $userId,
                                                'name' => $name));
        
        // save, update or delete rating
        if (!empty($userRating) && Configure::read('Rating.allowChange')) {
          $this->Rating->id = $userRating['Rating']['id'];
          
          if ($userRating['Rating']['rating'] == $value && Configure::read('Rating.allowDelete')) {
            $this->Rating->delete($userRating['Rating']['id']);
            $saved = true;
          } else {
            $saved = $this->Rating->saveField('rating', $value);
          }
        } else if (empty($userRating) && $userId) {
          $this->data['Rating']['rating'] = $value;
          $this->data['Rating']['model'] = $model;
          $this->data['Rating']['model_id'] = $id;
          $this->data['Rating']['user_id'] = $userId;
          $this->data['Rating']['name'] = $name;
          
          $this->Rating->create();
          $saved = $this->Rating->save($this->data);
        }
        
           
        // set flash message
        if ($saved && Configure::read('Rating.flash')) {
          $this->Session->setFlash(Configure::read('Rating.flashMessage'), 
                                   'default', 
                                   array('class' => 'rating-flash'),
                                   'rating');
        }    
        
        // save rating values to model
        if ($saved && Configure::read('Rating.saveToModel')) {
          // check if fields exist in model
          if (!$modelInstance->hasField(Configure::read('Rating.modelAverageField')) 
              && !$modelInstance->hasField(Configure::read('Rating.modelVotesField'))) {
            if (!$fallback) {
              $this->view($model, $id, base64_encode(json_encode(array('name' => $name, 'config' => $config))));
            } else {
              $this->redirect($referer);
            }
            
            return;
          }
          
          // retrieve actual rating values 
          $values = $this->Rating->find(array('model' => $model,
                                              'model_id' => $id,
                                              'name' => $name),
                                        array('AVG(Rating.rating)', 'COUNT(*)'));
    
          $avgRating = round($values[0]['AVG(`Rating`.`rating`)'], 1);
          $votes = $values[0]['COUNT(*)'];
          
          if ($avgRating && !strpos($avgRating, '.')) {
            $avgRating = $avgRating.'.0';
          } else if (!$avgRating) {
            $avgRating = '0.0';
          }
    
          if (empty($votes)) {
            $votes = '0';
          }
          
          $modelInstance->id = $id;
          
          // save rating values
          if ($modelInstance->exists()) {
            $modelInstance->saveField(Configure::read('Rating.modelAverageField'), $avgRating);
            $modelInstance->saveField(Configure::read('Rating.modelVotesField'), $votes);
          }
        }
        
        // show view again
        if (!$fallback) {
          $this->view($model, $id, base64_encode(json_encode(array('name' => $name, 'config' => $config))));
        } else {
          if ($saved && Configure::read('Rating.fallbackFlash')) {
            $this->flash(Configure::read('Rating.flashMessage'), Controller::referer());
            $this->Session->setFlash(null);
          } else {
            $this->redirect($referer);
          }
        }
        
        $this->autoRender = false;
      }
      
      /**
       * Loads a config file.
       * 
       * @param $file Name of the config file
       */
      function __loadConfig($file) {
        // still support config values of v2.3 elements
        if (count(explode('.', $file)) > 0) {
          $file = str_replace('.', '_', $file);
        }
        
        // load config from app config folder
        if (Configure::load($file) === false) {
          // load config from plugin config folder
          if (Configure::load('rating.'.$file) === false) {
            echo '<p>Error: The '.$file.'.php could not be found in your app/config or app/plugins/rating/config folder. Please create it from the default rating/config/plugin_rating.php.</p>';
          }
        }
      }
      
      /**
       * Setup the guest id in session and cookie.
       */
      function __setupGuest() {
        if (!$this->Session->check('Rating.guest_id') 
            && !$this->Cookie->read('Rating.guest_id')) {
          App::import('Core', 'String');
          $uuid = String::uuid();
    
          $this->Session->write('Rating.guest_id', $uuid);
          $this->Cookie->write('Rating.guest_id', $uuid, false, Configure::read('Rating.guestDuration'));
        } else if (Configure::read('Rating.guest') 
                   && $this->Cookie->read('Rating.guest_id')) {
          $this->Session->write('Rating.guest_id', $this->Cookie->read('Rating.guest_id'));
        }
      }
    }
    ?>

rating/rating_app_controller.php

Controller Class:
`````````````````

::

    <?php 
    /**
     * AppController for the AJAX star rating plugin.
     *
     * @author Michael Schneidt <michael.schneidt@arcor.de>
     * @copyright Copyright 2009, Michael Schneidt
     * @license http://www.opensource.org/licenses/mit-license.php
     * @link http://bakery.cakephp.org/articles/view/ajax-star-rating-plugin-1
     * @version 2.4
     */ 
    class RatingAppController extends AppController {
      var $uses = array('rating.Rating');
      var $helpers = array('Javascript', 'rating.Rating');
      var $components = array('Cookie', 'Session');
    }
    ?>


rating/vendors/js/rating_jquery.js

::

    
    /**
     * jQuery javascript for the CakePHP AJAX star rating plugin.
     *
     * @author Michael Schneidt <michael.schneidt@arcor.de>
     * @copyright Copyright 2009, Michael Schneidt
     * @license http://www.opensource.org/licenses/mit-license.php
     * @link http://bakery.cakephp.org/articles/view/ajax-star-rating-plugin-1
     * @version 2.4
     */
    
    /**
     * Holds the settings for all rating elements.
     */
    var ratingSettings = new Array();
    
    /**
     * Initializes the rating element.
     *
     * @param element Id of the rating element
     * @param data JSON encoded rating data
     * @param config JSON encoded plugin configurations
     * @param options JSON encoded rating options
     * @param enabled Enable the rating for a user
     */
    function ratingInit(element, data, config, options, enabled) {
      ratingSettings[element] = new Array();  
      
      ratingSettings[element]['data'] = eval('(' + data + ')');
      ratingSettings[element]['options'] = eval('(' + options + ')');
    	ratingSettings[element]['config'] =  eval('(' + config + ')');
      ratingSettings[element]['enabled'] = enabled;
      
      for (var i = 1; i <= ratingSettings[element]['data']['%MAX%']; i++) {
        $('#' + element + '_' + i).bind('mouseenter', {i: i}, function(e) {
          // workaround against event after reload
          var target = e.relatedTarget || e.toElement;
          
          if (target && target.id != element + '_' + e.data.i) {
            if (ratingSettings[element]['enabled']) {
              ratingSet(element, e.data.i);
            }
            
            if (ratingSettings[element]['config']['showMouseOverMessages']) {
              ratingMessages(element, e.data.i);
            }
          }
        });
        
        if (ratingSettings[element]['enabled']) {
          $('#' + element + '_' + i).bind('click', {i: i}, function(e) {
            ratingSave(element, e.data.i);
          });
        }
      }
    
      $('#' + element).bind('mouseleave', function(e) {
        ratingReset(element)
      });
      
      $('#' + element + '_text').bind('mouseenter', function(e) {
        ratingReset(element);
        
        if (ratingSettings[element]['config']['showMouseOverMessages']) {
          ratingMessages(element);
        }
      });
      
      ratingReset(element);
    }
    
    /**
     * Sets the rating element to a rating value.
     *
     * @param element Name of element
     * @param value Rating value 
     */
    function ratingSet(element, value) {
      var starImg = ratingSettings[element]['config']['starEmptyImageLocation'];
      
      for (i = 1; i <= ratingSettings[element]['data']['%MAX%']; i++) {
        if (i <= Math.floor(value)) {
          starImg = ratingSettings[element]['config']['starFullImageLocation'];
        } else if (i == Math.floor(value) + 1 && value.toString().match(/[0-9]\.[5-9]/)) {
          starImg = ratingSettings[element]['config']['starHalfImageLocation'];
        } else {
          starImg = ratingSettings[element]['config']['starEmptyImageLocation'];
        }
        
        $('#' + element + '_' + i).attr({'src': ratingSettings[element]['config']['appRoot'] + starImg});
        
        // set user mark
        if (ratingSettings[element]['config']['showUserRatingMark'] && i <= ratingSettings[element]['data']['%RATING%']) {
          $('#' + element + '_' + i).attr({'class': 'rating-user'});
        } else {
          $('#' + element + '_' + i).attr({'class': 'rating'});
        }
        
        // disable stars
        if (!ratingSettings[element]['enabled'] && !$('#' + element + '_' + i).attr('class').match(/-disabled/)) {
          $('#' + element + '_' + i).attr({'class': $('#' + element + '_' + i).attr('class') + '-disabled'});
        }    
      }
    }
    
    /**
     * Resets the rating element.
     *
     * @param element Element id
     */
    function ratingReset(element) {
      if (ratingSettings[element]['config']['showUserRatingStars']) {
        ratingSet(element, ratingSettings[element]['data']['%RATING%']);
      } else {
        ratingSet(element, ratingSettings[element]['data']['%AVG%']);
      }
      
      $('#' + element + '_text').html(ratingSettings[element]['config']['statusText']);
    }
    
    /**
     * Does the AJAX call to save the rating and 
     * updates the rating element.
     *
     * @param element Name of element
     * @param value Rating value
     */
    function ratingSave(element, value) {
      data = element.split('_');
      
      if (ratingSettings[element]['enabled']) {
        $.ajax({
          url: ratingSettings[element]['config']['appRoot'] + 'rating/ratings/save/' + data[0] + '/' + data[3] + '/' + value + '?' + Math.floor(Math.random() * 999999),
          async: true,
          data: ratingSettings[element]['options'],
          error: function() {
            //alert('AJAX error');
          },
          beforeSend: function() {
            //$('#loader').show();
          },
          complete: function(XMLHttpRequest) {
            //$('#loader').hide();
            $('#' + element).html(XMLHttpRequest.responseText);
          }
        });
      }
    }
    
    /**
     * Displays the mouseOverMessages.
     *
     * @param element Name of element
     * @param value Rating value
     */
    function ratingMessages(element, value) {
      if (ratingSettings[element]['enabled'] && value > 0) {
        if (value == ratingSettings[element]['data']['%RATING%'] 
            && ratingSettings[element]['config']['allowDelete']
            && ratingSettings[element]['config']['mouseOverMessages']['delete']) {
          $('#' + element + '_text').html(ratingSettings[element]['config']['mouseOverMessages']['delete']);
        } else if (ratingSettings[element]['config']['mouseOverMessages'][value]) {
          $('#' + element + '_text').html(ratingSettings[element]['config']['mouseOverMessages'][value]);
        }
      } else if (!ratingSettings[element]['enabled']
          && !ratingSettings[element]['config']['disable']
          && ratingSettings[element]['data']['%RATING%'] == 0
          && ratingSettings[element]['config']['mouseOverMessages']['login']){
        $('#' + element + '_text').html(ratingSettings[element]['config']['mouseOverMessages']['login']);
      } else if (!ratingSettings[element]['enabled'] 
          && ratingSettings[element]['data']['%RATING%'] > 0
          && ratingSettings[element]['config']['mouseOverMessages']['rated']){
        $('#' + element + '_text').html(ratingSettings[element]['config']['mouseOverMessages']['rated']);
      } else {
        $('#' + element + '_text').html(ratingSettings[element]['config']['statusText']);
      }
    }

rating/vendors/js/rating_prototype.js

::

    
    /**
     * Prototype javascript for the CakePHP AJAX star rating plugin.
     *
     * @author Michael Schneidt <michael.schneidt@arcor.de>
     * @copyright Copyright 2009, Michael Schneidt
     * @license http://www.opensource.org/licenses/mit-license.php
     * @link http://bakery.cakephp.org/articles/view/ajax-star-rating-plugin-1
     * @version 2.4
     */
    
    /**
     * Holds the settings for all rating elements.
     */
    var ratingSettings = new Array();
    
    /**
     * Initializes the rating element.
     *
     * @param element Id of the rating element
     * @param data JSON encoded rating data
     * @param options JSON encoded rating options
     * @param config JSON encoded plugin configurations
     * @param enabled Enable the rating for a user
     */
    function ratingInit(element, data, config, options, enabled) {
    	ratingSettings[element] = new Array();
      
      ratingSettings[element]['data'] = eval('(' + data + ')');
      ratingSettings[element]['options'] = eval('(' + options + ')');
      ratingSettings[element]['config'] =  eval('(' + config + ')');
    	ratingSettings[element]['enabled'] = enabled;
    
      for (var i = 1; i <= ratingSettings[element]['data']['%MAX%']; i++) {
        $(element + '_' + i).observe('mouseover', function(e) {
          var value = this.id.match(/[0-9]*$/);
          
          // workaround against event after reload
          var target = e.relatedTarget || e.toElement;
          
          if (target && target.id != element + '_' + value) {
            if (ratingSettings[element]['enabled']) {
              ratingSet(element, value);
            }
            
            if (ratingSettings[element]['config']['showMouseOverMessages']) {
              ratingMessages(element, value);
            }
          }      
        });
        
        $(element + '_' + i).observe('click', function(e) {
          var value = this.id.match(/[0-9]*$/);
          
          ratingSave(element, value);
        });
      }
      
      $(element).observe('mouseout', function(e) {
        // workaround against mouseout event on child elements like links
        var target = e.relatedTarget || e.toElement;
    
        if (target && target.parentNode && target.parentNode.id != null && !target.parentNode.id.match(element)) {
          ratingReset(element);
        }
      });
      
      $(element + '_text').observe('mouseover', function(e) {
        // workaround against mouseover event on child elements like links
        var target = e.relatedTarget || e.toElement;
        
        if (target && target.parentNode && target.parentNode.id != null && !target.parentNode.id.match(element)) {
          ratingReset(element);
          
          if (ratingSettings[element]['config']['showMouseOverMessages']) {
            ratingMessages(element);
          }
        }
      });
      
      ratingReset(element);
    }
    
    /**
     * Sets the rating element to a rating value.
     *
     * @param element Name of element
     * @param value Rating value 
     */
    function ratingSet(element, value) {
      var starImg = ratingSettings[element]['config']['starEmptyImageLocation'];
      
      for (i = 1; i <= ratingSettings[element]['data']['%MAX%']; i++) {
        if (i <= Math.floor(value)) {
          starImg = ratingSettings[element]['config']['starFullImageLocation'];
        } else if (i == Math.floor(value) + 1 && value.toString().match(/[0-9]\.[5-9]/)) {
          starImg = ratingSettings[element]['config']['starHalfImageLocation'];
        } else {
          starImg = ratingSettings[element]['config']['starEmptyImageLocation'];
        }
        
        $(element + '_' + i).src = ratingSettings[element]['config']['appRoot'] + starImg;
        
        // set user mark
        if (ratingSettings[element]['config']['showUserRatingMark'] && i <= ratingSettings[element]['data']['%RATING%']) {
          $(element + '_' + i).className = 'rating-user';
        } else {
          $(element + '_' + i).className = 'rating';
        }
        
        // disable stars
        if (!ratingSettings[element]['enabled'] && !$(element + '_' + i).className.match(/-disabled/)) {
          $(element + '_' + i).className += '-disabled';
        }    
      }
    }
    
    /**
     * Resets the rating element.
     *
     * @param element Element id
     */
    function ratingReset(element) {
      if (ratingSettings[element]['config']['showUserRatingStars']) {
        ratingSet(element, ratingSettings[element]['data']['%RATING%']);
      } else {
        ratingSet(element, ratingSettings[element]['data']['%AVG%']);
      }
      
      $(element + '_text').update(ratingSettings[element]['config']['statusText']);
    }
    
    /**
     * Does the AJAX call to save the rating and 
     * updates the rating element.
     *
     * @param element Name of element
     * @param value Rating value
     */
    function ratingSave(element, value) {
      data = element.split('_');
      
      if (ratingSettings[element]['enabled']) {
        new Ajax.Updater(element, ratingSettings[element]['config']['appRoot'] + 'rating/ratings/save/' + data[0] + '/' + data[3] + '/' + value + '?' + Math.floor(Math.random() * 999999), {               
          asynchronous: true,
          evalScripts: true,
          method: 'get',
          parameters: ratingSettings[element]['options'],
          onFailure: function error() {
            //alert('AJAX error');
          },
          onLoading: function (request) {
            //Element.show('loader');
          },
          onComplete: function (request, json) {
            //Element.hide('loader');
          }
        });
      }
    }
    
    /**
     * Displays the mouseOverMessages.
     *
     * @param element Name of element
     * @param value Rating value
     */
    function ratingMessages(element, value) {
      if (ratingSettings[element]['enabled'] && value > 0) {
        if (value == ratingSettings[element]['data']['%RATING%'] 
            && ratingSettings[element]['config']['allowDelete'] 
            && ratingSettings[element]['config']['mouseOverMessages']['delete']) {
          $(element + '_text').update(ratingSettings[element]['config']['mouseOverMessages']['delete']);
        } else if (ratingSettings[element]['config']['mouseOverMessages'][value]) {
          $(element + '_text').update(ratingSettings[element]['config']['mouseOverMessages'][value]);
        }
      } else if (!ratingSettings[element]['enabled']
    	    && !ratingSettings[element]['config']['disable']
          && ratingSettings[element]['data']['%RATING%'] == 0
          && ratingSettings[element]['config']['mouseOverMessages']['login']){
        $(element + '_text').update(ratingSettings[element]['config']['mouseOverMessages']['login']);
      } else if (!ratingSettings[element]['enabled'] 
          && ratingSettings[element]['data']['%RATING%'] > 0
          && ratingSettings[element]['config']['mouseOverMessages']['rated']){
        $(element + '_text').update(ratingSettings[element]['config']['mouseOverMessages']['rated']);
      } else {
        $(element + '_text').update(ratingSettings[element]['config']['statusText']);
      }
    }


rating/vendors/css/rating.css

::

    
    /**
     * CSS for the AJAX star rating plugin.
     *
     * @author Michael Schneidt <michael.schneidt@arcor.de>
     * @copyright Copyright 2009, Michael Schneidt
     * @license http://www.opensource.org/licenses/mit-license.php
     * @link http://bakery.cakephp.org/articles/view/ajax-star-rating-plugin-1
     * @version 2.4
     */
     
    div.rating {
      font-size: 8pt;
      white-space: nowrap;
    }
    
    div.rating-text {
      display: inline;
      position: relative;
      top: -4px;
      padding-left: 5px;
      white-space: nowrap;
    }
    
    div.rating-notext {
      display: none;
    }
    
    img.rating {
      cursor: pointer;
      border-bottom: 2px solid transparent;
    }
    
    img.rating-disabled {
      cursor: default;
      border-bottom: 2px solid transparent;
    }
    
    img.rating-user {
      cursor: pointer;
      border-bottom: 2px solid #dddddd;
    }
    
    img.rating-user-disabled {
      cursor: default;  
      border-bottom: 2px solid #dddddd;
    }
    
    div.fallback {
      display: inline;
    }
    
    div.fallback form {
      display: inline;
    }
    
    div.fallback label {
      display: inline;
      position: relative;
      top: -4px;
    }
    
    div.rating label.fallback {
      display: inline;
    }
    
    div.fallback input[type="radio"] {
    
    }
    
    div.fallback input[type="submit"] {
      position: relative;
      top: -4px;
      left: 5px;
    }
    
    div.rating-flash {
      display: inline;
      position: relative;
      top: -4px;
      padding-left: 5px;
      white-space: nowrap;
    }


content

asdfasdf
`1`_|`2`_|`3`_|`4`_|`5`_|`6`_|`7`_|`8`_|`9`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_
+ `Page 4`_
+ `Page 5`_
+ `Page 6`_
+ `Page 7`_
+ `Page 8`_
+ `Page 9`_

.. _http://jquery.com/: http://jquery.com/
.. _Page 6: :///articles/view/4caea0e5-8cf8-4a82-82ee-458d82f0cb67/lang:eng#page-6
.. _http://www.wuala.com/mystic11/public/rating2.3.zip: http://www.wuala.com/mystic11/public/rating2.3.zip
.. _Page 5: :///articles/view/4caea0e5-8cf8-4a82-82ee-458d82f0cb67/lang:eng#page-5
.. _Page 2: :///articles/view/4caea0e5-8cf8-4a82-82ee-458d82f0cb67/lang:eng#page-2
.. _Page 3: :///articles/view/4caea0e5-8cf8-4a82-82ee-458d82f0cb67/lang:eng#page-3
.. _Page 7: :///articles/view/4caea0e5-8cf8-4a82-82ee-458d82f0cb67/lang:eng#page-7
.. _Page 1: :///articles/view/4caea0e5-8cf8-4a82-82ee-458d82f0cb67/lang:eng#page-1
.. _http://www.prototypejs.org/: http://www.prototypejs.org/
.. _Page 4: :///articles/view/4caea0e5-8cf8-4a82-82ee-458d82f0cb67/lang:eng#page-4
.. _Page 8: :///articles/view/4caea0e5-8cf8-4a82-82ee-458d82f0cb67/lang:eng#page-8
.. _Page 9: :///articles/view/4caea0e5-8cf8-4a82-82ee-458d82f0cb67/lang:eng#page-9
.. _http://www.wuala.com/mystic11/public/rating2.4.zip: http://www.wuala.com/mystic11/public/rating2.4.zip
.. _http://ratingdemo.schneimi.spacequadrat.de/: http://ratingdemo.schneimi.spacequadrat.de/
.. meta::
    :title: AJAX star rating plugin
    :description: CakePHP Article related to AJAX,prototype,jquery,star,rating,Plugins
    :keywords: AJAX,prototype,jquery,star,rating,Plugins
    :copyright: Copyright 2010 schneimi
    :category: plugins

