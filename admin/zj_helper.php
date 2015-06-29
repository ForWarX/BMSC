<?php
header("content-type:text/html; charset=utf-8");
if (isset($_POST['action']) && isset($_POST['data'])){
$action = $_POST['action'];
$data = $_POST['data'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"http://scicglobal.com/wp-admin/admin-ajax.php");
curl_setopt($ch, CURLOPT_POST, 1);
$post_fields = array("action" => $action,"data"=>$data);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec($ch);
$json = json_decode($server_output,true);
$str ="提示：如果最后一行是一串字符，请复制该字符，并在“<a href='http://www.kuaidi100.com' target='_blank'>快递100</a>”进行查询。<br/>";
echo $str . $json["content"];
}
?>