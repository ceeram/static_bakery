Easy links in admin...
======================

by %s on May 08, 2009

sick of making links every were that are hard to maintain.. this may
be for you. easily generate your CRUD links in admin with this handy
extension to the Html helper
all you need to do is create the file /app/views/helpers/button.php

and add the code bellow:

Helper Class:
`````````````

::

    <?php 
    class ButtonHelper extends HtmlHelper
        {
            var $helpers  = array( 'Html' );
            var $settings = array();
            var $_return  = '';
    
            function _startup()
            {
                $this->_return = '';
    
                $image_dir = 'icons/';
    
                $this->settings['params']['image'] = Configure::read( 'Settings.Button.image_dir' ) != '' ? Configure::read( 'Settings.Button.image_dir' ) : $image_dir;
                if ( $this->params['plugin'] != '' )
                {
                    $this->settings['params']['image'] = '/'.$this->params['plugin'].'/img/'.$image_dir;
                }
                $this->settings['params']['delete_message'] = Configure::read( 'Settings.Button.delete_message' ) != '' ? Configure::read( 'Settings.Button.delete_message' ) : 'Are you sure you want to delete  %s?';
    
                $this->settings['params']['model']      = $this->settings['params']['model']       != '' ? $this->settings['params']['model']       : $this->params['models'][0];
                $this->settings['params']['plugin']     = $this->settings['params']['plugin']      != '' ? $this->settings['params']['plugin']      : $this->params['plugin'];
                $this->settings['params']['controller'] = $this->settings['params']['controller']  != '' ? $this->settings['params']['controller']  : $this->params['controller'];
    
                $check = false;
                if ( ( (string)$this->settings['params']['controller'] != '' ) && ( (int)$this->settings['params']['id'] >= 1 ) )
                {
                    $check = true;
                }
    
                return $check;
            }
    
            function get_actions( $_a = array(), $_p = array(), $size = 'small' )
            {
                if( !empty( $_a ) )
                {
                    $this->settings['actions'] = $_a;
                    $this->settings['size']    = $size;
                    if ( !empty( $_p ) )
                    {
                        $this->settings['params']['model']      = '';
                        $this->settings['params']['plugin']     = '';
                        $this->settings['params']['controller'] = '';
                        $this->settings['params'] = array_merge( $this->settings['params'], $_p );
                    }
    
                    if ( !$this->_startup() )
                    {
                        return false;
                    }
    
                    foreach( $this->settings['actions'] as $_action )
                    {
                        switch( $_action )
                        {
                            case 'view':
                            case 'edit':
                            case 'copy':
                                $this->generic( $_action );
                                break;
    
                            case 'delete':
                                $this->delete();
                                break;
                        } // switch
                    }
    
                    if ( $this->_return != '' )
                    {
                        return $this->_return;
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
    
            function generic( $type = null )
            {
                if ( $type == null )
                {
                    return false;
                }
                $this->_return = $this->_return.
                                 $this->image(
                                     $this->settings['params']['image'].
                                         sprintf( '%s-'.Configure::read( 'Settings.Button.image_'.$type ), $this->settings['size'] = $this->settings['size']  != '' ? $this->settings['size'] : 'small' ),
                                     array(
                                         'alt' => __( $type, true ),
                                         'title' => __( $type, true ),
                                         'width' => Configure::read( 'Settings.Button.image_size' ).'px',
                                         'url' => array(
                                                     'plugin' => $this->settings['params']['plugin'],
                                                     'controller' => $this->settings['params']['controller'],
                                                     'action' => $type,
                                                     $this->settings['params']['id']
                                                )
                                         )
                                 );
            }
    
            function delete()
            {
                $this->_return = $this->_return.
                                 $this->link(
                                     $this->image(
                                         $this->settings['params']['image'].
                                             sprintf( '%s-'.Configure::read( 'Settings.Button.image_delete' ), $this->settings['size'] = $this->settings['size']  != '' ? $this->settings['size'] : 'small' ),
                                         array(
                                             'alt' => __( 'Delete', true ),
                                             'title' => __( 'Delete', true ),
                                             'width' => Configure::read( 'Settings.Button.image_size' ).'px'
                                            )
                                     ),
                                     array(
                                         'plugin' => $this->settings['params']['plugin'],
                                         'controller' => $this->settings['params']['controller'],
                                         'action' => 'delete',
                                         $this->settings['params']['id']
                                     ),
                                     null,
                                     sprintf(
                                         __( $this->settings['params']['delete_message'], true ),
                                         $this->settings['params']['name'] = isset( $this->settings['params']['name'] ) ? $this->settings['params']['name']  : 'this entry'
                                     ),
                                     false
                                 );
            }
        }
    ?>

then add this to the controller you want it in, or app_controller.php
is the best bet

Controller Class:
`````````````````

::

    <?php 
    var $helpers = array( 'Button' );
    ?>

now in your views instead of doing something along the lines of

View Template:
``````````````

::

    
    echo $html->link( __( 'View', true),   array( 'action' => 'view',   $user['User']['id']));
    echo $html->link( __( 'Edit', true),   array( 'action' => 'edit',   $user['User']['id']));
    echo $html->link( __( 'Delete', true), array( 'action' => 'delete', $user['User']['id'];

you can do the following ( this will automaticaly create a link based
on the plugin and controller you are in )

View Template:
``````````````

::

    
    echo $button->get_actions(
            array(
                'view',
                'edit',
                'delete'
            ),
            array(
                'id' => $user['User']['id']
            )
        );

and if it is for another plugin prehaps

View Template:
``````````````

::

    
    echo $button->get_actions(
            array(
                'view',
                'edit',
                'delete'
            ),
            array(
                'id'         => $user['Post']['id'],
                'name'       => $user['Post']['title'], //will give a custom delete alert message
                'plugin'     => 'post',
                'controller' => 'posts'
            )
        );

this expects a few things to be in place though... your images should
be named as follows
[size]-edit.ext
eg..
small-edit.jpg
big-edit.jpg

this is because you can pass extra params to the helper eg:

View Template:
``````````````

::

    
    echo $button->get_actions(
            array(
                'view'
            ),
            array(
                'id'   => $user['User']['id'],
                'size' => 'large'
            )
        );

will out link to the Users-view method with a image that is large-
view.jpg

if you are creating a link that points to a plugin then the helper
will look for a image in /plugin/vendors/img/name.ext

Still extending to check if the image is there and then default to the
normal img/ folder if nothing is found.
another nice feature is the fact that it generates the alt text and
the title text for the link dynamicaly

one other shortfall that im working on is checking if there is actualy
a method like the one you have specified.. other options are to
integrate acl and see if the user has permission to visit said link
before its even generated.

.. meta::
    :title: Easy links in admin... 
    :description: CakePHP Article related to helper,automagic links,crud,Helpers
    :keywords: helper,automagic links,crud,Helpers
    :copyright: Copyright 2009 
    :category: helpers

