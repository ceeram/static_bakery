Cake Meet Pie
=============

by %s on January 14, 2008

A Helper class that integrates EQ Smooth Pie Graph with your Cake
application. EQ Smooth Pie Graph generates pie charts with smooth
edges, looks great, and it's free - read on.

For a Cake application that needed the ability to generate pie charts
I investigated many of the available classes for PHP. Ultimately I
choose EQ Smooth Pie Graph because it works with PHP 4/5, looks great
and it's free! Don't take my word for it - here is a clip from a
screen shot of my EQ Smooth Pie Graph implementation:

`http://www.electricprism.com/aeron/eq_pie.gif`_

To integrate EQ Smooth Pie Graph with your Cake application follow
these steps:


1. Download EQ Smooth Pie Graph from phpclasses.org!
````````````````````````````````````````````````````

The EQ Smooth Pie Graph class is provided free for non-commercial use
by the author Elibert Johan (which I interpret as just don't try to
sell his class!). Here is a direct link
`http://phpclasses.ca/browse/package/2498.html`_ from a Mirror site.
The file you are interested in is called "class_eq_pie.php" and must
be placed in the "vendors" folder of your Cake application.

Note: In order to function as I needed, I had to comment out line 172
of the class.

::

    
    // header('Content-type: image/png');



2. Prepare the Data for your Pie Chart!
```````````````````````````````````````

The eq_pie class requires data to be a nested array with the following
structure: array("title str", value_int, "#hex_color_str")

Personally, I prefered to prepare the data in a controller before
passing it to the view. An example of my controller code could be:

::

    
    $eq_data = array();
    
    $eq_data[] = array('English', 10, '#E5E5E5');
    $eq_data[] = array('Spanish', 5, '#C1D0D6');
    
    $this->set('eq_data', $eq_data);



3. Initialize the Pie Chart Helper!
```````````````````````````````````

Copy the following Helper class and paste it into a file named
"pie_chart.php" in the "view/helpers" folder of your Cake application.


