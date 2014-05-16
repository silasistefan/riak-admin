<?php

class RiakParser extends JsonStreamingParser_Parser {

    public function __construct($stream, $listener, $line_ending = "\n") {
        parent::__construct($stream, $listener, $line_ending);
    }

    public function parse() {

        $this->_line_number = 1;
        $this->_char_number = 1;

        $line = "[";
        while (!feof($this->_stream) && !$this->_listener->cancelledStream()) {
            $pos = ftell($this->_stream);
            //$line .= stream_get_line($this->_stream, $this->_buffer_size, $this->_line_ending);
            $line .= fgets($this->_stream, $this->_buffer_size);

            $line = str_replace('}{', "},{", $line);
            $ended = (bool) (ftell($this->_stream) - strlen($line) - $pos);
            if (feof($this->_stream)) {
                $line .= "]";
            }
//            echo $line . "<hr>";
            $byteLen = strlen($line);
            for ($i = 0; $i < $byteLen; $i++) {
                $this->_listener->file_position($this->_line_number, $this->_char_number);
                $this->_consume_char($line[$i]);
                $this->_char_number++;
            }

            if ($ended) {
                $this->_line_number++;
                $this->_char_number = 1;
            }
            $line = '';
        }
    }

}
