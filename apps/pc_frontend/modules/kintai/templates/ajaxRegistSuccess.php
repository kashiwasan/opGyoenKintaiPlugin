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
<br />
<form action="./kintai/ajaxRegistEasy?y=<?php echo $y; ?>&m=<?php echo $m; ?>&d=<?php echo $d; ?>"><input type="submit" value="素人入力モード" /></form>
<br />
<div id="kintai_explain_open"><a id="kintai_explain_open_link" href="#">▼勤怠入力の説明</a></div>
<div id="kintai_explain" style="display: none;">
<a id="kintai_explain_close_link" href="#">▲説明を閉じる</a><br /
<div style="font-size: 10px;">
<div>　【勤怠入力について】</div>ここにいくつかサンプルを載せますので、これを参考にしながら入力してください。<br />
<u>例１・オフィスに<font color="#ff00ff">出社(S)</font>して、<span style="background-color:rgb(255, 255, 255)"><font color="#ff0000">１０時００分</font></span>入り、<font color="#0000ff">１９時３０分</font>退勤、１時間（<font color="#38761d">６０分</font>）の休憩の場合</u><br />
→ <font size="3"><font color="#ff00ff">S</font><font color="#ff0000">1000</font><font color="#0000ff">1930</font><font color="#38761d">060</font></font>　と入力します。<br />
<br />
<u>例２・<font color="#ffff00" style="background-color:rgb(0, 0, 0)">在宅作業(Z)</font>で<font color="#d5a6bd" style="background-color:rgb(0, 0, 0)">２４時１０分</font>から始めて、<span style="background-color:rgb(0, 0, 0)"><font color="#00ffff">２６時００分</font></span>に終わり、<span style="background-color:rgb(0, 0, 0)"><font color="#e69138">休憩なし(０分)</font></span>の場合</u><br />
→ <font size="3" style="background-color:rgb(0, 0, 0)"><font color="#ffff00">Z</font><font color="#d5a6bd">2410</font><font color="#00ffff">2600</font><font color="#f6b26b">000</font></font>　と入力します。
<br /><br />
<u>例３・オフィスに出社(S)したときは１０時入りの１９時退勤、１時間の休憩、同じ日に在宅(Z)ノマドで<span style="background-color:rgb(0, 0, 0)"><font color="#ffffff">21時</font></span>から始めて<span style="background-color:rgb(0, 0, 0)"><font color="#ffffff">24時</font></span>に終わり<span style="background-color:rgb(0, 0, 0)"><font color="#ffffff">休憩３０分</font></span>の場合</u><br />
→ <font size="3"><u>S10001900060<span style="background-color:rgb(0, 0, 0)"><font color="#ffffff">Z21002400030</font></span></u></font>　と入力します。※自宅作業と在宅作業を同時に入力できます。<br /><font size="3"><u><span style="background-color:rgb(0, 0, 0)"><font color="#ffffff">Z21002400030</font></span>S10001900060</u></font>と入力しても問題ないです。<br />
ヒント：「簡単入力モード」というところから、従来の旧インナーと同じ形式で勤怠入力することもできます。
</div>
<br />
</div>
</div>
