Dynamic Css for CMS and More!
=============================

by t73net on June 22, 2007

Recently I was developing a cake app for a client who wanted to use
the same system several times for a project and the same layout, but
with different visual changes, like color etc. I had used simple
version of css management based on the config component. This is a
full blown version of this solution.


OVERVIEW:
`````````

The whole structure of this Dynamic Css System is driven by DB yet
written to a flat file anytime that the Dynamic Css System is written
or used. There are also some minor issues, like implanting a custom
hack for CSS Compliance for IE, but this is easily handled and
explained in more detail later.

Let's get this started. The first thing to do is setup the model. Here
is the sql for MySQL 5.

::

    
    -- 
    -- Table structure for table `dyna_csses`
    -- 
    
    DROP TABLE IF EXISTS `dyna_csses`;
    CREATE TABLE IF NOT EXISTS `dyna_csses` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `tag` varchar(255) collate utf8_bin default NULL,
      `background_attachment` enum('fixed','inherit','scroll') collate utf8_bin default NULL,
      `background_color` varchar(10) collate utf8_bin default NULL,
      `background_image` varchar(255) collate utf8_bin default NULL,
      `background_position` varchar(255) collate utf8_bin default NULL,
      `background_repeat` enum('no-repeat','repeat','repeat-x','repeat-y','inherit') collate utf8_bin default NULL,
      `border_bottom_color` varchar(10) collate utf8_bin default NULL,
      `border_bottom_style` enum('none','dotted','dashed','solid','double','groove','ridge','inset','outset') collate utf8_bin default NULL,
      `border_bottom_width` varchar(255) collate utf8_bin default NULL,
      `border_collapse` enum('collapse','seperate','inherit') collate utf8_bin default NULL,
      `border_left_color` varchar(10) collate utf8_bin default NULL,
      `border_left_style` enum('none','dotted','dashed','solid','double','groove','ridge','inset','outset') collate utf8_bin default NULL,
      `border_left_width` varchar(255) collate utf8_bin default NULL,
      `border_right_color` varchar(10) collate utf8_bin default NULL,
      `border_right_style` enum('none','dotted','dashed','solid','double','groove','ridge','inset','outset') collate utf8_bin default NULL,
      `border_right_width` varchar(255) collate utf8_bin default NULL,
      `border_spacing` varchar(255) collate utf8_bin default NULL,
      `border_top_color` varchar(10) collate utf8_bin default NULL,
      `border_top_style` enum('NULL','none','dotted','dashed','solid','double','groove','ridge','inset','outset') collate utf8_bin default NULL,
      `border_top_width` varchar(255) collate utf8_bin default NULL,
      `bottom` varchar(255) collate utf8_bin default NULL,
      `caption_side` enum('top','bottom','inherit') collate utf8_bin default NULL,
      `clear` enum('none','left','right','both','inherit') collate utf8_bin default NULL,
      `clip` varchar(255) collate utf8_bin default NULL,
      `color` varchar(10) collate utf8_bin default NULL,
      `cursor` varchar(255) collate utf8_bin default NULL,
      `direction` enum('ltr','rtl','inherit') collate utf8_bin default NULL,
      `empty_cells` enum('show','hide','inherit') collate utf8_bin default NULL,
      `float` enum('left','right','none','inherit') collate utf8_bin default NULL,
      `font` enum('caption','icon','menu','message-box','small-caption','status-bar') collate utf8_bin default NULL,
      `font_family` varchar(255) collate utf8_bin default NULL,
      `font_size` varchar(255) collate utf8_bin default NULL,
      `font_style` enum('normal','italic','oblique','inherit') collate utf8_bin default NULL,
      `font_variant` enum('normal','small-caps','inherit') collate utf8_bin default NULL,
      `font_weight` enum('normal','bold','bolder','lighter','100','200','300','400','500','600','700','800','900','inherit') collate utf8_bin default NULL,
      `height` varchar(255) collate utf8_bin default NULL,
      `left` varchar(255) collate utf8_bin default NULL,
      `letter_spacing` varchar(255) collate utf8_bin default NULL,
      `line_height` varchar(255) collate utf8_bin default NULL,
      `list_style_image` varchar(255) collate utf8_bin default NULL,
      `list_style_position` enum('inside','outside','inherit') collate utf8_bin default NULL,
      `list_style_type` enum('disc','square','circle','decimal','decimal-leading-zero','lower-roman','upper-roman','lower-greek','lower-alpha','lower-latin','upper-alpha','upper-latin','hebrew','armenian','georgian','cjk-ideographic','hiragana','katakana','hiragana-iroha','katakana-iroha','none','inherit') collate utf8_bin default NULL,
      `margin_bottom` varchar(255) collate utf8_bin default NULL,
      `margin_left` varchar(255) collate utf8_bin default NULL,
      `margin_right` varchar(255) collate utf8_bin default NULL,
      `margin_top` varchar(255) collate utf8_bin default NULL,
      `max_height` varchar(255) collate utf8_bin default NULL,
      `max_width` varchar(255) collate utf8_bin default NULL,
      `min_height` varchar(255) collate utf8_bin default NULL,
      `min_width` varchar(255) collate utf8_bin default NULL,
      `outline_color` varchar(255) collate utf8_bin default NULL,
      `outline_style` enum('none','hidden','dotted','dashed','solid','double','groove','ridge','inset','outset','inherit') collate utf8_bin default NULL,
      `outline_width` varchar(255) collate utf8_bin default NULL,
      `overflow` enum('visible','hidden','scroll','auto','inherit') collate utf8_bin default NULL,
      `padding_bottom` varchar(255) collate utf8_bin default NULL,
      `padding_left` varchar(255) collate utf8_bin default NULL,
      `padding_right` varchar(255) collate utf8_bin default NULL,
      `padding_top` varchar(255) collate utf8_bin default NULL,
      `page` varchar(255) collate utf8_bin default NULL,
      `page_break_after` enum('auto','always','avoid','left','right','inherit') collate utf8_bin default NULL,
      `page_break_before` enum('auto','always','avoid','left','right','inherit') collate utf8_bin default NULL,
      `page_break_inside` enum('auto','always','avoid','left','right','inherit') collate utf8_bin default NULL,
      `position` enum('static','relative','absolute','fixed','inherit') collate utf8_bin default NULL,
      `quotes` enum('none','inherit') collate utf8_bin default NULL,
      `right` varchar(255) collate utf8_bin default NULL,
      `table_layout` enum('auto','fixed','inherit') collate utf8_bin default NULL,
      `text_align` enum('center','justify','right','left','inherit') collate utf8_bin default NULL,
      `text_decoration` enum('none','underline','overline','line-through','blink','inherit') collate utf8_bin default NULL,
      `text_indent` varchar(255) collate utf8_bin default NULL,
      `text_transform` enum('capitalize','uppercase','lowercase','none','inherit') collate utf8_bin default NULL,
      `top` varchar(255) collate utf8_bin default NULL,
      `unicode_bidi` enum('normal','embed','bidi-override','inherit') collate utf8_bin default NULL,
      `vertical_align` varchar(255) collate utf8_bin default NULL,
      `visibility` enum('visible','hidden','collapse','inherit') collate utf8_bin default NULL,
      `white_space` enum('normal','pre','pre-wrap','pre-line','nowrap','inherit') collate utf8_bin default NULL,
      `width` varchar(255) collate utf8_bin default NULL,
      `word_spacing` varchar(255) collate utf8_bin default NULL,
      `z_index` varchar(255) collate utf8_bin default NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=96 DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=96 ;

I have used enum sets for the most common CSS elements according to
CSS2 standards set forth by W3C. The use of enums allows the views to
implement a select tag based on the enum sets.

Also please note that the column names follow CakePHP naming
conventions, which actually breaks the naming conventions for CSS2. We
take care of this in the controller, which is the next page in this
article.

The actual model for this is simple and straight forward. There is no
data validation implemented as of yet, but will be in future versions


Model Class:
````````````

::

    <?php 
    class DynaCss extends AppModel {
    	
    	var $name = "DynaCss";
    	
    }
    ?>

I have also used a modified version of Baked Enums getEnumList()
function implemented into the AppModel File for setting select option
values based on enum sets.

::

    
    	/**
    	* Retrieve a list of enum values for a specific field
    	*
    	* @param string
    	* @return array
    	*/
    	function getEnumList($fldName){
    		$fldInfoArray = $this->_tableInfo->findIn( 'name' , $fldName );
    		foreach($fldInfoArray as $fldInfo)
    			break;
    			
    		$lParenPos = strpos($fldInfo['type'], '(');
    		$rParenPos = strpos($fldInfo['type'], ')');
    	
    		if (false != $lParenPos) {
    			$type = substr($fldInfo['type'], 0, $lParenPos);
    			$fieldLength = substr($fldInfo['type'], $lParenPos + 1, $rParenPos - $lParenPos - 1);
    			$enumValues = split(',', $fieldLength);
    			
    			foreach($enumValues as $key => $enum) {
    				$enum = trim($enum, "'");
    				$enums[$enum] = $enum;
    			}
    		}else{
    			$enums = array();
    		}
    		return $enums;
    	}
    



The controller setup for Dyna Css implements enum checking and uses
table info via the $this->DynaCss->getEnumList() function implemented
in AppModel



Controller Class:
`````````````````

