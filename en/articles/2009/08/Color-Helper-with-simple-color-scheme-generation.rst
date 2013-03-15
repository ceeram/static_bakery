

Color Helper with simple color scheme generation
================================================

by %s on August 10, 2009

So, after having to come up with a way to generate color schemes on
the fly with only one input color I created this helper. With this
helper, ColorHelper you will be able to convert from any of the
available color formats to any other, convert them to a web safe
color, and generate color schemes off of them.
The first thing you should know about this helper is that it was made
using a different type of programming that would hopefully make it
easier to understand, and easier to modify. This exotic style of
programming is known as Fluent Interfaces; and, in a nutshell it lets
you do the following:

::

    
      echo $color->rgb(array('r'=>200, 'g'=> 125, 'b'=> 45))->asWebsafe()->toHtml();
      //OUTPUT: #C09030

Notice how the code is all on one line; and, that line reads almost
like a sentence? Nice isn't it? The way this is achieved is by
returning $this at the end of each function that you want to be
fluent.

The easiest way to understand this helper is to realize that it is
divided into a prefix, a modifier, and a suffix. Taking our example
color from above, we could do the following:

::

    
      echo $color->rgb(array('r'=>200, 'g'=> 125, 'b'=> 45))->asWebsafe()->toHtml();
      //OUTPUT: #C09030
    
      echo $color->hsv(array('h'=>31, 's'=> 78, 'v'=> 78))->asWebsafe()->toHtml();
      //OUTPUT: #C09030
    
      echo $color->hsl(array('h'=>31, 's'=> 44, 'l'=> 48))->asWebsafe()->toHtml();
      //OUTPUT: #C09030
    
      pr($color->html("#C09030")->toRgb());
      /* OUTPUT 
        Array
        (
          [ r ] => 192
          [ g ] => 144
          [ b ] => 48
        )
      */

You see by changing the first part of the statement, the prefix, we
are able to enter the same color in different formats. Also notice the
modifier 'asWebsafe'; what this does is convert what ever color you
have to the nearest web safe color.

In a similar fashion we can change our output format:

::

    
      $color->rgb(array('r'=>200, 'g'=> 125, 'b'=> 45))->toHtml();
      //RETURN: #C87D2D
    
      $color->rgb(array('r'=>200, 'g'=> 125, 'b'=> 45))->toHsv();
      //RETURN: array('h' => 31, 's' => 78, 'v' => 78)
    
      $color->rgb(array('r'=>200, 'g'=> 125, 'b'=> 45))->toHsl();
      //RETURN: array('h' => 31, 's' => 44, 'l' => 48)
    
      $color->rgb(array('r'=>200, 'g'=> 125, 'b'=> 45))->asWebsafe()->toRgb();
      //RETURN: array('r' => 192, 'g' => 144, 'b' => 48)
    
      $color->rgb(array('r'=>200, 'g'=> 125, 'b'=> 45))->asWebsafe()->toHsv();
      //RETURN: array('h' => 40, 's' => 75, 'v' => 75)
    
      $color->rgb(array('r'=>200, 'g'=> 125, 'b'=> 45))->asWebsafe()->toHsl();
      //RETURN: array('h' => 40, 's' => 39, 'l' => 47)
    
      $color->rgb(array('r'=>200, 'g'=> 125, 'b'=> 45))->asWebsafe()->toHtml();
      //RETURN: '#C09030'

As for the color scheme generation. I have provided 7 common color
schemes, which can be generated as a modifier to your statement. The
schemes I have included are: Triadic, Split Complements, Analogous,
Monochromatic, Analogous Complement, lightness, and saturation. They
are generated as follows:

::

    


Copy and paste the following helper into a new file located in
/app/views/helpers/ and named color.php


