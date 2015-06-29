<?php

	//银联汇率
	function get_xe_rate($curDate,$baseCurrency,$transactionCurrency){
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://www.unionpayintl.com/MainServlet");
		curl_setopt($ch, CURLOPT_POST, 1);
		
		$post_fields = array("curDate" => $curDate, "baseCurrency" => $baseCurrency, "transactionCurrency" => $transactionCurrency, "go" => "BIZTOOL_MERCHANT_PG_exchangeRate");
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch);
		$str = preg_replace('/\s+/', '', $server_output);
		
		$re = "/(?:".$transactionCurrency."&nbsp;=&nbsp;)(.*)(?:&nbsp;".$baseCurrency.")/"; 

		preg_match($re, $str, $matches);
		
		return $matches[1];
	}
	
?>