::

    <?php 
    class DynaCssController extends AppController {
    
    	var $name = 'DynaCss';
    	var $uses = array('DynaCss');
    	var $helpers = array ('Javascript');
            /**
             * Var for which column names contain enums to
             * be used in the select tag
             */
    	var $fldArray = array(
    				'background_attachment','background_repeat',
    				'border_bottom_style','border_left_style',
    				'border_right_style','border_top_style',
    				'caption_side','clear','direction',
    				'empty_cells','float','font','font_style',
    				'font_variant','font_weight','list_style_position',
    				'list_style_type','outline_style','overflow',
    				'page_break_after','page_break_before','page_break_inside',
    				'position','quotes','table_layout','text_align',
    				'text_decoration','text_transform','unicode_bidi',
    				'visibility','white_space',
    				);
    
    
    	function index() {
    		$this->DynaCss->recursive = 0;
    		$this->set('dynaCsses', $this->DynaCss->findAll());
    	}
    
    	function view($id = null) {
    		if(!$id) {
    			$this->Session->setFlash('Invalid id for Dyna Css.');
    			$this->redirect('/dyna_css/index');
    		}
    		$this->set('dynaCss', $this->_assemble_css($id));
    		$this->set('dynaCssId', $id);
    	}
    
    	function add() {
    		$this->set('fldArray', $this->fldArray);
    		foreach ($this->fldArray as $fldValue)
    		{
    			$this->set($fldValue, $this->DynaCss->getEnumList($fldValue));
    		}
    		if(empty($this->data)) {
    			$fldNameArray = array();
    			$tmp = (array) $this->DynaCss->loadInfo();
    			foreach($tmp['value'] as $key => $name)
    			{
    				$fldNameArray[] = $name['name'];
    			}
    			$this->set('fldNameArray', $fldNameArray);
    		} else {
    			$this->cleanUpFields();
    			if($this->DynaCss->save($this->data)) {
    				$this->_build_css();
    				$this->Session->setFlash('The Dyna Css has been saved');
    				$this->redirect('/dyna_css/index');
    			} else {
    				$this->Session->setFlash('Please correct errors below.');
    			}
    		}
    	}
    
    	function edit($id = null) {
    		$this->set('fldArray', $this->fldArray);
    		foreach ($this->fldArray as $fldValue)
    		{
    			$this->set($fldValue, $this->DynaCss->getEnumList($fldValue));
    		}
    		
    		if(empty($this->data)) {
    			if(!$id) {
    				$this->Session->setFlash('Invalid id for Dyna Css');
    				$this->redirect('/dyna_css/index');
    			}
    			$this->data = $this->DynaCss->read(null, $id);
    		} else {
    			$this->cleanUpFields();
    			if($this->DynaCss->save($this->data)) {
    				$this->_build_css();
    				$this->Session->setFlash('The DynaCss has been saved');
    				$this->redirect('/dyna_css/index');
    			} else {
    				$this->Session->setFlash('Please correct errors below.');
    			}
    		}
    	}
    
    	function delete($id = null) {
    		if(!$id) {
    			$this->Session->setFlash('Invalid id for Dyna Css');
    			$this->redirect('/dyna_css/index');
    		}
    		if($this->DynaCss->del($id)) {
    			$this->Session->setFlash('The Dyna Css deleted: id '.$id.'');
    			$this->_build_css();
    			$this->redirect('/dyna_css/index');
    		}
    	}
    	
    	function _build_css()
    	{
    		$cssFile = APP.WEBROOT_DIR.DS."css".DS."dyna.css";
    		$result = $this->_assemble_css();
    		if ($cssFile = fopen($cssFile, 'w')) {
    			fwrite ($cssFile, $result, strlen($result));
    			fclose($cssFile);
    		}
    		$this->set('cssData', $result);
    	}
    	
    	function _assemble_css($id=null)
    	{
    		$elementArray = array(
    			'background_attachment','background_color','background_image',
    			'background_position','background_repeat','border_bottom_color',
    			'border_bottom_style','border_bottom_width','border_collapse',
    			'border_left_color','border_left_style','border_left_width',
    			'border_right_color','border_right_style','border_right_width',
    			'border_spacing','border_top_color','border_top_style',
    			'border_top_width','bottom','caption_side','clear','clip',
    			'color','cursor','direction','empty_cells','float','font',
    			'font_family','font_size','font_style','font_variant',
    			'font_weight','height','left','letter_spacing','line_height',
    			'list_style_image','list_style_position','list_style_type',
    			'margin_bottom','margin_left','margin_right','margin_top',
    			'max_height','max_width','min_height','min_width','outline_color',
    			'outline_style','outline_width','overflow','padding_bottom',
    			'padding_left','padding_right','padding_top','page',
    			'page_break_after','page_break_before','page_break_inside',
    			'position','quotes','right','table_layout','text_align',
    			'text_decoration','text_indent','text_transform','top',
    			'unicode_bidi','vertical_align','visibility','white_space',
    			'width','word_spacing','z_index');
    		$output = '';
    		if (is_null($id))
    		{
    			$result = $this->DynaCss->findAll();
    			foreach($result as $tmp=>$tmp2)
    			{
    				$output .= $tmp2['DynaCss']['tag'] ."{\n\t";
    				foreach($elementArray as $element)
    				{
    					if (!empty($tmp2['DynaCss'][$element]))
    					{
    						$fixedElement = str_replace("_", "-",$element);
    						$output .= $fixedElement . " : " . $tmp2['DynaCss'][$element] . ";\n\t";
    					}
    				}
    				$output .= "\n}\n\n";
    			}
    		} else {
    			$result = $this->DynaCss->read(null, $id);
    			foreach($result as $tmp=>$tmp2)
    			{
    				
    				$output .= $tmp2['tag'] ."{\n\t";
    				foreach($elementArray as $element)
    				{
    					if (!empty($tmp2[$element]))
    					{
    						$fixedElement = str_replace("_", "-",$element);
    						$output .= $fixedElement . " : " . $tmp2[$element] . ";\n\t";
    					}
    				}
    				$output .= "\n}\n\n";
    			}
    		}
    		
    		return $output;
    	}
    
    }
    ?>


