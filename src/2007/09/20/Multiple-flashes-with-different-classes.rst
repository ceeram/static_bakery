Multiple flashes with different classes
=======================================

There were a couple of things I didn't like about using
$this->Session->setFlash(), so I wrote my own method for saving
flashes and helper for displaying them.
While it's a step up from the old flash method, using
$this->Session->setFlash() has two problems:


#. You can only do one flash message.
#. It's always the same color. I want to use red for problems, green
   for success and grey for neutral status messages.

I added this to my AppController:


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller
    {
      
      var $helpers = array('Flash');
      /*
       * Add a message to the messages array in the session like this:
       * $this->flash( 'You are logged in.', 'success' );
       */ 
      function flash( $message, $class = 'status' )
      {
        $old = $this->Session->read('messages');
        $old[$class][] = $message;
        $this->Session->write( 'messages', $old );
      }
    
    }
    ?>

Then I added this to app/views/helpers/flash.php:


Helper Class:
`````````````

::

    <?php 
    class FlashHelper extends Helper
    {
      var $helpers = array( 'Session' );
      function show()
      {
        // Get the messages from the session
        $messages = $this->Session->read( 'messages' );
        $html = '';
        
        // Add a div for each message using the type as the class
        foreach ($messages as $type => $msgs)
        {
          foreach ($msgs as $msg)
          {
            if (!empty($msg)) {
              $html .= "<div class='$type'><p>$msg</p></div>";
            }        
          }
        }
        $html .= "</div>";
        
        // Clear the messages array from the session
        $this->Session->del( 'messages' );
        
        return $this->output( $html );
      }
      
    }
    ?>

Now in my controllers I do this:

::

    
    $this->flash( 'Something normal happened.' );
    $this->flash( 'Something good happened.', 'success' );
    $this->flash( 'Danger!', 'error' );

And in my layout I added this:

::

    
    <?php echo $flash->show(); ?>

Now you can us css to style your messages. The classes are 'status',
'success' and 'error'.

This is inspired by the set_message() function in Drupal.


.. author:: personman
.. categories:: articles, helpers
.. tags:: flash,session,Helpers

