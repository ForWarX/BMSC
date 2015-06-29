<?php




function delete_temp_comment_img(){
	
	$user_id_for_temp_comment_id = $_SESSION['user_id']. '_';
	$temp_comment_id = $_SESSION[''.$user_id_for_temp_comment_id.'temp_comment_id'];
	
	// 删除图片文件 //
	$sql = "SELECT comment_img_url, comment_img_thumb_url FROM " . $GLOBALS['ecs']->table('comment_img') . " WHERE comment_id = '$temp_comment_id'";
	$res = $GLOBALS['db']->getAll($sql);
	foreach ($res as $key => $value){@unlink($value['comment_img_url']);@unlink($value['comment_img_thumb_url']);}

	// 删除数据库 数据 //
	$sql = "DELETE FROM " . $GLOBALS['ecs']->table('comment_img') . " WHERE comment_id = '$temp_comment_id'";
	$res = $GLOBALS['db']->query($sql);
	clear_cache_files();
	return true;
}


function update_temp_comment_img($comment_id){
	if (empty($comment_id)){return false;}
	
	$user_id_for_temp_comment_id = $_SESSION['user_id']. '_';
	$temp_comment_id = $_SESSION[''.$user_id_for_temp_comment_id.'temp_comment_id'];
	$sql = "UPDATE " . $GLOBALS['ecs']->table('comment_img') . " SET comment_id = '$comment_id' WHERE comment_id = '$temp_comment_id'";
    $result = $GLOBALS['db']->query($sql);
	return true;
}























?>