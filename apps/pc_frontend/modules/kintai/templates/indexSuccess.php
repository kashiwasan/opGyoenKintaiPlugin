<div class='partsHeading'><h3><?php echo $member_name; ?>さんの勤怠</h3></div>
<div class='block'>
<?php if (is_null($member_editablelink) && $currentMember==$viewMember) : ?>
<table width='100%'>
<tr><td width='20%'>年月日</td><td width='20%'></td><td width='35%'></td><td width='25%'></td></tr>

<?php

foreach ($data as $line)
{
  $y = substr($line['date'], 0, 4);
  $m = substr($line['date'], 5, 2);
  $d = substr($line['date'], 8, 2);
  if ($line['flg']==1)
  {
    $html[]= '<tr><td>' . $line['date'] . '</td><td>' . $line['meisai'] . '</td><td>' . $line['comment'] . '</td><td><a rel="prettyPopin" href="./kintai/ajaxEdit?y=' . $y . '&m=' . $m . '&d=' . $d . '">編集</a></td></tr>';
  }
  elseif ($line['flg']==0)
  {
    $html[]= '<tr><td>' . $line['date'] . '</td><td></td><td></td><td><a rel="prettyPopin" href="./kintai/ajaxRegist?y=' . $y . '&m=' . $m . '&d=' . $d . '">新規登録</a></td></tr>';
  }
}

$htmls = implode("\n", $html);
echo($htmls);

?>

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
