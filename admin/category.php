<?php

/**
 * PBCC 商品分类管理程序
 * ============================================================================
 * * 版权所有 2013-2014 加拿大极地熊集团，并保留所有权利。
 * ============================================================================
 * $Id: category.php $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . '/includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table("category"), $db, 'cat_id', 'cat_name');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

/*------------------------------------------------------ */
//-- 商品分类列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 获取分类列表 */
    $cat_list = cat_list(0, 0, false);

    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['03_category_list']);
    $smarty->assign('action_link',  array('href' => 'category.php?act=add', 'text' => $_LANG['04_category_add']));
    $smarty->assign('full_page',    1);

    $smarty->assign('cat_info',     $cat_list);

    /* 列表页面 */
    assign_query_info();
    $smarty->display('category_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $cat_list = cat_list(0, 0, false);
    $smarty->assign('cat_info',     $cat_list);

    make_json_result($smarty->fetch('category_list.htm'));
}
/*------------------------------------------------------ */
//-- 添加商品分类
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add')
{
    /* 权限检查 */
    admin_priv('cat_manage');



    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['04_category_add']);
    $smarty->assign('action_link',  array('href' => 'category.php?act=list', 'text' => $_LANG['03_category_list']));

    $smarty->assign('goods_type_list',  goods_type_list(0)); // 取得商品类型
    $smarty->assign('attr_list',        get_attr_list()); // 取得商品属性

    $smarty->assign('cat_select',   cat_list(0, 0, true));
    $smarty->assign('form_act',     'insert');
    $smarty->assign('cat_info',     array('is_show' => 1));



    /* 显示页面 */
    assign_query_info();
    $smarty->display('category_info.htm');
}

/*------------------------------------------------------ */
//-- 商品分类添加时的处理
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert')
{
    /* 权限检查 */
    admin_priv('cat_manage');

    /* 初始化变量 */
    $cat['cat_id']       = !empty($_POST['cat_id'])       ? intval($_POST['cat_id'])     : 0;
    $cat['parent_id']    = !empty($_POST['parent_id'])    ? intval($_POST['parent_id'])  : 0;
    $cat['sort_order']   = !empty($_POST['sort_order'])   ? intval($_POST['sort_order']) : 0;
    $cat['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
    $cat['cat_desc']     = !empty($_POST['cat_desc'])     ? $_POST['cat_desc']           : '';
    $cat['measure_unit'] = !empty($_POST['measure_unit']) ? trim($_POST['measure_unit']) : '';
    $cat['cat_name']     = !empty($_POST['cat_name'])     ? trim($_POST['cat_name'])     : '';
    $cat['show_in_nav']  = !empty($_POST['show_in_nav'])  ? intval($_POST['show_in_nav']): 0;
    $cat['style']        = !empty($_POST['style'])        ? trim($_POST['style'])        : '';
    $cat['is_show']      = !empty($_POST['is_show'])      ? intval($_POST['is_show'])    : 0;
    $cat['grade']        = !empty($_POST['grade'])        ? intval($_POST['grade'])      : 0;
    $cat['filter_attr']  = !empty($_POST['filter_attr'])  ? implode(',', array_unique(array_diff($_POST['filter_attr'],array(0)))) : 0;

    $cat['cat_recommend']  = !empty($_POST['cat_recommend'])  ? $_POST['cat_recommend'] : array();
	
	$cat['theme_color']     = $_POST['theme_color']!="FFFFFF"     ? "#".$_POST['theme_color']     : '#361F5B';
	$cat['show_banner_in_home_page']      = $_POST['show_banner_in_home_page'];
	$cat['show_banner_in_category_page']  = $_POST['show_banner_in_category_page'];
	$cat['is_event_area']                 = $_POST['is_event_area'];
	$cat['event_file_name']                 = $_POST['event_file_name'];	
	image_validator_by_file($_FILES['cat_icon_img']);
	if (cat_exists($cat['cat_name'], $cat['parent_id']))
    {
        /* 同级别下不能有重复的分类名称 */
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG['catname_exist'], 0, $link);
    }

    if($cat['grade'] > 10 || $cat['grade'] < 0)
    {
        /* 价格区间数超过范围 */
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG['grade_error'], 0, $link);
    }

    /* 入库的操作 */
    if ($db->autoExecute($ecs->table('category'), $cat) !== false)
    {
        $cat_id = $db->insert_id();
        if($cat['show_in_nav'] == 1)
        {
            $vieworder = $db->getOne("SELECT max(vieworder) FROM ". $ecs->table('nav') . " WHERE type = 'middle'");
            $vieworder += 2;
            //显示在自定义导航栏中
            $sql = "INSERT INTO " . $ecs->table('nav') .
                " (name,ctype,cid,ifshow,vieworder,opennew,url,type)".
                " VALUES('" . $cat['cat_name'] . "', 'c', '".$db->insert_id()."','1','$vieworder','0', '" . build_uri('category', array('cid'=> $cat_id), $cat['cat_name']) . "','middle')";
            $db->query($sql);
        }
        insert_cat_recommend($cat['cat_recommend'], $cat_id);
		if (($icon = category_icon_handler($cat_id,$_FILES['cat_icon_img']))!== false){
			$cat['icon'] = $icon;
		}
		//update icon path
		$sql = "UPDATE " . $ecs->table('category') . " SET icon = '" . $cat['icon'] . "' WHERE cat_id = " . $cat_id . "";
		$db->query($sql);
		
		
        admin_log($_POST['cat_name'], 'add', 'category');   // 记录管理员操作
        clear_cache_files();    // 清除缓存

        /*添加链接*/
        $link[0]['text'] = $_LANG['continue_add'];
        $link[0]['href'] = 'category.php?act=add';

        $link[1]['text'] = $_LANG['back_list'];
        $link[1]['href'] = 'category.php?act=list';

        sys_msg($_LANG['catadd_succed'], 0, $link);
    }
 }