Next up is the views and setting layouts to use this system.



There are 2 misc. files that need to be put into the webroot
structure. First, there is a file needs to be created in the
webroot/css folder named dyna.css . The file is simply a blank file
that will be written when a CRUD Function is used in the controller.
Make sure that this has proper write permissions for the server to be
able to write to it.

::

    
    echo $html->css('dyna') ."\n";


Secondly, there is a small javascript file that needs to be put into
webroot/js and refferenced in the layout used to display the views for
add and edit.


Implementation in the layout

::

    
    	if(isset($javascript)):
    	    echo $javascript->link('colorpick.js') ."\n";
    	endif;          
    


Javascript Code based on Yahoo's Collor picker framework[/]

::

    
    //JavaScript Document
    //Popup Color Picker 4.6
    //Copyright Kerberos Internet Services, CC; All rights reserved.
    //info@kerberosb2b.com
    //helpdesk@kerberosb2b.com
    //http://www.kerberosb2b.com
    isIE4 = document.all? true : false;
    isIE6 = document.getElementById && document.all ? true : false;
    isNS4 = document.layers? true : false;
    isNS6 = document.getElementById && !document.all ? true : false;
    var COLOUR_CONTAINER_PREFIX = "COLOUR_CONTAINER";
    var ColourPicker_idGenerator = new IDGenerator(0);
    var colourPickerMap = new Object();
    var colourPickerIDGenerator = null;
    //Colour Cubes
    var colourswatch_1 = [
        [ '000000', '000000', '003300', '006600', '009900', '00CC00', '00FF00', '330000', '333300', '336600', '339900', '33CC00', '33FF00', '660000', '663300', '666600', '669900', '66CC00', '66FF00' ],
        [ '333333', '000033', '003333', '006633', '009933', '00CC33', '00FF33', '330033', '333333', '336633', '339933', '33CC33', '33FF33', '660033', '663333', '666633', '669933', '66CC33', '66FF33' ],
        [ '666666', '000066', '003366', '006666', '009966', '00CC66', '00FF66', '330066', '333366', '336666', '339966', '33CC66', '33FF66', '660066', '663366', '666666', '669966', '66CC66', '66FF66' ],
        [ '999999', '000099', '003399', '006699', '009999', '00CC99', '00FF99', '330099', '333399', '336699', '339999', '33CC99', '33FF99', '660099', '663399', '666699', '669999', '66CC99', '66FF99' ],
        [ 'CCCCCC', '0000CC', '0033CC', '0066CC', '0099CC', '00CCCC', '00FFCC', '3300CC', '3333CC', '3366CC', '3399CC', '33CCCC', '33FFCC', '6600CC', '6633CC', '6666CC', '6699CC', '66CCCC', '66FFCC' ],
        [ 'FFFFFF', '0000FF', '0033FF', '0066FF', '0099FF', '00CCFF', '00FFFF', '3300FF', '3333FF', '3366FF', '3399FF', '33CCFF', '33FFFF', '6600FF', '6633FF', '6666FF', '6699FF', '66CCFF', '66FFFF'],
        [ 'FF0000', '990000', '993300', '996600', '999900', '99CC00', '99FF00', 'CC0000', 'CC3300', 'CC6600', 'CC9900', 'CCCC00', 'CCFF00', 'FF0000', 'FF3300', 'FF6600', 'FF9900', 'FFCC00', 'FFFF00' ],
        [ '00FF00', '990033', '993333', '996633', '999933', '99CC33', '99FF33', 'CC0033', 'CC3333', 'CC6633', 'CC9933', 'CCCC33', 'CCFF33', 'FF0033', 'FF3333', 'FF6633', 'FF9933', 'FFCC33', 'FFFF33' ],
        [ '0000FF', '990066', '993366', '996666', '999966', '99CC66', '99FF66', 'CC0066', 'CC3366', 'CC6666', 'CC9966', 'CCCC66', 'CCFF66', 'FF0066', 'FF3366', 'FF6666', 'FF9966', 'FFCC66', 'FFFF66' ],
        [ 'FFFF00', '990099', '993399', '996699', '999999', '99CC99', '99FF99', 'CC0099', 'CC3399', 'CC6699', 'CC9999', 'CCCC99', 'CCFF99', 'FF0099', 'FF3399', 'FF6699', 'FF9999', 'FFCC99', 'FFFF99' ],
        [ '00FFFF', '9900CC', '9933CC', '9966CC', '9999CC', '99CCCC', '99FFCC', 'CC00CC', 'CC33CC', 'CC66CC', 'CC99CC', 'CCCCCC', 'CCFFCC', 'FF00CC', 'FF33CC', 'FF66CC', 'FF99CC', 'FFCCCC', 'FFFFCC' ],
        [ 'FF00FF', '9900FF', '9933FF', '9966FF', '9999FF', '99CCFF', '99FFFF', 'CC00FF', 'CC33FF', 'CC66FF', 'CC99FF', 'CCCCFF', 'CCFFFF', 'FF00FF', 'FF33FF', 'FF66FF', 'FF99FF', 'FFCCFF', 'FFFFFF' ]
    ];
    //Continous Tone
    var colourswatch_2 = [
        [ '000000', 'CCFFFF', 'CCFFCC', 'CCFF99', 'CCFF66', 'CCFF33', 'CCFF00', '66FF00', '66FF33', '66FF66', '66FF99', '66FFCC', '66FFFF', '00FFFF', '00FFCC', '00FF99', '00FF66', '00FF33', '00FF00' ],
        [ '333333', 'CCCCFF', 'CCCCCC', 'CCCC99', 'CCCC66', 'CCCC33', 'CCCC00', '66CC00', '66CC33', '66CC66', '66CC99', '66CCCC', '66CCFF', '00CCFF', '00CCCC', '00CC99', '00CC66', '00CC33', '00CC00' ],
        [ '666666', 'CC99FF', 'CC99CC', 'CC9999', 'CC9966', 'CC9933', 'CC9900', '669900', '669933', '669966', '669999', '6699CC', '6699FF', '0099FF', '0099CC', '009999', '009966', '009933', '009900' ],
        [ '999999', 'CC66FF', 'CC66CC', 'CC6699', 'CC6666', 'CC6633', 'CC6600', '666600', '666633', '666666', '666699', '6666CC', '6666FF', '0066FF', '0066CC', '006699', '006666', '006633', '006600' ],
        [ 'CCCCCC', 'CC33FF', 'CC33CC', 'CC3399', 'CC3366', 'CC3333', 'CC3300', '663300', '663333', '663366', '663399', '6633CC', '6633FF', '0033FF', '0033CC', '003399', '003366', '003333', '003300' ],
        [ 'FFFFFF', 'CC00FF', 'CC00CC', 'CC0099', 'CC0066', 'CC0033', 'CC0000', '660000', '660033', '660066', '660099', '6600CC', '6600FF', '0000FF', '0000CC', '000099', '000066', '000033', '000000' ],
        [ 'FF0000', 'FF00FF', 'FF00CC', 'FF0099', 'FF0066', 'FF0033', 'FF0000', '990000', '990033', '990066', '990099', '9900CC', '9900FF', '3300FF', '3300CC', '330099', '330066', '330033', '330000' ],
        [ '00FF00', 'FF33FF', 'FF33CC', 'FF3399', 'FF3366', 'FF3333', 'FF3300', '993300', '993333', '993366', '993399', '9933CC', '9933FF', '3333FF', '3333CC', '333399', '333366', '333333', '333300' ],
        [ '0000FF', 'FF66FF', 'FF66CC', 'FF6699', 'FF6666', 'FF6633', 'FF6600', '996600', '996633', '996666', '996699', '9966CC', '9966FF', '3366FF', '3366CC', '336699', '336666', '336633', '336600' ],
        [ 'FFFF00', 'FF99FF', 'FF99CC', 'FF9999', 'FF9966', 'FF9933', 'FF9900', '999900', '999933', '999966', '999999', '9999CC', '9999FF', '3399FF', '3399CC', '339999', '339966', '339933', '339900' ],
        [ '00FFFF', 'FFCCFF', 'FFCCCC', 'FFCC99', 'FFCC66', 'FFCC33', 'FFCC00', '99CC00', '99CC33', '99CC66', '99CC99', '99CCCC', '99CCFF', '33CCFF', '33CCCC', '33CC99', '33CC66', '33CC33', '33CC00' ],
        [ 'FF00FF', 'FFFFFF', 'FFFFCC', 'FFFF99', 'FFFF66', 'FFFF33', 'FFFF00', '99FF00', '99FF33', '99FF66', '99FF99', '99FFCC', '99FFFF', '33FFFF', '33FFCC', '33FF99', '33FF66', '33FF33', '33FF00' ]
    ];
    //Gray Scale
    var colourswatch_3 = [
        [ 'FFFFFF', 'FEFEFE', 'FDFDFD', 'FCFCFC', 'FBFBFB', 'FAFAFA', 'F9F9F9', 'F8F8F8', 'F7F7F7', 'F6F6F6', 'F5F5F5', 'F4F4F4', 'F3F3F3', 'F2F2F2', 'F1F1F1', 'F0F0F0', 'EFEFEF', 'EEEEEE', 'EDEDED' ],
        [ 'ECECEC', 'EBEBEB', 'EAEAEA', 'E9E9E9', 'E8E8E8', 'E7E7E7', 'E6E6E6', 'E5E5E5', 'E4E4E4', 'E3E3E3', 'E2E2E2', 'E1E1E1', 'E0E0E0', 'DFDFDF', 'DEDEDE', 'DDDDDD', 'DCDCDC', 'DBDBDB', 'DADADA' ],
        [ 'D9D9D9', 'D8D8D8', 'D7D7D7', 'D6D6D6', 'D5D5D5', 'D4D4D4', 'D3D3D3', 'D2D2D2', 'D1D1D1', 'D0D0D0', 'CFCFCF', 'CECECE', 'CDCDCD', 'CCCCCC', 'CBCBCB', 'CACACA', 'C9C9C9', 'C8C8C8', 'C7C7C7' ],
        [ 'C6C6C6', 'C5C5C5', 'C4C4C4', 'C3C3C3', 'C2C2C2', 'C1C1C1', 'C0C0C0', 'BFBFBF', 'BEBEBE', 'BDBDBD', 'BCBCBC', 'BBBBBB', 'BABABA', 'B9B9B9', 'B8B8B8', 'B7B7B7', 'B6B6B6', 'B5B5B5', 'B4B4B4' ],
        [ 'B3B3B3', 'B2B2B2', 'B1B1B1', 'B0B0B0', 'AFAFAF', 'AEAEAE', 'ADADAD', 'ACACAC', 'ABABAB', 'AAAAAA', 'A9A9A9', 'A8A8A8', 'A7A7A7', 'A6A6A6', 'A5A5A5', 'A4A4A4', 'A3A3A3', 'A2A2A2', 'A1A1A1' ],
        [ 'A0A0A0', '9F9F9F', '9E9E9E', '9D9D9D', '9C9C9C', '9B9B9B', '9A9A9A', '999999', '989898', '979797', '969696', '959595', '949494', '939393', '929292', '919191', '909090', '8F8F8F', '8E8E8E' ],
        [ '8D8D8D', '8C8C8C', '8B8B8B', '8A8A8A', '898989', '888888', '878787', '868686', '858585', '848484', '838383', '828282', '818181', '808080', '7F7F7F', '7E7E7E', '7D7D7D', '7C7C7C', '7B7B7B' ],
        [ '7A7A7A', '6F6F6F', '6E6E6E', '6D6D6D', '6C6C6C', '6B6B6B', '6A6A6A', '696969', '686868', '676767', '666666', '656565', '646464', '636363', '626262', '616161', '606060', '5F5F5F', '5E5E5E' ],
        [ '5D5D5D', '5C5C5C', '5B5B5B', '5A5A5A', '595959', '585858', '575757', '565656', '555555', '545454', '535353', '525252', '515151', '505050', '4F4F4F', '4E4E4E', '4D4D4D', '4C4C4C', '4B4B4B' ],
        [ '4A4A4A', '494949', '484848', '474747', '464646', '454545', '444444', '434343', '424242', '414141', '404040', '3F3F3F', '3E3E3E', '3D3D3D', '3C3C3C', '3B3B3B', '3A3A3A', '393939', '383838' ],
        [ '373737', '363636', '353535', '343434', '333333', '323232', '313131', '303030', '2F2F2F', '2E2E2E', '2D2D2D', '2C2C2C', '2B2B2B', '2A2A2A', '292929', '282828', '272727', '262626', '252525' ],
        [ '242424', '232323', '222222', '212121', '202020', '1F1F1F', '1E1E1E', '1D1D1D', '1C1C1C', '1B1B1B', '1A1A1A', '191919', '181818', '171717', '161616', '151515', '141414', '131313', '121212' ],
    	[ '111111', '101010', '0F0F0F', '0E0E0E', '0D0D0D', '0C0C0C', '0B0B0B', '0A0A0A', '090909', '080808', '070707', '060606', '050505', '040404', '030303', '020202', '010101', '000000', '000000' ]
    ];
    
    //Utility Functions
    function IDGenerator(nextID){
    	this.nextID = nextID;
    	this.GenerateID = IDGeneratorGenerateID;
    }
    function IDGeneratorGenerateID(){
    	return this.nextID++;
    }
    function getDOMObject (documentID){
    	if (isIE4){return document.all[documentID];
    	}else if(isIE6){return document.getElementById(documentID);
    	}else if (isNS4){return document.layers[documentID];
    	}else if (isNS6){return document.getElementById(documentID);
    	}
    }
    function getFrameDOMObject (documentID, frameID){
    	if (isIE4){return eval(frameID).document.all[documentID];
    	}else if(isIE6){return eval(frameID).document.getElementById(documentID);
    	}else if (isNS4){return eval(frameID).document.layers[documentID];
    	}else if (isNS6){return eval(frameID).document.getElementById(documentID);
    	}
    }
    //Object Functions
    function ColourPicker (boundControl, scriptAction, swatchOption){
    //Properties
    	this.LF_button_borderColor = "#CCCCCC";
    	this.LF_input_borderColor = "#CCCCCC";
    	this.LF_input_bgColor = "#FFFFFF";
    	this.LF_input_textColor = "#000000";
    	this.LF_swatch_borderColor = "#CCCCCC";
    	this.LF_swatch_bgColor = "#FFFFFF";
    	this.LF_swatch_selectBorderColor = "#000000";
    	this.LF_swatch_textColor = "#000000";
    	this.showInput = true;
    	this.allowInput = true;
    	this.noColor = true;
    	this.scriptAction = scriptAction;
    //Object Variables
    	this.idGenerator = ColourPicker_idGenerator;
    	this.instantiated = false;
    	this.valueControl = '';
    	this.displayControl = '';
    	this.boundControl = boundControl;
    	this.swatchOption = 1;
    	if (swatchOption) this.swatchOption = swatchOption;
    	this.colourswatch = null;
    	this.isShowing = false;
    	this.width = 280;
    	this.height = 180;
    //Functions
    	this.Instantiate = ColourPicker_Instantiate;
    	this.parseInitString = ColourPicker_ParseInitString;
    	this.initSwatch = ColourPicker_InitSwatch;
    	this.show = ColourPicker_ShowThisPicker;
    	this.hide = ColourPicker_HideThisPicker;	
    }
    function  ColourPicker_Instantiate(){
    	if (this.instantiated) {
    		return;
    	}
    	this.id = this.idGenerator.GenerateID();
    	colourPickerMap[this.id] = this;
    	colourPickerIDGenerator = this.idGenerator;
    	switch (this.swatchOption){
    		case 1:this.colourswatch = colourswatch_1; break;
    		case 2:this.colourswatch = colourswatch_2; break;
    		case 3:this.colourswatch = colourswatch_3; break;
    		case 99:this.colourswatch = colourswatch_CUSTOM; break;
    		default:this.colourswatch = colourswatch_1;
    	}
    	this.valueControl = "txtColourPicker_TextBox" + this.id;
    	this.displayControl = "btnColourPicker_Button_" + this.id;
    	this.height = this.colourswatch.length * 14;
    	this.width = this.colourswatch[0].length * 14;
    	var html = "";
    	html += "<TABLE cellspacing=0 cellpadding=0 border=0><TR><TD>";
    	html += "<input name='" + this.displayControl + "' id='" + this.displayControl + "' type='button' style='width:20px; height:20px; border:2px ridge " + this.LF_button_borderColor + ";' value='' onClick='ColourPicker_TogglePicker("+this.id+"); void(0);'>";
    	html += "<IFRAME id='" + COLOUR_CONTAINER_PREFIX + this.id + "' name='" + COLOUR_CONTAINER_PREFIX + this.id + "' SRC='' frameborder=no scrolling=no width='" + this.width + "' height='" + eval(this.height +12) + "' STYLE='position: absolute; display: block; border:1px solid " + this.LF_swatch_borderColor + "'></IFRAME>";
    	html += "</TD><TD>";
    	html += "<input type='text' name='" + this.valueControl + "' id='" + this.valueControl + "' " + (this.allowInput ? "" : "disabled") + " style='width:60px; height:20px; border:1px solid "+this.LF_input_borderColor+"; font: 10px verdana; color: " + this.LF_input_textColor + "; background-color:"+this.LF_input_bgColor+"; " + (!this.showInput ? "display: none;":"") + " ' onblur='ColourPicker_SetBackgroundColour("+this.id+",this.value)'>";
    	html += "</TD></TR></TABLE>";
    	document.write (html);
    	this.parseInitString();
    	window.onload = function(){
    		window.setTimeout ("for (myID in colourPickerMap)colourPickerMap[myID].initSwatch();",1);
    		return true;
    	}
    	
    }
    function ColourPicker_InitSwatch(){
    	if (this.instantiated)return;
    	var html = "<!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'><HTML><HEAD></HEAD><BODY  scroll=no leftmargin='0' bgcolor='" + this.LF_swatch_bgColor + "' topmargin='0' marginwidth='0' marginheight='0' >";
    	html += "<table width=" + this.width + " height=" + this.height + " border=0 cellspacing=0 cellpadding=0>";
    	for (i=0; i < this.colourswatch.length; i++) {
    		html += "<tr>";
    			for (ii=0; ii < this.colourswatch[i].length; ii++) {
    				html += "<td valign=top width=12 height=12 bgcolor="+this.colourswatch[i][ii]+" style='height:12px; width:12px;border:1px solid " + this.LF_swatch_bgColor + ";' onmouseover='this.style.border=\"1px inset " + this.LF_swatch_selectBorderColor + "\";window.parent.status=\"#" + this.colourswatch[i][ii] + "\";window.parent.modifyText("+this.id+", \"#" + this.colourswatch[i][ii] + "\");return true;' onmouseout='this.style.border=\"1px solid " + this.LF_swatch_bgColor + "\";window.parent.status=window.parent.defaultStatus;\'><a href=\"javascript:window.parent.ColourPicker_SetColour("+this.id+",'#"+this.colourswatch[i][ii]+"');void(0);\" style=\"text-decoration:none; font: 10px verdana\">   </a></td>";
    				}
    		html += "</tr>";
    	}
    	html += "</table>";
    	html += "<table height=\"12\" width=" + this.width + " border=0 cellspacing=0 cellpadding=0>";
    	html += "<tr>";
    	if (this.noColor){
    		html += "<td width=\"5%\" valign=\"middle\" align=\"center\"><DIV style=\"border:1px solid " + this.LF_swatch_bgColor + "; width:12px; height:12px; \" onmouseover='this.style.border=\"1px inset " + this.LF_swatch_selectBorderColor + "\";window.parent.status=\"No Color\";window.parent.modifyText("+this.id+",\"No Colour\");return true;' onmouseout=\"this.style.border='1px solid " + this.LF_swatch_bgColor + "';window.parent.status = window.parent.defaultStatus\" onclick=\"window.parent.ColourPicker_TogglePicker("+this.id+"); void(0);\"><a href=\"javascript:window.parent.ColourPicker_SetColour("+this.id+",'')\" style=\"text-decoration:none;font: 10px Arial; color: "+this.LF_swatch_textColor+"\"><B>X</B></a></DIV></td>";
    		html += "<td width=\"20%\" valign=middle NOWRAP style='font: 10px Arial; color: "+this.LF_swatch_textColor+"'>No color</td>";
    	}
    	html += "<td align=right><SPAN id='thisColourObject"+this.id+"' name='thisColourObject"+this.id+"' style='font:10px Arial; color: "+this.LF_swatch_textColor+"'>No Color</SPAN></td>";
    	html += "</tr>";
    	html += "</table></BODY></HTML>";
    	eval (COLOUR_CONTAINER_PREFIX + this.id).document.open();
    	eval (COLOUR_CONTAINER_PREFIX + this.id).document.write(html);
    	eval (COLOUR_CONTAINER_PREFIX + this.id).document.close();
    	getDOMObject (COLOUR_CONTAINER_PREFIX+this.id).style.visibility='hidden';
    	this.instantiated = true;
    }
    function ColourPicker_ParseInitString(){
    	var isColorDefined = new RegExp("(#[A-Fa-f0-9]{6})","gi");
    	var initialCSSString = getDOMObject (this.boundControl).value;
    	var colourString = ["#000000"];
    	if (initialCSSString != "" && initialCSSString != "undefined" )colourString = initialCSSString.match(isColorDefined);
    	if (colourString.length > 0){
    		getDOMObject (this.valueControl).value = colourString[0];
    		getDOMObject (this.displayControl).style.backgroundColor = colourString[0];
    	}
    }
    function isColour(colour){
    	var myRegExp = new RegExp("#[A-Fa-f0-9]{6}","gi");
    	if (colour.search(myRegExp)!=-1)return true;
    	return false;
    }
    function ColourPicker_SetColour(id, colour){
    	colour = colour.toUpperCase();
    	if (!isColour(colour) && colour != '')return;
    	getDOMObject (colourPickerMap[id].valueControl).value = colour;
    	getDOMObject (colourPickerMap[id].boundControl).value = colour;
    	getDOMObject (colourPickerMap[id].displayControl).style.backgroundColor = colour;
    	eval(colourPickerMap[id].scriptAction);
    	colourPickerMap[id].hide();
    }
    function ColourPicker_SetBackgroundColour(id, colour){
    	colour = colour.toUpperCase();
    	if (!isColour(colour) && colour != '')return;
    	if (!colourPickerMap[id].noColor && colour.length == 0)return;
    	if (colour.length > 7) return;
    	getDOMObject (colourPickerMap[id].boundControl).value = colour;
    	getDOMObject (colourPickerMap[id].displayControl).style.backgroundColor = colour;
    	eval(colourPickerMap[id].scriptAction);
    } 
    function ColourPicker_TogglePicker(id){
    	if (isNS4)return;
    	if (colourPickerMap[id].isShowing){
    		colourPickerMap[id].hide();
    	}else{
    		for (i in colourPickerMap){
    			colourPickerMap[i].hide();	
    		}
    		colourPickerMap[id].show();
    	}
    }
    function ColourPicker_ShowThisPicker(){
    	if (isNS4)return;
    	getDOMObject (COLOUR_CONTAINER_PREFIX+this.id).style.visibility='visible';
    	this.isShowing = true;
    }
    function ColourPicker_HideThisPicker(){
    	if (isNS4)return;
    	getDOMObject (COLOUR_CONTAINER_PREFIX+this.id).style.visibility='hidden';
    	this.isShowing = false;
    }
    function modifyText(id, text) {
    	if (isNS4)return;
    	getFrameDOMObject ("thisColourObject" + id, COLOUR_CONTAINER_PREFIX + id).childNodes[0].data = text;
    }
    


