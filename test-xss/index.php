<?php

$docFile = __DIR__ . '/productDetail.html';
if ($_GET['productDetail']) {
    file_put_contents($docFile, $_GET['productDetail']);
    unset($_GET['productDetail']);
    if (!$requestUrl){
        $requestUrl = 'http://tool.pcy.tuniu.org/test-xss/index.php';
    }
    header('Location: '.$requestUrl.'?' . http_build_query($_GET));
    die;
} else {
    $_GET['productDetail'] = file_get_contents($docFile);
}

$originalDoc = $_GET['productDetail'];

$_GET[$_GET['operator']] = true;

if ($_GET['encode']) {
    $encode = function ($s) {
        return htmlspecialchars($s);
    };
} else {
    $encode = function ($s) {
        return $s;
    };
}

if ($_GET['security']) {
    require(__DIR__ . '/Security.php');
    $_GET['productDetail'] = Security::getXssSafeParam($_GET['productDetail']);
}

if ($_GET['purify']) {
    if (!$purifier){
        require __DIR__ . '/htmlpurifier/library/HTMLPurifier.auto.php';
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
    }
    $_GET['productDetail'] = $purifier->purify($_GET['productDetail']);
}

?>
<script type="application/javascript" src="http://tool.pcy.tuniu.org/static/js/jquery.js"></script>
<style>
    .box {
        float: left;

    }
    .output{
        min-width: 100em;
        border: 1px solid #000000;
    }
    #summary{
        background-color: #eadede;
        border-radius: 5px;
        border: 1px solid #102020;
        float: left;
    }
    #summary b{
        display: inline-block;
        width: 10em;
        text-align: left;
        margin-right: 2em;
    }
    #summary i{
        display: inline-block;
        font-style: normal;
        margin-right: 2em;
    }

</style>
<form method="get">

    <div class="">
        Operations:

        <label>
            <input type="radio" name="operator" value="none" <?php echo $_GET['none'] ? 'checked' : '' ?> />
            None
        </label>
        <label>
            <input type="radio" name="operator" value="encode" <?php echo $_GET['encode'] ? 'checked' : '' ?> />
            Encode
        </label>
        <label>
            <input type="radio" name="operator" value="security" <?php echo $_GET['security'] ? 'checked' : '' ?> />
            Security
        </label>
        <label>
            <input type="radio" name="operator" value="purify" <?php echo $_GET['purify'] ? 'checked' : '' ?> />
            Purify
        </label>
        <input type="submit"/>
        <a href="#summary">Summary</a>
    </div>
    <div class="box">
        <h3>Original Input:</h3>
        <textarea name="productDetail" cols="110" rows="10"><?php echo htmlspecialchars($originalDoc) ?></textarea>
    </div>
    <div class="box">
        <h3>After <?php echo $_GET['operator'] ?>:</h3>
        <textarea cols="110" rows="10"><?php echo $encode($_GET['productDetail']) ?></textarea>
    </div>

    <div class="box">
        <h3>Output:</h3>
        <div class="output">
            <?php echo $encode($_GET['productDetail']) ?>
        </div>
    </div>
</form>
<div id="summary">
    <ul>
    </ul>
</div>
<script type="application/javascript">
    function outputSupport(feature, isSupport){
        $('<li></li>')
            .append($('<b></b>').text(feature))
            .append($('<i></i>').text(isSupport ? 'Support' : 'NOT Support'))
            .css('color', isSupport ? 'green' : 'red')
            .appendTo('#summary ul');
    }

    String.prototype.contains = function(search){
        return this.indexOf(search) >= 0;
    };

    $(function(){
        var tags = 'h1 h2 h3 h4 h5 a img b i strong iframe script style embed'.split(' ');
        $.each(tags, function(i, tag){
            outputSupport('<'+tag+'>', $('.output').find(tag).size() > 0);
        });

        // 测试元素属性支持情况
        outputSupport('inline style', $('.output').find('[style]').size() > 0);
        outputSupport('Element class', $('.output').find('[class]').size() > 0);
        outputSupport('Element ID', $('.output').find('[id]').size() > 0);

        // 测试其他支持清空
        outputSupport('flash', $('.output').html().contains('application/x-shockwave-flash'));
    });
</script>