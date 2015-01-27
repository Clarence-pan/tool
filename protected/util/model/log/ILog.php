<?php
namespace tool\util\model\log;
interface ILog{
    public function count();
    public function seek($pos);
    public function next();
}