Simple Way to Unbind Validation & Set Remaining Rules to Required
=================================================================

I have messed around with various ways to unbind certain validation
and you know what, sometimes the mantra K.I.S.S. really hits home. I
think this is a good example of such a mentality. It might not be
fancy but it works in 99% of the situations one would want it to.


Background
;;;;;;;;;;

Before I detail this method, I want to explain how I develop in cake
without this program. Hopefully that will show you the true usefulness
of the unbindValidation method. In every $validate property, for every
field I plan on validating, I set a property called 'required'=>true
for each field. Many new bakers don't understand what this does, so
here is the lowdown. By default, cake sets every field in $validate to
'required'=>false. What this means is, if you want to validate the
'username' field, but for some reason it does not appear in
$this->data, then Cake will skip validation on that field. So what?
Well, here is how it can affect you...

When creating a new user account, I have a terms of service field
called 'agree_to_tos'. If I don't set 'required'=>true for that field
and merely leave it at the default (false), then the user can modify
the form data before it is submitted and remove 'agree_to_tos' from
the submitted form data. This means that if you performed
isset($this->data['agree_to_tos']) it would return false. Thus,
validation on the tos field is never performed, and assuming all other
fields pass validation, a user is now created without having to agree
to the terms of service! You can extrapolate this out to other
scenarios which could potentially cause even harm to your site.

Let's say you decide to set all your fields to 'required'=>true
beforehand in the $validate property. You could do this, but you'd run
into trouble later when it came time to validate certain data and not
other data... Here is an example:

In my UserModel::$validate property I have the following fields:
'username', 'nickname', 'password', 'password_confirm', 'newsletter',
'agree_to_tos', 'company_id', 'first_name', 'last_name'.

In my UsersController::add() method, I accept all of those fields but
not 'first_name' or 'last_name' (I allow them to add it later during
the ordering process or what not. I figure the less crap I ask users
to provide during account creation, the more likely the are to create
an account).

Now, lets say I set all fields in $validate to 'required'=>true. Well,
validation would fail because 'first_name' and 'last_name' are
required but not present. The solution would be to either 1) unset
both fields from $validate; or 2) set 'required'=>false on both fields

Doing either is a hassle in my opinion because in some cases it might
be more than 2 field that you want to modify, and there are other
scenarios when you would want to list the fields you want to keep but
not remove (examples below). It was for these reasons that I decided
to write this method.


Explanation
;;;;;;;;;;;

This method will remove certain fields that you do not want from
$validate and simultaneously set the remaining fields to 'required'.
It also allows specifying an array of either the fields you want to
keep, or the fields you want to remove. This can be useful in
different situations. Here is how you use it:

#1: We can specifically remove fields from UserModel::$validate like
this and simultaneously set the remaining fields to 'required'=>true

::

    $this->User->unbindValidation('remove', array('first_name', 'last_name'), true);

#2: Or we can specify the fields we want to keep in
UserModel::$validate and set as required (and automatically remove
everything else):

::

    $this->User->unbindValidation('keep', array('username', 'nickname', 'password', 'password_confirm', 'newsletter', 'agree_to_tos', 'company_id'), true);

Here are some other places I use it...

In my UsersController:edit() method, I don't require that users re-
agree to the terms of service. This line takes care of that:

::

    $this->User->unbindValidation('remove', array('agree_to_tos'), true);

In my UsersController::changeNickname() method, the only field I let
users modify is 'nickname' and therefore this line takes care of that:

::

    $this->User->unbindValidation('keep', array('nickname'), true);

So if you are already enticed then you can skip below to the code, but
you may at least be wondering why not merely
unset($this->User->validate['agree_to_tos'])? Well, that's fine, but
what about in the nickname scenario? You will have to list every
single field you want to unset, except for 'nickname'. You could of
course set nickname to 'required'=>true, but if you decide to take
that approach on a case-by-case basis, then when it comes time to save
a bunch of fields (like 5 or more) you'll have to set required on each
one... This is why having the parameter $type in the method is so
handy. It lets you set $type='keep' and set the field to
array('nickname') instead of listing every single field you want to
remove! Also, you don't have to manually set each field you want to
validate as 'required'=>true!


Code
;;;;
(I prefer to place this in my app_model)

::

    
    /**
     * Unbinds validation rules and optionally sets the remaining rules to required.
     * 
     * @param string $type 'Remove' = removes $fields from $this->validate
     *                       'Keep' = removes everything EXCEPT $fields from $this->validate
     * @param array $fields
     * @param bool $require Whether to set 'required'=>true on remaining fields after unbind
     * @return null
     * @access public
     */
    function unbindValidation($type, $fields, $require=false)
    {
        if ($type === 'remove')
        {
            $this->validate = array_diff_key($this->validate, array_flip($fields));
        }
        else
        if ($type === 'keep')
        {
            $this->validate = array_intersect_key($this->validate, array_flip($fields));
        }
        
        if ($require === true)
        {
            foreach ($this->validate as $field=>$rules)
            {
                if (is_array($rules))
                {
                    $rule = key($rules);
                    
                    $this->validate[$field][$rule]['required'] = true;
                }
                else
                {
                    $ruleName = (ctype_alpha($rules)) ? $rules : 'required';
                    
                    $this->validate[$field] = array($ruleName=>array('rule'=>$rules,'required'=>true));
                }
            }
        }
    }



Usage in the controller
;;;;;;;;;;;;;;;;;;;;;;;

(The following example REMOVES everything from $this->User->validate
EXCEPT 'nickname' and sets nickname to 'required'=>true)

::

    $this->User->unbindValidation('keep', array('nickname'), true);


(The following example REMOVES 'agree_to_tos' from
$this->User->validate and sets all remaining fields to
'required'=>true)

::

    $this->User->unbindValidation('remove', array('agree_to_tos') ,true);



.. author:: kiger
.. categories:: articles, snippets
.. tags:: unbind,Snippets

