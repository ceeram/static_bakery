Datasource for S3 - Amazon Simple Storage System
================================================

by eskil on July 31, 2009

Cake in the S3 clouds. Storing files for easy access in the cloud.
Accessing them across multiple platforms, guaranteeing up time,
distributing big files or many files to a large number of users. Store
your files and folders on a web service.
I was meeting the challenge to guarantee the uptime and the stability
of a few files created by cake, after a previous denial of service
attack on our server. A small number of files is to be accesses and
distributed out to a few thousand users, with a guarantee of our
servercapasity.

After the cake feast I ended up with creating a data source for S3
instead of a behavior. (Amazon web services - Simple Storage System)
Based on the Api and some of the S3 php defualt file.

For the datasource I only need a few of the functions

addBucket
deleteBucket
listBuckets

listKeys
createKey
deleteKey


Ups:
It scales infinitively. So your server will never be at risk. *
Fast access to your files across the world.
Payment after usage.
A light step into cloud networks.

Downs:
It costs money
Some latency between upload and the distribution (AWS says up to 10
min, but it is only seconds for normal filesizes)
Require an AWS account.

Links:
Amazon S3 `http://aws.amazon.com/s3/`_
Page 2: AwsSource the datasource
Page 3: Example of usage

* not 100% true, but for all normal scenarios. If you are to
distribute 5GB files to millions of users, this should not be a
problem.

.. _http://aws.amazon.com/s3/: http://aws.amazon.com/s3/

.. author:: eskil
.. categories:: articles, case_studies
.. tags:: datasource,Cloud,eskil,s,amazon web services,Case Studies