/*------------------------------------------------------ */
//-- 编辑商品分类信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit')
{
    admin_priv('cat_manage');   // 权限检查
    $cat_id = intval($_REQUEST['cat_id']);
    $cat_info = get_cat_info($cat_id);  // 查询分类信息数据
	//showr($cat_info);
    $attr_list = get_attr_list();
    $filter_attr_list = array();

    if ($cat_info['filter_attr'])
    {
        $filter_attr = explode(",", $cat_info['filter_attr']);  //把多个筛选属性放到数组中

        foreach ($filter_attr AS $k => $v)
        {
            $attr_cat_id = $db->getOne("SELECT cat_id FROM " . $ecs->table('attribute') . " WHERE attr_id = '" . intval($v) . "'");
            $filter_attr_list[$k]['goods_type_list'] = goods_type_list($attr_cat_id);  //取得每个属性的商品类型
            $filter_attr_list[$k]['filter_attr'] = $v;
            $attr_option = array();

            foreach ($attr_list[$attr_cat_id] as $val)
            {
                $attr_option[key($val)] = current ($val);
            }

            $filter_attr_list[$k]['option'] = $attr_option;
        }

        $smarty->assign('filter_attr_list', $filter_attr_list);
    }
    else
    {
        $attr_cat_id = 0;
    }
	
	//拿到首页分类馆banner
	$img_list = get_banner_xml('/banner/home_page_banner/'.$cat_id.'/home_page_banner_'.$cat_id.'.xml');
	//拿到分类页banner
	$img_list2 = get_banner_xml('/banner/category_page_banner/'.$cat_id.'/category_page_banner_'.$cat_id.'.xml');
	//活动专区广告banner(放于北美商城首页)
	$img_list3 = get_banner_xml('/banner/event_banner/'.$cat_id.'/event_banner_'.$cat_id.'.xml');	
	//活动专区广告banner(放于专区首页)
	$img_list4 = get_banner_xml('/banner/event_banner_on_event_index/'.$cat_id.'/event_banner_on_event_index_'.$cat_id.'.xml');
	
	$smarty->assign('img_list',   $img_list);
	$smarty->assign('img_list2',   $img_list2);
	$smarty->assign('img_list3',   $img_list3);
	$smarty->assign('img_list4',   $img_list4);
	/* 模板赋值 */
    $smarty->assign('attr_list',        $attr_list); // 取得商品属性
    $smarty->assign('attr_cat_id',      $attr_cat_id);
    $smarty->assign('ur_here',     $_LANG['category_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['03_category_list'], 'href' => 'category.php?act=list'));

    //分类是否存在首页推荐
    $res = $db->getAll("SELECT recommend_type FROM " . $ecs->table("cat_recommend") . " WHERE cat_id=" . $cat_id);
    if (!empty($res))
    {
        $cat_recommend = array();
        foreach($res as $data)
        {
            $cat_recommend[$data['recommend_type']] = 1;
        }
        $smarty->assign('cat_recommend', $cat_recommend);
    }

    $smarty->assign('cat_info',    $cat_info);
    $smarty->assign('form_act',    'update');
    $smarty->assign('cat_select',  cat_list(0, $cat_info['parent_id'], true));
    $smarty->assign('goods_type_list',  goods_type_list(0)); // 取得商品类型

    /* 显示页面 */
    assign_query_info();
    $smarty->display('category_info.htm');
}

elseif($_REQUEST['act'] == 'add_category')
{
    $parent_id = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
    $category = empty($_REQUEST['cat']) ? '' : json_str_iconv(trim($_REQUEST['cat']));

    if(cat_exists($category, $parent_id))
    {
        make_json_error($_LANG['catname_exist']);
    }
    else
    {
        $sql = "INSERT INTO " . $ecs->table('category') . "(cat_name, parent_id, is_show)" .
               "VALUES ( '$category', '$parent_id', 1)";

        $db->query($sql);
        $category_id = $db->insert_id();

        $arr = array("parent_id"=>$parent_id, "id"=>$category_id, "cat"=>$category);

        clear_cache_files();    // 清除缓存

        make_json_result($arr);
    }
}

/*------------------------------------------------------ */
//-- 编辑商品分类信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'update')
{
    /* 权限检查 */
    admin_priv('cat_manage');

    /* 初始化变量 */
    $cat_id              = !empty($_POST['cat_id'])       ? intval($_POST['cat_id'])     : 0;
    $old_cat_name        = $_POST['old_cat_name'];
    $cat['parent_id']    = !empty($_POST['parent_id'])    ? intval($_POST['parent_id'])  : 0;
    $cat['sort_order']   = !empty($_POST['sort_order'])   ? intval($_POST['sort_order']) : 0;
    $cat['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
    $cat['cat_desc']     = !empty($_POST['cat_desc'])     ? $_POST['cat_desc']           : '';
    $cat['measure_unit'] = !empty($_POST['measure_unit']) ? trim($_POST['measure_unit']) : '';
    $cat['cat_name']     = !empty($_POST['cat_name'])     ? trim($_POST['cat_name'])     : '';
    $cat['is_show']      = !empty($_POST['is_show'])      ? intval($_POST['is_show'])    : 0;
    $cat['show_in_nav']  = !empty($_POST['show_in_nav'])  ? intval($_POST['show_in_nav']): 0;
    $cat['style']        = !empty($_POST['style'])        ? trim($_POST['style'])        : '';
    $cat['grade']        = !empty($_POST['grade'])        ? intval($_POST['grade'])      : 0;
    $cat['filter_attr']  = !empty($_POST['filter_attr'])  ? implode(',', array_unique(array_diff($_POST['filter_attr'],array(0)))) : 0;
    $cat['cat_recommend']  = !empty($_POST['cat_recommend'])  ? $_POST['cat_recommend'] : array();
	
	$cat['theme_color']     = $_POST['theme_color']!="FFFFFF"     ? "#".$_POST['theme_color']     : '#361F5B';
	$cat['show_banner_in_home_page']      = $_POST['show_banner_in_home_page'];
	$cat['show_banner_in_category_page']  = $_POST['show_banner_in_category_page'];
			
	if (($icon = category_icon_handler($cat_id,$_FILES['cat_icon_img']))!== false){
		$cat['icon'] = $icon;
	}
	$cat['is_event_area']                 =  $_POST['is_event_area'];
	$cat['event_file_name']                 =  $_POST['event_file_name'];
	
	

	foreach ($_FILES['img_file']['error'] as $k => $v){
		image_validator_by_individual($v,$_FILES['img_file']['type'][$k]);
	}

	
	foreach ($_FILES['img_file2']['error'] as $k => $v){
		image_validator_by_individual($v,$_FILES['img_file2']['type'][$k]);
	}
	//showr($_REQUEST);return true;
	banner_handler($_REQUEST,$cat_id,true);//update first;
	banner_handler($_REQUEST,$cat_id);

	
	/* 判断分类名是否重复 */

    if ($cat['cat_name'] != $old_cat_name)
    {
        if (cat_exists($cat['cat_name'],$cat['parent_id'], $cat_id))
        {
           $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
           sys_msg($_LANG['catname_exist'], 0, $link);
        }
    }

    /* 判断上级目录是否合法 */
    $children = array_keys(cat_list($cat_id, 0, false));     // 获得当前分类的所有下级分类
    if (in_array($cat['parent_id'], $children))
    {
        /* 选定的父类是当前分类或当前分类的下级分类 */
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG["is_leaf_error"], 0, $link);
    }

    if($cat['grade'] > 10 || $cat['grade'] < 0)
    {
        /* 价格区间数超过范围 */
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG['grade_error'], 0, $link);
    }

    $dat = $db->getRow("SELECT cat_name, show_in_nav FROM ". $ecs->table('category') . " WHERE cat_id = '$cat_id'");

    if ($db->autoExecute($ecs->table('category'), $cat, 'UPDATE', "cat_id='$cat_id'"))
    {
        if($cat['cat_name'] != $dat['cat_name'])
        {
            //如果分类名称发生了改变
            $sql = "UPDATE " . $ecs->table('nav') . " SET name = '" . $cat['cat_name'] . "' WHERE ctype = 'c' AND cid = '" . $cat_id . "' AND type = 'middle'";
            $db->query($sql);
        }
        if($cat['show_in_nav'] != $dat['show_in_nav'])
        {
            //是否显示于导航栏发生了变化
            if($cat['show_in_nav'] == 1)
            {
                //显示
                $nid = $db->getOne("SELECT id FROM ". $ecs->table('nav') . " WHERE ctype = 'c' AND cid = '" . $cat_id . "' AND type = 'middle'");
                if(empty($nid))
                {
                    //不存在
                    $vieworder = $db->getOne("SELECT max(vieworder) FROM ". $ecs->table('nav') . " WHERE type = 'middle'");
                    $vieworder += 2;
                    $uri = build_uri('category', array('cid'=> $cat_id), $cat['cat_name']);

                    $sql = "INSERT INTO " . $ecs->table('nav') . " (name,ctype,cid,ifshow,vieworder,opennew,url,type) VALUES('" . $cat['cat_name'] . "', 'c', '$cat_id','1','$vieworder','0', '" . $uri . "','middle')";
                }
                else
                {
                    $sql = "UPDATE " . $ecs->table('nav') . " SET ifshow = 1 WHERE ctype = 'c' AND cid = '" . $cat_id . "' AND type = 'middle'";
                }
                $db->query($sql);
            }
            else
            {
                //去除
                $db->query("UPDATE " . $ecs->table('nav') . " SET ifshow = 0 WHERE ctype = 'c' AND cid = '" . $cat_id . "' AND type = 'middle'");
            }
        }

        //更新首页推荐
        insert_cat_recommend($cat['cat_recommend'], $cat_id);
        /* 更新分类信息成功 */
        clear_cache_files(); // 清除缓存
        admin_log($_POST['cat_name'], 'edit', 'category'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => $_LANG['back_list'], 'href' => 'category.php?act=list');
        sys_msg($_LANG['catedit_succed'], 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 批量转移商品分类页面
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'move')
{
    /* 权限检查 */
    admin_priv('cat_drop');

    $cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;

    /* 模板赋值 */
    $smarty->assign('ur_here',     $_LANG['move_goods']);
    $smarty->assign('action_link', array('href' => 'category.php?act=list', 'text' => $_LANG['03_category_list']));

    $smarty->assign('cat_select', cat_list(0, $cat_id, true));
    $smarty->assign('form_act',   'move_cat');

    /* 显示页面 */
    assign_query_info();
    $smarty->display('category_move.htm');
}

/*------------------------------------------------------ */
//-- 处理批量转移商品分类的处理程序
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'move_cat')
{
    /* 权限检查 */
    admin_priv('cat_drop');

    $cat_id        = !empty($_POST['cat_id'])        ? intval($_POST['cat_id'])        : 0;
    $target_cat_id = !empty($_POST['target_cat_id']) ? intval($_POST['target_cat_id']) : 0;

    /* 商品分类不允许为空 */
    if ($cat_id == 0 || $target_cat_id == 0)
    {
        $link[] = array('text' => $_LANG['go_back'], 'href' => 'category.php?act=move');
        sys_msg($_LANG['cat_move_empty'], 0, $link);
    }

    /* 更新商品分类 */
    $sql = "UPDATE " .$ecs->table('goods'). " SET cat_id = '$target_cat_id' ".
           "WHERE cat_id = '$cat_id'";
    if ($db->query($sql))
    {
        /* 清除缓存 */
        clear_cache_files();

        /* 提示信息 */
        $link[] = array('text' => $_LANG['go_back'], 'href' => 'category.php?act=list');
        sys_msg($_LANG['move_cat_success'], 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('sort_order' => $val)))
    {
        clear_cache_files(); // 清除缓存
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 编辑数量单位
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'edit_measure_unit')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = json_str_iconv($_POST['val']);

    if (cat_update($id, array('measure_unit' => $val)))
    {
        clear_cache_files(); // 清除缓存
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'edit_grade')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if($val > 10 || $val < 0)
    {
        /* 价格区间数超过范围 */
        make_json_error($_LANG['grade_error']);
    }

    if (cat_update($id, array('grade' => $val)))
    {
        clear_cache_files(); // 清除缓存
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示在导航栏
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'toggle_show_in_nav')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('show_in_nav' => $val)) != false)
    {
        if($val == 1)
        {
            //显示
            $vieworder = $db->getOne("SELECT max(vieworder) FROM ". $ecs->table('nav') . " WHERE type = 'middle'");
            $vieworder += 2;
            $catname = $db->getOne("SELECT cat_name FROM ". $ecs->table('category') . " WHERE cat_id = '$id'");
            //显示在自定义导航栏中
            $_CFG['rewrite'] = 0;
            $uri = build_uri('category', array('cid'=> $id), $catname);

            $nid = $db->getOne("SELECT id FROM ". $ecs->table('nav') . " WHERE ctype = 'c' AND cid = '" . $id . "' AND type = 'middle'");
            if(empty($nid))
            {
                //不存在
                $sql = "INSERT INTO " . $ecs->table('nav') . " (name,ctype,cid,ifshow,vieworder,opennew,url,type) VALUES('" . $catname . "', 'c', '$id','1','$vieworder','0', '" . $uri . "','middle')";
            }
            else
            {
                $sql = "UPDATE " . $ecs->table('nav') . " SET ifshow = 1 WHERE ctype = 'c' AND cid = '" . $id . "' AND type = 'middle'";
            }
            $db->query($sql);
        }
        else
        {
            //去除
            $db->query("UPDATE " . $ecs->table('nav') . "SET ifshow = 0 WHERE ctype = 'c' AND cid = '" . $id . "' AND type = 'middle'");
        }
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'toggle_is_show')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('is_show' => $val)) != false)
    {
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}
/*------------------------------------------------------ */
//-- 切换是否设置为分类馆
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'toggle_show_banner_in_home_page')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('show_banner_in_home_page' => $val)) != false)
    {
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 切换是否设置为分类页
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'toggle_show_banner_in_category_page')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('show_banner_in_category_page' => $val)) != false)
    {
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 切换是否设置为活动专区
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'toggle_is_event_area')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('is_event_area' => $val)) != false)
    {
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 删除商品分类
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'remove')
{
    check_authz_json('cat_manage');

    /* 初始化分类ID并取得分类名称 */
    $cat_id   = intval($_GET['id']);
    $cat_name = $db->getOne('SELECT cat_name FROM ' .$ecs->table('category'). " WHERE cat_id='$cat_id'");

    /* 当前分类下是否有子分类 */
    $cat_count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('category'). " WHERE parent_id='$cat_id'");

    /* 当前分类下是否存在商品 */
    $goods_count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('goods'). " WHERE cat_id='$cat_id'");

    /* 如果不存在下级子分类和商品，则删除之 */
    if ($cat_count == 0 && $goods_count == 0)
    {
        /* 删除分类 */
        $sql = 'DELETE FROM ' .$ecs->table('category'). " WHERE cat_id = '$cat_id'";
        if ($db->query($sql))
        {
            $db->query("DELETE FROM " . $ecs->table('nav') . "WHERE ctype = 'c' AND cid = '" . $cat_id . "' AND type = 'middle'");
            clear_cache_files();
            admin_log($cat_name, 'remove', 'category');
        }
    }
    else
    {
        make_json_error($cat_name .' '. $_LANG['cat_isleaf']);
    }

    $url = 'category.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

