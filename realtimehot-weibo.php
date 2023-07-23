<?php
/*
Plugin Name: Realtimehot Weibo（微博热搜榜）
Plugin URI: https://github.com/sy-records/realtimehot-weibo
Description: 在WordPress的仪表盘、小工具、文章、页面等地方加入微博热搜榜，随时随地 get 实时微博热搜，一键直达！
Version: 1.1.0
Author: 沈唁
Author URI: https://qq52o.me
License: Apache 2.0
*/

function luffy_http_get($url)
{
    $body = '';
    $headers = [
        'cookie' => 'SUB=_2AkMWJrkXf8NxqwJRmP8SxWjnaY12zwnEieKgekjMJRMxHRl-yj9jqmtbtRB6PaaX-IGp-AjmO6k5cS-OH2X9CayaTzVD'
    ];
    $response = wp_remote_get($url, ['headers' => $headers]);
    if (is_array($response) && !is_wp_error($response) && $response['response']['code'] == '200') {
        $body = $response['body'];
    }
    return $body;
}

function luffy_get_weibo_link_content($content)
{
    preg_match('/<tbody>(.*)<\/tbody>/s', $content, $tbody);
    preg_match_all('/<a.*?>(.*?)<\/a>/', $tbody[1], $result);
    return $result;
}

function luffy_weibo_realtimehot($num)
{
    if (is_admin()) {
        echo '<p>欢迎使用 <a href="https://github.com/sy-records/realtimehot-weibo" title="Realtimehot Weibo（微博热搜榜）" target="_blank">Realtimehot Weibo（微博热搜榜）</a> 插件。</p>';
    }

    $num = !empty($num) ? $num : 10;
    if ($num <= 51) {
        $num = $num - 1;
    } else {
        $num = 50;
    }

    $content = luffy_http_get('https://s.weibo.com/top/summary?cate=realtimehot');
    $result = luffy_get_weibo_link_content($content);
    if (!empty($result)) {
        echo '<ol>';
        for ($x = 0; $x <= $num; $x++) {
            $a = str_replace('https://s.weibo.com/weibo?q=', '/weibo?q=', $result[0][$x]);
            echo "<li>{$a}</li>";
        }
        echo '</ol>';
    }
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

    $content = luffy_http_get('https://s.weibo.com/top/summary?cate=realtimehot');
    $result = luffy_get_weibo_link_content($content);

    $html = '<div><ol>';
    if (!empty($result)) {
        for ($x = 0; $x <= $num; $x++) {
            $a = str_replace('https://s.weibo.com/weibo?q=', '/weibo?q=', $result[0][$x]);
            $html .= "<li>{$a}</li>";
        }
    }
    $html .= '</ol></div>';

    return $html;
}

add_shortcode('get_weibo_realtimehot', 'luffy_get_weibo_realtimehot');
