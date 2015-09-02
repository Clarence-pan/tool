<?php


class ViewController extends Controller
{
    public $layout = false;
    public function actionIndex(){
        if (Yii::app()->request->isPostRequest){
            $text = Yii::app()->request->getParam('data');
            $json = json_decode($text, 1);
            if (!$json){
                $json = eval("return {$text};");
                if (!$json){
                    $text = add_commas($text);
                    $json = eval("return {$text};");
                    if (!$json){
                        print_r(error_get_last());
                        echo $text;
                    }
                }
            }

            if (Yii::app()->request->getParam('toPhpArray')){
                header('Content-Type: text/php;');
                echo to_php_array($json);
            } else {
                header('Content-Type: text/json;');
                echo json_encode($json);
            }
        } else {
            $this->render('index');
        }
    }
}

// CVarDumper::dump() echos no comma. Let's add commas.
function add_commas($text){
    $lines = explode("\n", $text);
    foreach ($lines as &$line) {
        $line = rtrim($line);
        if (empty($line)){
            continue;
        }

        if (!preg_match("/array$|\\($|,$/", $line)){
            $line .= ',';
        }
    }

    return rtrim(implode("\n", $lines), ',');
}

function to_php_array($obj)
{
    return export((array)$obj);
}

function export($arr, $depth = 10, $indent=0, $tabSize=2, $newLine="\n"){
    $prefix = make_indent($indent, $tabSize);
    if (!is_array($arr)){
        if ($indent == 0){
            return var_export($arr, true);
        }

        $lines = array_map(function($line) use ($prefix){
            return $prefix . $line;
        }, explode($newLine, var_export($arr, true)));
        return implode($newLine, $lines);
    } else if (count($arr) == 0){
        return $prefix.'array()'.$newLine;
    } else if (array_keys($arr) === range(0, count($arr) - 1)){
        $s = $prefix.'array('.$newLine;
        foreach ($arr as $val) {
            $s .= export($val, $depth - 1, $indent + 1, $tabSize, $newLine) . ',' . $newLine;
        }
        $s.= $prefix.')';
        return $s;
    } else {
        $s = $prefix.'array('.$newLine;
        foreach ($arr as $key => $val) {
            $s .= export(strval($key), 1, $indent + 1, $tabSize, $newLine).
                ' => '. ltrim(export($val, $depth - 1, $indent + 1, $tabSize, $newLine)) . ',' . $newLine;
        }
        $s.= $prefix.')';
        return $s;
    }
}

function make_indent($indent, $tabSize=2){
    switch ($indent * $tabSize){
        case 0: return '';
        case 1: return ' ';
        case 2: return '  ';
        case 3: return '   ';
        case 4: return '    ';
        case 5: return '     ';
        case 6: return '      ';
        case 7: return '       ';
        case 8: return '        ';
        default:
            return str_pad('', $indent * $tabSize, ' ');
    }
}