if($_REQUEST['act'] == 'drop_image')
{
	$id     = $_REQUEST['img_id'];
	$type   = $_REQUEST['type'];
	$cat_id = $_REQUEST['cat_id'];
	
	if ($type == 0){$file_name = '/banner/home_page_banner/'.$cat_id.'/home_page_banner_'.$cat_id.'.xml';}
	elseif ($type == 1) {$file_name = '/banner/category_page_banner/'.$cat_id.'/category_page_banner_'.$cat_id.'.xml';}
	elseif ($type == 2) {$file_name = '/banner/event_banner/'.$cat_id.'/event_banner_'.$cat_id.'.xml';}
	elseif ($type == 3) {$file_name = '/banner/event_banner_on_event_index/'.$cat_id.'/event_banner_on_event_index_'.$cat_id.'.xml';}
	
	$banner_db = get_banner_xml($file_name);
	
	if (isset($banner_db[$id]))
	{
		$rt = $banner_db[$id];
	}

	if(file_exists(ROOT_PATH . $rt['src'])){
		@unlink(ROOT_PATH . $rt['src']);
	}
	$temp = array();
	foreach ($banner_db as $key => $val)
	{
		if ($key != $id)
		{
			$temp[] = $val;
		}
	}
	put_banner_xml($temp,$file_name,$cat_id);
	
	clear_cache_files();
	$content = array();
	$content['id'] = $id;
	$content['type'] = $type;
		
	make_json_result($content);
}

