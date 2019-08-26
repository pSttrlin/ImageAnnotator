<!DOCTYPE html>
<html>
  <head>
    <title>Annotation</title>
    <meta charset="utf-8" />
    <link type='text/css' rel='stylesheet' href="css.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="/fingerprint2.js" type="application/javascript"></script>
    </head>
    <body>
      <div id="imgpnl">
        <img src=""/>
        <div class="loader"></div>
        <div class=round-button onclick="goback();">
          <h2 class=round-button-text>&#10094;</h2>
        </div>
        <div id="annot-buttons">
          <div class="flat-button" id="btn-noad" onclick="annotate(0);">
            <h2 class="btn-text">Ohne Werbung</h2>
          </div>
          <div class="flat-button" id="btn-ad" onclick="annotate(1);">
            <h2 class="btn-text">Werbung</h2>
          </div>
        </div>
      </div>
      <div id='panel'>

      </div>
      <script>
        var fp = new Fingerprint({
          canvas: true,
          ie_activex: true,
          screen_resolution: true
        });

        var uid = fp.get();
        console.log("Fingerprint: " + uid);
        //uid = prompt("Fingerpint: ", uid);
        $(document).ready(function(){
          request = "annotate.php?getimg=1&fp=" + uid;
          document.getElementsByClassName("loader")[0].style.opacity = "1";
          console.log(request);
          $.get(request, function(data, status){
            if (status != 'success'){
              console.log("ERROR!");
            }
            img = document.getElementsByTagName('img')[0];
            img.setAttribute('src', data);
            document.getElementsByClassName("loader")[0].style.opacity = "0";
          })
        });
        document.body.addEventListener("keydown", function(event) {
          if (!(event.which == 97 || event.which == 98 ||
                event.which == 35 || event.which == 40)){ //97 = NumPad 1, 98 = NumPad 2
            return;                                       //     Webung         Ohne
          }
          anno = event.which == 97 || event.which == 35 ? 1 : 0;
          btn = document.getElementsByClassName("flat-button")[anno];
          btn.className += " " + btn.id + "-click"
          setTimeout(function() {
            btn.className = btn.className.replace(" " + btn.id + "-click", "");
          }, 500)
          annotate(anno);
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
            img.style.opacity = "0";
            document.getElementsByClassName("loader")[0].style.opacity = "1";
            $.get(request, function(data, status){
              if (status != 'success'){
                console.log("ERROR!");
              }
              if (data == "File doesn't exist"){
                console.log(imgF);
                request = "annotate.php?getimg=1&fp=" + uid;
                $.get(request, function(data, status){
                  if (status != 'success'){
                    console.log("ERROR!");
                  }
                  img = document.getElementsByTagName('img')[0];
                  img.setAttribute('src', data);
                  img.style.opacity = "1";
                  document.getElementsByClassName("loader")[0].style.opacity = "0";
                });
                return;
              }
              img.setAttribute('src', data);
              img.style.opacity = "1";
              document.getElementsByClassName("loader")[0].style.opacity = "0";
              createPanelNode(imgF, isAd);
            });
          }

          function createPanelNode(img, isAd){
            basename = img.split(/[\\/]/).pop();
            path = isAd == 1 ? "annotations/Ads/" + basename : "annotations/Other/" + basename;
            div = document.createElement("div");
            div.className = "panel-div";
            borderColor = isAd == 1 ? "#e74c3c" :"#3498db";
            div.style.border = borderColor + " 2px solid";
            img = document.createElement("img");
            h3 = document.createElement("h3");
            h3.innerHTML = isAd == 1 ? "Werbung" : "Ohne Werbung";
            h3.className = "panel-h3";
            img.className = "panel-img";
            img.setAttribute("src", path);
            div.appendChild(img);
            div.appendChild(h3);
            paneldiv = document.getElementById("panel");
            if (paneldiv.childNodes.length > 0){
              paneldiv.insertBefore(div, paneldiv.childNodes[0]);
            }
            else{
              paneldiv.appendChild(div);
            }
          }

          function goback(){
            request = "annotate.php?getlast=1&fp=" + uid;
            img = document.getElementsByTagName('img')[0];
            img.style.opacity = "0";
            document.getElementsByClassName("loader")[0].style.opacity = "1";
            $.get(request, function(data, status){
              if (status != 'success'){
                console.log("ERROR");
              }
              if (data != "No images recorded"){
              img.setAttribute('src', data);
              div = document.getElementById("panel");
              div.removeChild(div.firstChild);
            }
            img.style.opacity = "1";
            document.getElementsByClassName("loader")[0].style.opacity = "0";
            });
          }
      </script>
    </body>
</html>
