<?php
require_once 'config.php';

/*
 * ******************************************************************************
 * **                     NO CHANGES FROM HERE ON                             ***
  /*******************************************************************************
 */

$start_page = microtime(true);
require_once ("lib/riak-client.php");

// init the RIAK connection
$riak = new RiakClient(HOST, HTTP_PORT);
if (!$riak->isAlive()) {
    die("I couldn't ping the server. Check the HOST AND PORT settings...");
}

// init the $bucket && $key
if (isset($_GET['bucketName'])) {
    $bucket = new RiakBucket($riak, $_GET['bucketName']);
    $key = new RiakObject($riak, $bucket, $_GET['key']);
}

// delete a key
if (($_GET['cmd'] == "deleteKey") && ($_GET['bucketName']) && ($_GET['key'])) {
    $key->delete();
    unset($_GET['key']);
}

// create a bucket with key=>value : "created"=>1
if (($_GET['cmd'] == 'createBucket')) {
    if ($_POST['bucketName'] && ($_POST['bucketName'] != 'Create a new bucket')) {
        $data = array("created" => 1);
        $bucket = new RiakBucket($riak, $_POST['bucketName']);
        $x = $bucket->newObject("", $data);
        $x->store();

        $buckets_json = $_COOKIE['buckets'];

        if (!empty($buckets_json)) {
            $buckets_array = json_decode($buckets_json, true);

            if (is_array($buckets_array)) {
                $buckets_array[] = $_POST['bucketName'];
            }
            $buckets = json_encode($buckets_array);
            setcookie('buckets', $buckets);

            $page = $_SERVER['PHP_SELF'];
            header("Refresh: 0; url=$page/../");
        }
    } else {
        ?><div class="msg">Bucket Name can't be empty.</div><?php
    }
}

// delete a bucket and all keys from it
if (($_GET['cmd'] == 'delBucket') && ($_GET['bucketName'])) {
    if (RIAK_DATA_MIGRATOR) {
        if (RIAK_DATA_MIGRATOR_WORKING_PATH) {
            $folder = RIAK_DATA_MIGRATOR_WORKING_PATH;
        } else {
            $folder = sys_get_temp_dir();
        }

        $command = RIAK_DATA_MIGRATOR . " -h " . escapeshellarg(HOST) . " -p " . intval(BUFFERS_PORT) . " -r " . escapeshellarg($folder) . " -b " . escapeshellarg($_GET['bucketName']) . " --delete 2>&1";
        disable_ob();
    } else {
        $stream = $bucket->getContentStream();
        disable_ob();
    }

    $buckets_json = $_COOKIE['buckets'];

    if (!empty($buckets_json)) {
        $buckets_array = json_decode($buckets_json, true);

        if (is_array($buckets_array)) {
            $buckets_array = array_flip($buckets_array);
            unset($buckets_array[$_GET['bucketName']]);

            $buckets_array = array_flip($buckets_array);
            $buckets = json_encode($buckets_array);

            setcookie('buckets', $buckets);
        }
    }
}
if (($_GET['cmd'] == 'manageBuckets')) {
    disable_ob();
}

// add a new KEY in RIAK
if (($_GET['cmd'] == 'saveKey')) {
    $arrVal = $_GET['value'];
    $arrKey = $_GET['key'];
    $key_name = $_GET['key_name'];
    $key = new RiakObject($riak, $bucket, $_GET['key_name']);

    foreach ($arrKey AS $index => $keyTmp) {
        if ($arrVal[$index]) {
            $value = $arrVal[$index];
            $data[$keyTmp] = $value;
        }
    }

    $obj = $bucket->newObject($key_name, $data);
    $obj->store();

    $_GET['key'] = $key_name;
    $_GET['bucket_name'] = $bucket->getName();
    $_GET['cmd'] = 'useBucket';

    echo '<div class="msg">Key added.</div>';
}

// update the KEY with new $data
if (($_GET['cmd'] == 'updateKey') && (isset($_POST['value']))) {
    $arrVal = $_POST['value'];

    if (is_array($arrVal)) {
        foreach ($arrVal as $data) {
            if (!empty($data)) {
                $data = json_decode($data);
                $obj = $bucket->newObject($_GET['key'], $data);
                $obj->store();
                echo '<div class="msg">Value updated in RIAK.</div>';
            } else {
                echo '<div class="msg">You can\'t save an empty value</div>';
            }
        }
    }
}

