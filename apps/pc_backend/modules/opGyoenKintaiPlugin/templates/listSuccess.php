<h2>メンバーリスト</h2>

<table><tr><td>ID</td><td>ニックネーム</td><td></td></tr>
<?php
foreach($member as $members){
  $id = $members->getID();
  $name = $members->getName();
  echo "<tr><td>{$id}</td><td>{$name}</td><td><a href=\"list?member_id={$id}\">Edit</a></td></tr>";
}
?>
</table>
