Shells plugin
=============

If you have ever used some cheap web host that doesn't allow shell
access, it has likely been a bit of a pain to do some tasks. As my
first try at a contribution and a plugin, and one that I plan to use,
here is my first version of a shell plugin. It gives you access to a
shell command line. It uses ajax to send the commands and read the
response.
(This plugin can be found as a tarball at
`http://vectorjohn.com/cake/files/shells.tar.gz`_)
Here is the controller (shells_controller.php).
It is located in plugins/shells/controllers/

Controller Class:
`````````````````

::

    <?php 
    /*
     * class ShellsController  
     * Author: John Reeves
     */
    
    class ShellsController extends ShellAppController
    {
        var $name = 'Shells';
        var $helpers = array('Html', 'Ajax', 'Javascript');
    
        // prompt action has a default id of 1, so you should probably
        // have at least one shell set up.
        function prompt($id = 1)
        {
          $sh = $this->Shell->findById($id);
          $this->set('shell', $sh['Shell']);
          $this->render('prompt');
        }
        
        /**
          * action requrested by ajax while looking at the 
          * prompt view.  The the shell_hist div will end up.
          * with the output of the command added to it.
          */
        function shell_history($id=1){
          $sh = $this->Shell->findById($id);
          $this->set('cmd', $this->data['Shell']['cmd']);
          $this->set('exec', $sh['Shell']['exec']);
          $this->set('env', unserialize($sh['Shell']['env']));
          $this->set('title', $sh['Shell']['title']);
          $this->layout = 'results';
        } 
    
        // List all shells
        function index(){
          $this->set('shells', $this->Shell->findAll());
        }
    
        /* display the environment.  It could be big, 
         * so it has it's own view.
         */
        function env($id=null){
          $sh = $this->Shell->findById($id);
          $this->set('env', unserialize($sh['Shell']['env']));
          $this->set('shellname', $sh['Shell']['title']);
        }
    }
    ?>

I clearly don't use this for too much, but here it is.
shell.php, located in plugins/shells/models

Model Class:
````````````

::

    <?php 
    class Shell extends ShellAppModel
    {
        var $name = 'Shell';
    }
    ?>

Now the views. I think maybe some of the logic should be taken out of
the shell_hist view, but that seemed the best place to put it at the
time.
All views are in /plugins/shells/views/

Here is the main prompt view (prompt.thtml), which you see most of the
time.

View Template:
``````````````

::

    
    /* in the future, this would be nice as a configurable thing.
     * maybe another field in the shells table.
     */
    php echo $html->css('shells_greenonblack');
    ?>
    <div class="greenonblack">
    <pre>
    <div id="shell_prev"><div id="shell_hist"></div></div>
    </pre>
    <?php
    /* at event loaded, adds the updated div to the outer
     * shell_prev div, and sets the updated one to ''.
     */
    $loaded = "var newOut=document.getElementById('shell_hist').innerHTML; ".
      "document.getElementById('shell_hist').innerHTML='';".
      "document.getElementById('shell_prev').innerHTML=newOut + document.getElementById('shell_prev').innerHTML;";
    $opts = array('update' => 'shell_hist',
        'complete' => "document.getElementById('prompt').value='';",
        'loaded' => $loaded); //TODO: focus the prompt
    // the form.
    echo $ajax->form($html->url('/shells/shell_history/'. $shell['id']), 'post', $opts);
    ?>
      <table>
        <tr>
          <td>
            <?php
            $ps1 = unserialize($shell['env']);
            $ps1 = $ps1['PS1'] ? $ps1['PS1'] : $shell['title'];
            echo $ps1;
            ?> 
          </td>
          <td style="width: 100%">
            <?php echo $html->input('Shell/cmd', array('size' => '40', 'id' => 'prompt')); ?>
          </td>
        </tr>
      </table>
    <a name="p" />
    </form>

Here is the view that is used in updating the prompt
(shell_hist.thtml). It does not use the default layout, instead the
results layout is used since this output will just be inserted into
the prompt page.

View Template:
``````````````

::

    
    <?php
    /*
     * this starts the shell, gives it commands, gets it's
     * output.
     * It would be nice if I could keep the same shell process
     * running between requests, so environment variables
     * and cd's would truly work.  But right now the process
     * just dies.
     */
    $ps1 = $env['PS1'] ? $env['PS1']. ' ' : $title. '$ ';
    echo $ps1. $cmd;
    echo "\n";
    $fds = array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w')); //WWW_ROOT. '/../tmp/shells.log', 'a'));
    // TODO: configurable PWD.
    $proc = proc_open($exec, $fds, $pipes, WWW_ROOT, $env);
    if(is_resource($proc)){
      fwrite($pipes[0], $cmd);
      fflush($pipes[0]);
      fclose($pipes[0]);
      $sout = stream_get_contents($pipes[1]);
      $serr = stream_get_contents($pipes[2]);
      fclose($pipes[1]);
      fclose($pipes[2]);
      $ret = proc_close($proc);
      echo $sout. $serr;
    }
    ?>

The index (index.thtml). pretty straight forward.

View Template:
``````````````

::

    
    <h1>Shells</h2>
    <h2>Select from one of the below shells to use the specified environment.</h2>
    <?php
    $rows = array();
    $heads = array("Name", "Program", "PATH", 'ENV');
    foreach($shells as $sh){
      $sh=$sh['Shell'];
      $env = unserialize($sh['env']);
      $rows[] = array($html->link($sh['title'], '/shells/prompt/'. $sh['id']),
            $sh['exec'], $env['PATH'], $html->link('view', '/shells/env/'. $sh['id']));
    }
    
    echo '<table>'. "\n";
    echo $html->tableHeaders($heads);
    echo $html->tableCells($rows);
    echo '</table>'. "\n";
    ?>

A view for displaying environment variables (env.thtml). Maybe
sometime this could be an ajax thing that happens from the index view,
but for right now it's a view.

View Template:
``````````````

::

    
    <h1>Environment for shell <?php echo $shellname; ?></h1>
    <?php
    $heads = array('Var', 'Val');
    $rows = array();
    foreach($env as $v => $k){
      $rows[] = array($v, $k);
    }
    echo '<table>';
    echo $html->tableHeaders($heads);
    echo $html->tableCells($rows);
    echo '</table>';
    ?>

A layout is needed (results.thtml).
You could make your own, but the point is that it does nothing.

Layout:
```````

