Using Custom Validation Rules To Compare Two Form Fields
========================================================

by aranworld on January 14, 2008

Want to make sure that two submitted form fields have the same value?
Use the ability to write your own validation rules, to add this
validation to your model.
It is very common when collecting a user's email address, or password,
to force the user to type the string twice in two separate form
fields. Prior to saving the data, one can check that the string in
both fields match.

While there are already a number of ways to do this using Javascript
alone, or Ajax, I wanted to use Cakephp 1.2's validation system to
handle this task.

There doesn't exist a built-in validation that works quite right, but
luckily you can write your own validation rules (
`http://tempdocs.cakephp.org/#TOC132710`_ ) and they will process
along with any built-in rules you are using.

I will demonstrate this using a simple contact information form.


The Table
`````````

::

    CREATE TABLE `contacts` (               
        `id` int(6) NOT NULL auto_increment,  
        `name` varchar(100) default NULL,     
        `email` varchar(200) default NULL,    
        PRIMARY KEY  (`id`)                   
        );



Controller Class:
`````````````````

::

    <?php class ContactsController extends AppController {
    
        var $name='Contacts';
    	var $uses = array('Contact');
    	var $helpers = array('form');
    
        function add(){
    
    	    if( !empty( $this->data ) ){
    
    		    if( $this->Contact->save( $this->data ) ){
    			    $lastId = $this->Contact->getLastInsertId();
                    $this->flash('Your new user has been created.','/c/contacts/view/'.$lastId );
    			} 
    		}
    	}
    }
    ?>

Just a very basic add method. Note, however, that nothing in this
controller deals with comparing form fields.


View Template:
``````````````

::

    <h1>Enter Contact Information</h1>
    
    <form method="post" action="<?php echo $html->url('/contacts/add')?>">
        
        <?php echo $form->label('Contact.name', 'Full Name of Contact'); ?>
        <?php echo $form->error('Contact.name'); ?>
        <?php echo $form->text('Contact.name', array('size' => '80') ); ?>
    
    
        <?php echo $form->label('Contact.email', "Contact's E-mail"); ?>
        <?php echo $form->error('Contact.email'); ?>
        <?php echo $form->text('Contact.email', array('size' => '80') ); ?>
    
    	<?php echo $form->label('Contact.confirm_email', 'Re-enter E-mail For Verification'); ?>
    	<?php echo $form->text('Contact.confirm_email', array('size' => '80') ); ?>
    
    	<?php echo $form->submit('Add Person to Directory'); ?>
    </form>

If you are new to Cake 1.2, this might look a bit strange. The html
helper is no longer used for forms, but instead the 'form' helper is
used.

Note that in the calls to the **Form::error** method, I do not include
an error message. This message will be provided by the particular rule
in the Contact class.


Model Class:
````````````

::

    <?php  
    class Contact extends AppModel
    {
        var $name = 'Contact';
        var $validate = array(
            'email' => array(
    	    'identicalFieldValues' => array(
    		'rule' => array('identicalFieldValues', 'confirm_email' ),
    		'message' => 'Please re-enter your password twice so that the values match'
                    )
                )
            );
    	
    		
        function identicalFieldValues( $field=array(), $compare_field=null ) 
        {
            foreach( $field as $key => $value ){
                $v1 = $value;
                $v2 = $this->data[$this->name][ $compare_field ];                 
                if($v1 !== $v2) {
                    return FALSE;
                } else {
                    continue;
                }
            }
            return TRUE;
        }
    
    }
    ?>

This is where things get interesting. Here is more information about
Cake 1.2's new validation configuration:
`http://tempdocs.cakephp.org/#TOC127334`_.

The validate attribute contains an array. In the array, we declare
that for the field email , we will use a rule called
identicalFieldValues .

::

    'rule' => array('identicalFieldValues', 'confirm_email' )

This line says that the rule will use the validation method
identicalFieldValues, and when it calls this method it will provide as
the second argument the string 'confirm_email'.


The Home Brewed Validation Function
```````````````````````````````````
As the model code above illustrates, I added a method named
identicalFieldValues into the Contact class.

The call to this method happens from within Model::invalidFields().
When it is called, the first parameter is passed as an array:

::

    array('email' => 'webmaster@gmail.com')

The key is the string representing the field's name, and the value
represents the value of that field. This is how all customized
validation functions are now called.

The second argument is the string provided in the array under 'rule'
in the validate attribute. In this case it is the string
'confirm_email'. This string represents the name of the field I am
comparing the first field to.

To get the first value I extract it from the passed array.

The second value I extract from the Model's data array by using the
string passed as the second argument.

Once I have the two variables set, I can compare them however I want.
I return a false if the values don't match, and a true if they do.

Now, if a person submits the Contact form with mismatched values in
the two email fields, the Contact::save method will fail and the form
will be re-displayed with an error message.


Using this for Passwords
````````````````````````
The other obvious usage of this is when a new user registers and
provides a password.

If you are using the AuthComponent, and the name of the password field
you are checking is equal to the column name for the User's password
then this value will automatically be hashed prior to validation, but
the 'confirm_password' value will NOT be hashed.

A fix to this, is to name the password fields in your Users/add form
something like 'new_password' and 'confirm_password'. Before calling
the User::save() method, hash both of these values using the
Auth->password() function.

::

    //add this function to the users_controller.php
    function convertPasswords()
    {
    if(!empty( $this->data['User']['new_passwd'] ) ){
    $this->data['User']['new_passwd'] = $this->Auth->password($this->data['User']['new_passwd'] );
    }
    if(!empty( $this->data['User']['confirm_passwd'] ) ){
    $this->data['User']['confirm_passwd'] = $this->Auth->password( $this->data['User']['confirm_passwd'] );
    }
    }

Then in a custom User::beforeSave() method, which is called after
validation succeeds, pass the value of new_password to the data field
for the real password field (most likely something like 'passwrd').

::

    //add this function to your user model and call it from within beforeSave()
    function setNewPassword()
    {
        $this->data['User']['paswd'] = $this->data['User']['new_passwd'];
        return TRUE;
    }
    function beforeSave(){
        $this->setNewPassword();
        return true;
    }

Using these modifications, you can now use the identicalFieldValues()
function in your User model to make sure that when the user adds their
requested password, that both fields match. In addition, don't forget
that you can have multiple rules for each field (
`http://tempdocs.cakephp.org/#TOC127334`_ ), so if you want to do any
other checks on the password field you can do those as well.

.. _http://tempdocs.cakephp.org/#TOC127334: http://tempdocs.cakephp.org/#TOC127334
.. _http://tempdocs.cakephp.org/#TOC132710: http://tempdocs.cakephp.org/#TOC132710
.. meta::
    :title: Using Custom Validation Rules To Compare Two Form Fields
    :description: CakePHP Article related to email,password,rule,form,identical,custom,fields,Tutorials
    :keywords: email,password,rule,form,identical,custom,fields,Tutorials
    :copyright: Copyright 2008 aranworld
    :category: tutorials

