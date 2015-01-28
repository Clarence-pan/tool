<?php
namespace log\models;
interface ILog{
    public function count();
    public function seek($pos);
    public function next();
    public function eof();
}