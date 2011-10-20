<?php

$detail = array();

$detail[0] = "{$member_id},名前：{$member_name},,勤怠表,,{$year},年,{$month},月,,,,,,,,,,,,\n年,月,日,(出社),始業,時間,終業,時間,休憩,時間,実務,時間,(在宅),始業,時間,終業,時間,休憩,時間,実務,時間";

foreach($line as $entry){
  //echo "<tr>";
  $line_list = $entry->getCustom();
  $result = array();
  foreach($line_list as $line2){
    $key = $line2->getColumnName();
    switch($key){
      case "year":
        $y = $line2->getText();
        break;
      case "month":
        $m = $line2->getText();
        break;
      case "date": 
        $d = $line2->getText();
        break;
      case "ssh":
        $ssh = $line2->getText();
        break;
      case "ssm":
        $ssm = $line2->getText();
        break;
      case "seh":
        $seh = $line2->getText();
        break;
      case "sem":
        $sem = $line2->getText(); 
        break;
      case "srh":
        $srh = $line2->gettext();
        break;
      case "srm":
        $srm = $line2->getText();
        break;
      case "sjh":
        $sjh = $line2->getText();
        break;
      case "sjm":
        $sjm = $line2->getText();
        break;
      case "zsh":
        $zsh = $line2->getText();
        break;
      case "zsm":
        $zsm = $line2->getText();
        break;
      case "zeh":
        $zeh = $line2->getText();
        break;
      case "zem":
        $zem = $line2->getText();
        break;
      case "zrh":
        $zrh = $line2->getText();
        break;
      case "zrm":
        $zrm = $line2->getText();
        break;
      case "zjh":
        $zjh = $line2->getText();
        break;
      case "zjm":
        $zjm = $line2->getText();
        break;      
    }
  }
  $detail[$d] = "{$y},{$m},{$d},,{$ssh},{$ssm},{$seh},{$sem},{$srh},{$srm},{$sjh},{$sjm},,{$zsh},{$zsm},{$zeh},{$zem},{$zrh},{$zrm},{$zjh},{$zjm}";
}
  if($month==4 || $month==6 || $month==9 || $month==11){
    $maxday = 30; 
  }elseif($month==2){
    $maxday = 29; 
  }else{
    $maxday = 31; 
  }   
  for($i=1;$i<=$maxday;$i++){
    if(empty($detail[$i])){
      $detail[$i] = "{$y},{$m},{$d},,,,,,,,,,,,,,,,,,";
    }   
  }

$details = implode("\n", $detail);
echo($details);
?>