/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */
//
///**
// * 检查分类是否已经存在
// *
// * @param   string      $cat_name       分类名称
// * @param   integer     $parent_cat     上级分类
// * @param   integer     $exclude        排除的分类ID
// *
// * @return  boolean
// */
//function cat_exists($cat_name, $parent_cat, $exclude = 0)
//{
//    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('category').
//           " WHERE parent_id = '$parent_cat' AND cat_name = '$cat_name' AND cat_id<>'$exclude'";
//    return ($GLOBALS['db']->getOne($sql) > 0) ? true : false;
//}

/**
 * 获得商品分类的所有信息
 *
 * @param   integer     $cat_id     指定的分类ID
 *
 * @return  mix
 */
function get_cat_info($cat_id)
{
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('category'). " WHERE cat_id='$cat_id' LIMIT 1";
    return $GLOBALS['db']->getRow($sql);
}

/**
 * 添加商品分类
 *
 * @param   integer $cat_id
 * @param   array   $args
 *
 * @return  mix
 */
function cat_update($cat_id, $args)
{
    if (empty($args) || empty($cat_id))
    {
        return false;
    }

    return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('category'), $args, 'update', "cat_id='$cat_id'");
}


/**
 * 获取属性列表
 *
 * @access  public
 * @param
 *
 * @return void
 */
