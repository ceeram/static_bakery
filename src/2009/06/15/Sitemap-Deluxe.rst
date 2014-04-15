Sitemap Deluxe
==============

by CristianDeluxe on June 15, 2009

A nice and easy way for automatically generate your sitemaps. Main
features: generate html & xml sitemap, send your sitemap to search
engines (Google, Yahoo, Ask, Bing), auto generated robot.txt, support
for dynamics and statics sections...
First sorry for my bad English...

Hereâ€™s my first contribution to CakePHP community: "Sitemap Deluxe
1.0 Beta"

With this you can create Google sitemaps for your Cake application,
send your sitemap to common search engines like Google, MSN (Bing),
Yahoo, ASK and optionally auto generate your robot.txt file.

Now create the next files and paste code on it:

app/config/routes.php

::

    
    Router::connect('/sitemap', array('controller' => 'sitemaps', 'action' => 'index'));
    Router::connect('/sitemap/:action/*', array('controller' => 'sitemaps'));
    
    // Optional
    Router::connect('/robots/:action/*', array('controller' => 'sitemaps', 'action' => 'robot'));
    
    Router::parseExtensions();


app/controllers/sitemaps_controller.php

Controller Class:
`````````````````

::

    <?php 
    /**
     * Sitemap Deluxe v1.0 Beta
     *
     * by Cristian Deluxe http://www.cristiandeluxe.com // http://blog.cristiandeluxe.com
     * 
     * Licenced by a Creative Commons GNU LGPL license
     * http://creativecommons.org/license/cc-lgpl
     *
     * @copyright     Copyright 2008-2009, Cristian Deluxe (http://www.cristiandeluxe.com)
     * @link          http://bakery.cakephp.org/articles/view/sitemap-deluxe
     */ 
    class SitemapsController extends AppController{
        var $name = 'Sitemaps';
        var $helpers = array('Time', 'Xml', 'Javascript');
        var $components = array('RequestHandler');
    	var $uses = array();
    	var $array_dynamic = array();
    	var $array_static = array();
    	var $sitemap_url = '/sitemap.xml';
    	var $yahoo_key = 'insert your yahoo api key here';
    
    	/* 
    	 * Our sitemap 
    	 */
        function index(){
           	Configure::write('debug', 0);		
    		$this->__get_data();
    		$this->set('dynamics', $this->array_dynamic);
    		$this->set('statics', $this->array_static);		
    		if ($this->RequestHandler->accepts('html')) {
    			$this->RequestHandler->respondAs('html');
            } elseif ($this->RequestHandler->accepts('xml')) {
    			$this->RequestHandler->respondAs('xml');
    		}        
        }
    	
    	/* 
    	 * Action for send sitemaps to search engines
    	 */
    	function send_sitemap() {
    		// This action must be only for admins
    	}
    	
    	/* 
    	 * This make a simple robot.txt file use it if you don't have your own
    	 */
    	function robot() {
           	Configure::write('debug', 0);
    		$expire = 25920000;
    		header('Date: ' . date("D, j M Y G:i:s ", time()) . ' GMT');
    		header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $expire) . ' GMT');
    		header('Content-Type: text/plain');
    		header('Cache-Control: max-age='.$expire.', s-maxage='.$expire.', must-revalidate, proxy-revalidate');
    		header('Pragma: nocache');
    		echo 'User-Agent: *'."\n".'Allow: /'."\n".'Sitemap: ' . FULL_BASE_URL . $this->sitemap_url;
    		exit();
    	}
    
    	/* 
    	 * Here must be all our public controllers and actions
    	 */
    	function __get_data() {
    		ClassRegistry::init('Post')->recursive = false;
    		$this->__add_dynamic_section(
    							 'Post', 
    							 ClassRegistry::init('Post')->find('all', array('fields' => array('slug', 'modified', 'title'))), 
    							 array(
    									'controllertitle' => 'My Posts',
    									'fields' => array('id' => 'slug'),
    									'changefreq' => 'daily',
    									'pr' => '1.0', 
    									'url' => array('controller' => 'posts', 'action' => 'view')
    								   )
    							 );		
    		$this->__add_static_section(
    							 'Contact Form', 
    							 array('controller' => 'contact', 'action' => 'index'), 
    							 array(
    									'changefreq' => 'yearly',
    									'pr' => '0.4'
    								   )
    							 );		
    		ClassRegistry::init('Gallery')->recursive = false;
    		$this->__add_dynamic_section(
    							 'Gallery', 
    							 ClassRegistry::init('Gallery')->find('all', array('fields' => array('id', 'name'))), 
    							 array(
    									'controllertitle' => 'My supersite gallery',
    									'fields' => array('title' => 'name', 'date' => false),
    									'pr' => '0.7', 
    									'changefreq' => 'weekly',
    									'url' => array('controller' => 'gallery', 'action'=>'show')
    								   )
    							 );
    	}
    	
    	/* 
    	 * Add a "static" section
    	 */
    	function __add_static_section($title = null, $url = null, $options = null) {
    		if(is_null($title) || empty($title) || is_null($url) || empty($url) ) {
    			return false;
    		}
    		$defaultoptions = array(
    								'pr' => '0.5', // Valid values range from 0.0 to 1.0
    								'changefreq' => 'monthly',  // Possible values: always, hourly, daily, weekly, monthly, yearly, never
    							);
    		$options = array_merge($defaultoptions, $options);		
    		$this->array_static[] = array(
    									 'title' => $title,
    									 'url' => $url,
    									 'options' => $options
    									 );		
    	}
    	
    	
    	/* 
    	 * Add a section based on data from our database
    	 */
    	function __add_dynamic_section($model = null, $data = null, $options = null){
    		if(is_null($model) || empty($model) || is_null($data) || empty($data) ) {
    			return false;
    		}		
    		$defaultoptions = array(
    									'fields' => array(
    														'id' => 'id', 
    														'date' => 'modified',
    														'title' => 'title'
    														),
    									'controllertitle' => 'not set',
    									'pr' => '0.5', // Valid values range from 0.0 to 1.0
    									'changefreq' => 'monthly',  // Possible values: always, hourly, daily, weekly, monthly, yearly, never
    									'url' => array(
    												   'controller' => false, 
    												   'action' => false, 
    												   'index' => 'index'
    												   )
    								);
    		$options = array_merge($defaultoptions, $options);
    		$options['fields'] = array_merge($defaultoptions['fields'], $options['fields']);
    		$options['url'] = array_merge($defaultoptions['url'], $options['url']);		
    		if($options['fields']['date'] == false) {
    			$options['fields']['date'] = time();
    		}		
    		$this->array_dynamic[] = array(
    									 'model' => $model,
    									 'options' => $options,
    									 'data' => $data
    									 );
    	}
    	
    	/* 
    	 * This make a GET petition to search engine url
    	 */	
    	function __ping_site($url = null, $params = null) {
    		if(is_null($url) || empty($url) || is_null($params) || empty($params) ) {
    			return false;	
    		}
    		App::import('Core', 'HttpSocket');
    		$HttpSocket = new HttpSocket();
    		$html = $HttpSocket->get($url, $params);
    		return $HttpSocket->response;
    	}
    	
    	/* 
    	 * Show response for ajax based on a boolean result
    	 */	
    	function __ajaxresponse($result = false){
    		if(!$result) {
    			return 'fail';
    		}
    		return 'success';
    	}
    	
    	/* 
    	 * Function for ping Google
    	 */	
    	function ping_google() {
           	Configure::write('debug', 0);
    		$url = 'http://www.google.com/webmasters/tools/ping';
    		$params = 'sitemap=' . urlencode(FULL_BASE_URL . $this->sitemap_url);
    		echo $this->__ajaxresponse($this->__check_ok_google( $this->__ping_site($url, $params) ));		
    		exit();
    	}
    	
    	/* 
    	 * Function for check Google's response
    	 */	
    	function __check_ok_google($response = null){
    		if( is_null($response) || !is_array($response) || empty($response) ) {
    			return false;
    		}
    		if(
    		   isset($response['status']['code']) && $response['status']['code'] == '200' &&
    		   isset($response['status']['reason-phrase']) && $response['status']['reason-phrase'] == 'OK' &&
    		   isset($response['body']) && !empty($response['body']) && 
    		   strpos(strtolower($response['body']), "successfully added") != false) {
    			return true;
    		}
    		return false;
    	}
    	
    	/* 
    	 * Function for ping Ask.com
    	 */	
    	function ping_ask() { // fail if we are in local environment
           	Configure::write('debug', 0);
    		$url = 'http://submissions.ask.com/ping';
    		$params = 'sitemap=' .  urlencode(FULL_BASE_URL . $this->sitemap_url);
    		echo $this->__ajaxresponse($this->__check_ok_ask( $this->__ping_site($url, $params) ));
    		exit();
    	}
    	
    	/* 
    	 * Function for check Ask's response
    	 */	
    	function __check_ok_ask($response = null){
    		if( is_null($response) || !is_array($response) || empty($response) ) {
    			return false;
    		}
    		if(
    		   isset($response['status']['code']) && $response['status']['code'] == '200' &&
    		   isset($response['status']['reason-phrase']) && $response['status']['reason-phrase'] == 'OK' &&
    		   isset($response['body']) && !empty($response['body']) && 
    		   strpos(strtolower($response['body']), "has been successfully received and added") != false) {
    			return true;
    		}
    		return false;
    	}
    	
    	/* 
    	 * Function for ping Yahoo
    	 */	
    	function ping_yahoo() {
           	Configure::write('debug', 0);
    		$url = 'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification';
    		$params = 'appid='.$this->yahoo_key.'&url=' . urlencode(FULL_BASE_URL . $this->sitemap_url);
    		echo $this->__ajaxresponse($this->__check_ok_yahoo( $this->__ping_site($url, $params) ));
    		exit();
    	}
    	
    	/* 
    	 * Function for check Yahoo's response
    	 */	
    	function __check_ok_yahoo($response = null){
    		if( is_null($response) || !is_array($response) || empty($response) ) {
    			return false;
    		}
    		if(
    		   isset($response['status']['code']) && $response['status']['code'] == '200' &&
    		   isset($response['status']['reason-phrase']) && $response['status']['reason-phrase'] == 'OK' &&
    		   isset($response['body']) && !empty($response['body']) && 
    		   strpos(strtolower($response['body']), "successfully submitted") != false) {
    			return true;
    		}
    		return false;
    	}
    	
    	/* 
    	 * Function for ping Bing
    	 */	
    	function ping_bing() {
           	Configure::write('debug', 0);
    		$url = 'http://www.bing.com/webmaster/ping.aspx';
    		$params = '&siteMap=' . urlencode(FULL_BASE_URL . $this->sitemap_url);
    		echo $this->__ajaxresponse($this->__check_ok_bing( $this->__ping_site($url, $params) ));
    		exit();
    	}
    	
    	/* 
    	 * Function for check Bing's response
    	 */	
    	function __check_ok_bing($response = null){
    		if( is_null($response) || !is_array($response) || empty($response) ) {
    			return false;
    		}
    		if(
    		   isset($response['status']['code']) && $response['status']['code'] == '200' &&
    		   isset($response['status']['reason-phrase']) && $response['status']['reason-phrase'] == 'OK' &&
    		   isset($response['body']) && !empty($response['body']) && 
    		   strpos(strtolower($response['body']), "thanks for submitting your sitemap") != false) {
    			return true;
    		}
    		return false;
    	}
    } 
    ?>


app/views/layouts/xml/default.ctp

View Template:
``````````````

::

    
    <?php header('Content-Type: text/xml');
    echo $content_for_layout;?>


app/views/sitemaps/xml/index.ctp

View Template:
``````````````

::

    
    <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
             xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
      	<url>
            <loc><?php echo Router::url('/', true); ?></loc>
            <lastmod><?php echo trim($time->toAtom(time())); ?></lastmod>
            <changefreq>weekly</changefreq>
            <priority>1.0</priority>
        </url>
    <?php
    if( isset($statics) && !empty($statics) ):
    	foreach ($statics as $static):?>
        <url> 
            <loc><?php echo Router::url($static['url'], true); ?></loc> 
            <lastmod><?php echo trim($time->toAtom(time())); ?></lastmod>
            <priority><?php echo $static['options']['pr'] ?></priority>
            <changefreq><?php echo $static['options']['changefreq'] ?></changefreq>
        </url>
    <?php
    	endforeach;
    endif;
    
    if( isset($dynamics) && !empty($dynamics) ):
    	foreach ($dynamics as $dynamic):?> 
        <url> 
            <loc><?php echo Router::url(array(
    										  'controller' => $dynamic['options']['url']['controller'], 
    										  'action' => $dynamic['options']['url']['index']
    										  ), true); ?></loc> 
            <lastmod><?php echo trim($time->toAtom(time())); ?></lastmod>
            <priority><?php echo $dynamic['options']['pr'] ?></priority>
            <changefreq><?php echo $dynamic['options']['changefreq'] ?></changefreq>
        </url>
    	<?php foreach ($dynamic['data'] as $section):?> 
        <url> 
            <loc><?php echo Router::url(array(
    										  'controller' => $dynamic['options']['url']['controller'], 
    										  'action' => $dynamic['options']['url']['action'], 
    										  $section[$dynamic['model']][$dynamic['options']['fields']['id']]
    										  ), true); ?></loc> 
            <lastmod><?php echo trim($time->toAtom($section[$dynamic['model']][$dynamic['options']['fields']['date']]))?></lastmod> 
            <priority><?php echo $dynamic['options']['pr'] ?></priority> 
            <changefreq><?php echo $dynamic['options']['changefreq'] ?></changefreq>
        </url> 
    	<?php endforeach;
    	endforeach;
    endif; ?> 
    </urlset>


app/views/sitemaps/index.ctp

View Template:
``````````````

::

    
    <?php
    $this->pageTitle = 'Sitemap';
    ?>
    <h1>
        Sitemap
    </h1>
    
    <table cellpadding="0" cellspacing="0">
    <?php
    if( isset($dynamics) && !empty($dynamics) ):
    	foreach ($dynamics as $dynamic): ?>
        <tr>
        	<th>
    		<?php echo $html->link(
    							   $dynamic['options']['controllertitle'],
    							   array(
    										  'controller' => $dynamic['options']['url']['controller'], 
    										  'action' => $dynamic['options']['url']['index']
    										  )); ?>
    		</th>
        </tr>
    	<?php foreach ($dynamic['data'] as $section):?>
        <tr>
        	<td>
    		> <?php echo $html->link(
    							  $section[$dynamic['model']][$dynamic['options']['fields']['title']],
    							   array(
    										  'controller' => $dynamic['options']['url']['controller'], 
    										  'action' => $dynamic['options']['url']['action'], 
    										  $section[$dynamic['model']][$dynamic['options']['fields']['id']]
    										  ) ); ?>
    		</td>
        </tr>
    	<?php endforeach;?>
        <tr>
        	<td class="clear"> </td>
        </tr>
    <?php
    	endforeach;
    endif;
    
    if(isset($statics) && !empty($statics) ):?>
        <tr>
        	<td class="title">
    			Misc
    		</td>
        </tr>
    	<?php foreach ($statics as $static): ?>
        <tr>
        	<td>
    		<?php echo $html->link(
    							   $static['title'],
    							   $static['url']); ?>
    		</td>
        </tr>
    	<?php endforeach;?>
        <tr>
        	<td class="clear"> </td>
        </tr>
    <?php endif; ?>   
    </table>


app/views/sitemaps/send_sitemap.ctp

View Template:
``````````````

::

    
    <?php 
    $this->pageTitle = 'Send SiteMap';
    $javascript->link('jquery-1.3.2.min.js', false);
    ?>
    <h1>
        Send SiteMap
    </h1>
    
    <table cellpadding="0" cellspacing="0">
        <tr>
            <th>Site</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
    			Google
            </td>
            <td>
    			<div id="results_google">Not Send</div>
            </td>
            <td>
    			<?php echo $form->create('Sitemap', array('action' => 'ping_google', 'type' => 'get', 'id' => 'ping_google') );?>
                <?php echo $form->end('Send');?>
            </td>
        </tr>
        <tr>
            <td>
    			Ask
            </td>
            <td>
    			<div id="results_ask">Not Send</div>
            </td>
            <td>
    			<?php echo $form->create('Sitemap', array('action' => 'ping_ask', 'type' => 'get', 'id' => 'ping_ask') );?>
                <?php echo $form->end('Send');?>
            </td>
        </tr>
        <tr>
            <td>
    			Yahoo
            </td>
            <td>
    			<div id="results_yahoo">Not Send</div>
            </td>
            <td>
    			<?php echo $form->create('Sitemap', array('action' => 'ping_yahoo', 'type' => 'get', 'id' => 'ping_yahoo') );?>
                <?php echo $form->end('Send');?>
            </td>
        </tr>
        <tr>
            <td>
    			Bing
            </td>
            <td>
    			<div id="results_bing">Not Send</div>
            </td>
            <td>
    			<?php echo $form->create('Sitemap', array('action' => 'ping_bing', 'type' => 'get', 'id' => 'ping_bing') );?>
                <?php echo $form->end('Send');?>
            </td>
        </tr>
    </table>
    
    <script language="javascript" type="text/javascript">
    //<![CDATA[
    	var GoogleForm = 'ping_google';
    	var GoogleResult = 'results_google';
    	var AskForm = 'ping_ask';
    	var AskResult = 'results_ask';
    	var YahooForm = 'ping_yahoo';
    	var YahooResult = 'results_yahoo';
    	var BingForm = 'ping_bing';
    	var BingResult = 'results_bing';
    	
    	var msgProgress = 'Sending SiteMap...';
    	var msgOK = 'Sended and received OK';
    	var msgFail = 'Error, sitemap not sended';
    		   
    	$(document).ready(function(){
    		$('#' + GoogleForm).submit(processGoogle);
    		$('#' + AskForm).submit(processAsk);
    		$('#' + YahooForm).submit(processYahoo);
    		$('#' + BingForm).submit(processBing);
    	
    	});
    	
    	function showresults(divid, data){
    		$("#"+divid).html(data);
    		$("#"+divid).css({width: "0%"}).animate({width: "100%"}, 'slow');
    	}
    	
    	function parseresults(data) {
    		var bgcolor = '900';
    		var textcolor = 'FFF';
    		var message = msgFail;
    		if($.trim(data) == "success") {
    			var bgcolor = '090';
    			var textcolor = 'FFF';
    			var message = msgOK;
    		}
    		return '<div style="background:#'+bgcolor+'; color:#'+textcolor+'; padding: 10px;">'+message+'<\/div>';
    	}
    	
    	function processGoogle(event){
    		event.preventDefault();
    		$("#" + GoogleResult).html(msgProgress);
    		$.get("<?php echo Router::url(array('action' => 'ping_google'), true); ?>", null, function(data) {
    			showresults(GoogleResult, parseresults(data));
    		});
    	}
    	
    	function processAsk(event){
    		event.preventDefault();
    		$("#" + AskResult).html(msgProgress);
    		$.get("<?php echo Router::url(array('action' => 'ping_ask'), true); ?>", null, function(data) {
    			showresults(AskResult, parseresults(data));
    		});
    	}
    	
    	function processYahoo(event){
    		event.preventDefault();
    		$("#" + YahooResult).html(msgProgress);
    		$.get("<?php echo Router::url(array('action' => 'ping_yahoo'), true); ?>", null, function(data) {
    			showresults(YahooResult, parseresults(data));
    		});
    	}	
    	
    	function processBing(event){
    		event.preventDefault();
    		$("#" + BingResult).html(msgProgress);
    		$.get("<?php echo Router::url(array('action' => 'ping_bing'), true); ?>", null, function(data) {
    			showresults(BingResult, parseresults(data));
    		});
    	}	
    //]]>
    </script>

Download latest Jquery (Minified) version from here:
`http://docs.jquery.com/Downloading_jQuery`_ and copy it in your
"app/webroot/js" folder.

Now you need to change the sitemaps controller file for adapt it to
your needs, add as many statics and dynamics sections as you want.

Next step is check that xml and html sitemaps are working, point your
browser to:
http//yoursite.com/sitemap
and
http//yoursite.com/sitemap.xml

If all is ok you can submit your sitemaps to search engines, point
your browser to:
http//yoursite.com/send_sitemap

And click in "Send" button on each site for send your sitemap.

All done, now you must comment and tell how worked for you!! :P
Feel free to correct my English and my code.

.. _http://docs.jquery.com/Downloading_jQuery: http://docs.jquery.com/Downloading_jQuery

.. author:: CristianDeluxe
.. categories:: articles, tutorials
.. tags::
search,seo,sitemap,engines,indexation,ping,spiders,robots,Tutorials