Finally, the 4 views for the system.


add.thtml

View Template:
``````````````

::

    
    <h2>New Css Element</h2>
    <form action="<?php echo $html->url('/dyna_css/add'); ?>" method="post">
    <table cellspacing="0" cellpadding="3">
    <?php
    	$tro = "\t<tr>\n";
    	$trc = "\t</tr>\n";
    	$tdo = "\n\t\t<td>\n\t\t\t";
    	$tdc = "\n\t\t</td>\n";
    	$cnt = 0;
    	foreach ($fldNameArray as $key=> $value)
    	{
    		$cnt++;
    		echo $tro;
    		echo $tdo . $form->labelTag('DynaCss/'.$value, Inflector::camelize($value)) . $tdc;
    		echo $tdo . $html->tagErrorMsg('DynaCss/'.$value, 'Please enter the ' . Inflector::camelize($value));
    		if (in_array($value, $fldArray))
    		{
    			echo $html->selectTag('DynaCss/'.$value, $$value) . $tdc;
    		} else {
    			echo $html->input('DynaCss/'.$value, array('size' => '10'));
    			if (strpos($value, 'olor'))
    			{
    ?>
    <script language="JavaScript" type="text/javascript">
    	var colorPickerDemo = new ColourPicker('<?php echo 'DynaCss' . Inflector::camelize($value); ?>','',1);
    	colorPickerDemo.showInput = false;
    	colorPickerDemo.allowInput = false;
    	colorPickerDemo.Instantiate();
    </script>
    <?php
    			}
    			echo $tdc;
    
    		}
    		echo $trc;
    		if ($cnt == 12)
    		{
    			$cnt = 0;
    			echo $tro . $tdo;
    ?>
    <div class="submit">
    	<?php echo $html->submit('Add');?>
    </div>
    <?php
    
    ?>
    <ul class="actions">
    <li><?php echo $html->link('List Css Elements', '/dyna_css/index')?></li>
    </ul>
    <?php
    
    			echo $trc;
    		}
    	}
    ?>
    </table>
    <div class="submit">
    	<?php echo $html->submit('Add');?>
    </div>
    </form>
    <ul class="actions">
    <li><?php echo $html->link('List Css Elements', '/dyna_css/index')?></li>
    </ul>
    



