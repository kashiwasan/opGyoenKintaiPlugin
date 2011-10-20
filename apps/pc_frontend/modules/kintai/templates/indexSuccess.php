<div class="partsHeading"><h3><?php echo $member_name; ?>さんの勤怠( <?php echo $year; ?>年<?php echo $month; ?>月)</h3></div>
<div class="block">
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
    /******************************
      case "keitai":
        $keitai = $line2->getText();
        if($keitai=="S"){ $keitai = "出社"; }else{ $keitai = "在宅"; }
        break;
      case "start":
        $start = $line2->getText();
        if(strlen($start)==7){ $start = "0".$start; }
        $start = mb_substr($start, "0", "5");
        break;
      case "end":
        $end = $line2->getText();
        if(strlen($end)==7){ $end = "0".$end; }
        $end = mb_substr($end, "0", "5");
        break;
      case "rest":
        $rest = $line2->getText();
        if(strlen($rest)==7){ $rest = "0".$rest; }
        $rest = mb_substr($rest, "0", "5");
        break;
      case "jitsumu":
        $jitsumu = $line2->getText();
        if(strlen($jitsumu)==7){ $jitsumu = "0".$jitsumu; }
        $jitsumu = mb_substr($jitsumu, "0", "5");
        break;
     ********************************/
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
    $nowday = date('d');
    $nowday = $nowday + 1;
    for($i=1;$i<$nowday;$i++){
      if(!is_array($detail[$i])){
        $unixtime = mktime(0, 0, 0, $detail[$i]["m"], $detail[$i]["d"], $detail[$i]["y"]);
        $nowtime = time();
        $pasttime = $nowtime - $unixtime;
        if($pasttime>259200){
          $html[]= "<tr><td>{$detail[$i]["y"]}/{$detail[$i]["m"]}/{$detail[$i]["d"]}</td><td>{$detail[$i]["keitai"]}</td><td>{$detail[$i]["start"]}</td><td>{$detail[$i]["end"]}</td><td>{$detail[$i]["rest"]}</td><td>{$detail[$i]["jitsumu"]}</td><td>{$detail[$i]["comment"]}</td><td></td></tr>";
        }else{
          $html[]= "<tr><td>{$detail[$i]["y"]}/{$detail[$i]["m"]}/{$detail[$i]["d"]}</td><td>{$detail[$i]["keitai"]}</td><td>{$detail[$i]["start"]}</td><td>{$detail[$i]["end"]}</td><td>{$detail[$i]["rest"]}</td><td>{$detail[$i]["jitsumu"]}</td><td>{$detail[$i]["comment"]}</td><td><a rel=\"prettyPopin\" href=\"./kintai/ajaxEdit?y={$detail[$i]["y"]}&m={$detail[$i]["m"]}&d={$detail[$i]["d"]}\">編集</a></td></tr>";
        }
      }else{
        $Y = date("Y");
        $M = date("m");
        $unixtime = mktime(0, 0, 0, $M, $i, $Y);
        $nowtime = time();
        $pasttime = $nowtime - $unixtime;
        if($pasttime>259200){
          $html[]= "<tr><td>{$Y}/{$M}/{$i}</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
        }else{
          $html[]= "<tr><td>{$Y}/{$M}/{$i}</td><td></td><td></td><td></td><td></td><td></td><td></td><td><a rel=\"prettyPopin\"href=\"./kintai/ajaxRegist?y={$Y}&m={$M}&d={$i}\">新規登録</a></td></tr>";
        }
      }
    }



$htmls = implode("", $html);
echo($htmls);
?>

</table>
</div>
