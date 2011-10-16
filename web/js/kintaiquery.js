$(function(){
    $("#kintai_submit").click(function(){
      $("#kintai_loading").show();
      Data = $("#kintai_data").val();
      Rest = $("#kintai_rest").val();
      Comment = $("#kintai_comment").val();
      $.ajax({
        type: "POST",
        url: "./send2",
        dataType: "json",
        data: { "data":Data, "rest":Rest, "comment":Comment },
        success: function(json){
          if(json.status="ok"){
            $("#msg").css("color", "#00FF00").html(json.msg);
          }
          if(json.status="err"){
            $("#msg").css("color", "#FF0000").html(json.msg);
          }
          $("#kintai_loading").hide();
        },
      });
    return false;
    });
});