Helper Class:
`````````````

::

    <?php 
    /**
     * Pie Chart Helper
     *
     * @version 1.0
     * @author Aeron Glemann <http://www.electricprism.com/aeron>
     * @license MIT Style License
     */
    
    class PieChartHelper extends Helper {
    	var $helpers = array('Html');
    	
    	var $total = 0;
    	
    	var $data = null;
    	
    	var $path = '';
    	
    	var $eq_pie = null;
    
    	/**
    	 * Create eq_pie class instance
    	 *
    	 * @param array $data Data for pie chart
    	 * @return bool Successful
    	 */
    
    	function create($data = array()) {
    		if (empty($data)) {
    			return false;
            }
    
    		foreach($data as $eq_data) {
    			$this->total += $eq_data[1];   
    		}
    
    		if ($this->total == 0) {
    			return false;  
            }
    
    		$this->data = $data;  
    
    		$this->path = WWW_ROOT.'img'.DS.'eq_pie';
    
    		if (!is_dir($this->path)) {			
    			mkdir($this->path, 0777);  
    			
    			if (!is_dir($this->path)) {  
    				debug("Error, Unable to create folder at $this->path (check permissions).");
    				return false;			
    			}
    		}    
    
    		vendor('class_eq_pie');
    
    		$this->eq_pie = new eq_pie;  
    
    		return true;   
    	}
    
    
    	/**
    	 * Draw a pie chart
    	 *
    	 * @param int $width Width of pie chart graphic in pixels
    	 * @param int $height Height of pie chart graphic in pixels (controls perspective, does not include $shadow)
    	 * @param int $shadow How "tall" the pie chart is
    	 * @param string $backgroundColor Background color as hexidecimal
    	 * @param bool $legend Draw legend too?
    	 * @return string HTML image
    	 */
    
    	function draw($width = 100, $height = 100, $shadow = 10, $backgroundColor = '#ffffff', $legend = 1) {
    		$hash = md5($this->_implode_r(array($this->data, $width, $height, $shadow, $backgroundColor, $legend)));
    
    		$alt = array();
    
    		foreach($this->data as $eq_data) {
    			$alt[] = $eq_data[0].' '.number_format($eq_data[1] / $this->total * 100, 1).'%';
    		}
    
    		$alt = join(', ', $alt);    
    
    		$filename = $this->path.DS.$hash.'.png';
    
    		if (is_readable($filename)) {
    			list($w, $h) = getimagesize($filename); 
    
    			return $this->Html->image(('eq_pie'.DS.$hash.'.png'), array('alt' => $alt, 'width' => $w, 'height' => $h));
    		}
    
    		$this->eq_pie->MakePie($filename, $width, $height, $shadow, $backgroundColor, $this->data, $legend); 
    
    		list($w, $h) = getimagesize($filename); 
    
    		return $this->Html->image(('eq_pie'.DS.$hash.'.png'), array('width' => $w, 'height' => $h));
    	}
    
    
    	/**
    	 * Create an HTML legend
    	 *
    	 * @param string $class Name of CSS class to apply to legend div
    	 * @return string HTML div and unordered list 
    	 */
    
    	function legend($class = 'legend') {
    		$out = array();
    		$out[] = "<div class='$class'>";
    		$out[] = "<ul>";
    
    		foreach($this->data as $eq_data) {
    			$hash = md5($eq_data[2]);
    
    			$filename = $this->path.DS.$hash.'.png';
    
    			if (!is_readable($filename)) {				        
    				$img = @imagecreatetruecolor(1, 1);
    				$r = hexdec(substr($eq_data[2],1,2));
    				$g = hexdec(substr($eq_data[2],3,2));
    				$b = hexdec(substr($eq_data[2],5,2));   			  
    
    				imagefill($img, 0, 0, imagecolorallocate($img, $r, $g, $b));
    
    				imagepng($img, $filename);
    				imagedestroy($img);
    			}
    
    			$image = $this->Html->image(('eq_pie'.DS.$hash.'.png'), array('alt' => $eq_data[2]));
    
    			$out[] = sprintf("<li>$image %s</li>", $eq_data[0].' <span>'.number_format($eq_data[1] / $this->total * 100, 1).'%</span>'); 
    		}
    
    		$out[] = "</ul>";
    		$out[] = "</div>";
    
    		return join("\n", $out);
    	}
    
    
    	/**
    	 * Convenience function to merge multi-dimensional array as string
    	 *
    	 * @param array $pieces Multi-dimensional array to merge
    	 * @return string Merged array
    	 */
    
    	function _implode_r($pieces) {
    		$out = "";
    
    		foreach ($pieces as $piece) {
    			if (is_array($piece)) $out .= $this->_implode_r($piece);
    			else $out .= $piece;
    		}
    
    		return $out;
    	} 
    }
    ?>


The Helper is initialized with the "create" function. An example would
be (using the data from step 2):

::

    
    <?php if ($pieChart->create($eq_data)) {
    	// drawing code goes here
    } ?>


The Helper requires write permission (chmod +w) to the "webroot/img"
folder in order to function correctly. It will return an error if that
is not the case.


4. Draw a Pie Chart!
````````````````````

Once the Helper has been initialized you can draw as many charts as
you need with the "draw" function. A complete example would be:

::

    
    <?php if ($pieChart->create($eq_data)) {
    	echo $pieChart->draw(180, 100, 20); 
    } ?>


The parameters used above are 180px for the image width, 100px for the
image height and 20px for the "height" of the graph itself (how tall
it is). Other parameters not specified are "backgroundColor" as a
hexidecimal string (default is white) and "legend" a boolean that
tells the class whether to draw a legend or not (default is true).
Personally I prefered a legend rendered in HTML since it gave me the
control of styling with CSS and the benefit of being more accessible.
So I also added the "legend" function to the Helper which does just
that:

::

    
    <?php if ($pieChart->create($eq_data)) {
    	echo $pieChart->draw(180, 100, 20, '#ffffff', 0); 
    	echo $pieChart->legend(); 
    } ?>


The "legend" function takes an optional parameter "class" which is the
className applied to the bounding div of the legend as a string
(default is legend). The legend itself is an unordered list within the
aforementioned div. An example of the HTML (for styling) could be:

::

    
    <div class='legend'>
    	<ul>
    		<li><img src="/img/eq_pie/00dfbf30c9377fa1bbc0a247fb832f23.png" alt="#E5E5E5" /> Inglés <span>60.0%</span></li>
    		<li><img src="/img/eq_pie/164e9adadaee35857bc637a77b4ed7c5.png" alt="#C1D0D6" /> Español <span>40.0%</span></li>
    	</ul>
    </div>


Note: The legend function actually creates colored images to use for
the key - the benefit of this? You can print the chart and the colors
of the legend key will be retained!


.. _http://phpclasses.ca/browse/package/2498.html: http://phpclasses.ca/browse/package/2498.html
.. _http://www.electricprism.com/aeron/eq_pie.gif: http://www.electricprism.com/aeron/eq_pie.gif
.. meta::
    :title: Cake Meet Pie
    :description: CakePHP Article related to pie chart,smooth pie graph,Helpers
    :keywords: pie chart,smooth pie graph,Helpers
    :copyright: Copyright 2008 
    :category: helpers

