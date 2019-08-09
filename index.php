<!DOCTYPE html>
<html>
  <head>
    <title>Annotation</title>
    <meta charset="utf-8" />
    <link type='text/css' rel='stylesheet' href="css.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="fingerprint2.js"></script>
    </head>
    <body>
      <div class=round-button onclick="goback();">
        <h2 class=round-button-text>&#10094;</h2>
      </div>
      <div class="loader"></div>
      <img src=""/>
      
      <div class="flat-button" id="btn-noad" onclick="annotate(0);">
        <h2 class="btn-text">Ohne Werbung</h2>
      </div>
      <div class="flat-button" id="btn-ad" onclick="annotate(1);">
        <h2 class="btn-text">Werbung</h2>
      </div>
      <script>
        var fp = new Fingerprint({
          canvas: true,
          ie_activex: true,
          screen_resolution: true
        });

        var uid = fp.get();
        console.log("Fingerprint: " + uid);
        $(document).ready(function(){
          request = "annotate.php?getimg=1&fp=" + uid;
          console.log(request);
          $.get(request, function(data, status){
            if (status != 'success'){
              console.log("ERROR!");
            }
            img = document.getElementsByTagName('img')[0];
            img.setAttribute('src', data);
          })
        });
        document.body.addEventListener("keydown", function(event) {
          if (!(event.which == 97 || event.which == 98)){ //97 = NumPad 1, 98 = NumPad 2
            return;                                       //     Webung         Ohne
          }
          anno = event.which == 97 ? 1 : 0;
          btn = document.getElementsByClassName("flat-button")[anno];
          btn.className += " " + btn.id + "-click"
          setTimeout(function() {
            btn.className = btn.className.replace(" " + btn.id + "-click", "");
          }, 500)
          annotate(event.which == 97 ? 1 : 0);
        });

        $(window).on("beforeunload", function(){
          request = "annotate.php?close=1&fp=" + uid;
          $.get(request, function(data, status){
            console.log(data);
          });
        });

        function annotate(isAd){
            if(!(isAd == 0 || isAd == 1)){
              return;
            }
            img = document.getElementsByTagName("img")[0];
            imgF = img.getAttribute("src");
            request = encodeURI("annotate.php?annot=" + isAd + "&img=" + imgF + "&fp=" + uid);
            img.style.display = "none";
            $.get(request, function(data, status){
              if (status != 'success'){
                console.log("ERROR!");
              }
              console.log(data + "!!");
              if (data == "File doesn't exist"){
                console.log(imgF);
                request = "annotate.php?getimg=1&fp=" + uid;
                $.get(request, function(data, status){
                  if (status != 'success'){
                    console.log("ERROR!");
                  }
                  img = document.getElementsByTagName('img')[0];
                  img.setAttribute('src', data);
                  img.style.display = "block";
                });
                return;
              }
              img.setAttribute('src', data);
              img.style.display = "block";
            });
          }

          function goback(){
            request = "annotate.php?getlast=1&fp=" + uid;
            img = document.getElementsByTagName('img')[0];
            img.style.display = "block";
            $.get(request, function(data, status){
              if (status != 'success'){
                console.log("ERROR");
              }
              if (data != "No images recorded"){
              img.setAttribute('src', data);
            }
            img.style.display = "block";
            });
          }
      </script>
    </body>
</html>
