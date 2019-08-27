<?php
  include "ArgumentParser.php";
  include "ImageHandler.php";
  //TODO > Parser error falls nicht alles notwendingen Argumente vorhanden
  session_start();
  session_reset();

  $callDict = array("getimg"  => "get_new_image",
                    "getlast" => "get_last_image",
                    "goto"    => "goto_image",
                    "annot"   => "annotate_image",
                    "close"   => "close_client");
  $parser = new ArgumentParser($_GET, $callDict);

  if (!isset($_SESSION["image_handler"]))
    $_SESSION["image_handler"] = new ImageHandler();

  $image_handler = $_SESSION["image_handler"];

  call_user_func($parser->operation);

  function get_new_image(){
    global $parser, $image_handler;
    echo $image_handler->get_new_image($parser->fp);
    return;
  }

  function get_last_image(){
    global $parser, $image_handler;
    echo $image_handler->get_last_image($parser->fp);
  }

  function goto_image(){
    global $parser, $image_handler;
    echo $image_handler->goto_image($parser->img, $parser->ad, $parser->fp);
  }

  function annotate_image(){
    global $parser, $image_handler;
    echo $image_handler->annotate_image($parser->img, $parser->ad, $parser->fp);
  }

  function close_client(){
    global $parser, $image_handler;
    $image_handler->close_client($parser->fp);
  }
