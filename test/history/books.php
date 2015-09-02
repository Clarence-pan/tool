<?php
function render_layout($content){
    ?>
<!doctype html>
<html>
<head>
    <title>Books</title>
</head>
<body>
    <div id="content">
        <?PHP echo $content ?>
    </div>
    <script src="/static/js/jquery.js"></script>
    <script>
        $(function(){
            $('a').on('click', handleLinkClick);
            function handleLinkClick(e){
                var $link = $(this);
                var url = $link.attr('href');
                $.get(url, function(response){
                    $("#content").html(response)
                                 .find('a').on('click', handleLinkClick);
                    history.pushState(response, url, url);
                });

                e.preventDefault();
                return false;
            }

            window.onpopstate = function
        });
    </script>
</body>
</html>
    <?php
}

function render_book($book, $id){
    global $books;
    $prevId = $id - 1;
    $nextId = $id + 1;
    $prev = $prevId >= 0 ? "<a href=\"?id={$prevId}\">PREV</a>" : '';
    $next = $nextId < count($books) ? "<a href=\"?id={$nextId}\">NEXT</a>" : '';
    return <<<HTML
    <h2>{$book['title']}</h2>
    {$prev} <a href="?" >HOME</a> {$next}
HTML;
}

function render_index($books){
    return "<ol>" .
    implode("", array_map(function($id, $book){
        return "<li><a href='?id={$id}'>{$book['title']}</a></li>";
    }, array_keys($books), $books)) .
    "</ol>";
}


$books = array(
    array('title' => 'Dive Into HTML5'),
    array('title' => 'The Best Practice Of PHP'),
    array('title' =>'MySQL Inspection'),
    array('title' =>'High Performance Of Huge Web Application'),
    array('title' =>'Javascript, The Good Parts'),
    array('title' =>'TCP/IP (I)'),
    array('title' =>'TCP/IP (II)'),
    array('title' =>'TCP/IP (III)')
);

$id = $_GET['id'];
$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || $_REQUEST['_isAjax'];

if ($id === null){
    $content = render_index($books);
} else {
    $content = render_book($books[$id], $id);
}

if ($isAjax){
    echo $content;
} else {
    echo render_layout($content);
}
