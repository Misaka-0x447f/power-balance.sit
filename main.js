/**
 * Created by Aozak on 2017/3/27.
 */
function updateData(){
    $.ajax({
            type: "POST",
            contentType: "application/x-www-form-urlencoded",
            dataType: "html",
            url: "getFeeInfo.php?noCache=" + Math.random(),
            success: function (data) {
                sto = JSON.parse(data);
            },
            timeout: 30000,
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            }
        }
    )
}
var sto;
$(document).ready(function(){
    updateData();
    preLoadImg();
    $("#container").imagesLoaded(function(){
       draw();
    });
});
var imgSto = {};
function preLoadImg(){
    imgSto[0] = new Image();
    imgSto[1] = new Image();
    imgSto[0].src = "res/bg.png";
    imgSto[1].src = "res/bar.png";
}
function draw(){
    var canvas = document.getElementById("canvas");
    if(!canvas.getContext){
        canvas.innerHTML = "警告：你的浏览器不支持canvas。";
    }else{
        var ctx = canvas.getContext("2d");
        ctx.font = "48px 7barSp";
        ctx.fillText(sto["bal"],200,50);
        ctx.fillText(sto["est"],200,100);
        ctx.drawImage(imgSto[0],0,0);
    }
}