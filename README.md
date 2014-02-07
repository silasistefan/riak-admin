riak-admin
==========

PHP Admin panel for RIAK (v0.3)

***************************
<b>Work in progress, DON'T use it in production!</b>
***************************

##### Available actions:
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

### Change Log 
***************************
**v0.3**
- (New): Added riak-data-migrator supported delete, enhanced visual feedback for streaming delete (carmensingeorzan)

**v0.2**
- (New): Enhanced listing and deleting keys via streaming, to avoid costly getKeys which can lock your Riak node (carmensingeorzan)

**v0.1**
- Initial release.