PHP session locking problem with Ajax
=====================================

We now -and it is not a new thing- develop applications depend on Ajax up to %100 But when a web page sends two or more Ajax requests you find them take mush time and maybe finish almost in the same time and here we are trying to test that problem.

Here we created two buttons call server side PHP scripts both have use sleep(5); but one of them have session_start(); and the other have not.

When you click the one without session may times, it will send many request to the server but the result will come with out blocking each other.

The other button the one calles file starts with session_start() which will lock the session file on the server for that user so each request made from that button will wait for the previous ones.

Check the console for result

More info in my blog post <a href="http://eslam.me/blog/php-session-locks-how-to-prevent-blocking-ajax-requests/">Does your Ajax requests take long time? it's PHP session locking problem</a>.
