#!/usr/bin/env php
<?php

/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-10-29
 * Time: 下午1:14
 */

function get_file_path(){
    return '/d/workspaces/nbooking-test/jsrc/buckbeek.js';
//    return $_REQUEST['originalFilePath'];
}
define('FRAGMENT_BEGIN', '// !BEGIN:');
define('FRAGMENT_END', '// !END:');
define('FRAGMENT_PLACE_HOLDER', '// !PLACE_HOLDER: ');
define('LINE_BREAK', "\r\n");
function pre_process_line($line){
    return str_replace("\n", "", str_replace("\r", "", $line));
}
class DiffViewer{
    public static function  view($baseFile, $newFile){
//        echo "\n<pre>";
        $cmd = "diff -a -s -rcs \"$baseFile\" \"$newFile\"";
        echo $cmd."\n";
        $diffResult = `$cmd`;
//        $diffResult = self::highlight($diffResult);
        echo $diffResult;
//        echo "\n</pre>";
    }

    public static function highlight($result){
        $lines = explode("\n", $result);
        foreach ($lines as &$line) {
            if (preg_match('/^(\\*\\*\\*)|(\\-\\-\\-)/', $line)){
                $line = self::bold($line);
            }else if (preg_match('/^\\+/', $line)){
                $line = self::color($line, 'green');
            }else if (preg_match('/^\\-/', $line)){
                $line = self::color($line, 'red');
            }else if(preg_match('/^!/', $line)){
                $line = self::color(self::background_color($line, 'red'), 'white');
            }
        }

        return implode("\n", $lines);
    }
    static  function bold($line){
        return "<span style='font-weight: bold'>$line</span>";
    }
    static  function color($line, $color){
        return "<span style='color: $color'>$line</span>";
    }
    static function background_color($line, $color){
        return "<span style='background-color: $color'>$line</span>";
    }
}
$actions = array(
    'view' =>
        function () {
            echo "".get_file_path()."".LINE_BREAK;
//            echo "<pre>";
//            echo htmlspecialchars(file_get_contents(get_file_path()));
//            echo "<pre>";
        },
    'break' =>
        function(){
            $dir = dirname(get_file_path());
            $filename = basename(get_file_path());
            $dir .= DIRECTORY_SEPARATOR.'fragment'.DIRECTORY_SEPARATOR.basename(get_file_path(), ".js");
            `md $dir`;

            $oldFile = file(get_file_path());
            $newFile = '';
            $fragments = array();
            $findName = function($line, $mark){
                if (strstr($line, $mark)){
                    $name = str_replace($mark, "", $line);
                    $name = trim($name);
                    if ($name){
                        return $name;
                    }
                }
                return false;
            };
            for ($i = 0, $n = count($oldFile); $i < $n; $i++){
                $line = pre_process_line($oldFile[$i]);

                $name = $findName($line, FRAGMENT_BEGIN);
                if ($name){
                    echo "Info: find match begin of ".$name.LINE_BREAK;
                    $j = $i;
                    $fragmentContent = '';
                    for ($j++; $j < $n; $j++){
                        $line = pre_process_line($oldFile[$j]);
//                        if (strstr($line, $name)){
//                            echo $line."<br/>";
//                        }
                        $matchEnd = $findName($line, FRAGMENT_END);
                        if ($matchEnd && $matchEnd == $name){
                            break;
                        }else{
                            $matchEnd = false;
                            $fragmentContent .= $line . LINE_BREAK;
                        }
                    }
                    if ($matchEnd){
                        $fragments[$name] = $fragmentContent;
                        $i = $j;
                        $newFile .= FRAGMENT_PLACE_HOLDER.$name.LINE_BREAK;
                        echo "Info: find match end of ".$name.LINE_BREAK;
                        continue;
                    }else{
                        echo "Error: cannot find match end of ".$name.LINE_BREAK;
                    }

                }
                $newFile .= $line . LINE_BREAK;
            }

            foreach ($fragments as $name => $content) {
                file_put_contents($dir.DIRECTORY_SEPARATOR.$name.'.js', $content);
            }

            file_put_contents($dir.DIRECTORY_SEPARATOR.$filename, $newFile);

//            run_action('view');
        },
    'join' =>
        function(){
            $dir = dirname(get_file_path());
            $filename = basename(get_file_path());
            $dir .= DIRECTORY_SEPARATOR.'fragment'.DIRECTORY_SEPARATOR.basename(get_file_path(), ".js");
            $hdir = dir($dir);
            $fragments = array();
            while($file = $hdir->read()){
                $content = file_get_contents($dir.DIRECTORY_SEPARATOR.$file);
                if ($content){
                    $fragments[$file] = $content;
                }
            }
            
            $newFile = $fragments[$filename];
            echo "before join file length: ".strlen($newFile).LINE_BREAK;
            foreach ($fragments as $key => $value) {
                if ($key == $filename){
                    continue;
                }
//                echo FRAGMENT_PLACE_HOLDER.$key.LINE_BREAK.'<br/>'.
//                    "<pre>".
//                    FRAGMENT_BEGIN.$key.LINE_BREAK.
//                    $value.LINE_BREAK.
//                    FRAGMENT_END.$key.LINE_BREAK.'</pre>';
                $key = basename($key, '.js');

                $newFile = str_replace(FRAGMENT_PLACE_HOLDER.$key.LINE_BREAK,
                                    FRAGMENT_BEGIN.$key.LINE_BREAK.
                                    $value.
                                    FRAGMENT_END.$key.LINE_BREAK,
                                    $newFile);
            }
            echo "after join file length: ".strlen($newFile).LINE_BREAK;

            $oldFile = file_get_contents(get_file_path());
            file_put_contents(get_file_path().'.bak', $oldFile);
            file_put_contents(get_file_path(), $newFile);

            DiffViewer::view(get_file_path().'.bak', get_file_path());

//            run_action('view');
        },
    'default' =>
        function () {
            run_action('view');
        }
);

function run_action($action){
    global $actions;
    if ($actions[$action]){
        return $actions[$action]();
    } else {
        return $actions['default']();
    }
}


?>
<?php echo run_action($argv[1]); ?>
