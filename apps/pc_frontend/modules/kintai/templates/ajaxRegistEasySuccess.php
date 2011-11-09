<div id="easymode">
<div class="partsHeading"><h3><?php echo $nickname; ?>さんの今日の勤怠を登録する</h3></div>
<div class="block">
<form action="./kintai/ajaxRegist" method="post">
<span style="color: #F00">（素人入力モード）</span>
<div id="kintai_easy_regist_loading" style="display: none;"><img src="./opGyoenKintaiPlugin/js/loading.gif" alt="Now Loading..." /></div>
<div id="easy_regist_msg"></div>
<input type="hidden" name="y" value="<?php echo $y; ?>" />

<table>
<tr><td>勤怠入力する日付</td><td><?php echo $y; ?>年 <?php echo $m; ?>月 <?php echo $d; ?>日</td></tr>
<input type="hidden" name="m" value="<?php echo $m; ?>" />
<input type="hidden" name="d" value="<?php echo $d; ?>" />
<tr>
<td>形態</td>
<td><select name="keitai" id="kintai_easy_regist_keitai"><option value="S" selected>出社</option><option value="Z">在宅</option></select></td>
</tr>

<tr><td>始業時間</td>
<td>
<select name="sh" id="kintai_easy_regist_sh">
<option value="05">05</option>
<option value="06">06</option>
<option value="07">07</option>
<option value="08">08</option>
<option value="09">09</option>
<option value="10" selected="selected">10</option>
<option value="11">11</option>
<option value="12">12</option>
<option value="13">13</option>
<option value="14">14</option>
<option value="15">15</option>
<option value="16">16</option>
<option value="17">17</option>
<option value="18">18</option>
<option value="19">19</option>
<option value="20">20</option>
<option value="21">21</option>
<option value="22">22</option>
<option value="23">23</option>
<option value="24">24</option>
<option value="25">25</option>
<option value="26">26</option>
<option value="27">27</option>
</select> : 
<select name="sm" id="kintai_easy_regist_sm">
<option value="00" selected="selected">00</option>
<option value="05">05</option>
<option value="10">10</option>
<option value="15">15</option>
<option value="20">20</option>
<option value="25">25</option>
<option value="30">30</option>
<option value="35">35</option>
<option value="40">40</option>
<option value="45">45</option>
<option value="50">50</option>
<option value="55">55</option>
</select>
</td></tr>

<tr>
<td>終業時間</td><td>
<select name="eh" id="kintai_easy_regist_eh">
<option value="05">05</option>
<option value="06">06</option>
<option value="07">07</option>
<option value="08">08</option>
<option value="09">09</option>
<option value="10">10</option>
<option value="11">11</option>
<option value="12">12</option>
<option value="13">13</option>
<option value="14">14</option>
<option value="15">15</option>
<option value="16">16</option>
<option value="17">17</option>
<option value="18">18</option>
<option value="19" selected="selected">19</option>
<option value="20">20</option>
<option value="21">21</option>
<option value="22">22</option>
<option value="23">23</option>
<option value="24">24</option>
<option value="25">25</option>
<option value="26">26</option>
<option value="27">27</option>
</select> : 
<select name="em" id="kintai_easy_regist_em">
<option value="00" selected="selected">00</option>
<option value="05">05<option>
<option value="10">10</option>
<option value="15">15</option>
<option value="20">20</option>
<option value="25">25</option>
<option value="30">30</option>
<option value="35">35</option>
<option value="40">40</option>
<option value="45">45</option>
<option value="50">50</option>
<option value="55">55</option>
</select>
</td></tr>

<tr>
<td>休憩時間(分)</td><td>
<select name="rest" id="kintai_easy_regist_rest">
<option value="000">00</option>
<option value="005">05</option>
<option value="010">10</option>
<option value="015">15</option>
<option value="020">20</option>
<option value="025">25</option>
<option value="030">30</option>
<option value="035">35</option>
<option value="040">40</option>
<option value="045">45</option>
<option value="050">50</option>
<option value="055">55</option>
<option value="060" selected="selected">60</option>
<option value="065">65</option>
<option value="070">70</option>
<option value="075">75</option>
<option value="080">80</option>
<option value="085">85</option>
<option value="090">90</option>
<option value="095">95</option>
<option value="100">100</option>
<option value="105">105</option>
<option value="110">110</option>
<option value="115">115</option>
<option value="120">120</option>
<option value="125">125</option>
<option value="130">130</option>
<option value="135">135</option>
<option value="140">140</option>
<option value="145">145</option>
<option value="150">150</option>
</select>
</td></tr><tr>
<td>作業内容</td><td><textarea name="comment" id="kintai_easy_regist_comment"></textarea></td></tr>
<tr><td></td><td><input type="submit" name="submit" id="kintai_easy_regist_submit" value="確認する" /></td></tr></table>
</form>
<a href="./kintai/ajaxRegist?y=<?php echo $y; ?>&m=<?php echo $m; ?>&d=<?php echo $d; ?>" rel="internal">通常入力に戻る</a> <br />
</div>
</div>
<div id="normalmode"></div>

