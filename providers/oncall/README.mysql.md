# Using mysql for oncall reporting.
------------------------------------

## Importing into Mysql

Represenative Mysql Schema

```
  CREATE TABLE `notifications` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `hostname` varchar(32) DEFAULT NULL,
    `title` varchar(32) DEFAULT NULL,
    `state` varchar(16) DEFAULT NULL,
    `team` varchar(16) DEFAULT NULL,
    `description` text,
    `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB;
```

- This data is parsed from nagios.log and pushed into MYSQL in close to real time.

- The data can either trigger off of Nagios Alert Events or Notification Events.

- Additionally as long as you are able to shim into the Schema many other monitoring solutions should also work.


## Opsweekly configuration

Sample Mysql Global Config in the config.php file

```
 $oncall_providers = array(
  "mysql" => array (
   "display_name" => "Mysql",
   "lib" => "providers/oncall/mysql.php",
   "options" => array(
    'host' => '<hostname>',
    'port' => <port>,
    'user' => '<user>',
    'pass' => '<password>',
   )
  ),
 );
```

Sample Mysql Team config


```
  "oncall" => array(
              "provider" => "mysql",
              "provider_options" => array(
                  'dbname' => '<name_of_db>',
                  "use_teams" => true, //true | false
              ),
              "timezone" => "Europe/London",
              "start" => "monday 08:00",
              "end" => "monday 08:00",
          ),
```

- With these set up, you should be able to pull alerts or notifications from your DB to abstract your monitoring solution.