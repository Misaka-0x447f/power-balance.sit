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
                sto = eval(data);
            },
            timeout: 30000,
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            }
        }
    )
}
var sto = {};
$(document).ready(function(){
    updateData();
    draw();
});