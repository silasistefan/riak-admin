<?php

require_once dirname(__FILE__) . '/jsonstreamingparser/Listener.php';
require_once dirname(__FILE__) . '/jsonstreamingparser/Parser.php';

/**
 * This basic implementation of a listener simply constructs an in-memory
 * representation of the JSON document, which is a little silly since the whole
 * point of a streaming parser is to avoid doing just that. However, it gets
 * the point across.
 */
class ArrayMaker implements JsonStreamingParser_Listener {

    protected $_json;
    protected $_stack;
    protected $_key;
    /**/
    private $_limit;
    private $_stream_cancelled = false;
    /**/
    private $_result = array();

    function __construct() {
        
    }

    public function file_position($line, $char) {
        
    }

    public function get_json() {
        return $this->_json;
    }

    public function get_result() {
        return $this->_result;
    }

    public function start_document() {
        $this->_stack = array();

        $this->_key = null;
    }

    public function end_document() {
        // w00t!
    }

    public function start_object() {
        array_push($this->_stack, array());
    }

    public function end_object() {
        $obj = array_pop($this->_stack);
        if (empty($this->_stack) && empty($this->_json)) {
            // doc is DONE!
            $this->_json = $obj;
        } else {
            $this->value($obj);
        }
    }

    public function start_array() {
        $this->start_object();
        $this->_key = null;
    }

    public function end_array() {
        if (is_array($this->_stack)) {
            foreach ($this->_stack as $key => $value) {
                if (is_array($value) && !empty($value)) {
                    $this->_result = array_merge($this->_result, $value);
                    if (count($this->_result) >= $this->_limit) {
                        // we got enough items we need
                        $this->_result = array_slice($this->_result, 0, $this->_limit);
                        // we need to stop listening on stream
                        $this->_stream_cancelled = true;
                    }
                }
                $this->_stack[$key] = null;
                unset($this->_stack[$key]);
                $key = null;
            }
        }
        $this->end_object();
    }

    // Key will always be a string
    public function key($key) {
        $this->_key = $key;
    }

    // Note that value may be a string, integer, boolean, null
    public function value($value) {
        $obj = array_pop($this->_stack);
        if ($this->_key) {
            $obj[$this->_key] = $value;
            $this->_key = null;
        } else {
            if (is_array($obj)) {
                array_push($obj, $value);
            }
        }

        array_push($this->_stack, $obj);
    }

    public function cancelledStream() {
        return $this->_stream_cancelled;
    }

    public function setLimit($display_keys = 50) {
        $this->_limit = $display_keys;
    }

}
