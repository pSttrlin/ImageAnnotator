<?php
/**
 * Created by PhpStorm.
 * User: psu
 * Date: 27.08.2019
 * Time: 16:28
 */

class History
{
    public $history;
    public $size;

    function __construct($size = 5)
    {
        $this->history = array();
        $this->size = $size;
    }

    public function add_to_history($oldName, $newName){
        $this->history[$oldName] = $newName;

        if (count($this->history) > $this->size)
            array_shift($this->history);
    }

    public function remove_from_history($file){
        $basename = basename($file);
        for ($i = 0; $i < count($this->history); $i++){
            if (strpos($this->history[$i], $basename))
            {
                unset($this->history[$i]);
                break;
            }
        }
    }

    public function get_last(){
        end($this->history);
        $oldName = key($this->history);
        $newName = $this->history[$oldName];
        array_pop($this->history);
        return array($oldName, $newName);
    }
}