function get_attr_list()
{
    $sql = "SELECT a.attr_id, a.cat_id, a.attr_name ".
           " FROM " . $GLOBALS['ecs']->table('attribute'). " AS a,  ".
           $GLOBALS['ecs']->table('goods_type') . " AS c ".
           " WHERE  a.cat_id = c.cat_id AND c.enabled = 1 ".
           " ORDER BY a.cat_id , a.sort_order";

    $arr = $GLOBALS['db']->getAll($sql);

    $list = array();

    foreach ($arr as $val)
    {
        $list[$val['cat_id']][] = array($val['attr_id']=>$val['attr_name']);
    }

    return $list;
}

/**
 * 插入首页推荐扩展分类
 *
 * @access  public
 * @param   array   $recommend_type 推荐类型
 * @param   integer $cat_id     分类ID
 *
 * @return void
 */
function insert_cat_recommend($recommend_type, $cat_id)
{
    //检查分类是否为首页推荐
    if (!empty($recommend_type))
    {
        //取得之前的分类
        $recommend_res = $GLOBALS['db']->getAll("SELECT recommend_type FROM " . $GLOBALS['ecs']->table("cat_recommend") . " WHERE cat_id=" . $cat_id);
        if (empty($recommend_res))
        {
            foreach($recommend_type as $data)
            {
                $data = intval($data);
                $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table("cat_recommend") . "(cat_id, recommend_type) VALUES ('$cat_id', '$data')");
            }
        }
        else
        {
            $old_data = array();
            foreach($recommend_res as $data)
            {
                $old_data[] = $data['recommend_type'];
            }
            $delete_array = array_diff($old_data, $recommend_type);
            if (!empty($delete_array))
            {
                $GLOBALS['db']->query("DELETE FROM " . $GLOBALS['ecs']->table("cat_recommend") . " WHERE cat_id=$cat_id AND recommend_type " . db_create_in($delete_array));
            }
            $insert_array = array_diff($recommend_type, $old_data);
            if (!empty($insert_array))
            {
                foreach($insert_array as $data)
                {
                    $data = intval($data);
                    $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table("cat_recommend") . "(cat_id, recommend_type) VALUES ('$cat_id', '$data')");
                }
            }
        }
    }
    else
    {
        $GLOBALS['db']->query("DELETE FROM ". $GLOBALS['ecs']->table("cat_recommend") . " WHERE cat_id=" . $cat_id);
    }
}

