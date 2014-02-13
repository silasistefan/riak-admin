riak-admin
==========

Admin panel for [RIAK](http://basho.com/riak/) written in PHP (v0.4)

***************************
<b>Work in progress, DON'T use it in production!</b>
***************************

#### Available actions:
- create a bucket
- delete a bucket (with all keys in it, via streaming to avoid lock)
- view buckets keys (via streaming to avoid costly Riak::getKeys)
- view a key
- modify a key
- delete a key
- add a new key (JSON)

##### Supports [basho/riak-data-migrator](https://github.com/basho/riak-data-migrator) command line tool for:
- delete a bucket


#####  Further actions to be implemented (TODO):
- add a new key (binary)
- error reporting
- change bucket properties
- find a key in a bucket based on key=>value


### Installation
***************************

##### Use composer (*recommended*)

If you don't have Composer yet, download it following the instructions on http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

Then, use the `create-project` command to generate a new application:

    php composer.phar create-project pentium10/riak-admin -s dev path/to/install

Composer will install the riak-admin and all its dependencies under the `path/to/install` directory.

##### Setup using vagrant

Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads) and [vagrant](http://www.vagrantup.com/downloads.html) then run (from project root):

    vagrant up

After provision riak-admin will be available at [http://localhost:7654](http://localhost:7654) (port could be configured in Vagrantfile)

##### Download an Archive File

[Download](https://github.com/pentium10/riak-admin/archive/master.zip), unzip files to your *wwww* directory, edit config.php, and enjoy!


### Change Log
***************************
**v0.5**
- (New): Favorite based bucket listing, to avoid costly getBuckets of Riak, UI enhancements ([carmensingeorzan](https://github.com/carmensingeorzan))

**v0.4**
- (New): Composer and Vagrant support ([pentium10](https://github.com/pentium10))

**v0.3**
- (New): Added riak-data-migrator supported delete, enhanced visual feedback for streaming delete ([carmensingeorzan](https://github.com/carmensingeorzan))

**v0.2**
- (New): Enhanced listing and deleting keys via streaming, to avoid costly getKeys which can lock your Riak node ([carmensingeorzan](https://github.com/carmensingeorzan))

**v0.1**
- Initial release. ([silasistefan](https://github.com/silasistefan))