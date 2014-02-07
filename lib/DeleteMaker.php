<?php

require_once dirname(__FILE__) . '/jsonstreamingparser/Listener.php';
require_once dirname(__FILE__) . '/jsonstreamingparser/Parser.php';
require_once dirname(__FILE__) . '/ArrayMaker.php';

class DeleteMaker extends ArrayMaker {

    protected $_count = 0;
    public $_riak;
    public $_bucket;

    public function __construct($riak = null, $bucket = null) {
        parent::__construct();
        $this->_riak = $riak;
        $this->_bucket = $bucket;
        $this->_bucket->client->prefix = 'riak';
    }

    public function end_array() {
        if (is_array($this->_stack)) {
            require_once dirname(__FILE__) . '/riak-client.php';
            foreach ($this->_stack as $key => $value) {
                $this->deleteAll($value);
                $this->_json = $value;
                $this->_stack[$key] = null;
                unset($this->_stack[$key]);
                $key = null;
            }
        }
        $this->end_object();
    }

    public function deleteAll($values) {
        if (is_array($values)) {
            foreach ($values as $val) {
                if (!empty($val)) {
                    set_time_limit(30);
                    $riak_key = new RiakObject($this->_riak, $this->_bucket, $val);
                    if ($riak_key) {
                        $riak_key->delete();
                    }
                }
            }
            $n = count($values);
            $this->_count += $n;
            if ($n && $this->_count) {
                echo sprintf("%d... ", $this->_count);
            }
        }
    }

}
