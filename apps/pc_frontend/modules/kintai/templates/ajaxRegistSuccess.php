<script type="text/javascript">
$(function(){
  $("#kintai_regist_loading").hide();
    $("#kintai_regist_submit").click(function(){
      $("#kintai_regist_loading").show();
      $("#regist_msg").hide();
      Data = $("#kintai_regist_data").val();
      Rest = $("#kintai_regist_rest").val();
      Comment = $("#kintai_regist_comment").val();
      $.ajax({
        type: "POST",
        url: "./kintai/send2",
        dataType: "json",
        data: { "data":Data, "rest":Rest, "comment":Comment, "y": <?php echo $y ?>, "m": <?php echo $m ?>, "d": <?php echo $d ?>},
        success: function(json){
          $("#kintai_regist_loading").hide();
          if(json.status="ok"){
            $("#regist_msg").fadeIn("1000").css("color", "#00FF00").html(json.msg);
          }
          if(json.status="err"){
            $("#regist_msg").fadeIn("1000").css("color", "#FF0000").html(json.msg);
          }
        }
    });
    return false;
  });
});
</script>
<div class="partsHeading"><h3><?php echo $nickname; ?>さんの今日の勤怠を登録する</h3></div>
<div class="block">
<div id="kintai_regist_loading" style="display: none;"><img src="./opGyoenKintaiPlugin/js/loading.gif" alt="Now Loading..." /></div>
<div id="regist_msg"></div>
<table>
<tr><td>勤怠入力する日付</td><td><?php echo $y; ?>年 <?php echo $m; ?>月 <?php echo $d; ?>日</td></tr>
<tr>
<td><label for="data">勤怠入力</label></td><td><input type="text" id="kintai_regist_data" name="data" value="<?php echo $data; ?>" maxlength="9" /></td></tr>
<tr>
<td><label for="rest">休憩時間(分単位)</label></td><td><input type="text" id="kintai_regist_rest" name="rest" value="<?php echo $rest; ?>" maxlength="3"></td></tr>

<tr>
<td><label for="comment">作業内容コメント</label></td><td><textarea name="comment" id="kintai_regist_comment"><?php echo $comment; ?></textarea></td></tr>
<tr><td></td><td><input type="submit" name="submit" id="kintai_regist_submit" value="確認する" /></td></tr></table>
<a href="./kintai/ajaxRegistEasy?y=<?php echo $y; ?>&m=<?php echo $m; ?>&d=<?php echo $d; ?>" rel="internal">簡単入力モードにする</a></div>
