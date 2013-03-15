SMS Text Component
==================

by %s on March 12, 2008

Simple component for sending SMS text messages to mobile devices using
the Clickatell API (They provide free test accounts).

If you need a simple way to send SMS messages from your CakePHP app
then this component may well be what you need. It interfaces with the
Clickatell servers to send an SMS text message to your recipients
device using HTTP post.


Requirements
````````````

+ PHP5
+ CakePHP 1.2
+ `Clickatell`_ account (free testing credits)
+ The component code (see below or `get here`_)



Sending a Text
``````````````

To use it in your controller here's an example (explanation follows):

::

    
    function send_text() {
      $this->Sms->api_id = '000000';
      $this->Sms->api_user = 'youruser';
      $this->Sms->api_pass = 'apipassword';
      $this->Sms->api_from = 'fromcid';
      $message = sprintf("Hello World");
      $this->Sms->postSms($number, $message);
    }


You set all of the important class variables such as the api id,
password, user, etc. Then you simply call the postSms() method
providing both the number and your formatted message as parameters.


Checking Balance
````````````````

You can also check your Clickatell account balance by simply calling
the queryBalance() method with no parameters. Note: You'll still need
to provide the usual class variables such as api id, user, etc.

This method will return a floating point number of your remaining
account credits.


Component Code
``````````````

You can download the latest version of the component and all related
news from `my site`_.

Otherwise you can use this code here:

::

    
    <?php
    /**
     * SMS component for CakePHP using the Clickatell HTTP API interface.
     * @author Doug Bromley <doug.bromley@gmail.com>
     * @copyright Doug Bromley
     * @link http://www.cakephp.org CakePHP
     * @link http://www.clickatell.com Clickatell
     *
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
    
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     * You should have received a copy of the GNU General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/>.
     */
    
    class SmsComponent extends Object {
      /**
      * The username for the Clickatell API
      * @access public
      * @var string
      */
      var $api_user = null;
    
      /**
      * The password for the Clickatell API
      * @access public
      * @var string
      */
      var $api_pass = null;
    
      /**
      * Who will be shown as the sender of the text at the receivers handset.
      * @access public
      * @var string
      */
      var $api_from = null;
    
      /**
      * The API id for this product.
      * @access public
      * @var string
      */
      var $api_id = null;
    
    
      /**
      * The Clickatell XML API url
      */
      const API_XML_URL = 'http://api.clickatell.com/xml/xml';
    
      /**
      * The Clickatell HTTP API url for sending GET or POST requests too.
      */
      const API_HTTP_URL = 'http://api.clickatell.com/http/';
    
    
      /**
      * Post a message to the Clickatell servers for the number provided
      * @param string $tel The telephone number in international format.  Not inclduing a leading "+" or "00".
      * @param string $message The text message to send to the handset.
      * @return string
      * @see SmsComponent::api_id
      * @see SmsComponent::api_user
      * @see SmsComponent::api_pass
      * @see SmsComponent::api_from
      */
      function postSms($tel, $message) {
        $postdata = http_build_query(
          array(
            'api_id' => $this->api_id,
            'user' => $this->api_user,
            'password' => $this->api_pass,
            'from' => $this->from,
            'to' => $tel,
            'text' => $message
          )
        );
    
        $opts = array('http' =>
          array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
          )
        );
    
        $context  = stream_context_create($opts);
        $response = file_get_contents(self::API_HTTP_URL.'sendmsg', false, $context);
        return $response;
      }
    
      /**
      * Get the balance of your Clickatell account.
      * @return float
      * @see SmsComponent::api_id
      * @see SmsComponent::api_user
      * @see SmsComponent::api_pass
      * @see SmsComponent::api_from
      */
      function queryBalance() {
        $postdata = http_build_query(
          array(
            'api_id' => $this->api_id,
            'user' => $this->api_user,
            'password' => $this->api_pass
          )
        );
    
        $opts = array('http' =>
          array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
          )
        );
    
        $context  = stream_context_create($opts);
        $response = file_get_contents(self::API_HTTP_URL.'getbalance', false, $context);
        return $response;
      }
    }

I'm in no way affiliated with Clickatell and they've not been involved
in the development of this component. I do not recommend them by
providing this component - its merely the messaging gateway we use at
our company.


.. _Clickatell: http://www.clickatell.com/
.. _my site: http://www.straw-dogs.co.uk/03/03/cakephp-sms-component/
.. meta::
    :title: SMS Text Component
    :description: CakePHP Article related to text,api,mobile,clickatell,sms,cell,interface,phone,Components
    :keywords: text,api,mobile,clickatell,sms,cell,interface,phone,Components
    :copyright: Copyright 2008 
    :category: components

