/**
 * Created by Aozak on 2017/3/27.
 */
function requestData(){
    console.log("requesting");
    $.ajax({
        type: "POST",
        contentType: "application/x-www-form-urlencoded",
        dataType: "html",
        url: "refreshNow.php?noCache=" + Math.random(),
        success: function(){
            console.log("successfully connected to update module");
        },
        timeout:30000
})
}
function reCalc(){
    console.log("reCalculating");
    $.ajax({
            type: "POST",
            contentType: "application/x-www-form-urlencoded",
            dataType: "html",
            url: "getFeeInfo.php?noCache=" + Math.random(),
            success: function (data) {
                var sto = JSON.parse(data);
                var setList = ["bal","estBal","burnRate","est"];
                for(var i=0;i<setList.length;i++){
                    if(isNaN(sto[i])){
                        sto[i] = "---.--";
                    }
                    $("#".concat(setList[i])).html(Number(sto[setList[i]]).toFixed(2));
                }
                if(sto["est"]<5){
                    $("#est").css("color","#ffbb3c");
                }else{
                    $("#est").css("color","inherit");
                }
            },
            timeout: 30000
        }
    );
}
/*
 sto = {};
 imgSto = {};
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
    */