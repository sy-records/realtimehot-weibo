<?php
/*
Plugin Name: Realtimehot Weibo（微博热搜榜）
Plugin URI: https://github.com/sy-records/realtimehot-weibo
Description: 在WordPress的仪表盘、小工具、文章、页面等地方加入微博热搜榜，随时随地 get 实时微博热搜，一键直达！
Version: 1.0.0
Author: 沈唁
Author URI: https://qq52o.me
License: Apache 2.0
*/

function luffy_getSubstr($str, $leftStr, $rightStr)
{
    $left = strpos($str, $leftStr);
    $right = strpos($str, $rightStr, $left);
    if ($left < 0 or $right < $left) {
        return '';
    }
    return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
}

function luffy_http_get($url)
{
    $body = '';
    $response = wp_remote_get($url);
    if (is_array($response) && !is_wp_error($response) && $response['response']['code'] == '200') {
        $body = $response['body'];
    }
    return $body;
}

function luffy_weibo_realtimehot($num)
{
    echo '<p>欢迎使用 <a href="https://github.com/sy-records/weibo-realtimehot" title="Weibo Realtimehot（微博热搜榜）" target="_blank">Weibo Realtimehot（微博热搜榜）</a> 插件。</p>';

    $num = !empty($num) ? $num : 10;
    if ($num <= 51) {
        $num = $num - 1;
    } else {
        $num = 51;
    }

    $data = luffy_http_get("https://s.weibo.com/top/summary?cate=realtimehot");
    $arr = [];
    if (!empty($data)) {
        $data = luffy_getSubstr($data, '<tbody>', '</tbody>');
        preg_match_all('/weibo\?q=(.*)&Refer/', $data, $matches);
        for ($x = 0; $x <= $num; $x++) {
            $str = urldecode($matches[1][$x]);
            $str = str_replace("#", "", $str);
            $str = str_replace("&topic_ad=1", "", $str);
            $arr[] = $str;
        }
    }
    echo '<ol>';
    foreach ($arr as $item) {
        echo '<li><a href="https://s.weibo.com/weibo?q=' . $item . '" title="' . $item . '" target="_blank">' . $item . '</a></li>';
    }
    echo '</ol>';
}

function luffy_weibo_realtimehot_register_widgets()
{
    wp_add_dashboard_widget('dashboard_luffy_weibo_realtimehot', '微博热搜榜', 'luffy_weibo_realtimehot');
}

add_action('wp_dashboard_setup', 'luffy_weibo_realtimehot_register_widgets');

add_filter('widget_text', 'luffy_text2php', 99);
function luffy_text2php($text)
{
    if (strpos($text, '<' . '?') !== false) {
        ob_start();
        eval('?' . '>' . $text);
        $text = ob_get_contents();
        ob_end_clean();
    }
    return $text;
}

function luffy_get_weibo_realtimehot($atts, $content = null)
{
    extract(shortcode_atts(array('num' => ''), $atts));

    if (empty($atts)) {
        $num = 10;
    }
    if ($num <= 51) {
        $num = $num - 1;
    } else {
        $num = 50;
    }

    $data = luffy_http_get("https://s.weibo.com/top/summary?cate=realtimehot");
    $arr = [];
    if (!empty($data)) {
        $data = luffy_getSubstr($data, '<tbody>', '</tbody>');
        preg_match_all('/weibo\?q=(.*)&Refer/', $data, $matches);
        for ($x = 0; $x <= $num; $x++) {
            $str = urldecode($matches[1][$x]);
            $str = str_replace("#", "", $str);
            $str = str_replace("&topic_ad=1", "", $str);
            $arr[] = $str;
        }
    }
    $content = '<div><ol>';
    foreach ($arr as $item) {
        $content .= '<li><a href="https://s.weibo.com/weibo?q=' . $item . '" title="' . $item . '" target="_blank">' . $item . '</a></li>';
    }
    $content .= '</ol></div>';
    return $content;
}

add_shortcode('get_weibo_realtimehot', 'luffy_get_weibo_realtimehot');