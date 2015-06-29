<?php

/**
 * PBCC 货到付款插件

 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/cod.php';

if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'cod_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '1';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '0';

    /* 支付费用，由配送决定 */
    $modules[$i]['pay_fee'] = '0';

    /* 作者 */
    $modules[$i]['author']  = 'PBCC网销部';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.pbcc.ca';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 配置信息 */
    $modules[$i]['config']  = array();

    return;
}

/**
 * 类
 */
class cod
{
    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function cod()
    {
    }

    function __construct()
    {
        $this->cod();
    }

    /**
     * 提交函数
     */
    function get_code($order)
    {
        return '<form style="text-align:center;" id="pay_form" name="pay_form" action="http://localhost/beimeishangcheng/respond.php?code=cod" method="post" target="_blank">
		<input type="hidden" name="logId" id="logId" value="'.$order['log_id'].'">
<input type="submit" value="马上支付"></form>';
    }

    /**
     * 处理函数
     */
    function response()
    {
        return;
    }
	function respond()
    {
		print_r($_POST['logId']);
		order_paid($_POST['logId'], PS_PAYED, '');

            //告诉用户交易完成
            return true;
	}
}

?>