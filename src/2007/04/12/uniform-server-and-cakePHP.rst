uniform server and cakePHP
==========================

I wrote the tutorial about this on the cake wiki before it was taken
down and i noticed a lot of links to that tutorial on the web which
are now dead so here is a short version of what i had there, minus the
cake basics but still all the uniform server settings i had to change:
The uniform server is a handy, small, no-installer WAMP package good
for development or production available at:
`http://www.uniformserver.com/`_ (this tutorial also includes the php4
plugin and system try plugin (unitray) available there.

The uniform server doesn't "install" so much as you just unpack it. I
recommend unpacking it in the root of a drive and not buried somewhere
in a special "my documents" or desktop folder, so like, "c:\". Once
you have unpacked it, you have to follow the instructions that come
with the php4 plugin and the unitray. I prefer to install the php4
plugin because, although cake runs on php5, most hosting services do
not run php5, so i want to make sure my development environment is the
same just in case there are any issues (especially with vendor
classes). anyway it's simple: you just replace some crap into the
uniserver/diskw/usr/local/php folder and change one line in your
apache's httpd.conf (all described in the plugin's readme). installing
the unitray is also easy, but to make the unitray compatible with
uniserver 3.3 (not 3.2) you have to change one line in the
uniserver\diskw\plugins\UniTray\unitray.ini file from "Start.bat" to
"Server_Start.bat" under the "StartUniServer" section. no biggie, and
then we get to use the ultra-handy shortcuts on the uniserver system
tray icon. you can also launch the unitray now and/or make a shortcut
in your startup items folder if you like.

[StartUniServer] Action: run; FileName: "%USRoot%\Start.bat"; ShowCmd:
hidden; Flags: waituntilterminated

change to:

[StartUniServer] Action: run; FileName: "%USRoot%\Server_Start.bat";
ShowCmd: hidden; Flags: waituntilterminated

Since the unifrom server doesn't perform a fancy install, we can
manually add the directory that the php.exe executable is located in
to the windows PATH environment variable so that we can run command-
line php scripts easier. this is optional but good. to do it, you go
to the system control panel, "advanced" tab, "environment variables"
button, then highlight "PATH" in the "system variables" then "edit"
and then to the end of whatever is already there append this:
";w:\usr\local\php". "W:"? you ask? well once you actually fire up the
uniform server it launches a virtual drive called w: so it doesn't
really matter where you run it from. if you decide not to edit the
path setting, you will have to call php with a complete path (do you
remember how to use the command line?).

We have to edit the php.ini located in w:\usr\local\php (or just go to
the unitray and hit "advanced" and "edit php configuration") and
search for the line that says "max_execution_time" and increase it
from 30 to maybe 300 or something. otherwise command line scripts keep
timing out.

;;;;;;;;;;;;;;;;;;;
; Resource Limits ;
;;;;;;;;;;;;;;;;;;;

max_execution_time = 300
; Maximum execution time of each script, in seconds

One final thing we have to do is change the httpd.conf to allow us to
use the awesome powers of apache's rewrite module. Again you can use
the unitray to open the httpd.conf quickly in "advanced" then "edit
apache configuration". All you have to do is add the option
"SymLinksIfOwnerMatch". Without that apache 2.0 assumes that the
rewrite rules essential to cake's operation are forbidden.

# The Options directive is both complicated and important. Please see
# `http://httpd.apache.org/docs-2.0/mod/core.html#options`_ # for more
information.

Options Indexes Includes SymLinksIfOwnerMatch

Finally we can launch the server. to do so use the
uniserver/server_start.bat file or use the unitray's "start uniserver"
option. this will open up an admin panel that will show you
uniserver's various features, the only one you need to care about now
is the "start mysql", but we don't need to use it yet since we will
still be restarting the server once first.

in the httpd.conf you should also change the document root to point to
the "webroot" folder of your cake install. for a development
environment, this makes things easier because you can change your
document root to the webroot of whatever app you are working on and
reset the uniform server. (that way you can also share the cake folder
amongst many apps, which is even handier if you add virtual host
directives for each project, as well as corresponding entries to your
windows/system32/drivers/etc/hosts file.)

from here you unpack cake into your document root, (i don't think you
even have to edit the app/webroot/index.php file), edit your
database.config file, and start baking (open a command prompt inside
cake/scripts/ and type: "w:\usr\local\php\php.exe bake.php -help" or
just "php.exe bake.php -help" if you did the path setting step above).

i know this tutorial had a lot more beginner instructions on baking a
basic app, but a lot has changed since then anyway and mostly i just
wanted to repost this uniform-server-specific info. hope someone finds
it useful.

.. _http://httpd.apache.org/docs-2.0/mod/core.html#options: http://httpd.apache.org/docs-2.0/mod/core.html#options
.. _http://www.uniformserver.com/: http://www.uniformserver.com/

.. author:: yeastinflexion
.. categories:: articles, tutorials
.. tags:: wamp,Tutorials

