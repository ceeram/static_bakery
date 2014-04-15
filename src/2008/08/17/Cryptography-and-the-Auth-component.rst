Cryptography and the Auth component
===================================

by achew22 on August 17, 2008

Learn how, and why the auth component is so secure for your passwords.
I'm going to start this by doing an abbreviated, without explanation,
3 liner to anyone who doesn't want to read all of this. It is
irresponsible for anyone who runs a website that has users/logins to
not encrypt your user's password. Step 1: Identify a cryptographically
strong function (SHA1/MD5 or even RIPEMD-160) and hash something,
anything! Your name, your address, parents middle names followed by
their birthdays â€“ whatever you want, but write it down. That is
going to be our 'salt'. Now, in your authorization component's
password function prepend (put before) the 'salt' to whatever the
password is before it goes through your hashing algorithm. Now you
have a cryptographically strong password function where as you did not
before. Step 3: profit.


Why is that so?
---------------

Now that you know what you are going to do, I'd bet you'd like to know
why that is cryptographically strong. Well, fortunately I have an
answer. First we need to step into what all hashing functions are. A
hashing function is a mathematical function (strict definition) in
that if you put something in, there is one and exactly one output no
matter when you use the function. It is because of this nature (read
repeatability) that you can hash a password and store the hash and
know that there is no time when you will ever arrive at a different
hash (unless the input is changed.) It is important to differentiate
hashes from compression, hashing is lossy where as as compression is
lossless, having two things compress to the same value gives you
certainty that the two values are from identical sources where as a
hash gives you 99.999% certainty that it is from the same source.
Don't worry though we will use that to our advantage.


Prove it
--------

Well for the sake of this I am going to assume you have taken 7th
grade algebra. There is a property called the transitive property
which is one of my favorite properties.

A = 15
B = A
C = B
D = C

That means that of course, D = 15 because D = C = B = A = 15 simple
enough, right? Well what happens if you add in a function. If you're
rusty on function notation check this out:
`http://www.purplemath.com/modules/fcnnot.htm`_
f(x) = x + 3
A = 15
B = f(A)
C = A
D = f(C)

Again, there are some transitive things going on, A = C = 15 and B = D
= f(A) = A + 3 = 18. That's not too bad is it? Believe it or not, that
is the same concept that password hashing is based off of. Lets
replace f(x) = x+3 with something a little bit more complex like a
SHA1 hashing algorithm.

g(x) = SHA1[x]
And following the previous example

A = 15
B = g(A)
C = A
D = g(C)

Now, C = A = 15 and B = D = g(A) = SHA1[A] =
587b596f04f7db9c2cad3d6b87dd2b3a05de4f35 No matter which way you cut
it when you put anything into SHA1 you will get out something that is
perfectly identical if you do it now or in a week.


How is that helpful?
--------------------

Hashes are considered one way functions, the randomness of the output
makes it difficult to guess the input. Whereas encryption/compression
are designed for you to be able to recover the data at any point (if
you know enough about the method of encryption/compression.) A hash is
designed for you not to be able to get it back, it is designed to not
have collisions very often. A collision in a cryptographic function is
an instance in which two inputs resolve to the same function.
Unfortunately though there is no such thing as a perfect, collision
free, hashing function. Yes you will never be able to create a hashing
function that is perfect but you can get to a point where it is
â€œgood enough.â€ SHA1 is 40 hexadecimal characters of which there
are 32 (Aâ€“Z 0â€“9). That means there are only 32^40 ( 1,606,938,044,
258,990,275,541,962,092,341,162,602,522,202,993,782,792,835,301,376 )
possible combinations. Don't get me wrong, that number is immeasurably
large, however there are more possible combinations of letters in the
binary alphabet as soon as you get the the 204th 1 or 0. There are
people in the world who have names longer than 204 bites (provided
they are using standard ASCII encoding) thats only 25.5 characters!
That means that if you put anything into this hashing function that is
greater than 25 characters you will run into collisions.


Aren't collisions bad?
----------------------

Yes and no, you don't want them if your doing linear poling or
something of that nature, but we will use them to our advantage. A
collision is by definition a point where two inputs map to the same
output, that means that you have no idea what the input was. What if I
told you that for every hashing function there are an unlimited number
of collisions for every possible input? Well it's true! There are
probably between 0 and 1 collisions when you are only using an input
that is shorter than the output (provided your using a
cryptographically strong hashing algorithm) but as soon as you getting
past the length of the output, you will get more and more collisions.
That means that there is no way to go back from whence you came and
derive the password that was used to generate that password. Now are
you beginning to see the beauty of the collision?


How can I use this?
-------------------

The beauty of hashing functions means that you could (and I don't
recommend it) post a direct output from your `users` table and be
fairly sure that no one would ever be able to guess what the real
password that was used to create that hash! I'm not going to go into
how to create a secure cryptographic authorization mechanism because
the coders at the CakePHP foundation have already come up with a
beautifully elegant solution to this problem, the Auth component.
Using the Auth component in CakePHP you can quickly and simply include
it in your components list and you basically have safe users including
login/logout/account creation and account management! I won't bore you
with the details of implementation. But know this, very simply you can
add users to your system, keep them safe and not have to worry even if
there is a massive data leak from your SQL database's `users` table.

If you think I'm a liar about the security of this mechanism and it
isn't that strong â€“ here is the password (encoded by this process)
that came out of one of the Auth component for my email, the keys to
the castle. 500dfc5af2012f9e0af3d7faa5cd62db2e113fa7


.. _http://www.purplemath.com/modules/fcnnot.htm: http://www.purplemath.com/modules/fcnnot.htm

.. author:: achew22
.. categories:: articles, general_interest
.. tags:: cryptography auth co,General Interest

