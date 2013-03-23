Detecting duplicate entries when updating
=========================================

by %s on October 04, 2007

Cake doesn't tell you the difference between an Model->save() that
fails because of a validation error, and one that fails because of key
constraint violation (that is, a duplicate entry). Here, I will show
you an easy way to overcome this. (MySQL specific)
I have a system where players of a game can register. Each must have a
unique email address, and also must have a unique name, school id and
date of birth.

My table looks like this:

::

    CREATE TABLE players (
      id int unsigned not null primary key auto_increment,
      school_id int unsigned,
      first_name varchar(255) not null,
      last_name varchar(255) not null,
      gender enum('m', 'f') not null,
      date_of_birth date not null,
      registration_date date not null,
      email varchar(255),
      unique(email),
      unique(first_name,last_name,date_of_birth,school_id),
      index (school_id),
      index (date_of_birth),
      index (registration_date),
      index (gender)
    );

And my model looks like this:

::

    
    <?php
    
    // I suggest moving this line to config/core.php
    define('VALID_DATE', '/^\d{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/'); 
    
    class Player extends AppModel
    {
    	var $name = 'Player';
    	var $displayField = 'fullname';
    	var $validate = array(
    		'schoold_id' => '/^(|[0-9]+)$/',
    		'first_name' => VALID_NOT_EMPTY,
    		'last_name' => VALID_NOT_EMPTY,
    		'gender' => '/^[mf]$/',
    		'date_of_birth' => VALID_DATE,
    		'registration_date' => VALID_DATE,
    		'email' => VALID_EMAIL,
    	);
    }
    
    ?>

Now... if we scaffold that (to get the basics of a registration page)
we will have 7 mandatory fields. If you miss any one of them out, you
will get a flash message: "Please correct the errors below", and the
appropriate missing field will have an error next to it.

But what happens when you enter every field, but pick an email address
that's already been registered with?

The save() will fail, and you will get that flash message "Please
correct the errors below" --- but no errors below! Very confusing.

By turning on DEBUG -> 2 (in config/core.php) you will see something
like this in your SQL:

INSERT INTO `players` (`first_name`,`last_name`,`gender`,`email`,`scho
ol_id`,`registration_date`,`date_of_birth`) VALUES
('Ben','XO','m','test@test.com','1','2006-11-06','1982-01-01')

with the error message:

1062: Duplicate entry 'test@test.com' for key 2

This is a MySQL error message.

There are two options to solve this problem. The first is to do an
additional check, in the controller, to see if the email address is
already being used. Unfortunately, if you don't put the entire check-
and-save operation inside a transaction, you run the risk of a race
condition. Anyway, the code is at least as complicated as doing it the
second way: to simply check for the error message, and show the right
error to the user. So, that's what I shall do.

Here is an easy way to do it.

Create (or add to) app/app_model.php:

::

    <?php
    class AppModel extends Model
    {
    
    	// First, we override save(). On a successful save(), 
    	// afterSave() is called. But we want something to be 
    	// called on a NOT-successful save().
    	function save($data = null, $validate = true, $fieldList = array()) {
    		$returnval = parent::save($data, $validate, $fieldList);
    		if(false === $returnval) {
    			$this->afterSaveFailed();
    		}
    		return $returnval;
    	}
    
    	// This is a stub which is called after a save has failed. 
    	// You will override this in the model.
    	function afterSaveFailed() {
    	}
    
    	// This is a (MySQL specific) check to see if a 
    	// constraint was violated as the last error. If it was,
    	// the VALUE of the field which failed is returned.
    	// this is not ideal, but will do for most situations.
    	// The logic to work out the specific field which failed
    	// requires more MySQL specific SQL (such as 'show keys from...'
    	// so I shall leave it out. Most tables only have one 
    	// unique constraint anyway, although our example above
    	// has 2.
    	function checkFailedConstraint() {
    		$db =& ConnectionManager::getDataSource($this->useDbConfig); 
    		$lastError = $db->lastError();
    
    		// this is MYSQL SPECIFIC
    		if(preg_match('/^\d+: Duplicate entry \'(.*)\' for key \d+$/i', $lastError, $matches)) {
    			return $matches[1];
    		}
    
    		return false;
    	}
    
    }
    ?>

Now, let's add something to the model, so that it looks like THIS:

::

    <?php
    
    // I suggest moving this line to config/core.php
    define('VALID_DATE', '/^\d{4}-(0[1-9]|1[12])-(0[1-9]|[012][0-9]|3[01])$/'); 
    
    class Player extends AppModel
    {
    	var $name = 'Player';
    	var $displayField = 'fullname';
    	var $validate = array(
    		'schoold_id' => '/^(|[0-9]+)$/',
    		'first_name' => VALID_NOT_EMPTY,
    		'last_name' => VALID_NOT_EMPTY,
    		'gender' => '/^[mf]$/',
    		'date_of_birth' => VALID_DATE,
    		'registration_date' => VALID_DATE,
    		'email' => VALID_EMAIL,
    	);
    
    	function afterSaveFailed() {
    		$failed_constraint = $this->checkFailedConstraint();
    		if($failed_constraint) {
    			// player has 2 constraints: (email), and (first_name,last_name,date_of_birth,school_id).
    			// let's see if it was the email.
    			if ($failed_constraint == $this->data['Player']['email']) {
    				$this->invalidate('email_duplicate');
    			} else {
    				$this->invalidate('everything_else_duplicate');
    			}
    		}
    	}
    
    }
    
    ?>

finally, in our view, we add two tags like this, in appropriate
places:

::

    
    <?php echo $html->tagErrorMsg('Player/email_duplicate', 'This email address is already in use.');?>
    <?php echo $html->tagErrorMsg('Player/everything_else_duplicate', 'There is already a player with this name and date of birth at this school.');?>

I hope this helps somebody!


.. meta::
    :title: Detecting duplicate entries when updating
    :description: CakePHP Article related to aftersave,duplicate,constraint,aftersavefailed,key,violation,mysql,Tutorials
    :keywords: aftersave,duplicate,constraint,aftersavefailed,key,violation,mysql,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