Helper Class:
`````````````

::

    <?php 
      class ColorHelper extends AppHelper {
        var $color = array('rgb' => array('r'=> 0, 'g'=> 0, 'b'=> 0),
                           'hsl' => array('h'=> 0, 's'=> 0, 'l'=> 0),
                           'hsv' => array('h'=> 0, 's'=> 0, 'v'=> 0),
                           'html'=> '000000');
        var $harmonies = false;
    
        function html($html)            { $this->__reset(); return $this->__fromHtml($html); }
        function rgb($r=0, $g=0, $b=0)  { $this->__reset(); return $this->__fromRgb($r, $g, $b); }
        function hsv($h=0, $s=0, $v=0)  { $this->__reset(); return $this->__fromHsv($h, $s, $v); }
        function hsl($h=0, $s=0, $l=0)  { $this->__reset(); return $this->__fromHsl($h, $s, $l); }
    
        function toHtml() { return $this->__to('html'); }
        function toHsv()  { return $this->__to('hsv'); }
        function toHsl()  { return $this->__to('hsl'); }
        function toRgb()  { return $this->__to('rgb'); }
        function toAll()  { return $this->__to('all'); }
    
        function asSchemeTriadic()             { return $this->__asScheme(0); }
        function asSchemeSplitComplement()     { return $this->__asScheme(1); }
        function asSchemeAnalogous()           { return $this->__asScheme(2); }
        function asSchemeMonochromatic()       { return $this->__asScheme(3); }
        function asSchemeAnalogousComplement() { return $this->__asScheme(4); }
        function asSchemeLightness()           { return $this->__asScheme(5); }
        function asSchemeSaturation()          { return $this->__asScheme(6); }
    
        function asWebsafe() {
          if($this->harmonies) {
            foreach($this->harmonies as $k => &$harm) {
              $rgb = $this->__roundRgb($this->__hslrgb($harm['h'], $harm['s'], $harm['l']));
              $harm = $this->__rgbhsl($rgb);
            }
          } else {
            $rgb  = $this->__roundRgb($this->color['rgb']);
            $html = $this->__rgbhtml($rgb);
            $hsv  = $this->__rgbhsv($rgb['r'], $rgb['g'], $rgb['b']);
            $hsl  = $this->__rgbhsl($rgb['r'], $rgb['g'], $rgb['b']);
            $this->__storeColor($rgb, $hsv, $hsl, $html);
          }
          return $this;
        }
    
        function __reset() {
          $this->color = array('rgb' => array('r'=> 0, 'g'=> 0, 'b'=> 0),
                           'hsl' => array('h'=> 0, 's'=> 0, 'l'=> 0),
                           'hsv' => array('h'=> 0, 's'=> 0, 'v'=> 0),
                           'html'=> '000000');
          $this->harmonies = false;
        }
        
        function __roundRgb($rgb) {
          $rgb['r'] = round($rgb['r'] / 0x30) * 0x30;
          $rgb['g'] = round($rgb['g'] / 0x30) * 0x30;
          $rgb['b'] = round($rgb['b'] / 0x30) * 0x30;
          return $rgb;
        }
    
        function __fromHtml($html) {
          $html = str_replace('#', '', $html);
          if(strlen($html) == 6) {
            $hexvals = str_split($html, 2);
          } else {
            $hexvals = str_split($html);
            foreach($hexvals as &$hex) $hex .= $hex;
          }
          $rgb = array('r'=> hexdec($hexvals[0]), 'g'=> hexdec($hexvals[1]), 'b'=> hexdec($hexvals[2]));
          $html = $this->__rgbhtml($rgb);
          $hsv = $this->__rgbhsv($rgb['r'], $rgb['g'], $rgb['b']);
          $hsl = $this->__rgbhsl($rgb['r'], $rgb['g'], $rgb['b']);
          $this->__storeColor($rgb, $hsv, $hsl, $html);
          return $this;
        }
    
        function __fromRgb($r, $g, $b) {
          if(is_array($r)) { $g = $r['g']; $b = $r['b']; $r = $r['r']; }
          $html = $this->__rgbhtml($r, $g, $b);
          $hsv = $this->__rgbhsv($r, $g, $b);
          $hsl = $this->__rgbhsl($r, $g, $b);
          $this->__storeColor(array('r'=> $r, 'g'=> $g, 'b'=> $b), $hsv, $hsl, $html);
          return $this;
        }
    
        function __fromHsv($h, $s, $v) {
          if(is_array($h)) { $s = $h['s']; $v = $h['v']; $h = $h['h']; }
          $rgb  = $this->__hsvrgb($h, $s, $v);
          $html = $this->__rgbhtml($rgb);
          $hsl  = $this->__rgbhsl($rgb['r'], $rgb['g'], $rgb['b']);
          $this->__storeColor($rgb, array('h'=> $h, 's'=> $s, 'v'=> $v), $hsl, $html);
          return $this;
        }
    
        function __fromHsl($h, $s, $l) {
          if(is_array($h)) { $s = $h['s']; $l = $h['l']; $h = $h['h']; }
          $rgb  = $this->__hslrgb($h, $s, $l);
          $html = $this->__rgbhtml($rgb);
          $hsv  = $this->__rgbhsv($rgb['r'], $rgb['g'], $rgb['b']);
          $this->__storeColor($rgb, $hsv, array('h'=> $h, 's'=> $s, 'l'=> $l), $html);
          return $this;
        }
    
        function __asScheme($type) {
          $this->harmonies = array();
          switch($type) {
            case 0: //Triadic
              $hsl = $this->color['hsl'];
              $$this->harmonies[] = $this->hsl($hsl['h'] + 120, $hsl['s'], $hsl['l']);
              $$this->harmonies[] = $this->hsl($hsl['h'], $hsl['s'], $hsl['l']);
              $$this->harmonies[] = $this->hsl($hsl['h'] - 120, $hsl['s'], $hsl['l']);
            break;
            case 1: //Split Complements
              $hsl = $this->color['hsl'];
              $$this->harmonies[] = $this->hsl($hsl['h'] + 150, $hsl['s'], $hsl['l']);
              $$this->harmonies[] = $this->hsl($hsl['h'], $hsl['s'], $hsl['l']);
              $$this->harmonies[] = $this->hsl($hsl['h'] - 150, $hsl['s'], $hsl['l']);
            break;
            case 2: //Analogous
              $hsl = $this->color['hsl'];
              $$this->harmonies[] = $this->hsl($hsl['h'] + 30, $hsl['s'], $hsl['l']);
              $$this->harmonies[] = $this->hsl($hsl['h'], $hsl['s'], $hsl['l']);
              $$this->harmonies[] = $this->hsl($hsl['h'] - 30, $hsl['s'], $hsl['l']);
            break;
            case 3: //Monochromatic
              $hsl = $this->color['hsl'];
              for($i = 10; $i <= 100; $i+=10) {
                for($k = 10; $k <= 100; $k+=10) {
                  $$this->harmonies[] = $this->hsl($hsl['h'], $i, $k);
                }
              }
            break;
            case 4: //Analogous Complement
              $hsl = $this->color['hsl'];
              $$this->harmonies[] = $this->hsl($hsl['h'] - 30, $hsl['s'], $hsl['l']);
              $$this->harmonies[] = $this->hsl($hsl['h'], $hsl['s'], $hsl['l']);
              $$this->harmonies[] = $this->hsl($hsl['h'] + 30, $hsl['s'], $hsl['l']);
              $$this->harmonies[] = $this->hsl($hsl['h'] - 150, $hsl['s'], $hsl['l']);
              $$this->harmonies[] = $this->hsl($hsl['h'] - 180, $hsl['s'], $hsl['l']);
              $$this->harmonies[] = $this->hsl($hsl['h'] - 210, $hsl['s'], $hsl['l']);
            break;
            case 5: //lightness
              $hsl = $this->color['hsl'];
              $step = round($hsl['l'] / 10) * 10;
              for($i = 10; $i <= 100; $i += 10) {
                if($i == $step) {
                  $$this->harmonies[] = $this->hsl($hsl['h'], $hsl['s'], $hsl['l']);
                } else {
                  $$this->harmonies[] = $this->hsl($hsl['h'], $hsl['s'], $i);
                }
              }
            break;
            case 6: //saturation
              $hsl = $this->color['hsl'];
              $step = round($hsl['s'] / 10) * 10;
              for($i = 10; $i <= 100; $i += 10) {
                if($i == $step) {
                  $$this->harmonies[] = $this->hsl($hsl['h'], $hsl['s'], $hsl['l']);
                } else {
                  $$this->harmonies[] = $this->hsl($hsl['h'], $i, $hsl['l']);
                }
              }
            break;
          }
    
          return $harmonies;
        }
    
        function __to($type) {
          $out = '';
          if($this->harmonies) {
            $out = array();
            foreach($this->harmonies as $k => &$harm) {
              switch($type) {
                case 'hsl':
                  $out[] = $harm;
                break;
                case 'hsv':
                  $out[] = $this->__rgbhsv($this->__hslrgb($harm));
                break;
                case 'rgb':
                  $out[] = $this->__hslrgb($harm);
                break;
                case 'html':
                  $out[] = $this->__rgbhtml($this->__hslrgb($harm));
                break;
                default:
                  $c = array();
                  $c['hsl']  = $harm;
                  $c['rgb']  = $this->__hslrgb($harm);
                  $c['hsv']  = $this->__rgbhsv($c['rgb']);
                  $c['html'] = $this->__rgbhtml($c['rgb']);
                  $out[] = $c;
                break;
              }
            }
          } else if($type == 'all') {
            $out = $this->color;
          } else {
            $out = $this->color[strtolower($type)];
          }
          return $out;
        }
    
        function __rgbhsv ($r=0, $g=0, $b=0) {
          if(is_array($r)) { $g = $r['g']; $b = $r['b']; $r = $r['r']; }
          $min   = min($r, $g, $b);
          $max   = max($r, $g, $b);
          $delta = $max-$min;
    
          //Convert the brightness to a percentage
          $value = round($max * 100 / 255);
    
          if ($max != 0 && $delta != 0) $saturation = round($delta * 100 / $max);
    
          //Compute the hue
          if     ($r == $max)   $hue = 0 + ($g - $b) / $delta;
          elseif ($g == $max)   $hue = 2 + ($b - $r) / $delta;
          else                  $hue = 4 + ($r - $g) / $delta;
    
          $hue = round($hue * 60);
          if ($hue < 0) $hue += 360;
    
          return array('h'=> $hue, 's'=> $saturation, 'v'=> $value);
        }
    
        function __rgbhsl($r=0, $g=0, $b=0) {
          if(is_array($r)) { $g = $r['g']; $b = $r['b']; $r = $r['r']; }
          $var_r = ($r / 0xFF);
          $var_g = ($g / 0xFF);
          $var_b = ($b / 0xFF);
    
          $var_min = min($var_r, $var_g, $var_b);
          $var_max = max($var_r, $var_g, $var_b);
          $del_max = $var_max - $var_min;
    
          $lightness  = round(($var_min + $var_max) * 100 / 2);
          $saturation = $hue = 0;
    
          if ($var_min != $var_max) {
            $saturation = round($del_max * 100 / (($lightness < 0.5) ? $var_max + $var_min : 2 - $del_max));
    
            if      ($var_r == $var_max) $hue = 0 + ($var_g - $var_b) / ($del_max);
            else if ($var_g == $var_max) $hue = 2 + ($var_b - $var_r) / ($del_max);
            else if ($var_b == $var_max) $hue = 4 + ($var_r - $var_g) / ($del_max);
    
            $hue = round($hue * 60);
            if ($hue < 0) $hue += 360;
          }
          return array('h'=> $hue, 's'=> $saturation, 'l'=> $lightness);
        }
    
        function __rgbhtml($r=0, $g=0, $b=0) {
          if(is_array($r)) { $g = $r['g']; $b = $r['b']; $r = $r['r']; }
          return strtoupper(sprintf('#%02X%02X%02X', $r, $g, $b));
        }
    
        function __hsvrgb($h=0, $s=0, $v=0) {
          if(is_array($h)) { $s = $h['s']; $v = $h['v']; $h = $h['h']; }
          $s = ($s > 1) ? $s / 100 : $s;
          $v = ($v > 1) ? $v / 100 : $v;
          if($s == 0) {
            $r = $g = $b = $v * 0xFF;
          } else {
            $var_h = $h * 6;
            $var_i = floor( $var_h );
            $var_1 = $v * ( 1 - $s );
            $var_2 = $v * ( 1 - $s * ( $var_h - $var_i ) );
            $var_3 = $v * ( 1 - $s * (1 - ( $var_h - $var_i ) ) );
    
            if       ($var_i == 0) { $var_r = $v     ; $var_g = $var_3  ; $var_b = $var_1 ; }
            else if  ($var_i == 1) { $var_r = $var_2 ; $var_g = $v      ; $var_b = $var_1 ; }
            else if  ($var_i == 2) { $var_r = $var_1 ; $var_g = $v      ; $var_b = $var_3 ; }
            else if  ($var_i == 3) { $var_r = $var_1 ; $var_g = $var_2  ; $var_b = $v     ; }
            else if  ($var_i == 4) { $var_r = $var_3 ; $var_g = $var_1  ; $var_b = $v     ; }
            else                   { $var_r = $v     ; $var_g = $var_1  ; $var_b = $var_2 ; }
    
            $r = $var_r * 0xFF;
            $g = $var_g * 0xFF;
            $b = $var_b * 0xFF;
          }
          return array('r'=> $r, 'g'=> $g, 'b'=> b);
        }
    
        function __hslrgb($h=0, $s=0, $l=0) {
          if(is_array($h)) { $s = $h['s']; $l = $h['l']; $h = $h['h']; }
          $r = $g = $b = $l;
          $s = ($s > 1) ? $s / 100 : $s;
          $l = ($l > 1) ? $l / 100 : $l;
          if ($s != 0) {
            $temp2 = $l * ($s + 1);
            if ($l >= 0.5) $temp2 = $l + $s - $l * $s;
    
            $temp1 = 2 * $l - $temp2;
            $h = $h / 360;
            $r = $this->__hslHelper($temp1, $temp2, $h + 1.0/3.0);
            $g = $this->__hslHelper($temp1, $temp2, $h);
            $b = $this->__hslHelper($temp1, $temp2, $h - 1.0/3.0);
          }
          $r *= 0xFF;
          $g *= 0xFF;
          $b *= 0xFF;
    
          return array('r'=> $r, 'g'=> $g, 'b'=> $b);
        }
    
        function __hslHelper($temp1, $temp2, $temp3) {
          if      ($temp3 < 0)         $temp3 = $temp3 + 1.0;
          else if ($temp3 > 1.0)       $temp3 = $temp3 - 1.0;
          if      (6.0 * $temp3 < 1.0) $temp3 = $temp1 + ($temp2 - $temp1)*6.0*$temp3;
          else if (2.0 * $temp3 < 1.0) $temp3 = $temp2;
          else if (3.0 * $temp3 < 2.0) $temp3 = $temp1 + ($temp2 - $temp1)*(2.0/3.0-$temp3)*6.0;
          else                         $temp3 = $temp1;
    
          return $temp3;
        }
    
        function __storeColor($rgb, $hsv, $hsl, $html) {
          $this->color = array('rgb' =>$rgb,
                               'hsv' =>$hsv,
                               'hsl' => $hsl,
                               'html'=>$html);
        }
      }
    ?>


.. meta::
    :title: Color Helper with simple color scheme generation
    :description: CakePHP Article related to ,Helpers
    :keywords: ,Helpers
    :copyright: Copyright 2009 
    :category: helpers

