DateHelper for fuzzy date differences
=====================================

by g2010a on April 12, 2008

A semi-useful helper that calculates the difference between two dates
and gives it in *slightly* fuzzy logic: "almost 2 years," "over 1
month".
Some of the coding is strange and flat out improper, but that's why
I'm putting out here, because many minds think better than one. It's
also incomplete, I would like to add a few more options; but once
again, it might be useful to some as it is.
The helper is set up to take dates from CakePHP default date models.
I've actually only used it for dates without time (DATE fields in the
db), and not with DATETIME fields.
It uses __(), but in a hacky way. Just look at the code... works for
me for the time being, but people scandinavian countries might want to
revise it.

To use it you would type the following in your controller or view:

View Template:
``````````````

::

    
    echo $date->getDiff('1992-02-17', '1992-05-19');

which would output something like

::

    
    over 3 months

Helper code to download:

Helper Class:
`````````````

::

    <?php 
    /**
     * Date Helper
     *
     * Code by A.G.
     *
     *
     * @version        0.1
     * @author         A.G.
     * @created        23.03.2008
     * @updated        23.03.2008
     * @note		   first parameter show be smaller than second parameter.
     *                 dates should be in this format 'yyyy-mm-dd 00:00:00' (time optional)
     */ 
    class DateHelper extends Helper {
    
        function _getDiff($from = array() , $to = array() ) {
            $dateDiff =     mktime( $to['hour']    , $to['minutes']   , $to['seconds'] ,
                            $to['month']   , $to['day']       , $to['year'] )
                            -
                            mktime( $from['hour']  , $from['minutes'] , $from['seconds'] ,
                            $from['month'] , $from['day']     , $from['year'] );
            return abs($dateDiff);
        }
        
        function _isValidDate( $sDate = "01/01/1980 00:00:00" ) {
            $dateString = split( " "    , $sDate      );
            $dateParts  = split( "[/-]" , $dateString[0] );
            $dateParts2 = isset($dateString[1]) ? split( "[:]"  , $dateString[1] ) : array('00','00','00');
            if( !checkdate($dateParts[1], $dateParts[2], $dateParts[0]) )
            {  return false; }
            return array
                   (
                     'month'   => $dateParts[1] ,
                     'day'     => $dateParts[2] ,
                     'year'    => $dateParts[0] ,
                     'hour'    => $dateParts2[0] ,
                     'minutes' => $dateParts2[1] ,
                     'seconds' => $dateParts2[2]
                   );
        }
        
        function getDiff($dateFrom, $dateTo) {
            $from   = $this->_isValidDate($dateFrom);
            $to     = $this->_isValidDate($dateTo);
            $yearinseconds  = (60*60*24*365.242199);
            $monthinseconds = (60*60*24*30.4);
            $dayinseconds   = (60*60*24);
            $hourinseconds  = (60*60);
            $minuteinseconds = 60;
            if($from && $to) {
                $dateDiff = $this->_getDiff($from, $to);
                $r = $dateDiff;
                $dd['years'] =     floor ( $dateDiff / $yearinseconds );
                $r -= $dd['years']*$yearinseconds;
                $remainder['years'] = $r/$yearinseconds;
                $dd['months'] =    floor ($r / $monthinseconds);
                $r -= $dd['months']*$monthinseconds;
                $remainder['months'] = $r/$monthinseconds;
                $dd['days']  =     floor ($r / $dayinseconds );
                $r -= $dd['days']*$dayinseconds;
                $remainder['days'] = $r/$dayinseconds;
                $dd['hours'] =     floor ($r  / $hourinseconds);
                $r -= $dd['hours']*$hourinseconds;
                $remainder['hours'] = $r/$hourinseconds;
                $dd['minutes'] =   floor ($r / $minuteinseconds);
                $r -= $dd['minutes']*$minuteinseconds;
                $remainder['minutes'] = $r/$minuteinseconds;
                $dd['seconds'] =   $r; // $dateDiff;
                $remainder['seconds'] = 0;
                foreach ($dd as $period => $amt) {
                    if ($remainder[$period] >= .94) {
                        return  (__('almost',true)." ".($amt+1). " ".__n(rtrim($period,"s" ), $period, $amt+1, true));
                    }
                    else if($dd[$period] > 0 && $remainder[$period] > 0 && $remainder[$period]  <= .3) {
                        return (__('over',true)." ".($amt). " ".__n(rtrim($period, "s"), $period, $amt, true));   
                    } else {
                        // continue;
                    }
                }
                return $return;
            }
            return false;
        }
    }
    ?>



.. author:: g2010a
.. categories:: articles, helpers
.. tags:: fuzzy,logic,difference,date,Helpers