::

    
    <?php echo($content_for_layout); ?>

There are 2 files that are needed for any plugin, the app model and
app controller. Here are mine, although they don't do anything. This
may be the place to add in security.
These belong in /plugins/shells/

::

    
    <?php
    class ShellAppModel extends AppModel {}
    ?>

and

::

    
    <?php
    class ShellAppController extends AppController{}
    ?>


Once you have this stuff, there needs to be at least one entry in the
database to do anything useful. Here is a mysql file that sets up the
database and puts in one default shell.

shells.mysql
````````````

::

    
    
    /* First, create our shells table: */
    CREATE TABLE shells (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        exec VARCHAR(255) NOT NULL,
        env TEXT,
        output TEXT
    );
    
    /* Then insert some shells for testing: */
    INSERT INTO shells (title,exec,env)
        VALUES ('Bash', '/bin/bash',
    'a:2:{s:4:"PATH";s:43:"/bin:/usr/bin:/var/www/cake/app/webroot/bin";s:3:"PS1";s:5:"bash$";}');

You will also want to use some styling. It's pretty easy to do
yourself, but here are the colors and styling I like:
shells_greenonblack.css
This should go in your webroot/css directory.

::

    
    .greenonblack {
      background-color: black;
      color: green;
    }
    div.greenonblack pre,
    div.greenonblack pre div,
    div.greenonblack pre div div{
      padding: 0;
      margin: 0;
      border: 0;
      /*overflow: auto;
      height: 400px;*/
    }
    
    .greenonblack form input{
      background-color: black;
      color: green;
      border: 0;
      width: 100%;
    }
    
    .greenonblack table, .greenonblack table td {
      background-color: black;
      color: green;
      padding: 0;
      margin: 0;
      border: none;
      text-align: left;
    }



.. _http://vectorjohn.com/cake/files/shells.tar.gz: http://vectorjohn.com/cake/files/shells.tar.gz

.. author:: reevesj
.. categories:: articles, plugins
.. tags:: utility,administration,plugin,shell,Plugins

