Weather Forecast Sample Application
======================

This sample application provides interfaces to display and manage weather forecasts
for different locations and dates.

User can manage list of locations, set the weather for each of them for different date / time.
There is a simple interface to navigate between dates: by selecting previous/next date or
selection from calendar.

There is notifications support: user may set up the rules to notify him by email
of any extremal changes of weather in defined location: to hot weather, to cold,
strong wind.

The emails with notifications are sent after weather setup or notification creation.
 Also, there is a command-line script to process notifications for current date, to
 be added as cron task.


The project based on FOSRestBundle with Symfony 2.3 standard distribution.
frontend is made with Knockout.js, sammy.js and jquery.

Documentation
-------------

The documentation of FOSRestbundle is stored in the

app/Resources/doc/index.md

The generated list of api interfaces with description is accessible by

/app.php/api-docs

How to start
------------

Download dependencies - run command  from project root:

php composer update


Specify database params:

app/config/parameters.yml


Restore database structure:

sh Resources/bin/validate.sh

Prepare assets:

php app/console assets:install

php app/console cache:clear
php app/console assetic:dump


Add calling of cron script to process notifications:

php cron_task.php forecast:notify [location=name]

Demo:

http://forecast.devbion.com