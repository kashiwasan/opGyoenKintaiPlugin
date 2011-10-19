<h2>メンバーリスト</h2>

<table><tr><td>ID</td><td>ニックネーム</td><td></td></tr>
<?php
foreach($members as $member){
  $id = $member->getId();
  $name = $member->getName();
  echo "<tr><td>{$id}</td><td>{$name}</td><td><a href=\"./edit?member_id={$id}\">Edit</a></td></tr>";
}
?>
</table>

<br />

<br />