edit.thtml

View Template:
``````````````

::

    
    <h2>Edit Dyna Css</h2>
    <form action="<?php echo $html->url('/dyna_css/edit/'.$html->tagValue('DynaCss/id')); ?>" method="post">
    <table cellspacing="0" cellpadding="3">
    <?php
    	$tro = "\t<tr>\n";
    	$trc = "\t</tr>\n";
    	$tdo = "\n\t\t<td>\n\t\t\t";
    	$tdc = "\n\t\t</td>\n";
    	$cnt = 0;
    	foreach ($data['DynaCss'] as $key=> $value)
    	{
    		$cnt++;
    		echo $tro;
    		echo $tdo . $form->labelTag('DynaCss/'.$key, Inflector::camelize($key)) . $tdc;
    		echo $tdo . $html->tagErrorMsg('DynaCss/'.$key, 'Please enter the ' . Inflector::camelize($key) . '<br />');
    		if (in_array($key, $fldArray))
    		{
    			echo $html->selectTag('DynaCss/'.$key, $$key, $value) . $tdc;
    		} else {
    			echo $html->input('DynaCss/'.$key, array('size' => '10', 'value'=>$value));
    			if (strpos($key, 'olor'))
    			{
    ?>
    <script language="JavaScript" type="text/javascript">
    	var colorPickerDemo = new ColourPicker('<?php echo 'DynaCss' . Inflector::camelize($key); ?>','',1);
    	colorPickerDemo.showInput = false;
    	colorPickerDemo.allowInput = false;
    	colorPickerDemo.Instantiate();
    </script>
    <?php
    			}
    			echo $tdc;
    		}
    		echo $trc;
    		if ($cnt == 12)
    		{
    			$cnt = 0;
    			echo $tro . $tdo;
    ?>
    <div class="submit">
    	<?php echo $html->submit('Save Changes');?>
    </div>
    <?php
    
    ?>
    <ul class="actions">
    <li><?php echo $html->link('Delete Element','/dyna_css/delete/' . $html->tagValue('DynaCss/id'), null, 'Are you sure you want to delete: id ' . $html->tagValue('DynaCss/id'));?>
    <li><?php echo $html->link('List Css Elements', '/dyna_css/index')?></li>
    </ul>
    <?php
    
    			echo $trc;
    		}
    	}
    ?>
    </table>
    <?php echo $html->hidden('DynaCss/id')?>
    <div class="submit">
    	<?php echo $html->submit('Save Changes');?>
    </div>
    </form>
    <ul class="actions">
    <li><?php echo $html->link('Delete Element','/dyna_css/delete/' . $html->tagValue('DynaCss/id'), null, 'Are you sure you want to delete: id ' . $html->tagValue('DynaCss/id'));?>
    <li><?php echo $html->link('List Css Elements', '/dyna_css/index')?></li>
    </ul>
    



