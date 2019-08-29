let fp = new Fingerprint({
    canvas: true,
    ie_activex: true,
    screen_resolution: true,
    user_agent: false
});

let uid = fp.get();
console.log("Fingerprint: " + uid);
//uid = prompt("Fingerpint: ", uid);
let canAnnotate;
let annotated = 0;

$(window).on("beforeunload", function(){
    let request = "annotate.php?close=1&fp=" + uid;
    $.get(request, function(data, status){});
});

$(window).ready(function () {

    document.getElementsByTagName("img")[0].addEventListener("load", on_image_load);

    //Hotkeys
    document.body.addEventListener("keydown", function(event) {
        if (!(event.which === 65 || event.which === 68))  //65 = A,        68 = B
            return;                                       //Ohne Werbung   Werbung

        if (!canAnnotate) return;

        let anno = event.which === 68 ? 1 : 0; //A = Mit Werbung ( 1 ), B = Ohne Werbung ( 0 )
        let btn = document.getElementsByTagName("button")[anno]; //Das erste Element ist der Button "Ohne Werbung", das zweite Element der Button "Werbung"
        btn.className += " " + btn.id + "-click";
        setTimeout(function() {
            btn.className = btn.className.replace(" " + btn.id + "-click", "");
        }, 500);
        annotate(anno);
    });

    document.getElementsByClassName("loader")[0].style.opacity = "1"; //Ladebalken anzeigen
    //let display = document.getElementsByTagName("img")[0];

    let img = get_image();

    //display.setAttribute("src", img);
    set_image(img);
    document.getElementsByClassName("loader")[0].style.opacity = "0";
});

function get_image(){
    let request = "annotate.php?getimg=1&fp=" + uid;
    let resp;
    $.ajax({
        type: "GET",
        url: request,
        async: false,
        success: function (response) { resp = response; }
    });

    if (resp == "No files left"){
        alert("No files left");
        return null;
    }

    return resp;
}

function make_annotation_request(isAd, image){
    let request = encodeURI("annotate.php?annot=1&ad=" + isAd + "&img=" + image + "&fp=" + uid);
    let resp;

    $.ajax({
        type: "GET",
        url: request,
        async: false,
        success: function (response) { resp = response; }
    });

    if (resp == "No files left"){
        alert("Keine Bild übrig");
        return null;
    }

    if (resp == "File not found"){
        resp = get_image();
        return resp;
    }

    return resp;
}

function annotate(isAd){

    if (!canAnnotate) return;

    if(!(isAd === 0 || isAd === 1)){
        return;
    }

    let img = document.getElementsByTagName("img")[0];
    let imgF = img.getAttribute("src");

    img.style.opacity = "0";
    document.getElementsByClassName("loader")[0].style.opacity = "1";

    let newImage = make_annotation_request(isAd, imgF);

    //img.setAttribute("src", newImage);
    set_image(newImage);
    img.style.opacity = "1";
    document.getElementsByClassName("loader")[0].style.opacity = "0";

    createPanelNode(imgF, isAd);
    increase_and_update_annotated();
}

function createPanelNode(img, isAd){
    let basename = img.split(/[\\/]/).pop(); //Bildname ohne Pfad
    let path = isAd === 1 ? "annotations/Ads/" + basename : "annotations/Other/" + basename;

    let div = document.createElement("div");
    div.className = "panel-div";
    let borderColor = isAd === 1 ? "#e74c3c" :"#3498db";
    div.style.border = borderColor + " 2px solid";
    img = document.createElement("img");
    let h3 = document.createElement("h3");
    h3.innerHTML = isAd === 1 ? "Werbung" : "Ohne Werbung";
    h3.className = "panel-h3";
    img.className = "panel-img";
    img.setAttribute("src", path);
    div.appendChild(img);
    div.appendChild(h3);
    div.addEventListener("click", function () {
        goto(div);
    });


    let paneldiv = document.getElementById("panel");
    //Div an erster Stelle anfügen
    if (paneldiv.childNodes.length > 0)
        paneldiv.insertBefore(div, paneldiv.childNodes[0]);
    else
        paneldiv.appendChild(div);
}

function goto(div){
    let panel = document.getElementById("panel");

    let imagePath = div.getElementsByTagName("img")[0].getAttribute("src");
    let request = "annotate.php?goto=1&img=" + imagePath + "&fp=" + uid;

    let img = document.getElementsByTagName("img")[0];
    img.style.opacity = "0";
    document.getElementsByClassName("loader")[0].style.opacity = "1";

    $.get(request, function(data, status) {

        if(status != "success"){
            console.log("ERROR!");
            return;
        }

        if (data == "File not found"){
            alert("Bild nicht gefunden"); //Neues Bild laden?
            img.style.opacity = "1";
            document.getElementsByClassName("loader")[0].style.opacity = "1";
            return;
        }

        //img.setAttribute("src", data);
        set_image(data);
        img.style.opacity = "1";
        document.getElementsByClassName("loader")[0].style.opacity = "0";
        decrease_and_update_annotated();
    });

    panel.removeChild(div);
}

function goback(){
    let request = "annotate.php?getlast=1&fp=" + uid;
    let img = document.getElementsByTagName('img')[0];
    img.style.opacity = "0";
    document.getElementsByClassName("loader")[0].style.opacity = "1";
    $.get(request, function(data, status){
        if (status != 'success'){
            console.log("ERROR");
            return;
        }
        if (data != "No images recorded"){
            //img.setAttribute('src', data);
            set_image(data);

            //Erstes div entfernen
            let div = document.getElementById("panel");
            div.removeChild(div.firstChild);
        }
        img.style.opacity = "1";
        document.getElementsByClassName("loader")[0].style.opacity = "0";
        decrease_and_update_annotated();
    });
}

function increase_and_update_annotated(){
    annotated++;
    document.getElementById("numAnno").innerHTML = annotated;
}

function decrease_and_update_annotated(){
    annotated--;
    document.getElementById("numAnno").innerHTML = annotated;
}

function on_image_load(){
    enable_annot();
}

function set_image(image){
    disable_annot();
    let img = document.getElementsByTagName("img")[0];
    img.setAttribute("src", image);
}

function disable_annot(){
    for (let btn in document.getElementsByTagName("button"))
        btn.enabled = false;
    canAnnotate = false;
}

function enable_annot(){
    for (let btn in document.getElementsByTagName("button"))
        btn.enabled = true;
    canAnnotate = true;
}