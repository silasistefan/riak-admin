<?php

error_reporting(1);
/**
 * Riak Admin version
 */
define('VERSION', '0.4');

/**
 * your RIAK server
 */
define('HOST', '127.0.0.1');
/**
 * Specify Riak HTTP Port
 */
define('HTTP_PORT', 8098);
/**
 * Specify Riak Protocol Buffers Port. It is used by the riak-data-migrator command line tool if set.
 * Not required to run the plain web interface when you don't have the tool.
 */
define('BUFFERS_PORT', 8087);

/**
 * number of keys to display on a page
 */
define('DISPLAY_KEYS', 50);

/**
 * Riak-data-migrator command line tool has the ability to delete a whole bucket, export/import buckets
 * Grab from https://github.com/basho/riak-data-migrator
 * 
 * When this path is set, the system uses the command line tool to perform delete on a bucket (instead of the classic get all keys by stream and delete one by one)
 * 
 * @see Caveats from the github (must read) 
 * @link https://github.com/basho/riak-data-migrator#caveats
 * @depends "java command line tool"
 * 
 * @example "/usr/bin/java -jar /opt/riak-data-migrator-0.2.5.jar"
 */
define('RIAK_DATA_MIGRATOR', false);
/**
 * Set the path for riak-data-migrator working directory,
 * This is the directory where all loads or dumps will be written. Must have write permission.
 * This is also needed when running delete bucket operations.
 */
define('RIAK_DATA_MIGRATOR_WORKING_PATH', false);
