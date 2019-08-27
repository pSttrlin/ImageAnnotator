<?php
  session_start();

  if(!file_exists("inprogress.arr"))
    file_put_contents("inprogress.arr", serialize(array()));

  function get_in_progress_arr(){
    return unserialize(file_get_contents("inprogress.arr"));
  }

  function set_in_progress($arr){
    file_put_contents("inprogress.arr", serialize($arr));
  }

  function update_in_progress($element, $value){
    $in_progress = get_in_progress_arr();
    $in_progress[$element] = $value;
    set_in_progress($in_progress);
  }

  function get_new_img($fp = null){
    if ($fp != null)
    {
      $in_progress = get_in_progress_arr();
      if (isset($in_progress["fp_" . $fp]))
        return $in_progress["fp_" . $fp];
    }

    update_file_list();
    if (empty($_SESSION["files"])){ //Keine Bilder mehr in images/
      return "No files left";
    }

    $img = array_shift($_SESSION['files']);
    while(in_array($img, get_in_progress_arr())){
      update_file_list();
      $img = array_shift($_SESSION['files']);
    }
    return $img;
  }

  function update_file_list(){
    $files = array();
    $in_progress = get_in_progress_arr();
    foreach (glob("images/*.jpeg") as $file){
      if (!in_array($file, $in_progress)){ //Bilder die bearbeitet werden ignorieren
        $files[] =  $file;
      }
    }
    $_SESSION["files"] = $files;
  }

  if (isset($_GET['close'])){
        if (isset($_GET['fp'])){
          if (isset($_SESSION['fp_' . $_GET['fp']]))
            unset($_SESSION['fp_' . $_GET['fp']]);

          $in_progress = get_in_progress_arr(); //Aus der inprogress Array entfernen
          if (isset($in_progress['fp_' . $_GET['fp']])){
            unset($in_progress["fp_" . $_GET["fp"]]);
            set_in_progress($in_progress);
          }
        }
        update_file_list();
      return;
    }

  update_file_list();

  if (isset($_GET["goto"])){
    $imgPath = $_GET["goto"];
    $isAd = $_GET["ad"];
    $fp = $_GET["fp"];
    if (!file_exists($imgPath)){
      echo "File not found";
      return;
    }

    $basename = basename($imgPath);
    file_put_contents("test.txt", $basename);
    $newname = "images/" . $basename;
    rename($imgPath, $newname);

    update_in_progress("fp_" . $fp, $newname);

    foreach ($_SESSION["fp_" . $fp] as $line){
      if (strpos($line, $basename) !== false){
        $key = array_search($line, $_SESSION["fp_" . $fp]);
        unset($_SESSION["fp_" . $fp][$key]);
      }
    }

    echo $newname;
    return;
  }

  if (isset($_GET["getimg"])){
    if (!isset($_GET['fp'])){
      echo "Fingerprint needed!";
      return;
    }

    $img = get_new_img($_GET["fp"]);

    if ($img == "No files left"){
      echo $img;
      return;
    }

    update_in_progress("fp_" . $_GET["fp"], $img);
    update_file_list();

    echo $img;
    return;
  }

  if (isset($_GET['getlast'])){
    if (!isset($_GET['fp'])){
      echo "Fingerprint needed";
      return;
    }
    if (!isset($_SESSION["fp_" . $_GET['fp']]) || empty($_SESSION["fp_" . $_GET['fp']])){ //Noch keine Bilder markiert
      echo "No images recorded";
      return;
    }

    $line = array_pop($_SESSION['fp_' . $_GET['fp']]);
    list($oldFile, $newFile) = explode(">", $line);

    rename($newFile, $oldFile);
    echo $oldFile;

    update_in_progress("fp_" . $_GET["fp"], $oldFile);
    update_file_list();

    return;
  }

  if (isset($_GET['annot']) && isset($_GET['img'])){
    if (!file_exists($_GET['img'])){
      echo "File doesn't exist";
      return;
    }

    if (!isset($_GET['fp'])){
      echo $_GET["img"]; //echo fingerprint needed
      return;
    }

    if (!isset($_SESSION["fp_" . $_GET['fp']])) //Array fürs speichern der 5 letzten bilder
      $_SESSION["fp_" . $_GET['fp']] = array();

    if ($_GET['annot']==0){ // Ohne Werbung
      $newName = "annotations/Other/" . basename($_GET['img']);
      rename($_GET['img'], $newName);
      $line = $_GET['img'] . ">" . $newName; //Alter + Neuer Bildname speichern um zurück gehen zu können
    }
    else{ //Mit Werbung
      $newName = "annotations/Ads/" . basename($_GET['img']);
      rename($_GET['img'], $newName);
      $line = $_GET['img'] . ">" . $newName;
    }

    $img = get_new_img();

    if ($img == "No files left"){
      echo $img;
      return;
    }

    update_in_progress("fp_" . $_GET["fp"], $img);

    $_SESSION['fp_' . $_GET['fp']][] = $line;
    if (count($_SESSION['fp_' . $_GET['fp']]) > 5) //Wenn mehr als 5 Bilder gespeichert sind, das letzte entfernen
      array_shift($_SESSION['fp_' . $_GET['fp']]);

    update_file_list();
    echo $img;
  }
?>
