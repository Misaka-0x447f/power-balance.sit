/**
 * Created by Aozak on 2017/3/27.
 */
sto = {};
imgSto = {};
function updateData(){
    $.ajax({
            type: "POST",
            contentType: "application/x-www-form-urlencoded",
            dataType: "html",
            url: "getFeeInfo.php?noCache=" + Math.random(),
            success: function (data) {
                window.sto = JSON.parse(data);
            },
            timeout: 30000,
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            }
        }
    )
}
function preLoadImg(){
    window.imgSto[0] = new Image();
    window.imgSto[1] = new Image();
    window.imgSto[0].src = "res/bg.png";
    window.imgSto[1].src = "res/bar.png";
}
function draw(){
    var canvas = document.getElementById("canvas");
    if(!canvas.getContext){
        canvas.innerHTML = "警告：你的浏览器不支持canvas。";
    }else{
        var ctx = canvas.getContext("2d");
        ctx.font = "48px 7barSp";
        ctx.fillStyle = "#66ccff";
        ctx.fillText(window.sto["bal"],200,50);
        ctx.fillText(window.sto["est"],200,100);
        ctx.drawImage(window.imgSto[0],0,0);

    }
}
$(document).ready(function(){
    updateData();
    preLoadImg();
    $("#container").imagesLoaded(function(){
        draw();
    });
});