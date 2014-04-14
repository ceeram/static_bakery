j2eeAuth component v0.1.0
=========================

by racklin on May 23, 2007

J2eeAuth Component is J2ee Realm like User/Group/Role Base Auth. And
J2eeAuth Component's user/role database is compatible with Tomcat
JDBCRealm/DataSourceRealm. So, you can use exists tomcat user database
or cakephp's project can use with tomcat java project. :D The Realm of
Tomcat's Document: A Realm is a "database" of usernames and passwords
that identify valid users of a web application (or set of web
applications), plus an enumeration of the list of roles associated
with each valid user. You can think of roles as similar to groups in
Unix-like operating systems, because access to specific web
application resources is granted to all users possessing a particular
role (rather than enumerating the list of associated usernames). A
particular user can have any number of roles associated with their
username.


j2eeAuth Usage:
~~~~~~~~~~~~~~~

getUserPrincipal
++++++++++++++++
Returns a Principal object containing the name of the current
authenticated user. If the user has not been authenticated, the method
returns null.


isUserInRole
++++++++++++
Returns a boolean indicating whether the authenticated user is
included in the specified logical "role". Roles and role membership
can be defined using deployment descriptors. If the user has not been
authenticated, the method returns false.


.. meta::
    :title: j2eeAuth component v0.1.0
    :description: CakePHP Article related to Role,Components
    :keywords: Role,Components
    :copyright: Copyright 2007 racklin
    :category: components

