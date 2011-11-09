<script type="text/javascript">
$(function(){
  $("#kintai_regist_loading").hide();
  $("#kintai_regist_submit").click(function(){
      $("#kintai_regist_loading").show();
      $("#regist_msg").hide();
      Data = $("#kintai_regist_data").val();
      Comment = $("#kintai_regist_comment").val();
      $.ajax({
        type: "POST",
        url: "./kintai/send2",
        dataType: "json",
        data: { "data":Data, "comment":Comment, "y": <?php echo $y ?>, "m": <?php echo $m ?>, "d": <?php echo $d ?>},
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
  
  $("#kintai_explain_open_link").click(function(){
    $("#kintai_explain_open").fadeOut("1000");
    $("#kintai_explain").fadeIn("1000");
  });
  $("#kintai_explain_close_link").click(function(){
    $("#kintai_explain").fadeOut("1000");
    $("#kintai_explain_open").fadeIn("1000");
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
<td><label for="data">勤怠入力</label></td><td><input type="text" id="kintai_regist_data" name="data" value="<?php echo $data, $rest; ?>" maxlength="24" /></td></tr>

<tr><td><label for="comment">作業内容コメント</label></td><td><textarea name="comment" id="kintai_regist_comment"><?php echo $comment; ?></textarea></td></tr>
<tr><td></td><td><input type="submit" name="submit" id="kintai_regist_submit" value="確認する" /></td></tr></table>
<a href="./kintai/ajaxRegistEasy?y=<?php echo $y; ?>&m=<?php echo $m; ?>&d=<?php echo $d; ?>" rel="internal">素人入力モードにする</a><br />

<div id="kintai_explain_open"><a id="kintai_explain_open_link">▼勤怠入力の説明</a></div>
<div id="kintai_explain" style="display: none;">
<h4>勤怠入力の方法</h4>
例１）会社に<span class="op_font" style="color:#00FF00;"><span class="op_font" style="font-size:large">出社</span></span>して、<span class="op_font" style="color:#3366FF;"><span class="op_font" style="font-size:large">10:00</span></span>に出勤、<span class="op_font" style="color:#FF0000;"><span class="op_font" style="font-size:large">25:00(翌日)</span></span>に退勤した場合。<br />
<span class="op_font" style="color:#00FF00;">出勤</span>なので、
<span class="op_font" style="font-size:large"><span class="op_font" style="color:#00FF00;">S</span><span class="op_font" style="color:#3366FF;">1000</span><span class="op_font" style="color:#FF0000;">2500</span></span>
と入力します。<br />
<br />
例２）<span class="op_font" style="color:#CC99FF;"><span class="op_font" style="font-size:large">在宅</span></span>で<span class="op_font" style="color:#3366FF;"><span class="op_font" style="font-size:large">09:00</span></span>から<span class="op_font" style="color:#FF0000;"><span class="op_font" style="font-size:large">16:00</span></span>まで働いた場合<br /> 
<span class="op_font" style="color:#CC99FF;">在宅</span>なので、
<span class="op_font" style="font-size:large"><span class="op_font" style="color:#CC99FF;">Z</span><span class="op_font" style="color:#3366FF;">0900</span><span class="op_font" style="color:#FF0000;">1600</span></span>
と入力します。<br />
・「<b>休憩時間</b>」は、休憩した時間（分単位）を入力してください。<br />
・「<b>コメント</b>」は、今日１日行った業務を簡潔に入力してください。<br />
<br />
<a id="kintai_explain_close_link">▲説明を閉じる</a><br />
</div>
</div>