/*function get_catbanner_xml($cat_id)
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
function put_catbanner_xml($flashdb,$cat_id)
{
    if (!empty($flashdb))
    {
        $xml = '<?xml version="1.0" encoding="' . EC_CHARSET . '"?><bcaster>';
        foreach ($flashdb as $key => $val)
        {
            $xml .= '<item item_url="' . $val['src'] . '" link="' . $val['url'] . '" text="' . $val['text'] . '" sort="' . $val['sort'] . '"/>';
        }
        $xml .= '</bcaster>';
        file_put_contents(ROOT_PATH . DATA_DIR . '/catbanner/'. $cat_id.'.xml', $xml);
    }
    else
    {
        @unlink(ROOT_PATH . DATA_DIR . '/catbanner/'. $cat_id.'.xml');
    }
}

function get_catpagebanner_xml($cat_id)
{
    $flashdb = array();
    if (file_exists(ROOT_PATH . DATA_DIR . '/catpagebanner/'.$cat_id.'.xml'))
    {

        // 兼容v2.7.0及以前版本
        if (!preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"\ssort="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/catpagebanner/'.$cat_id.'.xml'), $t, PREG_SET_ORDER))
        {
            preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/catpagebanner/'.$cat_id.'.xml'), $t, PREG_SET_ORDER);
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
function put_catpagebanner_xml($flashdb,$cat_id)
{
    if (!empty($flashdb))
    {
        $xml = '<?xml version="1.0" encoding="' . EC_CHARSET . '"?><bcaster>';
        foreach ($flashdb as $key => $val)
        {
            $xml .= '<item item_url="' . $val['src'] . '" link="' . $val['url'] . '" text="' . $val['text'] . '" sort="' . $val['sort'] . '"/>';
        }
        $xml .= '</bcaster>';
        file_put_contents(ROOT_PATH . DATA_DIR . '/catpagebanner/'. $cat_id.'.xml', $xml);
    }
    else
    {
        @unlink(ROOT_PATH . DATA_DIR . '/catpagebanner/'. $cat_id.'.xml');
    }
}*/


