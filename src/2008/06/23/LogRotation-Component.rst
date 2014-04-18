LogRotation Component
=====================

My logs were getting big really fast. CakeLog does (at the time of
writing) not have rotation built in so I put this little component
together. Possibly someone else is logging as much as I am :)
This component has some of the basic features of something like
logrotation on Linux systems.
The component is written so that pasting the code into the CakeLog
class should be possible if you like hacking the core files.
I hacked this together in a hurry... it is hardly prefect.

Let's get started by including the component in your app.
Once the component is downloaded and places in
app/controllers/components/ you need to include it in either:
1. AppController (will check rotations on each request)
2. UsersController (i.e. some controller that run less often may be
preferable)


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller
    {
        var $components = array('Logrotation');
    }
    ?>

Now you need to open app/config/bootstrap.php and add a few lines to
configure log rotation.

::

    
    <?php
    Configure::write('Log.rotate', array(
    	'daily' => array(
    		LOG_DEBUG =>	0
    	),
    	'weekly' => array(
    		LOG_ERROR =>	5,
    		'my_test' =>	8,
    		'*'	=>			3
    	),
    	'monthly' => array()
    ));
    ?>

The configuration is hopefully obvious. You have three intervals.
Daily, weekly and monthly. Each running at midnight, on monday, on the
first day of the month. In each array you add the logfiles you want to
be rotate as the key and the number of archives to keep in rotation as
the value.
Note:
â€¢ a catch-all filename is available by entering * for one of the
intervals. This will rotate all files not elsewhere specified using
the given number of archives and the current interval.
â€¢ specifying 0 as the number of archives indicates that you want to
keep all archives generated. Warning: this can slow down the rotation
when you reach a really high number of logs.

That is it. Now you just need to download the component and get
logging.


Component Class:
````````````````

::

    <?php 
    if (!class_exists('Folder')) {
    	 uses('folder');
    }
    
    if (!defined('LOG_WARNING')) {
    	define('LOG_WARNING', 3);
    }
    if (!defined('LOG_NOTICE')) {
    	define('LOG_NOTICE', 4);
    }
    if (!defined('LOG_DEBUG')) {
    	define('LOG_DEBUG', 5);
    }
    if (!defined('LOG_INFO')) {
    	define('LOG_INFO', 6);
    }
    
    
    class LogrotationComponent extends Object
    {
    	var $conf;
    	var $log_folder;
    		
    	function startup(&$controller) {
    		
    		$this->runRotation();
    	}
    
    	function runRotation() {
    		
    		if ( !Configure::read('Log.rotate') ) {
    			return;
    		}
    		
    		$cache = Cache::getInstance();
    		$last_daily = $cache->read('Log.daily');
    		$last_weekly = $cache->read('Log.weekly');
    		$last_monthly = $cache->read('Log.monthly');
    		
    		if ( $last_daily < strtotime('today') ) {
    			$this->loadConfiguration();
    			$this->rotate($this->conf['daily']);
    			$cache->write('Log.daily',time());
    		}
    		
    		if ( $last_weekly < strtotime('-1 monday') ) {
    			$this->loadConfiguration();
    			$this->rotate($this->conf['weekly']);
    			$cache->write('Log.weekly',time());
    		}
    		
    		if ( $last_monthly < mktime(0, 0, 0, date('m'), 1, date('Y')) ) {
    			$this->loadConfiguration();
    			$this->rotate($this->conf['monthly']);
    			$cache->write('Log.monthly',time());
    		}
    	}
    	
    	function loadConfiguration() {
    		if (empty($this->conf)) {
    			
    			$this->conf = Configure::read('Log.rotate');
    			$configured_files = array();
    			foreach ( $this->conf as $interval => $files ) {
    				foreach ( $files as $type => $num_logs ) {
    					
    					if ( !is_numeric($num_logs) ) {
    						$num_logs = 5;
    					}
    					
    					if ( $type == '*' && !isset($default) ) {
    						$default = array($interval, $num_logs);
    					} else {
    						$configured_files[] = basename($this->getFilename($type));
    					}					
    				}
    			}
    			
    			if ( isset($default) ) {
    				list($interval, $num_logs) = $default;
    				unset($this->conf[$interval]['*']);
    				
    				if ($this->log_folder == null) {
    					$this->log_folder = new Folder(LOGS);
    				}
    				$files = $this->log_folder->find('.*\.log',true);
    								
    				foreach ( $files as $filename ) {
    					if ( !in_array($filename,$configured_files) ) {
    						$this->conf[$interval][basename($filename,'.log')] = $num_logs;
    					}
    				}
    			}
    		}
    	}
    	
    	function rotate($files) {		
    		foreach ( $files as $type => $num_logs) {
    			$this->_rotate($type, $num_logs);
    		}
    	}
    	
    	function _rotate($type, $num_logs) {
    
    		$filename = $this->getFilename($type);
    		
    		if ($this->log_folder == null) {
    			$this->log_folder = new Folder(LOGS);
    		}
    		
    		$files = $this->log_folder->find(basename($filename).'.*',true);
    		$files = array_reverse($files);
    		
    		foreach ( $files as $file ) {
    			$info = pathinfo(LOGS.$file);
    			
    			if ( is_numeric($info['extension']) ) {
    				// this one of the numbered logfiles in rotation
    				
    				if ( ($num_logs > 0) && ($info['extension']+1) > $num_logs ) {
    					unlink(LOGS.$file);
    					continue;
    				}
    				$newfile = basename($file,$info['extension']) . ($info['extension']+1);
    				$move = array('from' => LOGS.$file,	'to' =>	LOGS.$newfile);
    			} else {
    				// this is the active logfile
    				$move = array('from' => LOGS.$file, 'to' =>	LOGS.$file.'.1');
    			}
    			rename($move['from'],$move['to']);
    		}		
    	}	
    	
    	function getFilename($type) {
    		/* pasted directly from CakeLog::write() */
    		if (!defined('LOG_ERROR')) {
    			define('LOG_ERROR', 2);
    		}
    		if (!defined('LOG_ERR')) {
    			define('LOG_ERR', LOG_ERROR);
    		}
    		$levels = array(
    			LOG_WARNING => 'warning',
    			LOG_NOTICE => 'notice',
    			LOG_INFO => 'info',
    			LOG_DEBUG => 'debug',
    			LOG_ERR => 'error',
    			LOG_ERROR => 'error'
    		);
    
    		if (is_int($type) && isset($levels[$type])) {
    			$type = $levels[$type];
    		}
    		
    		if ($type == 'error' || $type == 'warning') {
    			$filename = LOGS . 'error.log';
    		} elseif (in_array($type, $levels)) {
    			$filename = LOGS . 'debug.log';
    		} else {
    			$filename = LOGS . $type . '.log';
    		}
    		/* END pasted directly from CakeLog::write() */
    		return $filename;
    	}
    	
    }
    ?>



.. author:: eimermusic
.. categories:: articles, components
.. tags:: ,Components

