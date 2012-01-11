<script type="text/javascript">
//<![CDATA[

var kintaiMemberId = "<?php echo $viewMember; ?>";

//]]>
</script>
<script id="kintai-list-template" type="text/x-jquery-tmpl">
<?php if (is_null($member_editablelink) && $currentMember==$viewMember) : ?>
<tr>
  <td>${date}</td>
  <td>出社: ${kintai1} (${rest1})<br />在宅: ${kintai2} (${rest2})</td>
  <td>${comment}</td>
  <td>
  {{if flag=="1"}}
  <a rel="prettyPopinEdit" href="<?php echo url_for('@homepage'); ?>/kintai/ajaxEdit?y=${y}&m=${m}&d=${d}">編集</a>
  {{else}}
  <a rel="prettyPopinRegist" href="<?php echo url_for('@homepage'); ?>/kintai/ajaxRegist?y=${y}&m=${m}&d=${d}">新規登録</a>
  {{/if}}
  </td>
</tr>
<?php else: ?>
<tr><td>${date}</td><td>出社: ${kintai1} (${rest1}) <br /> 在宅: ${kintai2} (${rest2})</td><td>${comment}</td><td></td></tr>
<?php endif; ?>
</script>
<script type="text/javascript" src="<?php echo url_for('@homepage'); ?>/opGyoenKintaiPlugin/js/kintailist.js"></script>
<div class='partsHeading'><h3><?php echo $member_name; ?>さんの勤怠</h3></div>
<div class='block'>
<?php if (is_null($member_editablelink) && $currentMember==$viewMember) : ?>
<div id="kintai-loader" style="width: 100px; float: center; text-align: center;">
<img src="<?php echo url_for('@homepage'); ?>/opGyoenKintaiPlugin/css/images/loader-bouncing-white-gray.gif" alt="loader" />
</div>
<table width='100%' id="kintai-list" data-baseurl="<?php echo url_for('@homepage'); ?>" style="display: none;">
<tr><td width='17%'>Y / m / d</td><td width='25%'>勤怠</td><td width='33%'>作業コメント</td><td width='25%'></td></tr>
</table>
<br />

<?php endif; ?>
<?php if (!is_null($member_editablelink) && $currentMember==$viewMember) : ?>
【社員さん用編集リンク】<br />
以下のURLより直接編集可能です。<br />
<a href='<?php echo $member_editablelink; ?>' target='_blank'>勤怠を編集する</a><br /><br />
<?php endif; ?>
<?php echo $member_name; ?> さんの勤怠明細（スプレッドシート）はこちらです。<br />
<a href='<?php echo $member_splink; ?>' target='_blank'>勤怠を閲覧する</a>
<br /><br />
</div>
