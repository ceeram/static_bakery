Using LDAP as a database
========================

by %s on October 15, 2006

John Anderson wrote an article about building a model around LDAP.
This snippit is an extension, implementing a much more full-featured
LDAP model
John Anderson wrote an article at
`http://bakery.cakephp.org/articles/view/1`_ about building a model
around LDAP. His example implements findAll() and auth(). I've
extended this to do read(), save(), del(), so I believe this to be a
fully functioning model. I've also added functions to convert between
the funky structures returned from PHP LDAP calls to more cake-
compliant ones. And I've added a findLargestUidNumber() function so
one can create new users with a unique uid number.

Also, I've put together a controller and views to build a fully-
functional LDAP user manager, allowing adds, deletes and modifies.

Ideally, this should be a tutorial or more of an article, but I'm
afraid I don't have time to write any text around it. Read John's
article for a primer on LDAP and accessing it via a model. If anyone
is interested in turning this into a tutorial/article, go for it.

Here's the code:

// models/ldap_user.php

Model Class:
````````````

::

    <?php 
    <?php 
    class LdapUser extends AppModel
    {
       var $name = 'LdapUser';
       var $useTable = false;
       var $primaryKey = 'uid';
    
       var $host       = 'sigma';
       var $port       = 389;
       var $baseDn     = 'ou=People,dc=willygarage,dc=com';
       var $user       = 'cn=Manager,dc=willygarage,dc=com';
       var $pass       = 'secret';
    
       var $ds;
    
       function __construct()
       {
          parent::__construct();
          $this->ds = ldap_connect($this->host, $this->port);
          ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3);
          ldap_bind($this->ds, $this->user, $this->pass);
       }
    
       function __destruct()
       {
          ldap_close($this->ds);
       }
    
       function findAll($attribute = 'uid', $value = '*', $baseDn = 'ou=People,dc=willygarage,dc=com')
       {
          $r = ldap_search($this->ds, $baseDn, $attribute . '=' . $value);
          if ($r)
          {
             //if the result contains entries with surnames,
             //sort by surname:
             ldap_sort($this->ds, $r, "sn");
       
             $result = ldap_get_entries($this->ds, $r);
             return $this->convert_from_ldap($result);
          }
          return null;
       }
    
       // would be nice to read fields. left the parameter in as placeholder and to be compatible with other read()'s
       function read($fields=null, $uid)
       {
          $r = ldap_search($this->ds, $this->baseDn, 'uid='. $uid);
          if ($r)
          {
             $l = ldap_get_entries($this->ds, $r);
             $convert = $this->convert_from_ldap($l);
             return $convert[0];
          }
       }
    
       function save($data)
       {
          $dn = "uid=".$data['LdapUser']['uid'].",".$this->baseDn;
    
          foreach ($data['LdapUser'] as $field => $value):
             $data_ldap[$field][0] = $value;
          endforeach;
    
          // The following line sets the object classes. The ones shown are the default for users. Other environments may be different.
          // For example, in my world, I use array('inetOrgPerson','posixAccount','top','shadowAccount').
          // However, this depends on how your ldap schema is setup.
          $data_ldap['objectClass'] = array('account','posixAccount','top','shadowAccount');
    
          return ldap_add($this->ds, $dn, $data_ldap);
       }
    
       function del($uid)
       {
          $dn = "uid=$uid,".$this->baseDn;
          return ldap_delete($this->ds, $dn);
       }
       
       function auth($uid, $password)
       {
           $result = $this->findAll('uid', $uid);
       
           if($result[0])
           {
               if (ldap_bind($this->ds, $result[0]['dn'], $password))
                   {
                       return true;
                   }
                   else
                   {
                       return false;
                   }
           }
           else
           {
               return false;
           }
       }
    
       function findLargestUidNumber()
       {
          $r = ldap_search($this->ds, $this->baseDn, 'uidnumber=*');
          if ($r)
          {
             // there must be a better way to get the largest uidnumber, but I can't find a way to reverse sort.
             ldap_sort($this->ds, $r, "uidnumber");
                
             $result = ldap_get_entries($this->ds, $r);
             $count = $result['count'];
             $biguid = $result[$count-1]['uidnumber'][0];
             return $biguid;
          }
          return null;
       }
    
       private function convert_from_ldap($data)
       {
          foreach ($data as $key => $row):
             if($key === 'count') continue;
     
             foreach($row as $key1 => $param):
                if(!is_numeric($key1)) continue;
                if($row[$param]['count'] === 1)
                   $final[$key]['LdapUser'][$param] = $row[$param][0];
                else
                {
                   foreach($row[$param] as $key2 => $item):
                      if($key2 === 'count') continue;
                      $final[$key]['LdapUser'][$param][] = $item;
                   endforeach;
                }
             endforeach;
          endforeach;
          return $final;
       }
    }
    ?>
    ?>

