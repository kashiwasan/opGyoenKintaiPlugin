
<div class="partsHeading"><h3><?php echo $nickname; ?>さんの今日の勤怠を登録する</h3></div>
<div class="block">
<div id="kintai_loading" style="display: none;"><img src="../opGyoenKintaiPlugin/js/loading.gif" alt="Now Loading..." /></div>
<div id="msg"></div>

<table>
<tr><td>今日の日付</td><td><?php echo date("Y/m/d H:i:s"); ?></td></tr>
<tr>
<td><label for="data">勤怠入力</label></td><td><input type="text" id="kintai_data" name="data" value="" maxlength="9" /></td></tr>
<tr>
<td><label for="rest">休憩時間(分単位)</label></td><td><input type="text" id="kintai_rest" name="rest" value="" maxlength="3"></td></tr>
<tr>
<td><label for="comment">作業内容コメント</label></td><td><textarea name="comment" id="kintai_comment"></textarea></td></tr>
<tr><td></td><td><input type="submit" name="submit" id="kintai_submit" value="確認する" /></td></tr></table>
</div>
