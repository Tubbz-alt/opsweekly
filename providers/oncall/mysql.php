<?php

/**
 * Copyright 2014 Shazam Entertainment Limited
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use
 * this file except in compliance with the License.
 * You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific language governing permissions
 * and limitations under the License.
 */

/**
 * Mysql on call provider
 * @author Brad Bonkoski <brad.bonkoski@gmail.com>
 */

/** Plugin specific variables required
 *
 *  Check the README.mysql.md in this directory
 *
 */

/**
 * getOnCallNotifications - Returns the notifications for a given time period and parameters
 *
 * Parameters:
 *   $on_call_name - The username of the user compiling this report
 *   $provider_global_config - All options from config.php in $oncall_providers - That is, global options.
 *   $provider_team_config - All options from config.php in $teams - That is, specific team configuration options
 *   $start - The unix timestamp of when to start looking for notifications
 *   $end - The unix timestamp of when to stop looking for notifications
 *
 * Returns 0 or more notifications as array()
 * - Each notification should have the following keys:
 *    - time: Unix timestamp of when the alert was sent to the user
 *    - hostname: Ideally contains the hostname of the problem. Must be populated but feel free to make bogus if not applicable.
 *    - service: Contains the service name or a description of the problem. Must be populated. Perhaps use "Host Check" for host alerts.
 *    - output: The plugin output, e.g. from Nagios, describing the issue so the user can reference easily/remember issue
 *    - state: The level of the problem. One of: CRITICAL, WARNING, UNKNOWN, DOWN
 */
function getOnCallNotifications($on_call_name, $provider_global_config, $provider_team_config, $start, $end) {

    global $gblTeams;

    /*
     * If you are using Teams.  The concept here is that oncall not necessarily be the responsibility
     * of 1 single person, but a team.  While all members of the team might not get a page for an alert
     * each member of the team should be aware of all the components of the system and also be fully
     * aware of any critical problems that happens to their system so they can easily fill out the weekly
     * oncall report even if they were not the individual oncall every night of that week.
     *
     * This is <optional> and if set to false in config.php it will go by individual and not team.
     */
    if ($provider_team_config['use_teams']) {
        $profile = checkForUserProfile($on_call_name);
        if (!in_array($profile['team'], $gblTeams)) {
            throw new Exception("Team Not Defined");
        }
        $onCallName = $profile['team'];
    }

    $dsn = "mysql:host={$provider_global_config['host']};port={$provider_global_config['port']};dbname={$provider_team_config['dbname']}";
    $db = new PDO($dsn, $provider_global_config['user'],$provider_global_config['pass']);

    $sql = "select *, UNIX_TIMESTAMP(added) as start
      from notifications where team = :team and contact_name = 'SET'
      and state != 'OK' and unix_timestamp(added) between :start and :end";

    logline("SQL: $sql");
    logline("OnCall: $onCallName -- state: $start -- end: $end");
    $stmt = $db->prepare($sql);
    $stmt->execute(
        array(
            ':team' => $onCallName,
            ':start' => $start,
            ':end' => $end
        )
    );

    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $notifications = array();
    foreach($res as $r) {
        $row = array(
            'time' => $r['start'],
            'hostname' => $r['hostname'],
            'service' => $r['title'],
            'output' => $r['description'],
            'state' => $r['state']
        );
        $notifications[] = $row;
    }
    return $notifications;
}
