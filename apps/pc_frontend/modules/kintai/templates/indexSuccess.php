<div class="partsHeading"><h3><?php echo $member_name; ?>さんの勤怠</h3></div>
<div class="block">
<table width="80%">
<tr><td width="10%">年月日</td><td width="5%">作業形態</td><td width="15%">始業時間</td><td width="15%">就業時間</td><td width="15%">休憩</td><td width="15%">実務</td><td width="20%">作業内容</td><td width="5%"></td></tr>

<?php
$html = array();
$i = 0;
foreach($line as $entry){
  
  //echo "<tr>";
  $line_list = $entry->getCustom();
	  $result = array();
  foreach($line_list as $line2){
    $key = $line2->getColumnName();
    switch($key){
      case "keitai":
        $keitai = $line2->getText();
        if($keitai=="S"){ $keitai = "出社"; }else{ $keitai = "在宅"; }
        break;
      case "start":
        $start = $line2->getText();
        break;
      case "end":
        $end = $line2->getText();
        break;
      case "rest":
        $rest = $line2->getText();
        break;
      case "jitsumu":
        $jitsumu = $line2->getText();
        break;
      case "comment":
        $comment = $line2->getText();
        break;
      case "year":
        $y = $line2->getText();
        break;
      case "month":
        $m = $line2->getText();
        break;
      case "date": 
        $d = $line2->getText();
        break;
    }    
  }
    $unixtime = mktime(0, 0, 0, $m, $d, $y);
    $nowtime = time();
    $pasttime = $nowtime - $unixtime;
    if($pasttime>259200 || $currentMember=!$viewmember){ // 259200(seconds) = 3(days) * 24(hours) * 60(minutes) * 60(seconds)
      $html[] = "<tr id=\"{$i}-1\"><td id=\"{$i}-2\">{$y}/{$m}/{$d}</td><td id=\"{$i}-3\">{$keitai}</td><td id=\"{$i}-4\">{$start}</td><td id=\"{$i}-5\">{$end}</td><td id=\"{$i}-6\">{$rest}</td><td id=\"{$i}-7\">{$jitsumu}</td><td id=\"{$i}-8\">{$comment}</td><td></td></tr>";
    }else{
      $html[] = "<tr><td>{$y}/{$m}/{$d}</td><td>{$keitai}</td><td>{$start}</td><td>{$end}</td><td>{$rest}</td><td>{$jitsumu}</td><td>{$comment}</td><td><a rel=\"prettyPopin\" href=\"./kintai/ajaxEdit?y={$y}&m={$m}&d={$d}\">編集</a></td></tr>";
    }
  $i++;
}
$htmls = implode("", $html);
echo($htmls);
?>

</table>
</div>