index.thtml

View Template:
``````````````

::

    
    <div class="dynaCss">
    <h2>List Dyna Css</h2>
    
    <table cellpadding="0" cellspacing="5">
    <tr>
    	<th>Tag</th>
    	<th>Tagtype</th>
    	<th>Actions</th>
    </tr>
    <?php foreach ($dynaCsses as $dynaCss): ?>
    <tr>
    	<td><?php echo $dynaCss['DynaCss']['tag']; ?></td>
    	<td class="actions">
    		<?php echo $html->link('View','/dyna_css/view/' . $dynaCss['DynaCss']['id'])?>
    		<?php echo $html->link('Edit','/dyna_css/edit/' . $dynaCss['DynaCss']['id'])?>
    		<?php echo $html->link('Delete','/dyna_css/delete/' . $dynaCss['DynaCss']['id'], null, 'Are you sure you want to delete id : ' . $dynaCss['DynaCss']['id'])?>
    	</td>
    </tr>
    <?php endforeach; ?>
    </table>
    
    <ul class="actions">
    	<li><?php echo $html->link('New Element', '/dyna_css/add'); ?></li>
    </ul>
    </div>



view.thtml

View Template:
``````````````

::

    
    <div class="dynaCss">
    <h2>View Css Element</h2>
    
    <?php
    	pr($dynaCss);
    ?>
    <ul class="actions">
    	<li><?php echo $html->link('Edit Element',   '/dyna_css/edit/' . $dynaCssId) ?> </li>
    	<li><?php echo $html->link('Delete Element', '/dyna_css/delete/' . $dynaCssId, null, 'Are you sure you want to delete: id ' . $dynaCssId . '?') ?> </li>
    	<li><?php echo $html->link('List Elements',   '/dyna_css/index') ?> </li>
    	<li><?php echo $html->link('New Element',	'/dyna_css/add') ?> </li>
    </ul>
    
    </div>
    



