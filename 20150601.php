<?php

/**
 * PBCC ��ҳ�ļ�
 * ============================================================================
 * * ��Ȩ���� 2013-2014 ���ô󼫵��ܼ��ţ�����������Ȩ����
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
//-- Shopexϵͳ��ַת��
/*------------------------------------------------------ */
if (!empty($_GET['gOo']))
{
    if (!empty($_GET['gcat']))
    {
        /* ��Ʒ���ࡣ*/
        $Loaction = 'category.php?id=' . $_GET['gcat'];
    }
    elseif (!empty($_GET['acat']))
    {
        /* ���·��ࡣ*/
        $Loaction = 'article_cat.php?id=' . $_GET['acat'];
    }
    elseif (!empty($_GET['goodsid']))
    {
        /* ��Ʒ���顣*/
        $Loaction = 'goods.php?id=' . $_GET['goodsid'];
    }
    elseif (!empty($_GET['articleid']))
    {
        /* �������顣*/
        $Loaction = 'article.php?id=' . $_GET['articleid'];
    }

    if (!empty($Loaction))
    {
        ecs_header("Location: $Loaction\n");

        exit;
    }
}

//�ж��Ƿ���ajax����
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
    $smarty->assign($rec_array[$rec_type] . '_goods',      get_category_recommend_goods($rec_array[$rec_type], $children));    // �Ƽ���Ʒ
    $smarty->assign('cat_rec_sign', 1);
    $result['content'] = $smarty->fetch('library/recommend_' . $rec_array[$rec_type] . '.lbi');
    die($json->encode($result));
}

/*------------------------------------------------------ */
//-- �ж��Ƿ���ڻ��棬�����������û��棬��֮��ȡ��Ӧ����
/*------------------------------------------------------ */
/* ������ */
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

if (!$smarty->is_cached('20150601.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // ҳ�����
    $smarty->assign('ur_here',         $position['ur_here']);  // ��ǰλ��

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
    $smarty->assign('flash_theme',     $_CFG['flash_theme']);  // Flash�ֲ�ͼƬģ��

    $smarty->assign('feed_url',        ($_CFG['rewrite'] == 1) ? 'feed.xml' : 'feed.php'); // RSS URL

    $smarty->assign('categories',      get_categories_tree()); // ������
    $smarty->assign('helps',           get_shop_help());       // �������
    $smarty->assign('top_goods',       get_top10());           // ��������

    $smarty->assign('best_goods',      get_recommend_goods('best'));    // �Ƽ���Ʒ
    $smarty->assign('new_goods',       get_recommend_goods('new'));     // ������Ʒ
    $smarty->assign('hot_goods',       get_recommend_goods('hot'));     // �ȵ�����
    $smarty->assign('promotion_goods', get_promote_goods()); // �ؼ���Ʒ
    $smarty->assign('brand_list',      get_brands());
    $smarty->assign('promotion_info',  get_promotion_info()); // ����һ����̬��ʾ���д�����Ϣ�ı�ǩ��

    $smarty->assign('invoice_list',    index_get_invoice_query());  // ������ѯ
    $smarty->assign('new_articles',    index_get_new_articles());   // ��������
    $smarty->assign('group_buy_goods', index_get_group_buy());      // �Ź���Ʒ
    $smarty->assign('auction_list',    index_get_auction());        // �����
	
	$smarty->assign('playerdb',         get_flash_xml());       // FLASHJS���
	
    $smarty->assign('shop_notice',     $_CFG['shop_notice']);       // �̵깫��

    /* ��ҳ��������� */
    $smarty->assign('index_ad',     $_CFG['index_ad']);
    if ($_CFG['index_ad'] == 'cus')
    {
        $sql = 'SELECT ad_type, content, url FROM ' . $ecs->table("ad_custom") . ' WHERE ad_status = 1';
        $ad = $db->getRow($sql, true);
        $smarty->assign('ad', $ad);
    }

    /* links */
    $links = index_get_links();
    $smarty->assign('img_links',       $links['img']);
    $smarty->assign('txt_links',       $links['txt']);
    $smarty->assign('data_dir',        DATA_DIR);       // ����Ŀ¼
	$smarty->assign('all-categories',       get_categories_tree()); // ������
	
	$cat['event_file_name'] = '20150601';
	$event_template_folder = 'themes/' . $_CFG['template'];
	$event_css_file   = $event_template_folder . '/' . $cat['event_file_name'] . '.css';
    $smarty->assign('event_css_path',        $event_css_file);       // css

    /* ��ҳ�Ƽ����� */
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

	/*�����*/
	$cat_info_list = $db->getAll("SELECT * FROM ". $ecs->table("category") . " WHERE show_banner_in_home_page = 1 ORDER BY sort_order ASC LIMIT 10");

	$goods = array();
	foreach($cat_info_list as $idx => $row){
		$category_homepage_banner = get_category_banner_xml('/banner/home_page_banner/'.$row['cat_id'].'/home_page_banner_'.$row['cat_id'].'.xml');
		foreach ($category_homepage_banner as $k => $v){
				$goods[$idx]['banner'][$k]['src'] = $v['src'];
				$goods[$idx]['banner'][$k]['url'] = $v['url'];		
		}
			$goods[$idx]['cat_id'] = $row['cat_id'];
			$goods[$idx]['cat_name'] = $row['cat_name'];
			$goods[$idx]['sort_order'] = $row['sort_order'];
			$goods[$idx]['theme_color'] = $row['theme_color'];
			$goods[$idx]['icon']        = $row['icon'];
			//$goods[$idx]['cat_banners'] = $banners;
			if($row['cat_id'] == 999){
				$cat_top_15 = assign_ext_cat_goods($row["cat_id"], 15, 'ORDER BY cat_id ASC');	
			}
			else{
				$cat_top_15 = assign_cat_goods($row["cat_id"], 15, 'wap', 'ORDER BY click_count DESC');
			}
			$goods[$idx]['top_7'] = array_slice($cat_top_15["goods"],0,7);
			$goods[$idx]['rank815'] = array_slice($cat_top_15["goods"],7);
		
	}
	$smarty->assign('cat_info_list',       $goods);
//showr(get_categories_tree());
//showr($goods);
    
	/*Ʒ�ư�*/
	$smarty->assign('top_brands', get_top_brands());
	
	/* ҳ���еĶ�̬���� */
    assign_dynamic('index');
}

$smarty->display('20150601.dwt', $cache_id);

function get_flash_xml()
{
    $flashdb = array();
    if (file_exists(ROOT_PATH . DATA_DIR . '/flash_data.xml'))
    {

        // ����v2.7.0����ǰ�汾
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

        // ����v2.7.0����ǰ�汾
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
 * ���÷�������ѯ
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
 * ������µ������б���
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
 * ������µ��Ź��
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
            /* �������ͼΪ�գ�ʹ��Ĭ��ͼƬ */
            $row['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
            $row['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);

            /* ���ݼ۸���ݣ�������ͼ� */
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
 * ȡ��������б�
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
 * ������е���������
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
        // ����v2.7.0����ǰ�汾
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

?>