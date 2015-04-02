<?php

$targetDir = '/d/usr/test/posts';
if (!is_dir($targetDir)){
    mkdir($targetDir);
}

$srcJsonFile = '/d/usr/test/spider/cnblogs/posts-detail.json';
$json = file_get_contents($srcJsonFile);
$postList = json_decode($json, true);
//if (!$postList){
//    $postList = json_decode(substr(1, $json), true);
//}
if (!$postList){
    die('Error: invalid JSON file: '.$srcJsonFile.' error: '.json_last_error().PHP_EOL);
}

function render_post($link, $title, $body, $postDate){
    $title = htmlspecialchars($title);
    return <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{$title}</title>
</head>
<body>
<h1>{$title}</h1>
<div><a href="{$link}">cnblogs</a><span> Post date: $postDate</span></div>
{$body}
</body>
</html>
HTML;
}

$indexContents = array();

require __DIR__.'/../protected/components/DataQuery.php';

$postList = DataQuery::from($postList)->orderBy('postDate', SORT_DESC)->toArray();

foreach ($postList as $post) {
    echo "Processing {$post['title']} {$post['link']}".PHP_EOL;
    $linkParts = explode('/', str_replace('http://www.cnblogs.com/pcy0/', '', $post['link']));
    $linkParts[0] = 'p';
    $postFileName = implode('_', $linkParts);

    $html = render_post($post['link'], $post['title'], $post['body'], $post['postDate']);
    file_put_contents($targetDir.'/'.$postFileName, $html);

    $indexContents[] = array('link' => $postFileName, 'title' => $post['title'] );
}

echo "Processing index.html".PHP_EOL;

$indexContentsHtml = array_reduce($indexContents, function($html, $post){
    $html.= <<<HTML
<li><a href="{$post['link']}">{$post['title']}</a></li>
HTML;
    return $html;
});

$indexHtml = <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{$title}</title>
</head>
<body>
  <h1>Index</h1>
  <ul>
    {$indexContentsHtml}
  </ul>
</body>
</html>
HTML;

file_put_contents($targetDir.'/index.html', $indexHtml);

 