//remove bucket from cookie
if ($_GET['cmd'] == 'removeBucketFromCookie') {

    $buckets_json = $_COOKIE['buckets'];

    if (!empty($buckets_json)) {
        $buckets_array = json_decode($buckets_json, true);

        if (is_array($buckets_array)) {
            $buckets_array = array_flip($buckets_array);
            unset($buckets_array[$_GET['bucketName']]);

            $buckets_array = array_flip($buckets_array);
            $buckets = json_encode($buckets_array);

            setcookie('buckets', $buckets);

            $page = $_SERVER['PHP_SELF'];
            header("Refresh: 0; url=$page/../");
        }
    }
}

function disable_ob() {
// Turn off output buffering
    ini_set('output_buffering', 'off');
// Turn off PHP output compression
    ini_set('zlib.output_compression', false);
// Implicitly flush the buffer(s)
    ini_set('implicit_flush', true);
    ob_implicit_flush(true);
// Clear, and turn off output buffering
    while (ob_get_level() > 0) {
// Get the curent level
        $level = ob_get_level();
// End the buffering
        ob_end_clean();
// If the current level has not changed, abort
        if (ob_get_level() == $level)
            break;
    }
// Disable apache output buffering/compression
    if (function_exists('apache_setenv')) {
        apache_setenv('no-gzip', '1');
        apache_setenv('dont-vary', '1');
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>RiakAdmin v<?php echo VERSION . ' @ ' . HOST; ?></title>
        <style type="text/css">
            body { background-color: #fff; color: #666; font-family: sans-serif, Arial; font-size: 12px; margin: 0px; margin-top: 10px;}
            a {color: #002aFF;}
            a:hover { text-decoration: underline;color: #333 !important;}
            h3 {text-decoration: underline;}
            .page {width: 1200px; margin-left: auto; margin-right: auto; text-align: center;}
            .left { background-color: #f8f8f8; width: 300px; padding: 7px; display: table-cell; text-align: left; border: 1px solid #666; border-right: 0px; vertical-align: top;}
            .right { background-color: #fff; width: 900px; color: #666; display: table-cell; border: 1px solid #666; text-align: left; margin: 5px; vertical-align: top;}
            .bucketName {font-weight: none;}
            .bucketNameSelected {font-weight: bold;}
            .bucketActions { font-weight: bold; font-size: 10px; text-decoration: none; margin-left: 10px;}
            .content {margin: 10px;}
            .td_left { background-color: #f8f8f8; border: 1px dashed; border-right: 0px; display: table-cell; width:250px; padding: 7px; vertical-align: middle;}
            .td_right { border: 1px dashed; border-left: 0px; display: table-cell; width: 600px; padding: 7px; vertical-align: middle;}
            .msg { border: 1px dashed; text-align: center; margin-left: auto; margin-right: auto; margin: 10px; font-weight: bold; background-color: #f0f0f0; padding: 7px;}
            .msgSmall { font-size: 12px; margin-left: auto; margin-right: auto; text-align: justify; padding: 5px; }
            .clear { clear:both }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="left"><?php echo left_menu(); ?></div>
            <?php
            if (!empty($command)) {
                ?>
                <div class="right">
                    <pre style="margin-left: 10px;"><?php
                        passthru($command, $output);
                        ?></pre>
                </div>
                <?php
            } elseif ($stream) {
                require_once dirname(__FILE__) . '/lib/DeleteMaker.php';
                require_once dirname(__FILE__) . '/lib/jsonstreamingparser/RiakParser.php';
                ?>
                <div class="right">
                    <p style="margin-left: 10px;">
                        <span style="font-size: 16px;font-weight: bold;">Deleting keys using streaming
                            <br/>
                        </span>
                        <br/>
                        <b>Deleted keys:</b>
                        <?php
                        $listener = new DeleteMaker($riak, $bucket);
                        try {
                            $parser = new RiakParser($stream, $listener);
                            $parser->parse();
                        } catch (Exception $e) {
                            fclose($stream);
                            throw $e;
                        }
                        fclose($stream);
                        ?>
                        <br/>
                        <br/>
                        <b>Delete completed!</b> 
                        <br/>
                        <small style="color: gray">* The bucket might be present while delete is syncronized with all nodes. In a few seconds all should be deleted.</small>
                    </p>
                </div>
                <?php
            } else {
                ?>
                <div class="right"><?php echo right_content(); ?></div>
                <?php
            }
            ?>
        </div>
        <?php
        $page_generation = microtime(true) - $start_page;
        echo '<div class="msg">It took me ' . number_format($page_generation, 3) . ' seconds to generate this page...</div>';
        ?>
    </body>
</html>

<?php

// left menu: create new bucket + show list of current ones
function left_menu() {
    global $riak, $_GET;
    // screate a new bucket
    $ret = '
    <center><h3>RiakAdmin v' . VERSION . ' @ ' . HOST . '</h3>
    <form name="createBucket" method="POST" action="?cmd=createBucket">
        <input type="text" name="bucketName" value="Create a new bucket" onClick="this.value=\'\';">
        <input type="submit" name="ok" value="Create">
    </form></center>
    <div class="msgSmall">When creating a new bucket, a key named "created" with value "1" will be set in that bucket.</div>
    <hr>';

    $buckets = $_COOKIE['buckets'];
    $buckets = json_decode($buckets, true);

    if (count($buckets) == 0) {
        if (empty($_GET['cmd'])) {
            header("Location: ?cmd=manageBuckets");
        }
        $ret .= '<b>No favorite buckets found for listing. <a href="?cmd=manageBuckets">Choose one?</a></b>';
    } else {
        $ret .= '<div>
                    <div style="float:left">Favorite buckets:</div>
                    <div style="float:right"><a href="?cmd=manageBuckets">Manage</a></div>
                   </div>
            <div class="clear"></div>
            <ul type="square">';

        foreach ($buckets as $bucket) {
            if (($_GET['cmd'] == 'delBucket') && ($_GET['bucketName'] == $bucket)) {
                continue;
            }
            if ($bucket == $_GET['bucketName']) {
                $ret .= '<li class="bucketNameSelected">'
                        . '<a href="?cmd=useBucket&bucketName=' . $bucket . '">' . $bucket . '</a><br/>'
                        . '<a href="?cmd=addKey&bucketName=' . $_GET['bucketName'] . '" class="bucketActions">[ Add a new key ]</a><br/>'
                        . '<a href="?cmd=findKey&bucketName=' . $_GET['bucketName'] . '" class="bucketActions">[ Find a key ]</a><br/>'
                        . '<a href="?cmd=delBucket&bucketName=' . $bucket . '" class="bucketActions" onclick="return confirm(\'Are you sure you want to delete?\');">[ Delete bucket ]</a><br/>'
                        . '<a href="?cmd=removeBucketFromCookie&bucketName=' . $bucket . '" class="bucketActions">[ Remove from favorites]</a><br/>'
                        . '</li><br/>';
            } else {
                $ret .= '<li class="bucketNameSelected">'
                        . '<a href="?cmd=useBucket&bucketName=' . $bucket . '">'
                        . $bucket . '</a></li><br/>';
            }
        }
        $ret .= '
            </ul>';
    }

    return $ret;
}

// right menu: list all keys from a bucket + create/delete/modify
function right_content() {
    global $riak, $bucket, $key, $_GET, $_POST;

    $ret = '';
    // if i have a bucket selected, but no KEY, I'll display all keys from it

    if ((isset($bucket) && (!isset($_GET['key'])))) {
//        $keys = $bucket->getKeys();
        $stream = $bucket->getContentStream();
        if ($stream) {
            require_once dirname(__FILE__) . '/lib/ArrayMaker.php';
            require_once dirname(__FILE__) . '/lib/jsonstreamingparser/RiakParser.php';

            $listener = new ArrayMaker();
            $listener->setLimit(DISPLAY_KEYS);
            try {
                $parser = new RiakParser($stream, $listener);
                $parser->parse();
            } catch (Exception $e) {
                fclose($stream);
                throw $e;
            }
            fclose($stream);
        } else {
            echo "No stream";
            exit;
        }
//        exit;
        $arr_keys = $listener->get_result();
//        print_R($arr_keys);
        $keys = array_map("urldecode", $arr_keys);

        // pagination ???

        $ret .= '
        <div class="content">
            <h3>Selected BUCKET: "' . $_GET['bucketName'] . '"</h3>';

        // add a new key in this bucket
        if ($_GET['cmd'] == 'addKey') {
            $ret .= '
            <form method="GET" name="addKey" action="?">
            <input type="hidden" name="cmd" value="saveKey">
            <input type="hidden" name="bucketName" value="' . $_GET['bucketName'] . '">
            <div class=content>
                <div class="td_left">Key name:<div class="msgSmall">Leave empty for random value.</div></div>
                <div class="td_right"><textarea name="key_name" rows=3 cols="30"></textarea></div>
            </div>
            <div id="fieldList">
                <div class="content">
                    <div class=td_left><input type=text name=key[]></div>
                    <div class=td_right><textarea name=value[] rows=3 cols=30></textarea></div>
                </div>
            </div>
            <div style="text-align:center">
                <input type="submit" name="ok" value="Save">
                <a href="#" onClick="document.getElementById(\'fieldList\').innerHTML=document.getElementById(\'fieldList\').innerHTML + \'<div class=content><div class=td_left><input type=text name=key[]></div><div class=td_right><textarea name=value[] rows=3 cols=30></textarea></div></div>\'">Add another key => value!</a>
            </div>
            </form>';

            return $ret;
        }
        // find a key in this bucket
        elseif ($_GET['cmd'] == 'findKey') {
            $ret .= '
            <form method="GET" name="searchKey" action="?">
            <input type="hidden" name="cmd" value="searchKey">
            <input type="hidden" name="bucketName" value="' . $_GET['bucketName'] . '">
            <div class="content">
                <div class="td_left">Search for:</div>
                <div class="td_right"><input type="text" name="q" value="">
                <input type="submit" name="ok" value="Search"></div>
            </div>
            </form>';

            return $ret;
        } elseif ($_GET['cmd'] == 'searchKey') {
            // display the search results
        }

        $ret .= '
            <div class="td_left" align="center"><b>KEY NAME</b></div>
            <div class="td_right" align="center"><b>ACTIONS</b></div>
        </div>';
        $total = 0;
        $count = count($keys);
        for ($i = 0; $i < min($count, DISPLAY_KEYS); $i++) {
            $total++;
            $ret .= '
            <div class="content">
                <div class="td_left"><b>' . $keys[$i] . '</b></div>
                <div class="td_right">
                    <a href="?cmd=useBucket&bucketName=' . $_GET['bucketName'] . '&key=' . $keys[$i] . '">View/Modify</a> | 
                    <a href="?cmd=deleteKey&bucketName=' . $_GET['bucketName'] . '&key=' . $keys[$i] . '">Delete</a>
                </div>
            </div>';
        }
        if ($total == 0) {
            $ret = '
            <div class="msg">No keys found in this bucket.</div>';
        }
        $ret .= '</table>';
    }
    // else if I have a bucket selected and a KEY, I'll display the key properties
    elseif ((isset($bucket)) && (isset($_GET['key']))) {
        $ret .= '
        <form name="updateKey" method="POST" action="?cmd=updateKey&bucketName=' . $_GET['bucketName'] . '&key=' . $_GET['key'] . '">
        <div class="content">
            <h3>Selected KEY: "' . $_GET['key'] . '"</h3>
        </div>';
        $arr_key = $key->reload()->getData();
        if (!empty($arr_key)) {
            $key = json_readable_encode($arr_key);
            $ret .= '<div class="content">'
                    . '<textarea id="documentdata" name="value[]" style="width:778px; height:375px;">' . $key . "</textarea>"
                    . "<script>document.getElementById('documentdata').value=document.getElementById('documentdata').value.replace(/\*\/[^\/]+$/, '').replace(/\t/g, ' ');</script>"
                    . "</div>"
                    . '<input type="submit" name="ok" value="Save">';
        } else {
            $ret = '<div class="msg">For some reasons, this key could not be read.</div>';
        }

        $ret .= '
            <div id = "fieldList"></div>
       </form>';
    } elseif ($_GET['cmd'] == 'setCookies') {
        $ret .= '<div="content">'
                . '<div style="float:left"><img src="' . $_SERVER['SCRIPT_NAME'] . '/../images/iLoading.gif" style="width:90px;"/></div>'
                . '<div style="float:left; margin-top:30px;">Loading...</div>'
                . '</div>';

        if (isset($_POST)) {
            $arr = $_POST;
            $buckets = array();

            if (is_array($arr)) {
                foreach ($arr as $key => $value) {
                    $buckets[] = $key;
                }
            }
            $buckets = json_encode($buckets);
            setcookie('buckets', $buckets);
        }

        $page = $_SERVER['PHP_SELF'];
        header("Refresh: 0; url=$page/../");
    } elseif ($_GET['cmd'] == 'manageBuckets') {
        ?>
        <form name="manageBuckets" action="?cmd=setCookies" method="POST">
            <div class="content">
                <div id="beforeLoad">Listing all known buckets (ones that have keys stored in them) from Riak...<br/>
                    Please wait while this is fetched from all your nodes... <a href="http://docs.basho.com/riak/1.3.0/references/apis/http/HTTP-List-Buckets/" target="_blank">(i)</a>
                </div>
                <div class="clear"></div>
                <div id="afterLoad" style='display:none'><h3>Manage Favorite Buckets:</h3>(select the buckets you want to be displayed on left, as we cannot read each time from Riak, <a href="http://docs.basho.com/riak/1.3.0/references/apis/http/HTTP-List-Buckets/" target="_blank">find out why</a>.)<br/><br/></div>

                <?php
                $buckets = $riak->buckets();
                $b = json_decode($_COOKIE['buckets'], true);

                if (is_array($buckets)) {
                    foreach ($buckets as $bucket) {
                        if (is_array($b) && in_array($bucket->name, $b)) {
                            $checked = true;
                        } else {
                            $checked = false;
                        }

                        $ret .= '<input type="checkbox" id="' . $bucket->name . '"' . ($checked ? 'checked="checked"' : '') . ' name="' . $bucket->name . '"> <label for="' . $bucket->name . '">' . $bucket->name . '</label><br/><br/>';
                    }
                }
                $ret .= '<input type="submit" value="Save favorites"></form><br/><br/>';
                $ret .= '* favorites are kept in cookies</div>';
                ?>
                <script>
                    document.getElementById('afterLoad').style.display = document.getElementById('beforeLoad').style.display;
                    document.getElementById('beforeLoad').style.display = 'none';
                </script>
                <?php
            } else {
                $ret = '
            <div class = "msg">Chose a bucket from the left panel, or create a new one...</div>';
            }
            $ret .= '</div>';
            return $ret;
        }

        function json_readable_encode($in, $indent = 0, Closure $_escape = null) {
            if (__CLASS__ && isset($this)) {
                $_myself = array($this, __FUNCTION__);
            } elseif (__CLASS__) {
                $_myself = array('self', __FUNCTION__);
            } else {
                $_myself = __FUNCTION__;
            }

            if (is_null($_escape)) {
                $_escape = function ($str) {
                    return str_replace(
                            array('\\', '"', "\n", "\r", "\b", "\f", "\t", '/', '\\\\u'), array('\\\\', '\\"', "\\n", "\\r", "\\b", "\\f", "\\t", '\\/', '\\u'), $str);
                };
            }

            $out = '';

            foreach ($in as $key => $value) {
                $out .= str_repeat("\t", $indent + 1);
                $out .= "\"" . $_escape((string) $key) . "\": ";

                if (is_object($value) || is_array($value)) {
                    $out .= "\n";
                    $out .= call_user_func($_myself, $value, $indent + 1, $_escape);
                } elseif (is_bool($value)) {
                    $out .= $value ? 'true' : 'false';
                } elseif (is_null($value)) {
                    $out .= 'null';
                } elseif (is_string($value)) {
                    $out .= "\"" . $_escape($value) . "\"";
                } else {
                    $out .= $value;
                }

                $out .= ",\n";
            }

            if (!empty($out)) {
                $out = substr($out, 0, -2);
            }

            $out = str_repeat("\t", $indent) . "{\n" . $out;
            $out .= "\n" . str_repeat("\t", $indent) . "}";

            return $out;
        }
        