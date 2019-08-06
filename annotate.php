<?php
  session_start();
  ignore_user_abort(true);

  function get_new_img(){
    return array_shift($_SESSION['files']);
  }

  function update_file_list(){
    $files = array();
    foreach (glob("images/*.jpeg") as $file){
      $files[] =  $file;
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

    $lastImg = array_pop($_SESSION["fp_" . $_GET['fp']]);
    $file = fopen('filemap.txt', 'r');
    //Nicht sehr effizient
    $oldFiles = array();
    $newFiles = array();
    while (!feof($file)){
      $line = fgets($file);
      if ($line == "") break;
      list($oldFile, $newFile) = explode(">", $line);
      $oldFiles[] = $oldFile;
      $newFiles[] = trim($newFile);
    }
    fclose($file);
    $index = array_search($lastImg, $oldFiles);
    rename($newFiles[$index], $oldFiles[$index]);
    echo $oldFiles[$index];

    $contents = file_get_contents('filemap.txt');
    $line = $oldFiles[$index] . ">" . $newFiles[$index];
    $contents = str_replace($line, '', $contents);
    file_put_contents('filemap.txt', $contents);
    return;
  }

  if (isset($_GET['annot']) && isset($_GET['img'])){
    if (!file_exists($_GET['img'])){
      echo "rip";
      return 2;
    }
    else{
      if (!isset($_GET['fp'])){
        echo $_GET["img"];
        return;
      }
      if (!isset($_SESSION["fp_" . $_GET['fp']])){
        $_SESSION["fp_" . $_GET['fp']] = array();
      }
      $_SESSION['fp_' . $_GET['fp']][] = $_GET['img'];

      if ($_GET['annot']==0){
        $newName = "annotations/Other/" . basename($_GET['img']);
        rename($_GET['img'], $newName);
        $line = $_GET['img'] . ">" . $newName . "\n";
        $file = fopen("filemap.txt", 'a');
        fwrite($file, $line);
        fclose($file);
        echo get_new_img();
        return;
      }
      else{
        $newName = "annotations/Ads/" . basename($_GET['img']);
        rename($_GET['img'], $newName);
        $line = $_GET['img'] . ">" . $newName . "\n";
        $file = fopen("filemap.txt", 'a');
        fwrite($file, $line);
        fclose($file);
        echo get_new_img();
        return;
      }
    }
  }
?>