There is room for improvement in the ssytem I am sure. In the future I
will be looking to add in several features including drop down select
lists for the CSS measurement type like px,in,pt etc...

I will also be looking into setting up validation for each field based
on W3C CSS2 rules. It will give me a chance to improve my RegExp
skills :)

[p] If you have any suggestions, complaints, etc... please let me
know.

Ron Chaplin
T73 Software and Design
`http://t73-softdesign.com`_
`1`_|`2`_|`3`_|`4`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_
+ `Page 4`_

.. _Page 4: :///articles/view/4caea0df-c518-42f5-a8c2-490482f0cb67/lang:eng#page-4
.. _Page 2: :///articles/view/4caea0df-c518-42f5-a8c2-490482f0cb67/lang:eng#page-2
.. _Page 3: :///articles/view/4caea0df-c518-42f5-a8c2-490482f0cb67/lang:eng#page-3
.. _Page 1: :///articles/view/4caea0df-c518-42f5-a8c2-490482f0cb67/lang:eng#page-1
.. _http://t73-softdesign.com: http://t73-softdesign.com/
.. meta::
    :title: Dynamic Css for CMS and More!
    :description: CakePHP Article related to CMS,Dynamic Css,Plugins
    :keywords: CMS,Dynamic Css,Plugins
    :copyright: Copyright 2007 t73net
    :category: plugins

