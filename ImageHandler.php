<?php
/**
 * Created by PhpStorm.
 * User: psu
 * Date: 27.08.2019
 * Time: 15:49
 */

include "History.php";

class ImageHandler
{
    public $files = array();
    public $history;


    function __construct()
    {
        if (isset($_SESSION["history"]))
            $this->history = &$_SESSION["history"];
        else $this->history = new History();
        if (!file_exists("inprogress.arr")) {
            $this->set_in_progress_arr(array());
        }
        $this->update_file_list();
    }

    function get_new_image($fp = null){
        $in_progress = $this->get_in_progress_arr();

        if ($fp != null)
            if (isset($in_progress["fp_" . $fp]))
                return $in_progress["fp_" . $fp];

        $this->update_file_list();

        $img = array_shift($this->files);
        while (in_array($img, $in_progress)){
            $this->update_file_list();
            $img = array_shift($this->files);
        }
        return $img;
    }

    function annotate_image($img, $ad, $fp){
        $img = "images/" . basename($img);
        if (!file_exists($img))
            return "File not found";

        $newName = ($ad == 1 ? "annotations/Ads/" : "annotations/Other/") . basename($img);

        rename($img, $newName);

        $this->history->add_to_history($img, $newName);

        $img = $this->get_new_image();
        $this->update_in_progress_arr("fp_" . $fp, $img);
        return $img;
    }

    function goto_image($img, $fp){
        if (!file_exists($img)) return "File not found";

        $this->history->remove_from_history($img);

        $newName = "images/" . basename($img);
        rename($img, $newName);

        $this->update_in_progress_arr("fp_" . $fp, $newName);
        $this->update_file_list();
        return $newName;
    }

    function close_client($fp){
        $in_progress = $this->get_in_progress_arr();
        if (isset($in_progress["fp_" . $_GET["fp"]])){
            unset($in_progress["fp_" . $fp]);
            $this->set_in_progress_arr($in_progress);
        }
        session_reset();
    }

    function get_last_image($fp){
        list($newName, $oldName) = $this->history->get_last();
        rename($oldName, $newName);
        $this->update_in_progress_arr("fp_" . $fp, $newName);
        $this->update_file_list();
        return $newName;
    }

    function update_file_list(){
        $in_progress = $this->get_in_progress_arr();
        $files = array();
        foreach (glob("images/*.jpeg") as $file){
            if (!in_array($file, $in_progress)) $files[] = $file;
        }
        $this->files = $files;
    }

    //In Progress functions
    function get_in_progress_arr(){
        return unserialize(file_get_contents("inprogress.arr"));
    }

    function set_in_progress_arr($arr){
        file_put_contents("inprogress.arr", serialize($arr));
    }

    function update_in_progress_arr($element, $value){
        $in_progress = $this->get_in_progress_arr();
        $in_progress[$element] = $value;
        $this->set_in_progress_arr($in_progress);
    }
}