function banner_handler($request,$cat_id,$update=false){
	
	/*banner处理*/
	$old = '';
	if ($update === true){$old = 'old_';}
	

	
	$banner_file = array();
	$banner_file[$old. 'img_file']  = $old. 'img_file';
	$banner_file[$old. 'img_file2'] = $old. 'img_file2';
	$banner_file[$old. 'img_file3'] = $old. 'img_file3';
	$banner_file[$old. 'img_file4'] = $old. 'img_file4';
	
	$banner_desc = array();
	$banner_desc[$old. 'img_file'] = $old. 'img_desc';
	$banner_desc[$old. 'img_file2'] = $old. 'img_desc2';
	$banner_desc[$old. 'img_file3'] = $old. 'img_desc3';
	$banner_desc[$old. 'img_file4'] = $old. 'img_desc4';
	
	$banner_url = array();
	$banner_url[$old. 'img_file'] = $old. 'img_url';
	$banner_url[$old. 'img_file2'] = $old. 'img_url2';
	$banner_url[$old. 'img_file3'] = $old. 'img_url3';
	$banner_url[$old. 'img_file4'] = $old. 'img_url4';
	
	$banner_order = array();
	$banner_order[$old. 'img_file'] = $old. 'img_order';
	$banner_order[$old. 'img_file2'] = $old. 'img_order2';
	$banner_order[$old. 'img_file3'] = $old. 'img_order3';
	$banner_order[$old. 'img_file4'] = $old. 'img_order4';
	
	$banner_position = array();
	$banner_position[$old. 'img_file'] = $old. 'img_position';
	$banner_position[$old. 'img_file2'] = $old. 'img_position2';
	$banner_position[$old. 'img_file3'] = $old. 'img_position3';
	$banner_position[$old. 'img_file4'] = $old. 'img_position4';

	$banner_show = array();
	$banner_show[$old. 'img_file'] = $old. 'img_show';
	$banner_show[$old. 'img_file2'] = $old. 'img_show2';
	$banner_show[$old. 'img_file3'] = $old. 'img_show3';
	$banner_show[$old. 'img_file4'] = $old. 'img_show4';
	
	$banner_id = array();
	$banner_id[$old. 'img_file'] = $old. 'img_id';	
	$banner_id[$old. 'img_file2'] = $old. 'img_id2';	
	$banner_id[$old. 'img_file3'] = $old. 'img_id3';	
	$banner_id[$old. 'img_file4'] = $old. 'img_id4';	
	
	$banner_xml_filename = array();
	$banner_xml_filename[$old. 'img_file'] = '/banner/home_page_banner/'.$cat_id.'/home_page_banner_'.$cat_id.'.xml';
	$banner_xml_filename[$old. 'img_file2'] = '/banner/category_page_banner/'.$cat_id.'/category_page_banner_'.$cat_id.'.xml';	
	$banner_xml_filename[$old. 'img_file3'] = '/banner/event_banner/'.$cat_id.'/event_banner_'.$cat_id.'.xml';	
	$banner_xml_filename[$old. 'img_file4'] = '/banner/event_banner_on_event_index/'.$cat_id.'/event_banner_on_event_index_'.$cat_id.'.xml';	
	
	$banner_img_path_without_name = array();
	$banner_img_path_without_name[$old. 'img_file'] = '/banner/home_page_banner/'.$cat_id.'/';
	$banner_img_path_without_name[$old. 'img_file2'] = '/banner/category_page_banner/'.$cat_id.'/';	
	$banner_img_path_without_name[$old. 'img_file3'] = '/banner/event_banner/'.$cat_id.'/';	
	$banner_img_path_without_name[$old. 'img_file4'] = '/banner/event_banner_on_event_index/'.$cat_id.'/';	

	$banner_file_folder = array();
	$banner_file_folder[$old. 'img_file'] = 'home_page_banner';
	$banner_file_folder[$old. 'img_file2'] = 'category_page_banner';
	$banner_file_folder[$old. 'img_file3'] = 'event_banner';
	$banner_file_folder[$old. 'img_file4'] = 'event_banner_on_event_index';
	//showr($_REQUEST);


	
	
	
		foreach ($banner_file as $key => $value){	
			if ($update === false){ //新加
				foreach ($_FILES[$key]['name'] as $k => $v){
						if ($_FILES[$key]['error'][$k] == 0 && $_FILES[$key]['tmp_name'][$k] != ''){//没有error
							$name = date('Ymd');
							$random1 = rand(1, 99);
							$random2 = rand(1, 99);
							$name .= $random1 . $random2 . '.' . end(explode('.', $v));
							$target = ROOT_PATH . DATA_DIR . $banner_img_path_without_name[$key] . $name;
							if (move_upload_file($_FILES[$key]['tmp_name'][$k], $target)){
								$src = DATA_DIR . $banner_img_path_without_name[$key] . $name;
							}
							// 获取banner_db数据
							$banner_db = get_banner_xml($banner_xml_filename[$key]);
							//showr($banner_db);

							//拿到id
							$id = 1;
							if (!empty($banner_db)){
								foreach ($banner_db as $banner_db_key => $banner_db_value){
									if ($banner_db_value['id'] >= $id){$id = $banner_db_value['id'] + 1;}
								}
							}
							//showr($banner_db);
							// 插入新数据
							//if ($request[$banner_desc[$key]][$k] == ''){$request[$banner_desc[$key]][$k] = 'none';}
							array_unshift($banner_db, array('desc'=>$request[$banner_desc[$key]][$k], 'src'=>$src, 'url'=>$request[$banner_url[$key]][$k], 'order'=>$request[$banner_order[$key]][$k], 'position'=>$request[$banner_position[$key]][$k], 'id'=>$id, 'show'=>$request[$banner_show[$key]][$k]));
							//showr($banner_db);
							// 实现排序
							$bannerdb_sort   = array();
							$_bannerdb       = array();
							foreach ($banner_db as $banner_db_key => $banner_db_value)
							{
								$bannerdb_sort[$banner_db_key] = $banner_db_value['order'];
							}
							asort($bannerdb_sort, SORT_NUMERIC);				
							foreach ($bannerdb_sort as $banner_db_sort_key => $banner_db_sort_value)
							{
								$_bannerdb[] = $banner_db[$banner_db_sort_key];
							}
							unset($banner_db, $bannerdb_sort);
							put_banner_xml($_bannerdb,$banner_xml_filename[$key],$cat_id,$banner_file_folder[$key]);
						}
					}
				}
				else{// 更新
				//showr($request[$banner_id[$key]]);
				
					$banner_db = get_banner_xml($banner_xml_filename[$key]);
					if (!empty($request[$banner_id[$key]])){
						foreach ($request[$banner_id[$key]] as $id_key => $id_value){
							$banner_db[$id_key]['desc'] = $request[$banner_desc[$key]][$id_key];
							$banner_db[$id_key]['order'] = $request[$banner_order[$key]][$id_key];
							$banner_db[$id_key]['url'] = $request[$banner_url[$key]][$id_key];
							if (isset($request[$banner_show[$key]][$id_key]) === false || $request[$banner_show[$key]][$id_key] == ''){$request[$banner_show[$key]][$id_key] = 0;}
							$banner_db[$id_key]['show'] = $request[$banner_show[$key]][$id_key];
							
						}
					}
					$bannerdb_sort   = array();
					$_bannerdb       = array();
					foreach ($banner_db as $banner_db_key => $banner_db_value)
					{
						$bannerdb_sort[$banner_db_key] = $banner_db_value['order'];
					}
					asort($bannerdb_sort, SORT_NUMERIC);		
					foreach ($bannerdb_sort as $banner_db_sort_key => $banner_db_sort_value)
					{
						$_bannerdb[] = $banner_db[$banner_db_sort_key];
					}
					unset($banner_db, $bannerdb_sort);
					//showr($_bannerdb);
					put_banner_xml($_bannerdb,$banner_xml_filename[$key],$cat_id,$banner_file_folder[$key]);
				}
		}
	

	
		return true;
}
	
	
function get_banner_xml($file_name)
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


