<?php
  session_start();
  ignore_user_abort(true);

  function get_new_img(){
    return array_shift($_SESSION['files']);
  }

  function update_file_list($dontInclude = ""){
    $files = array();
    foreach (glob("images/*.jpeg") as $file){
      if ($file != $dontInclude){
        $files[] =  $file;
      }
    }
    $_SESSION["files"] = $files;
  }

  if (isset($_GET['close']) &&
      $_GET['close'] == 1 ){
        if (isset($_GET['fp']) && isset($_SESSION['fp_' . $_GET['fp']])){
          unset($_SESSION['fp_' . $_GET['fp']]);
        }
        update_file_list();
      return;
    }

  if (isset($_GET['getlast']) && $_GET['getlast'] == 1){
    if (!isset($_GET['fp'])){
      echo "Fingerprint needed";
      return;
    }
    if (!isset($_SESSION["fp_" . $_GET['fp']]) || empty($_SESSION["fp_" . $_GET['fp']])){
      echo "No images recorded";
      return;
    }

    $line = array_pop($_SESSION['fp_' . $_GET['fp']]);
    list($oldFile, $newFile) = explode(">", $line);

    rename($newFile, $oldFile);
    echo $oldFile;

    update_file_list($oldFile);

    return;
  }

  if (isset($_GET['annot']) && isset($_GET['img'])){
    if (!file_exists($_GET['img'])){
      echo "File doesn't exist";
      return;
    }
    else{
      if (!isset($_GET['fp'])){
        echo $_GET["img"];
        return;
      }
      if (!isset($_SESSION["fp_" . $_GET['fp']])){
        $_SESSION["fp_" . $_GET['fp']] = array();
      }

      if ($_GET['annot']==0){
        $newName = "annotations/Other/" . basename($_GET['img']);
        rename($_GET['img'], $newName);
        $line = $_GET['img'] . ">" . $newName;
        echo get_new_img();
      }
      else{
        $newName = "annotations/Ads/" . basename($_GET['img']);
        rename($_GET['img'], $newName);
        $line = $_GET['img'] . ">" . $newName;
        echo get_new_img();
      }
      $_SESSION['fp_' . $_GET['fp']][] = $line;
      if (count($_SESSION['fp_' . $_GET['fp']]) > 5){
        array_shift($_SESSION['fp_' . $_GET['fp']]);
      }
    }
  }
?>
