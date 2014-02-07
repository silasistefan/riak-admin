riak-admin
==========

PHP Admin panel for RIAK (v0.2)

***************************
<b>Work in progress, DON'T use it in production!</b>
***************************

Available actions:
- create a bucket
- delete a bucket (with all keys in it, via streaming to avoid lock)
- view buckets keys (via streaming to avoid costly Riak::getKeys)
- view a key
- modify a key
- delete a key
- add a new key (JSON)

Further actions to be implemented:
- add a new key (binary)
- error reporting
- change bucket properties
- find a key in a bucket based on key=>value

Change Log 
=============================

**v0.2**

- (New): Enhanced listing and deleting keys via streaming, to avoid costly getKeys which can lock your Riak node (carmensingeorzan)

**v0.1**

- Initial release.