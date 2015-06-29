<?php

/**
 * PBCC 首页文件
 * ============================================================================
 * * 版权所有 2013-2014 加拿大极地熊集团，并保留所有权利。
 * ============================================================================
 * $Id: index.php $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);

$uachar = "/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile)/i";

if(($ua == '' || preg_match($uachar, $ua))&& !strpos(strtolower($_SERVER['REQUEST_URI']),'wap'))
{
    $Loaction = 'mobile/';

    if (!empty($Loaction))
    {
        // ecs_header("Location: $Loaction\n");

        // exit;
    }

}
/*------------------------------------------------------ */
//-- Shopex系统地址转换
/*------------------------------------------------------ */
if (!empty($_GET['gOo']))
{
    if (!empty($_GET['gcat']))
    {
        /* 商品分类。*/
        $Loaction = 'category.php?id=' . $_GET['gcat'];
    }
    elseif (!empty($_GET['acat']))
    {
        /* 文章分类。*/
        $Loaction = 'article_cat.php?id=' . $_GET['acat'];
    }
    elseif (!empty($_GET['goodsid']))
    {
        /* 商品详情。*/
        $Loaction = 'goods.php?id=' . $_GET['goodsid'];
    }
    elseif (!empty($_GET['articleid']))
    {
        /* 文章详情。*/
        $Loaction = 'article.php?id=' . $_GET['articleid'];
    }

    if (!empty($Loaction))
    {
        ecs_header("Location: $Loaction\n");

        exit;
    }
}

//判断是否有ajax请求
$act = !empty($_GET['act']) ? $_GET['act'] : '';
if ($act == 'cat_rec')
{
    $rec_array = array(1 => 'best', 2 => 'new', 3 => 'hot');
    $rec_type = !empty($_REQUEST['rec_type']) ? intval($_REQUEST['rec_type']) : '1';
    $cat_id = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : '0';
    include_once('includes/cls_json.php');
    $json = new JSON;
    $result   = array('error' => 0, 'content' => '', 'type' => $rec_type, 'cat_id' => $cat_id);

    $children = get_children($cat_id);
    $smarty->assign($rec_array[$rec_type] . '_goods',      get_category_recommend_goods($rec_array[$rec_type], $children));    // 推荐商品
    $smarty->assign('cat_rec_sign', 1);
    $result['content'] = $smarty->fetch('library/recommend_' . $rec_array[$rec_type] . '.lbi');
    die($json->encode($result));
}

/*------------------------------------------------------ */
//-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
/*------------------------------------------------------ */
/* 缓存编号 */
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

	$cat['event_file_name'] = 'PBN';
	$dwt_template = 'index.dwt';
	$event_template_folder = 'themes/' . $_CFG['template'];
	$event_template_file   = $event_template_folder . '/' . $cat['event_file_name'] . '.dwt';
	$event_css_file   = $event_template_folder . '/' . $cat['event_file_name'] . '.css';
	$event_header_file   = $event_template_folder . '/library/' . $cat['event_file_name'] . '_page_header.lbi';
	$event_footer_file   = $event_template_folder . '/library/' . $cat['event_file_name'] . '_page_footer.lbi';
	
	$event_file_arry = array();
	$event_file_arry[] = $event_template_file;
	$event_file_arry[] = $event_css_file;
	$event_file_arry[] = $event_header_file;
	$event_file_arry[] = $event_footer_file;
	
	if (!file_exists($event_template_folder)){
		mkdir($event_template_folder);
	}
	
	foreach ($event_file_arry as $value){
		if (!file_exists($value)){
			$my_file = $value;
			$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
		}
	}

$dwt_template = $cat['event_file_name'] . '.dwt';


