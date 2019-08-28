<?php

class ArgumentParser
{
    public $operation;
    public $fp;
    public $img;
    public $ad;

    function __construct($args, $callDict){
        if (isset($args["fp"]))  $this->fp = $args["fp"];
        if (isset($args["img"])) $this->img = $args["img"];
        if (isset($args["ad"]))  $this->ad = $args["ad"] == 1;

        if (isset($args["getimg"])) $this->operation = $callDict["getimg"];
        if (isset($args["getlast"])) $this->operation = $callDict["getlast"];
        if (isset($args["annot"])) $this->operation = $callDict["annot"];
        if (isset($args["goto"])) $this->operation = $callDict["goto"];
        if (isset($args["close"])) $this->operation = $callDict["close"];
    }
}