<div class="panel">
<style>
    #code, #result{
        min-width: 10em;
        width: 100%;
    }
    #code textarea{
        width: 100%;
    }
    #code input{
        width: 100%;
    }
    #result pre{
        border-color: darkgray;
        border-width: 1px;
        border-style: solid;
    }

</style>
<script type="text/javascript" >
    function eval_form_submit(){
        eval_form.submit();
    }
</script>
<div id="code">
<form target="_self" method="post" id="eval_form" action="#result" >
    <textarea id="code" name="code" rows="10"
              placeholder="Input what to eval..." onchange=""
        ><?php echo $code ?></textarea>
    <br/>
    <input type="submit" value="EVAL IT" />
</form>
</div>
<div id="result">
    <?php
    function my_eval($code){
        if (!strstr($code, 'return') && !strstr($code, "\n")){
            $code = 'return ' . $code;
        }
        $filename = "eval-code.php";
        file_put_contents($filename, '<?php '.$code);
        $result = require($filename);
        unlink($filename);
        return $result;
    }

    ?>
    <pre>
    <?php
    if ($code){
        $result = my_eval($code . ';' );
    }else{
        $result = my_eval('$GLOBALS;');
    }
    ?>
    </pre>
    <pre><code><?php                var_dump($result);        ?></code></pre>
</div>

</div>