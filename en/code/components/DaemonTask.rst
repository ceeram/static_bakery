

DaemonTask
==========

by %s on August 26, 2008

DaemonTask will help you "deamonize" any cake shell allowing you to
schedule it to run with cron (say every minute) and make sure only one
instance of the shell is running at any given time.
It uses cake's cache to log the pid (and an optional user supplied
prefix) of a current running shell. When that shell is executed it
refers to the cache and checks with PS to see if that process is still
running. If it is still running then it quits. I had a site that
needed to generate thumbnails for uploaded files, the process
sometimes took many minutes so I didn't want to tie it to the
controller handling the upload. I made a task to do it but since the
process was very resource intensive I only wanted to run one at one
time. My host didn't appreciate me running more then one either and
would kill my processes. This solved my problem.



Component Class:
````````````````

::

    <?php 
    class DaemonTask extends Shell {
    
       function execute($prefix = '') {
       		//the key the pid is stored with - default to just 'pid'
       		$pidstring = $prefix . 'pid';
    		if(!Cache::read($pidstring)){
    			Cache::write($pidstring, getmypid(), 3600);	
    		}else{
    			$ps = shell_exec('ps -o pid -A');
    			$ps = explode("\n", trim($ps));
    			foreach($ps as $key => &$value){
    				$value = trim($value);
    			}
    			if(in_array(Cache::read($pidstring), $ps)){
    				exit("already got a process running\n");
    			}else{
    				echo "replacing stale pid\n";
    				Cache::write($pidstring, getmypid(), 3600);	
    			}
    		}
    	}
    }
    ?>

It should work with most flavors of unix/linux (works on my osx 10.4,
10.5 and on debian and ubuntu linux's, sometimes the ps command is a
little different so you may have to tweak that if it doesn't work).
Here is the basic usage.


Controller Class:
`````````````````

::

    <?php 
    class ThumbShell extends Shell {
        var $uses = array('Thumb');
     	var $tasks = array('Daemon');
    
        function generate(){
            $this->Daemon->execute('thumb');
            //Configure::write('debug', 0);
            $thumbs = $this->Thumb->findAll(array('status' => 'pending'));
            if(!$thumbs){$this->out("Processing 0 Thumbs");return true;}
            $this->out('Processing ' . count($thumbs) . ' thumbs');
            foreach($thumbs as $thumb){
                $thumb = $this->Thumb->read(null,$thumb['Thumb']['id']);
                $this->out('Processing thumb id ' . $this->Thumb->id); 
                if(!$this->Thumb->generate()){
                    $this->out('Processing of thumb id ' . $this->Thumb->id . ' failed!');
                }
            }
        }
    
    }
    ?>

Enjoy!

.. meta::
    :title: DaemonTask
    :description: CakePHP Article related to task,shell,daemon,Components
    :keywords: task,shell,daemon,Components
    :copyright: Copyright 2008 
    :category: components