// controllers/ldap_users_controller.php

Controller Class:
`````````````````

::

    <?php 
    <?php
    class LdapUsersController extends AppController
    {
       var $name = 'LdapUsers';
       var $uses = array('LdapUser');
    
       function index()
       {
          $users = $this->LdapUser->findAll('uid', '*');
          $this->set('ldap_users', $users);
       }
    
       function add() {
          if(empty($this->data)) {
             $this->set('ldap_users', null);
             $newuid = $this->LdapUser->findLargestUidNumber() + 1;
             $this->set('newuid',$newuid);
          } else {
             if($this->LdapUser->save($this->data)) {
                if(is_object($this->Session)) {
                   $this->Session->setFlash('The LDAP User has been saved');
                   $this->redirect('/ldap_users/index');
                } else {
                   $this->flash('LDAP User saved.', '/ldap_users/index');
                }
             } else {
                if(is_object($this->Session)) {
                   $this->Session->setFlash('Please correct errors below.');
                }
                $data = $this->data;
                $this->set('ldap_users', $data);
             }
          }
       }
    
       function edit($id) {
          if(empty($this->data)) {
             $data = $this->LdapUser->read(null, $id);
             $this->set('ldap_user', $data );
          } else {
             $this->LdapUser->del($id);
             if($this->LdapUser->save($this->data)) {
                if(is_object($this->Session)) {
                   $this->Session->setFlash('The LDAP User has been saved');
                   $this->redirect('/ldap_users/index');
                } else {
                   $this->flash('LDAP User saved.', '/ldap_users/index');
                }
             } else {
                if(is_object($this->Session)) {
                   $this->Session->setFlash('Please correct errors below.');
                }
                $data = $this->data;
                $this->set('ldap_user', $data);
             }
          }
       }
    
       function view($uid) {
          $this->set('ldap_user', $this->LdapUser->read(null, $uid));
       }
    
       function delete($id) {
          $this->LdapUser->del($id);
          $this->redirect('/ldap_users/index');
       }
    }
    ?>
    ?>

// views/ldap_users/index.thtml

View Template:
``````````````

::

    
    <h1>List LDAP Users</h1>
    <table>
    <tr>
       <th>username</th>
       <th>cn</th>
       <th>shell</th>
       <th>uid</th>
       <th>gid</th>
       <th>home</th>
       <th>gecos</th>
       <th>Actions</th>
    </tr>
    </tr>
    <?php foreach ($ldap_users as $key => $value): ?>
    <tr>
       <td><?=$value['LdapUser']['uid']?></td>
       <td><?=$value['LdapUser']['cn']?></td>
       <td><?=$value['LdapUser']['loginshell']?></td>
       <td><?=$value['LdapUser']['uidnumber']?></td>
       <td><?=$value['LdapUser']['gidnumber']?></td>
       <td><?=$value['LdapUser']['homedirectory']?></td>
       <td><? if(isset($value['LdapUser']['gecos'])) echo $value['LdapUser']['gecos'] ?></td>
       <td>
          <?php echo $html->link('View', '/ldap_users/view/' . $value['LdapUser'][$this->controller->LdapUser->primaryKey])?>
          <?php echo $html->link('Edit', '/ldap_users/edit/' . $value['LdapUser'][$this->controller->LdapUser->primaryKey])?>
          <?php echo $html->link('Delete', '/ldap_users/delete/' . $value['LdapUser'][$this->controller->LdapUser->primaryKey])?>
       </td>
    </tr>
    <?php endforeach; ?>
    </table>
    
    <ul>
       <li><?php echo $html->link('New Ldap User', '/ldap_users/add'); ?></li>
    </ul>

// views/ldap_users/add.thtml

View Template:
``````````````

::

    
    <h1>New LDAP User</h1>
    <? if(isset($ldap_users['LdapUser']['uidnumber'])) $newuid = $ldap_users['LdapUser']['uidnumber'] ?>
    <form action="<?php echo $html->url('/ldap_users/add'); ?>" method="post">
    <div class="required"> 
       <label for="ldap_user_uid">uid</label>
       <?php echo $html->input('LdapUser/uid', array('id' => 'ldap_user_uid', 'size' => '40', 'value' => $ldap_users['LdapUser']['uid'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/uid', 'uid can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_cn">cn</label>
       <?php echo $html->input('LdapUser/cn', array('id' => 'ldap_user_cn', 'size' => '40', 'value' => $ldap_users['LdapUser']['cn'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/cn', 'cn can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_userpassword">userpassword</label>
       <?php echo $html->input('LdapUser/userpassword', array('id' => 'ldap_user_userpassword', 'size' => '40', 'value' => $ldap_users['LdapUser']['userpassword'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/userpassword', 'userpassword can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_loginshell">loginshell</label>
       <?php echo $html->input('LdapUser/loginshell', array('id' => 'ldap_user_loginshell', 'size' => '40', 'value' => $ldap_users['LdapUser']['loginshell'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/loginshell', 'loginshell can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_uidnumber">uidnumber</label>
       <?php echo $html->input('LdapUser/uidnumber', array('id' => 'ldap_user_uidnumber', 'size' => '40', 'value' => $newuid )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/uidnumber', 'uidnumber can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_gidnumber">gidnumber</label>
       <?php echo $html->input('LdapUser/gidnumber', array('id' => 'ldap_user_gidnumber', 'size' => '40', 'value' => $ldap_users['LdapUser']['gidnumber'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/gidnumber', 'gidnumber can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_homedirectory">homedirectory</label>
       <?php echo $html->input('LdapUser/homedirectory', array('id' => 'ldap_user_homedirectory', 'size' => '40', 'value' => $ldap_users['LdapUser']['homedirectory'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/homedirectory', 'homedirectory can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_gecos">gecos</label>
       <?php echo $html->input('LdapUser/gecos', array('id' => 'ldap_user_gecos', 'size' => '40', 'value' => $ldap_users['LdapUser']['gecos'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/gecos', 'gecos can not be blank.') ?>
    </div>
    <div class="submit"><input type="submit" value="Add" /></div>
    </form>
    <ul>
    <li><?php echo $html->link('List LDAP Users', '/ldap_users/index')?></li>
    </ul>

// views/ldap_users/edit.thtml

View Template:
``````````````

::

    
    <h1>Edit LDAP User</h1>
    <form action="<?php echo $html->url('/ldap_users/edit/'.$ldap_user['LdapUser']['uid'].''); ?>" method="post">
    <div class="required"> 
       <label for="ldap_user_uid">uid</label>
       <?php echo $html->input('LdapUser/uid', array('id' => 'ldap_user_uid', 'size' => '40', 'value' => $ldap_user['LdapUser']['uid'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/uid', 'uid can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_cn">cn</label>
       <?php echo $html->input('LdapUser/cn', array('id' => 'ldap_user_cn', 'size' => '40', 'value' => $ldap_user['LdapUser']['cn'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/cn', 'cn can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_userpassword">userpassword</label>
       <?php echo $html->input('LdapUser/userpassword', array('id' => 'ldap_user_userpassword', 'size' => '40', 'value' => $ldap_user['LdapUser']['userpassword'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/userpassword', 'userpassword can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_loginshell">loginshell</label>
       <?php echo $html->input('LdapUser/loginshell', array('id' => 'ldap_user_loginshell', 'size' => '40', 'value' => $ldap_user['LdapUser']['loginshell'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/loginshell', 'loginshell can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_uidnumber">uidnumber</label>
       <?php echo $html->input('LdapUser/uidnumber', array('id' => 'ldap_user_uidnumber', 'size' => '40', 'value' => $ldap_user['LdapUser']['uidnumber'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/uidnumber', 'uidnumber can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_gidnumber">gidnumber</label>
       <?php echo $html->input('LdapUser/gidnumber', array('id' => 'ldap_user_gidnumber', 'size' => '40', 'value' => $ldap_user['LdapUser']['gidnumber'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/gidnumber', 'gidnumber can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_homedirectory">homedirectory</label>
       <?php echo $html->input('LdapUser/homedirectory', array('id' => 'ldap_user_homedirectory', 'size' => '40', 'value' => $ldap_user['LdapUser']['homedirectory'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/homedirectory', 'homedirectory can not be blank.') ?>
    </div>
    <div class="required"> 
       <label for="ldap_user_gecos">gecos</label>
       <?php echo $html->input('LdapUser/gecos', array('id' => 'ldap_user_gecos', 'size' => '40', 'value' => $ldap_user['LdapUser']['gecos'], )) ?>
       <?php echo $html->tagErrorMsg('LdapUser/gecos', 'gecos can not be blank.') ?>
    </div>
    
    <?php echo $html->hidden('LdapUser/uid', array('value' => $ldap_user['LdapUser']['uid']))?><div class="submit"><input type="submit" value="Save" /></div>
    </form>
    <ul>
       <li><?php echo $html->link('List ldap_user', '/ldap_users/index')?></li>
    </ul>

//views/ldap_users/view.thtml

View Template:
``````````````

::

    
    <h1>View LDAP User</h1>
    <table>
    <tr>
       <td>Username</td>
       <td><?php echo $ldap_user['LdapUser']['uid']?></td>
    </tr>
    <tr>
       <td>cn</td>
       <td><?php echo $ldap_user['LdapUser']['cn']?></td>
    </tr>
    <tr>
       <td>Login Shell</td>
       <td><?php echo $ldap_user['LdapUser']['loginshell']?></td>
    </tr>
    <tr>
       <td>User ID</td>
       <td><?php echo $ldap_user['LdapUser']['uidnumber']?></td>
    </tr>
    <tr>
       <td>LdapUser ID</td>
       <td><?php echo $ldap_user['LdapUser']['gidnumber']?></td>
    </tr>
    <tr>
       <td>Home Directory</td>
       <td><?php echo $ldap_user['LdapUser']['homedirectory']?></td>
    </tr>
    <tr>
       <td>Gecos</td>
       <td><?php echo $ldap_user['LdapUser']['gecos']?></td>
    </tr>
    </table>
    <ul>
       <li><?php echo $html->link('Edit LdapUser',   '/ldap_users/edit/' . $ldap_user['LdapUser']['uid']) ?> </li>
       <li><?php echo $html->link('Delete LdapUser', '/ldap_users/delete/' . $ldap_user['LdapUser']['uid']) ?> </li>
       <li><?php echo $html->link('List LdapUser',   '/ldap_users/index') ?> </li>
       <li><?php echo $html->link('New LdapUser',      '/ldap_users/add') ?> </li>
    </ul>



.. _http://bakery.cakephp.org/articles/view/1: http://bakery.cakephp.org/articles/view/1
.. meta::
    :title: Using LDAP as a database
    :description: CakePHP Article related to ldap,Models
    :keywords: ldap,Models
    :copyright: Copyright 2006 
    :category: models

