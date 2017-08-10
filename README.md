# List

This is a simple webpage system combined with a MySQL database. This system
gives you the ability to make lists for groceries, movies to see or a day at
some zoo to visit. It is based on the facts that you hear some movie title
and you are not able to see it directly. Small notes on paper will get lost,
your memory cant keep all information clear every day, so put it on the list.

The same for a day out, if you have seen some advertisement to come visit
the zoo at SomePlace and it intrigues you to go visit it when you have a 
day off, put it on the list. Much more fun; if you have a full list of items
to go to as a day-visit, you can open the list and see what you can visit.

Once your item, grocery, movie of day out has been orderd, seen or visited,
you can simple click it to the correct state. If you don't need the lattish
because your friend brought it with him/her you can obviously delete it 
straight away.

There is also support for multiple groups so your household and that of 
your parent of friends can use it too!

Disclamer:
	**All text items are currently in Dutch.**


## Prerequisites
- PHP 5.6 or higher
- mysqli/mysqlnd
- MySQL 5.5 or higher
- Apache2


### Setting up

Place all files in a directoy where you like it, where it can be read from
the Apache webserver (common place should be '/var/www/html').

Then, set up a user account and database. Most commonly it is done by:
```
CREAT DATABASE list;
GRANT ALL PRIVILEGES ON list.* TO "list"@"127.0.0.1" IDENTIFIED BY "veryG00dpas5w0rD!";
FLUSH PRIVILEGES;
```

Follow by updating the 'config.php' file with the correct credentials.

It is now time to create the tables. If your system requires different table names,
please correct them in the 'createtables.sql' file. Then, import the file into SQL.

```
mysql -u list -p list < createtables.sql
```

You can now browse to your new list system and login with the default admin account:
https://domain.tld/your_install_directory/

U: admin
P: p4s5w0rd!1

Please consider to change the password directly in the Admin section.


Making others admin requires, at this moment, a manual query against the user table:
```
USE list;
UPDATE `users` SET admin = 1 WHERE id = 6;
```
Where 6 of course represents the correct id of the user you want to make administrator.


To set the email part to work add this to your crontab:
```
*/5 * * * *     /usr/bin/php /var/www/html/list/bgroundjobs.php
```


Things to correct to your situation:

functions.php
	function __dispHtmlErrorpage__
	- set the email headers and sender correctly
bgroundjobs.php
	- set the email headers and sender correctly
	



disclamer:
Yes of course there are still some functions in the admin portal that aren't working
properly. Please feel free to pull some requests!



Donations can be made!

DigiByte: DQ5smCNqGz1r2YrF1womnS7JPKmkES1rrp
Ripple: rHTygsViWdjP1x1RX9i1g5ZfkZhyCMiNFL
ReddCoin: RbgAAMuJ3MzAK4bDdXMvv1uWUGWYMp3mad
