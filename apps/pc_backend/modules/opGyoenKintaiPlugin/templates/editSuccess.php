<h2>メンバーのワークシートIDを編集する</h2><br />

<span style="color: #F00;"><?php echo $message; ?></span><br />
<?php $id = $member->getId(); ?>
<?php echo $form->renderFormTag("edit?member_id={$id}"); ?>
<table width= 50%>
<tr><td width=25%>メンバーID</td><td width="75%"><?php echo $member->getId(); ?></td></tr>
<tr><td width="25%">ニックネーム</td><td width="75%"><?php echo $member->getName(); ?></td></tr>
<tr><td width="25%">ワークシートID</td><td width="75%"><input type="text" name="wid" value="<?php echo $value; ?>" /></td></tr>
<tr><td width="25%"></td><td width="75%"><input type="submit" name="submit" value="編集する" /></td></tr>
</table>

<br />
<a href="./list">メンバーリストに戻る</a><br />
<br />
