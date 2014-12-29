<?php


namespace tool\util\model;


class Markdown {
    private $markdown;
    public function __construct($markdown){
        $this->markdown = $markdown;
    }

    public function toHtml(){
        return $this->parse($this->markdown);
    }

    private function parse($markdown){
        $baseDir = dirname(__FILE__).'/../..';
        $tmpFile = '/tmp/markdown.tmp.text';
        file_put_contents($tmpFile, $this->trimTailSpacesOfEachLine($markdown));
        $html = `$baseDir/console/bin/markdown.pl --html4tags $tmpFile`;
        return $html;
    }

    private function trimTailSpacesOfEachLine($text){
        $lines = explode("\n", $text);
        foreach ($lines as &$line) {
            $line = rtrim($line, "\r\t ");
        }
        return implode("\n", $lines);
    }
} 