function put_banner_xml($flashdb,$file_name,$cat_id,$file_folder='')
{			//showr($flashdb);	
	if ($file_folder != ''){
		if (!file_exists(ROOT_PATH . DATA_DIR . '/banner/'.$file_folder)){
			mkdir(ROOT_PATH . DATA_DIR . '/banner/'.$file_folder);
		}
		if (!file_exists(ROOT_PATH . DATA_DIR . '/banner/'.$file_folder.'/'.$cat_id)){
			mkdir(ROOT_PATH . DATA_DIR . '/banner/'.$file_folder.'/'.$cat_id);
		}
	}
	
	 if (!empty($flashdb))
    {
        $xml = '<?xml version="1.0" encoding="' . EC_CHARSET . '"?><bcaster>';
        foreach ($flashdb as $key => $val)
        {
            $xml .= '<item desc="' . $val['desc'] . '" src="' . $val['src'] . '" url="' . $val['url'] . '" order="' . $val['order'] . '" position="' . $val['position'] . '" id="' . $val['id'] . '" show="' . $val['show'] . '"/>';
        }
        $xml .= '</bcaster>';
		
        file_put_contents(ROOT_PATH . DATA_DIR . $file_name, $xml);
    }
    else
    {
        //@unlink(ROOT_PATH . DATA_DIR . $file_name);
        $xml = '<?xml version="1.0" encoding="' . EC_CHARSET . '"?><bcaster>';
        $xml .= '</bcaster>';
        file_put_contents(ROOT_PATH . DATA_DIR . $file_name, $xml);
    }
	 

}


function category_icon_handler($cat_id,$file_icon,$width=24,$height=24){	
	$image = new cls_image($_CFG['bgcolor']);
	
	image_validator_by_file($file_icon);

	if ($file_icon['size'] != 0 && $file_icon['error'] == 0){

		$category_icon              = '';  // 分类图标
		$original_category_icon     = '';  // 初始化原始分类图标
		$old_original_category_icon = '';  // 初始化原始分类图标旧图
		
			// 如果上传了图标，相应处理
 

            /* 删除原来的图片文件 */
            $sql = "SELECT icon " .
                    " FROM " . $GLOBALS['ecs']->table("category") .
                    " WHERE cat_id = '$cat_id'";
			$row = $GLOBALS['db']->getRow($sql);

            if ($row['icon'] != '' && is_file('../' . $row['icon']))
            {
                @unlink('../' . $row['icon']);
            }        

			$original_icon = $image->upload_image($file_icon,'category_icon'); // 原始图片
/*
			$icon_name = 'icon_' . $cat_id;
			$icon = $image->make_thumb('../' . $original_icon, $width,  $height,'../data/category_icon/');
            if ($icon === false)
            {
              sys_msg($image->error_msg(), 1, array(), false);
            }
			@unlink('../' . $original_icon);
			$icon = str_replace('../', '', $icon);*/
			$icon = str_replace('../', '', $original_icon);
		return $icon;
	}
	else{return false;}
}


function image_validator_by_file($file){
	$image = new cls_image($_CFG['bgcolor']);
	if (isset($file['error'])) // php 4.2 版本才支持 error
	{
		// 最大上传文件大小
		$php_maxsize = ini_get('upload_max_filesize');
		$htm_maxsize = '2M';
		// 商品图片
		if ($file['error'] == 0)
		{
			if (!$image->check_img_type($file['type']))
			{
					sys_msg($_LANG['invalid_goods_img'], 1, array(), false);
			}
		}
		elseif ($file['error'] == 1)
		{
				sys_msg(sprintf('图片过大，不能超过2M', $php_maxsize), 1, array(), false);
		}
		elseif ($file['error'] == 2)
		{
				sys_msg(sprintf($_LANG['goods_img_too_big'], $htm_maxsize), 1, array(), false);
		}	
	}
	return true;
}

function image_validator_by_individual($error,$type){
	$image = new cls_image($_CFG['bgcolor']);
	if (isset($error)) // php 4.2 版本才支持 error
	{
		// 最大上传文件大小
		$php_maxsize = ini_get('upload_max_filesize');
		$htm_maxsize = '2M';
		// 商品图片
		if ($error == 0)
		{
			if (!$image->check_img_type($type))
			{
					sys_msg($_LANG['invalid_goods_img'], 1, array(), false);
			}
		}
		elseif ($error == 1)
		{
				sys_msg(sprintf('图片过大，不能超过2M', $php_maxsize), 1, array(), false);
		}
		elseif ($error == 2)
		{
				sys_msg(sprintf($_LANG['goods_img_too_big'], $htm_maxsize), 1, array(), false);
		}	
	}
	return true;
}

?>