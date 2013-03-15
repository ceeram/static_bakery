Helping small companies share BIG files
=======================================

by %s on October 15, 2006

My pet project, called Fileshifter, is secure file-manager targeted at
small companied needing to transfer big files to and from clients and
coworkers. This one-man creation written in PHP using Cake has unique
features such as: drag-n-drop uploads and no maximum file-size (7GB
DVD image is the largest uploaded to date). Without CakePHP it would
not exist.
Many of my clients are in the media industry. Productions companies,
Ad agencies, Photographers, Musicians... The big problem for these,
often smaller, companies is transferring work back and forth. They
need to receive a logo from the client, send working files to project
members, send finished work to clients for approval, things like that.
I built FIleshifter to meet the needs of these people and thanks to
CakePHP it was enjoyable and fast to develop.

The list of must-have features were:

#. Simple to use for "the digitally challenged".
#. Simple to administer for "the digitally challenged".
#. Needs to be able to cope with large media files. (GBs not MBs)
#. Installable on almost any web-host.
#. Simple access control that anyone can understand.

At first glance that is quite a challenge. And at this point I had
only tested CakePHP when porting my CMS from my old framework. But
doing this had given me enough confidence in the system that I had no
problem choosing it for this project.


Performance in FIleshifter using CakePHP
++++++++++++++++++++++++++++++++++++++++

The server I run the demo and host for a few clients is seeing some
remarkable traffic. Each and every request to the server invokes
CakePHP. And these are the stats:



#. Server: Pentium 3, 733mhz, 512MB RAM, 2TB SATA RAID.
#. OS: Fedora Linux, ext3 filesystem.
#. Data on server at the time of writing: 1.37 TB.
#. Daily traffic: on average 28 GB.
#. Upload / download speed: up to 9Mbit measured.
#. Response time: 0.3-0.5 sec.


IMHO that's not too bad for a rusty old web-server. Any claims that
using a RAD framework like CakePHP has performance hit is for the most
part nonsense. I managed to shave 0.3sec off the request times when I
re-factored a few maintenance tasks. I am very happy with these
numbers and even more happy that clients can email me about a bug of a
new feature and I can have that code written and published in a matter
of minutes or hours and not days.



Simple GUI for "the digitally challenged".
++++++++++++++++++++++++++++++++++++++++++

The biggest problem in designing a simple, humane, elegant GUI is that
human interaction never ever matches the database of code structure on
the server. This makes it so easy to make a bad feature since it is
usually easier to code. This is probably where CakePHP (and other
similar frameworks I guess) shines the brightest. The solid and very
modular MVC structure makes it very easy to setup and re-factor code
as to let the GUI do what it wants. It was very easy to go from
wanting it to work a certain way to creating a working implementation.

One example of this is the way I set up many features in Fileshifter.
Since I wanted a cohesive GUI I did not want a lot of screens doing
specific things. I instead created "display methods" and "action
methods" in my controllers. The displays simply present the
applications state. THis can be administration, browsing a project or
similar. Action methods never render anything. They simply perform a
task and redirects you back to the display screen. They notify the
user of their success of failure through Flash messages stored in the
session. To the user the result is a GUI that has buttons that "just
do stuff". Another advantage is that these actions are well suited for
Ajax integration even in areas that do not currently use Ajax. Note: I
actively chose not to use Ajax for the main GUI. This means that users
can select a file, copy the URL and email it to someone just like an
web-page. The receiver clicks the link and is take directly to that
file in Fileshifter (if they have access to it that is).



Coping with large files.
++++++++++++++++++++++++

This was the biggest technical hurdle to overcome. I still have not
seen any competing or similar product that will let users upload files
that are several GB in size. The biggest file tested in FIleshifter
was a 7GB DVD-image, no problem. The real limit is only the servers
filesystem. An older filesystem may have a 2GB limit but any decent
server can handle really big files.


Now for the secret. I cheated. There is no way you can upload a 7GB in
the browser without crashing the server, browser or both. Fileshifter
uses a Java-applet capable of transferring the files in a more
efficient way.


The problem with big files is that the server likes to keep them in
memory when sending or receiving them. To get around this, the applet
reads 1MB of the file at a time and sends it to the server. PHP then
takes that MB and saves it to disk. Each subsequent chunk will be
appended to the file on the server. This applet also has other
benefits. It can handle resumable uploads if your connection is
broken. It will let you upload by drag-n-drop.

Downloading can have the same memory problem if using the simple
readfile() function in PHP. The trick here is to do what the applet
does. You tell PHP to read a chunk at a time. A further problem is
that PHP does not send the file to the client until execution is
complete... same problem... the output buffer will fill up until you
are out of RAM. So not only do you need to read a chunk at a time, you
also need to flush the output. These kinds of problems can keep people
like me occupied for ages. Thanks to PHPs wonderful community the
answers are only a few mouse-clicks away. All the gory technical stuff
could be found on php.net. I quickly calmed down and got on with
developing my app.

`http://se.php.net/manual/en/function.readfile.php#54878`_

Access control and administration.
++++++++++++++++++++++++++++++++++

Most graphic designers, video editors, assistant copyrighters and
other people working with digital media do not have a degree in
computer science. Most don't know much about technical stuff like ftp
or, god forbid, sftp. Most don't know how to set up a secure ACL
access system. I am not complaining or trying to make these people
look bad. Many of them are friends of mine. They just don't want to or
need to care about these things. And why should they? Why should you
need a science degree to be able to share files with your clients?


The whole structure of Fileshifter was set up for the benefit of these
people. You have your projects and your files. No folders in folders
or other "complicated" deep hierarchies. Administratorss have projects
and users. No per-file permissions. Just access or no access on a
project basis. It may be simpler to state what is not available.



#. No hierarchies in either permissions of projects.
#. No uploader and downloader. Just users.
#. No checkboxes. Green light for access. Red light for no access.
#. No user administration. Users have the power to take care of
   themselves.


I could go on for ages about any number of details. Instead I would
encourage the reader to jump over to the public demo of Fileshifter.
It will give you a feel for the application in no time.

`http://engdemo.fileshifter.se`_

Martin Westin

.. _http://se.php.net/manual/en/function.readfile.php#54878: http://se.php.net/manual/en/function.readfile.php#54878
.. _http://engdemo.fileshifter.se: http://engdemo.fileshifter.se/
.. meta::
    :title: Helping small companies share BIG files
    :description: CakePHP Article related to filesharing,webapp,Fileshifter,experience,Application,Case Studies
    :keywords: filesharing,webapp,Fileshifter,experience,Application,Case Studies
    :copyright: Copyright 2006 
    :category: case_studies

