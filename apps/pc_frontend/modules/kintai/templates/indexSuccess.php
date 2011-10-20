<div class="partsHeading"><h3><?php echo $member_name; ?>さんの勤怠( <?php echo $year; ?>年<?php echo $month; ?>月)</h3></div>
<div class="block">
<?php
$myear = $year;
$nyear = $year;
$NextMonth = $month + 1;
$PreviousMonth = $month - 1;
if($NextMonth==13){
  $NextMonth = 1;
  $nyear = $year + 1;
}
if($PreviousMonth==0){
  $PreviousMonth = 12;
  $myear = $year - 1;
}

?>
<div id="kintai_pager" style="float: right;">
<a href="./kintai?year=<?php echo $myear; ?>&month=<?php echo $PreviousMonth; ?>&id=<?php echo $viewmember; ?>">前の月</a> | <a href="./kintai?year=<?php echo $nyear; ?>&month=<?php echo $NextMonth; ?>&id=<?php echo $viewmember; ?>">次の月</a> 
<form action="./kintai?id=<?php echo $viewmember; ?>" method="GET"><input type="text" name="year" value="<?php echo $nyear; ?>" size="4" maxlength="4" / >年 <input type="text" name="month" value="<?php echo $NextMonth; ?>" size="4" maxlength="4" />月 <input type="submit" name="submit" value="移動" /></form>
</div>
<br />
<table width="100%">
<tr><td width="15%">年月日</td><td width="10%">作業形態</td><td width="10%">始業時間</td><td width="10%">就業時間</td><td width="10%">休憩</td><td width="10%">実務</td><td width="25%">作業内容</td><td width="10%"></td></tr>

<?php

$html = array();
$detail = array(); 
foreach($line as $entry){
  
  //echo "<tr>";
  $line_list = $entry->getCustom();
  $result = array();
  foreach($line_list as $line2){
    $key = $line2->getColumnName();
    switch($key){
      case "rest":
        $rest = $line2->getText();
        break;
      case "data":
        $data = $line2->getText();
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
    $keitai = substr($data, "0", "1");
    if($keitai=="S"){ $keitai = "出社"; }else{ $keitai = "在宅"; }
    $sh = substr($data, "1", "2");
    $sm = substr($data, "3", "2");
    $start = $sh.":".$sm;
    $eh = substr($data, "5", "2");
    $em = substr($data, "7", "2");
    $end = $eh.":".$em;
    $starttime = $sh * 60 + $sm;
    $endtime = $eh * 60 + $em;
    $jitsumu = $endtime - $starttime - $rest;
    $jh = floor($jitsumu / 60);
    $jm = $jitsumu - $jh * 60;
    if(strlen($jh)==1){ $jh = "0".$jh; }
    if(strlen($jm)==1){ $jm = "0".$jm; }
    $jitsumu = $jh.":".$jm;
    $detail[$d] = array("y" => $y, "m" => $m, "d" => $d, "keitai" => $keitai, "start" => $start, "end" => $end, "rest" => $rest, "jitsumu" => $jitsumu, "comment" => $comment);
  }
}
    if($month==4 || $month==6 || $month==9 || $month==11){
      $maxday = 30;
    }elseif($month==2){
      $maxday = 29;
    }else{
      $maxday = 31;
    }
    for($i=1;$i<=$maxday;$i++){
      if(is_array($detail[$i])){
        $unixtime = mktime(0, 0, 0, $detail[$i]["m"], $detail[$i]["d"], $detail[$i]["y"]);
        $nowtime = time();
        $pasttime = $nowtime - $unixtime;
        if($pasttime>259200 || $currentMember !== $viewmember){
          $html[]= "<tr><td>{$detail[$i]["y"]}/{$detail[$i]["m"]}/{$detail[$i]["d"]}</td><td>{$detail[$i]["keitai"]}</td><td>{$detail[$i]["start"]}</td><td>{$detail[$i]["end"]}</td><td>{$detail[$i]["rest"]}</td><td>{$detail[$i]["jitsumu"]}</td><td>{$detail[$i]["comment"]}</td><td></td></tr>";
        }else{
          $html[]= "<tr><td>{$detail[$i]["y"]}/{$detail[$i]["m"]}/{$detail[$i]["d"]}</td><td>{$detail[$i]["keitai"]}</td><td>{$detail[$i]["start"]}</td><td>{$detail[$i]["end"]}</td><td>{$detail[$i]["rest"]}</td><td>{$detail[$i]["jitsumu"]}</td><td>{$detail[$i]["comment"]}</td><td><a rel=\"prettyPopin\" href=\"./kintai/ajaxEdit?y={$detail[$i]["y"]}&m={$detail[$i]["m"]}&d={$detail[$i]["d"]}\">編集</a></td></tr>";
        }
      }else{
        $unixtime = mktime(0, 0, 0, $month, $i, $year);
        $nowtime = time();
        $pasttime = $nowtime - $unixtime;
        if($pasttime>259200 || $pasttime<0 || $currentMember !== $viewmember){
          $html[]= "<tr><td>{$year}/{$month}/{$i}</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
        }else{
          $html[]= "<tr><td>{$year}/{$month}/{$i}</td><td></td><td></td><td></td><td></td><td></td><td></td><td><a rel=\"prettyPopin\"href=\"./kintai/ajaxRegist?y={$year}&m={$month}&d={$i}\">新規登録</a></td></tr>";
        }
      }
    }



$htmls = implode("", $html);
echo($htmls);
?>

</table> <br />
<br />
<?php
if($currentMember == $viewmember) :
?>
<form action="./kintai/downloadCSV" method="POST">
<input type="hidden" name="year" value="<?php echo $year; ?>" />
<input type="hidden" name="month" value="<?php echo $month; ?>" />
<input type="submit" name="submit" value="<?php echo $year; ?>年<?php echo $month; ?>月の勤怠をダウンロード(CSV)" />
</form>
<br />
<?php
endif;
?>
<br />
</div>
