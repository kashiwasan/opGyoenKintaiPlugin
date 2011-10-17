//$(function(){
//  $("#kintai_regist_loading").hide();
//    $("#kintai_regist_submit").click(function(){
//      $("#kintai_regist_loading").show();
//      $("#regist_msg").hide();
//      Data = $("#kintai_regist_data").val();
//      Rest = $("#kintai_regist_rest").val();
//      Comment = $("#kintai_regist_comment").val();
//      $.ajax({
//        type: "POST",
//        url: "./kintai/send2",
//        dataType: "json",
//        data: { "data":Data, "rest":Rest, "comment":Comment },
//        success: function(json){
//          $("#kintai_regist_loading").hide();
//          if(json.status="ok"){
//           $("#regist_msg").fadeIn("1000").css("color", "#00FF00").html(json.msg);
//          }
//          if(json.status="err"){
//            $("#regist_msg").fadeIn("1000").css("color", "#FF0000").html(json.msg);
//          }
//        },
//      });
//    return false;
//    });
//});
