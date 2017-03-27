/**
 * Created by Aozak on 2017/3/27.
 */
function draw(){
    imagePreload(
        "res/bg.png",
        "res/bar.png"
    );
    var canvas = document.getElementById("canvas");
    if(!canvas.getContext){
        canvas.innerHTML = "警告：你的浏览器不支持canvas。";
    }else{
        var ctx = canvas.getContext("2d");
        ctx.font = "48px 7barSp";
        ctx.fillText(sto["bal"],200,50);
        ctx.fillText(sto["est"],200,100);
        var bgi = new Image();
        bgi.src = "res/bg.png";
        var bar = new Image();
        bar.src = "res/bar.png";
        ctx.drawImage(bgi,0,0);
    }
}
function imagePreload(){
    var images = [];
    for(var i=0;i<imagePreload.arguments.length;i++){
        images[i] = new Image();
        images[i].src = imagePreload.arguments[i];
    }
}