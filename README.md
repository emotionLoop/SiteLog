# SiteLog

This is SiteLog's source code. A product shut down by emotionLoop in August 2013.

SiteLog was a simple and easy server and website monitoring tool. You can get more information about why it was shutdown at http://emotionloop.com/blog/sitelog-shutting-down-august-1st

There are 3 crons for SiteLog:

1. cron/services.php (every minute) is the script that will "ask" if the services are up or down
2. cron/accounts.php (daily) is meant to look for accounts nearing limits, etc., and send emails about it.
3. cron/clean.php (twice a day) is meant to do some cleanup after accounts have aged, etc.

You just need to setup cron/config.inc.php, cron/helper.inc.php (just the Mandrill API Key), framework/config.inc.php, and framework/app/fw.class.php (just the Mandrill API Key).

You have the dbschema.sql.gz and dbseed.sql so that you can have the DB structure and some starting data.

If you need any other license than GNU GPL v3, contact us through http://emotionloop.com.