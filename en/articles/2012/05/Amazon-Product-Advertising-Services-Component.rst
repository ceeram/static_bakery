

Amazon Product Advertising Services Component
=============================================

by %s on May 18, 2012

A CakePHP 2.x component for using the Amazon PAS
The source for this component is available from `Github`_

I first toyed with the idea of making the official AWS/PAS libraries (
available from github in the repositories 'aws-sdk-for-php' and
'cloudfusion' ) usable by CakePHP but then went away from this as it
ties you into using CURL. Personally I would rather have the choice of
using Cake's own HttpSocket to make requests if I choose to or even my
own Caching and Throttled versions of HttpSocket.

So instead, I created a component whose sole purpose is to construct
URL's that can then be sent to Amazon using any method I choose in
order obtain the information.

Installation, Configuration and Usage documentation are contained
within the readme file and the component source so there is little
need to repeat it here.

I hope it is of use to someone other than myself !!!



.. _Github: https://github.com/SteveFound/CakePHP_AmazonPAS
.. meta::
    :title: Amazon Product Advertising Services Component
    :description: CakePHP Article related to service,component,amazon,advertising,product,Components
    :keywords: service,component,amazon,advertising,product,Components
    :copyright: Copyright 2012 
    :category: components

