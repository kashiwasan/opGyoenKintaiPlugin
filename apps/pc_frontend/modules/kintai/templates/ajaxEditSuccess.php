<script type="text/javascript">
$(function(){
  $("#kintai_loading").hide();
  $("#kintai_submit").click(function(){
    $("#msg").hide();
    $("#kintai_loading").show();
    $("#kintai_table").hide();
    Data = $("#kintai_data").val();
    Rest = $("#kintai_rest").val();
    Comment = $("#kintai_comment").val();
    $.ajax({
      type: "POST",
      url: "./kintai/ajaxSend",
      dataType: "json",
      data: { "data":Data, "rest":Rest, "comment":Comment, "y":<?php echo $y; ?>, "m":<?php echo $m; ?>, "d":<?php echo $d; ?> },
      success: function(json){
        if(json.status="ok"){
          $("#msg").fadeIn(1000).css("color", "#00FF00").html(json.msg);
        }
        if(json.status="err"){
          $("#msg").fadeIn(1000).css("color", "#FF0000").html(json.msg);
        }
        $("#kintai_loading").hide();
        $("#kintai_table").show();
      },
    });
  return false;
  });
});
</script>

<div class="partsHeading"><h3><?php echo $nickname; ?>さんの<?php echo $y; ?>年 <?php echo $m; ?>月 <?php echo $d; ?>日の勤怠を編集する</h3></div>
<div class="block">
<div id="msg"></div>
<div id="kintai_loading"><img src="./opGyoenKintaiPlugin/css/images/prettyPopin/loader.gif" alt="Now Loading..."/></div>
<table id="kintai_table">
<tr><td>勤怠入力</td><td><input type="text" name="kintai_data" value="<?php echo $data; ?>" maxlength="9" id="kintai_data" /></td></tr>
<tr><td>休憩時間(分単位)</td><td><input type="text" name="kintai_rest" value="<?php echo $rest; ?>" maxlength="3" id="kintai_rest" /></td></tr>
<tr><td>作業内容コメント</td><td><textarea name="kintai_comment" id="kintai_comment"><?php echo $comment; ?></textarea></td></tr>
<tr><td></td><td><input type="submit" name="submit" id="kintai_submit" value="修正する" /></td></tr>

</table>
</div>	
