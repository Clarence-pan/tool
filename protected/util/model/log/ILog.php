<?php
namespace tool\util\model\log;
interface ILog{
    public function seek($pos);
    public function next();
}