if (!$smarty->is_cached('index.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
    $smarty->assign('flash_theme',     $_CFG['flash_theme']);  // Flash轮播图片模板

    $smarty->assign('feed_url',        ($_CFG['rewrite'] == 1) ? 'feed.xml' : 'feed.php'); // RSS URL

    $smarty->assign('categories',      get_categories_tree()); // 分类树
    $smarty->assign('helps',           get_shop_help());       // 网店帮助
    $smarty->assign('top_goods',       get_top10());           // 销售排行

    $smarty->assign('best_goods',      get_recommend_goods('best'));    // 推荐商品
    $smarty->assign('new_goods',       get_recommend_goods('new'));     // 最新商品
    $smarty->assign('hot_goods',       get_recommend_goods('hot'));     // 热点文章
    $smarty->assign('promotion_goods', get_promote_goods()); // 特价商品
    $smarty->assign('brand_list',      get_brands());
    $smarty->assign('promotion_info',  get_promotion_info()); // 增加一个动态显示所有促销信息的标签栏

    $smarty->assign('invoice_list',    index_get_invoice_query());  // 发货查询
    $smarty->assign('new_articles',    index_get_new_articles());   // 最新文章
    $smarty->assign('group_buy_goods', index_get_group_buy());      // 团购商品
    $smarty->assign('auction_list',    index_get_auction());        // 拍卖活动
	
	$smarty->assign('playerdb',         get_flash_xml());       // FLASHJS广告
	
	
    $smarty->assign('shop_notice',     $_CFG['shop_notice']);       // 商店公告

    /* 首页主广告设置 */
    $smarty->assign('index_ad',     $_CFG['index_ad']);
    if ($_CFG['index_ad'] == 'cus')
    {
        $sql = 'SELECT ad_type, content, url FROM ' . $ecs->table("ad_custom") . ' WHERE ad_status = 1';
        $ad = $db->getRow($sql, true);
        $smarty->assign('ad', $ad);
    }
	$event_cat_id = 108;

	$main_banner = get_category_banner_xml('/banner/event_banner_on_event_index/'.$event_cat_id.'/event_banner_on_event_index_'.$event_cat_id.'.xml');
	
    /* links */
    $links = index_get_links();
    $smarty->assign('img_links',       $links['img']);
    $smarty->assign('txt_links',       $links['txt']);
    $smarty->assign('data_dir',        DATA_DIR);       // 数据目录
	
    $smarty->assign('css',        $event_css_file);       // css
    $smarty->assign('main_banner', $main_banner);       // 首页banner
	
	
	
	
	
	

    /* 首页推荐分类 */
    $cat_recommend_res = $db->getAll("SELECT c.cat_id, c.cat_name, cr.recommend_type FROM " . $ecs->table("cat_recommend") . " AS cr INNER JOIN " . $ecs->table("category") . " AS c ON cr.cat_id=c.cat_id");
    if (!empty($cat_recommend_res))
    {
        $cat_rec_array = array();
        foreach($cat_recommend_res as $cat_recommend_data)
        {
            $cat_rec[$cat_recommend_data['recommend_type']][] = array('cat_id' => $cat_recommend_data['cat_id'], 'cat_name' => $cat_recommend_data['cat_name']);
        }
        $smarty->assign('cat_rec', $cat_rec);
    }

	/*分类馆*/
	$cat_info_list = $db->getAll("SELECT * FROM ". $ecs->table("category") . " WHERE parent_id = ".$event_cat_id." AND show_banner_in_home_page = 1 ORDER BY sort_order ASC LIMIT 10");

	$category_info = array();
	foreach($cat_info_list as $idx => $row){
		$category_homepage_banner = get_category_banner_xml('/banner/home_page_banner/'.$row['cat_id'].'/home_page_banner_'.$row['cat_id'].'.xml');
		if (empty($category_homepage_banner) === true){
			$category_info[$idx]['banner'][0]['src'] = "";
			$category_info[$idx]['banner'][0]['url'] = "";
		}
		else{
			foreach ($category_homepage_banner as $k => $v){
					$category_info[$idx]['banner'][$k]['src'] = $v['src'];
					$category_info[$idx]['banner'][$k]['url'] = $v['url'];
			}
		}
			$category_info[$idx]['cat_id']      = $row['cat_id'];
			$category_info[$idx]['cat_name']    = $row['cat_name'];
			$category_info[$idx]['sort_order']  = $row['sort_order'];
			$category_info[$idx]['theme_color'] = $row['theme_color'];
			$category_info[$idx]['icon']        = $row['icon'];
			/*if($row['cat_id'] == 999){
				$cat_top_15 = assign_ext_cat_goods($row["cat_id"], 15, 'ORDER BY cat_id ASC');	
			}
			else{
				$cat_top_15 = assign_cat_goods($row["cat_id"], 15, 'wap', 'ORDER BY click_count DESC');
			}
			$category_info[$idx]['top_7'] = array_slice($cat_top_15["goods"],0,7);
			$category_info[$idx]['rank815'] = array_slice($cat_top_15["goods"],7);*/
	}
	
	$perpage = 8;
	$temp_category_goods_info = array();
	foreach ($cat_info_list as $idx => $row){
		$cat_goods_info_list = assign_cat_goods($row["cat_id"], 30, 'wap', 'ORDER BY click_count DESC');
		$paged_result = index_get_panel_promo_array_paged($cat_goods_info_list['goods'],$perpage,4);
		$category_info[$idx]['paged_result'] = $paged_result['arry'];
	}
	
	//PBN RANK
	$pbn_rank_info_list = assign_cat_goods(108, 30, 'wap', 'AND sort_order < 50 ORDER BY click_count DESC');




	//$pbn_rank_info_list = index_get_panel_promo_array_paged($pbn_rank_info_list['goods'],$perpage,4);
	$pbn_rank_info_list = index_get_panel_promo_array_paged_without_seperate_part($pbn_rank_info_list['goods'],4);
	$pbn_rank_info_list = $pbn_rank_info_list['arry'];
	
	//showr($category_info);print "d";
	$smarty->assign('cat_info_list',       $category_info);//showr($category_info);
	$smarty->assign('pbn_rank_info_list',  $pbn_rank_info_list);
	$smarty->assign('all-categories',      get_child_tree(108)); // pbn分类树
//showr(get_categories_tree());
//showr($pbn_rank_info_list);
    
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*品牌榜*/
	$smarty->assign('top_brands', get_top_brands_depend_category($event_cat_id));
	
	/* 页面中的动态内容 */
    assign_dynamic('index');
}

$smarty->display($dwt_template, $cache_id);

function get_flash_xml()
{
    $flashdb = array();
    if (file_exists(ROOT_PATH . DATA_DIR . '/flash_data.xml'))
    {

        // 兼容v2.7.0及以前版本
        if (!preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"\ssort="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/flash_data.xml'), $t, PREG_SET_ORDER))
        {
            preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/flash_data.xml'), $t, PREG_SET_ORDER);
        }

        if (!empty($t))
        {
            foreach ($t as $key => $val)
            {
                $val[4] = isset($val[4]) ? $val[4] : 0;
                $flashdb[] = array('src'=>$val[1],'url'=>$val[2],'text'=>$val[3],'sort'=>$val[4]);
            }
        }
    }
    return $flashdb;
}

function get_catbanner_xml($cat_id)
{
    $flashdb = array();
    if (file_exists(ROOT_PATH . DATA_DIR . '/catbanner/'.$cat_id.'.xml'))
    {

        // 兼容v2.7.0及以前版本
        if (!preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"\ssort="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/catbanner/'.$cat_id.'.xml'), $t, PREG_SET_ORDER))
        {
            preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/catbanner/'.$cat_id.'.xml'), $t, PREG_SET_ORDER);
        }

        if (!empty($t))
        {
            foreach ($t as $key => $val)
            {
                $val[4] = isset($val[4]) ? $val[4] : 0;
                $flashdb[] = array('src'=>$val[1],'url'=>$val[2],'text'=>$val[3],'sort'=>$val[4]);
            }
        }
    }
    return $flashdb;
}	
/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */

/**
 * 调用发货单查询
 *
 * @access  private
 * @return  array
 */
function index_get_invoice_query()
{
    $sql = 'SELECT o.order_sn, o.invoice_no, s.shipping_code FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o' .
            ' LEFT JOIN ' . $GLOBALS['ecs']->table('shipping') . ' AS s ON s.shipping_id = o.shipping_id' .
            " WHERE invoice_no > '' AND shipping_status = " . SS_SHIPPED .
            ' ORDER BY shipping_time DESC LIMIT 10';
    $all = $GLOBALS['db']->getAll($sql);

    foreach ($all AS $key => $row)
    {
        $plugin = ROOT_PATH . 'includes/modules/shipping/' . $row['shipping_code'] . '.php';

        if (file_exists($plugin))
        {
            include_once($plugin);

            $shipping = new $row['shipping_code'];
            $all[$key]['invoice_no'] = $shipping->query((string)$row['invoice_no']);
        }
    }

    clearstatcache();

    return $all;
}

/**
 * 获得最新的文章列表。
 *
 * @access  private
 * @return  array
 */
function index_get_new_articles()
{
    $sql = 'SELECT a.article_id, a.title, ac.cat_name, a.add_time, a.file_url, a.open_type, ac.cat_id, ac.cat_name ' .
            ' FROM ' . $GLOBALS['ecs']->table('article') . ' AS a, ' .
                $GLOBALS['ecs']->table('article_cat') . ' AS ac' .
            ' WHERE a.is_open = 1 AND a.cat_id = ac.cat_id AND ac.cat_type = 1' .
            ' ORDER BY a.article_type DESC, a.add_time DESC LIMIT ' . $GLOBALS['_CFG']['article_number'];
    $res = $GLOBALS['db']->getAll($sql);

    $arr = array();
    foreach ($res AS $idx => $row)
    {
        $arr[$idx]['id']          = $row['article_id'];
        $arr[$idx]['title']       = $row['title'];
        $arr[$idx]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ?
                                        sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
        $arr[$idx]['cat_name']    = $row['cat_name'];
        $arr[$idx]['add_time']    = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);
        $arr[$idx]['url']         = $row['open_type'] != 1 ?
                                        build_uri('article', array('aid' => $row['article_id']), $row['title']) : trim($row['file_url']);
        $arr[$idx]['cat_url']     = build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']);
    }

    return $arr;
}

/**
 * 获得最新的团购活动
 *
 * @access  private
 * @return  array
 */
function index_get_group_buy()
{
    $time = gmtime();
    $limit = get_library_number('group_buy', 'index');

    $group_buy_list = array();
    if ($limit > 0)
    {
        $sql = 'SELECT gb.act_id AS group_buy_id, gb.goods_id, gb.ext_info, gb.goods_name, g.goods_thumb, g.goods_img ' .
                'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS gb, ' .
                    $GLOBALS['ecs']->table('goods') . ' AS g ' .
                "WHERE gb.act_type = '" . GAT_GROUP_BUY . "' " .
                "AND g.goods_id = gb.goods_id " .
                "AND gb.start_time <= '" . $time . "' " .
                "AND gb.end_time >= '" . $time . "' " .
                "AND g.is_delete = 0 " .
                "ORDER BY gb.act_id DESC " .
                "LIMIT $limit" ;
        $res = $GLOBALS['db']->query($sql);

        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            /* 如果缩略图为空，使用默认图片 */
            $row['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
            $row['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);

            /* 根据价格阶梯，计算最低价 */
            $ext_info = unserialize($row['ext_info']);
            $price_ladder = $ext_info['price_ladder'];
            if (!is_array($price_ladder) || empty($price_ladder))
            {
                $row['last_price'] = price_format(0);
            }
            else
            {
                foreach ($price_ladder AS $amount_price)
                {
                    $price_ladder[$amount_price['amount']] = $amount_price['price'];
                }
            }
            ksort($price_ladder);
            $row['last_price'] = price_format(end($price_ladder));
            $row['url'] = build_uri('group_buy', array('gbid' => $row['group_buy_id']));
            $row['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                           sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $row['short_style_name']   = add_style($row['short_name'],'');
            $group_buy_list[] = $row;
        }
    }

    return $group_buy_list;
}

/**
 * 取得拍卖活动列表
 * @return  array
 */
function index_get_auction()
{
    $now = gmtime();
    $limit = get_library_number('auction', 'index');
    $sql = "SELECT a.act_id, a.goods_id, a.goods_name, a.ext_info, g.goods_thumb ".
            "FROM " . $GLOBALS['ecs']->table('goods_activity') . " AS a," .
                      $GLOBALS['ecs']->table('goods') . " AS g" .
            " WHERE a.goods_id = g.goods_id" .
            " AND a.act_type = '" . GAT_AUCTION . "'" .
            " AND a.is_finished = 0" .
            " AND a.start_time <= '$now'" .
            " AND a.end_time >= '$now'" .
            " AND g.is_delete = 0" .
            " ORDER BY a.start_time DESC" .
            " LIMIT $limit";
    $res = $GLOBALS['db']->query($sql);

    $list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $ext_info = unserialize($row['ext_info']);
        $arr = array_merge($row, $ext_info);
        $arr['formated_start_price'] = price_format($arr['start_price']);
        $arr['formated_end_price'] = price_format($arr['end_price']);
        $arr['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr['url'] = build_uri('auction', array('auid' => $arr['act_id']));
        $arr['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                           sub_str($arr['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $arr['goods_name'];
        $arr['short_style_name']   = add_style($arr['short_name'],'');
        $list[] = $arr;
    }

    return $list;
}

/**
 * 获得所有的友情链接
 *
 * @access  private
 * @return  array
 */
function index_get_links()
{
    $sql = 'SELECT link_logo, link_name, link_url FROM ' . $GLOBALS['ecs']->table('friend_link') . ' ORDER BY show_order';
    $res = $GLOBALS['db']->getAll($sql);

    $links['img'] = $links['txt'] = array();

    foreach ($res AS $row)
    {
        if (!empty($row['link_logo']))
        {
            $links['img'][] = array('name' => $row['link_name'],
                                    'url'  => $row['link_url'],
                                    'logo' => $row['link_logo']);
        }
        else
        {
            $links['txt'][] = array('name' => $row['link_name'],
                                    'url'  => $row['link_url']);
        }
    }

    return $links;
}

function get_category_banner_xml($file_name)
{
    $flashdb = array();
    if (file_exists(ROOT_PATH . DATA_DIR . $file_name))
    {
        // 兼容v2.7.0及以前版本
    if (!preg_match_all('/desc="([^"]*)"\ssrc="([^"]+)"\surl="([^"]*)"\sorder="([^"]*)"\sposition="([^"]*)"\sid="([^"]*)"\sshow="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . $file_name), $t, PREG_SET_ORDER))
        {
            preg_match_all('/desc="([^"]*)"\ssrc="([^"]+)"\surl="([^"]*)"\sorder="([^"]*)"\sposition="([^"]*)"\sid="([^"]*)"\sshow="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . $file_name), $t, PREG_SET_ORDER);
        }
//showr($t);
        if (!empty($t))
        {
            foreach ($t as $key => $val)
            {
                //$val[4] = isset($val[4]) ? $val[4] : 0;
                $flashdb[$val[6]] = array('desc'=>$val[1],'src'=>$val[2],'url'=>$val[3],'order'=>$val[4],'position'=>$val[5],'id'=>$val[6],'show'=>$val[7]);
            }
        }
    }//showr($flashdb);
    return $flashdb;
	
}



function index_get_panel_promo_array_paged($arry,$perpage,$number_of_one_row=5){


$result = array();
$result['arry'] = '';
$result['page'] = '';

$group_array = array();
$group = 1;
if (empty($arry) === true){
	$count_arry = 0;
}
else{
	$count_arry= count($arry);
}

$page = ceil($count_arry / $perpage);
$left_number = fmod($count_arry, $perpage);
if ($left_number != 0){
    $empty_number = $perpage - $left_number;

    for ($i = $count_arry; $i < ($count_arry + $empty_number); $i++){
        $arry[$i]['promote_price'] = '';
        $arry[$i]['id'] = '';
        $arry[$i]['name'] = '';
		$arry[$i]['brief'] = '';
		$arry[$i]['market_price'] = '';
		$arry[$i]['short_name'] = '';
		$arry[$i]['shop_price'] = '';
		$arry[$i]['thumb'] = '';
		$arry[$i]['goods_img'] = '';
		$arry[$i]['url'] = '';
    }
}
else{
	for ($i = $count_arry; $i < $perpage; $i++){
        $arry[$i]['promote_price'] = '';
        $arry[$i]['id'] = '';
        $arry[$i]['name'] = '';
		$arry[$i]['brief'] = '';
		$arry[$i]['market_price'] = '';
		$arry[$i]['short_name'] = '';
		$arry[$i]['shop_price'] = '';
		$arry[$i]['thumb'] = '';
		$arry[$i]['goods_img'] = '';
		$arry[$i]['url'] = '';
    }
}
$count_arry = count($arry);
for ($i = 0; $i < $count_arry; $i++){
    $r = fmod($i,$perpage);
    if ($r == 0 && $i != 0){$group = $group + 1;}
    $group_arry[$group][] = $arry[$i];
}


$temp_top_arry = array();
$temp_bottom_arry = array();
$temp_group_arry = array();

foreach ($group_arry as $key => $value){
    foreach ($value as $index => $item){ 
        if($index < $number_of_one_row){
            $temp_top_arry[$key][] = $item;
        }
        else{
            $temp_bottom_arry[$key][] = $item;
        }
    }
    $temp_group_arry[$key]['top'] = $temp_top_arry[$key];
    $temp_group_arry[$key]['bottom'] = $temp_bottom_arry[$key];




$group_arry = $temp_group_arry;
}

$result['arry'] = $group_arry;
$result['page'] = $page;

return $result;

}

function index_get_panel_promo_array_paged_without_seperate_part($arry,$perpage){


$result = array();
$result['arry'] = '';
$result['page'] = '';

$group_array = array();
$group = 1;
if (empty($arry) === true){
	$count_arry = 0;
}
else{
	$count_arry= count($arry);
}

$page = ceil($count_arry / $perpage);
$left_number = fmod($count_arry, $perpage);
if ($left_number != 0){
    $empty_number = $perpage - $left_number;

    for ($i = $count_arry; $i < ($count_arry + $empty_number); $i++){
        $arry[$i]['promote_price'] = '';
        $arry[$i]['id'] = '';
        $arry[$i]['name'] = '';
		$arry[$i]['brief'] = '';
		$arry[$i]['market_price'] = '';
		$arry[$i]['short_name'] = '';
		$arry[$i]['shop_price'] = '';
		$arry[$i]['thumb'] = '';
		$arry[$i]['goods_img'] = '';
		$arry[$i]['url'] = '';
    }
}
else{
	for ($i = $count_arry; $i < $perpage; $i++){
        $arry[$i]['promote_price'] = '';
        $arry[$i]['id'] = '';
        $arry[$i]['name'] = '';
		$arry[$i]['brief'] = '';
		$arry[$i]['market_price'] = '';
		$arry[$i]['short_name'] = '';
		$arry[$i]['shop_price'] = '';
		$arry[$i]['thumb'] = '';
		$arry[$i]['goods_img'] = '';
		$arry[$i]['url'] = '';
    }
}
$count_arry = count($arry);
for ($i = 0; $i < $count_arry; $i++){
    $r = fmod($i,$perpage);
    if ($r == 0 && $i != 0){$group = $group + 1;}
    $group_arry[$group][] = $arry[$i];
}


/*$temp_top_arry = array();
$temp_bottom_arry = array();
$temp_group_arry = array();

foreach ($group_arry as $key => $value){
    foreach ($value as $index => $item){ 
        if($index < $number_of_one_row){
            $temp_top_arry[$key][] = $item;
        }
        else{
            $temp_bottom_arry[$key][] = $item;
        }
    }
    $temp_group_arry[$key]['top'] = $temp_top_arry[$key];
    $temp_group_arry[$key]['bottom'] = $temp_bottom_arry[$key];




$group_arry = $temp_group_arry;
}*/

$result['arry'] = $group_arry;
$result['page'] = $page;

return $result;

}


function get_top_brands_depend_category($cat_id){
	$sql = "SELECT b.brand_name, b.brand_id, t.total, b.brand_logo 
			FROM ".$GLOBALS['ecs']->table('brand'). " AS b, (SELECT cat_id, brand_id, sum(click_count) AS total FROM ".$GLOBALS['ecs']->table('goods')." GROUP BY brand_id) AS t 
			WHERE b.brand_id = t.brand_id AND t.cat_id = ".$cat_id." AND (b.brand_name REGEXP '^[a-zA-Z]+') AND b.is_show = 1 
			ORDER BY t.total desc 
			LIMIT 16";
			
	$raw = $GLOBALS['db']->getAll($sql);
	$edited = array();
	foreach($raw as $idx => $row){
		$edited[$idx]['brand_name'] = $row['brand_name'];
		$edited[$idx]['brand_url']	= build_uri('brand', array('bid'=>$row['brand_id']));
		$edited[$idx]['brand_logo']	= $row['brand_logo'];
	}
	return $edited;
}

?>