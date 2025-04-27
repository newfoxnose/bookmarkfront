<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
    }

    public function bilibili_hot_ajax() {
        // 调用B站API
        $ch = curl_init();
        $url = 'https://api.bilibili.com/x/web-interface/ranking/v2?rid=0&type=all';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Referer: https://www.bilibili.com/ranking',
            'Origin: https://www.bilibili.com',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // 记录请求信息
        error_log('B站API请求URL: ' . $url);
        error_log('HTTP状态码: ' . $httpCode);
        if ($error) {
            error_log('CURL错误: ' . $error);
        }
        error_log('响应内容: ' . $response);

        if ($httpCode !== 200) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '获取B站数据失败，HTTP状态码：' . $httpCode,
                    'error' => $error
                )));
            return;
        }

        $data = json_decode($response, true);
        if (!$data) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '解析B站数据失败，JSON解析错误',
                    'raw' => $response
                )));
            return;
        }

        if ($data['code'] !== 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => 'B站API返回错误：' . $data['message'],
                    'data' => $data
                )));
            return;
        }

        // 返回数据
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'code' => 0,
                'msg' => 'success',
                'data' => $data['data']
            )));
    }

    public function zhihu_hot_ajax() {
        // 调用知乎API
        $ch = curl_init();
        $url = 'https://www.zhihu.com/api/v3/feed/topstory/hot-lists/total';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // 记录请求信息
        error_log('知乎API请求URL: ' . $url);
        error_log('HTTP状态码: ' . $httpCode);
        if ($error) {
            error_log('CURL错误: ' . $error);
        }
        error_log('响应内容: ' . $response);

        if ($httpCode !== 200) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '获取知乎数据失败，HTTP状态码：' . $httpCode,
                    'error' => $error
                )));
            return;
        }

        // 将大整数作为字符串处理
        $data = json_decode($response, true, 512, JSON_BIGINT_AS_STRING);
        if (!$data) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '解析知乎数据失败，JSON解析错误',
                    'raw' => $response
                )));
            return;
        }

        if (!isset($data['data'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '知乎API返回数据格式错误',
                    'data' => $data
                )));
            return;
        }

        // 处理数据
        $hotList = array();
        foreach ($data['data'] as $index => $item) {
            $target = $item['target'];
            // 确保问题ID保持字符串格式
            $questionId = is_string($target['id']) ? $target['id'] : strval($target['id']);
            $hotList[] = array(
                'title' => $target['title'],
                'url' => 'https://www.zhihu.com/question/' . $questionId,
                'author' => $target['author']['name'],
                'excerpt' => $target['excerpt'],
                'heat' => str_replace('热度', '', $item['detail_text']),
                'rank' => $index + 1
            );
        }

        // 返回数据
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'code' => 0,
                'msg' => 'success',
                'data' => array(
                    'list' => $hotList
                )
            )));
    }

    public function weibo_hot_ajax() {
        // 调用微博API
        $ch = curl_init();
        $url = 'https://weibo.com/ajax/side/hotSearch';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Referer: https://weibo.com/',
            'Origin: https://weibo.com'
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // 记录请求信息
        error_log('微博API请求URL: ' . $url);
        error_log('HTTP状态码: ' . $httpCode);
        if ($error) {
            error_log('CURL错误: ' . $error);
        }
        error_log('响应内容: ' . $response);

        if ($httpCode !== 200) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '获取微博数据失败，HTTP状态码：' . $httpCode,
                    'error' => $error
                )));
            return;
        }

        $data = json_decode($response, true);
        if (!$data) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '解析微博数据失败，JSON解析错误',
                    'raw' => $response
                )));
            return;
        }

        if (!isset($data['data']['realtime'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '微博API返回数据格式错误',
                    'data' => $data
                )));
            return;
        }

        // 处理数据
        $hotList = array();
        foreach ($data['data']['realtime'] as $index => $item) {
            $title = $item['word'];
            $hotList[] = array(
                'title' => $title,
                'url' => 'https://s.weibo.com/weibo?q=%23' . urlencode($title) . '%23',
                'heat' => $item['num'],
                'rank' => $index + 1
            );
        }

        // 返回数据
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'code' => 0,
                'msg' => 'success',
                'data' => array(
                    'list' => $hotList
                )
            )));
    }

    public function toutiao_hot_ajax() {
        // 调用今日头条API
        $ch = curl_init();
        $url = 'https://www.toutiao.com/hot-event/hot-board/?origin=toutiao_pc';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Referer: https://www.toutiao.com/',
            'Origin: https://www.toutiao.com'
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // 记录请求信息
        error_log('今日头条API请求URL: ' . $url);
        error_log('HTTP状态码: ' . $httpCode);
        if ($error) {
            error_log('CURL错误: ' . $error);
        }
        error_log('响应内容: ' . $response);

        if ($httpCode !== 200) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '获取今日头条数据失败，HTTP状态码：' . $httpCode,
                    'error' => $error
                )));
            return;
        }

        $data = json_decode($response, true);
        if (!$data) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '解析今日头条数据失败，JSON解析错误',
                    'raw' => $response
                )));
            return;
        }

        if (!isset($data['data'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '今日头条API返回数据格式错误',
                    'data' => $data
                )));
            return;
        }

        // 处理数据
        $hotList = array();
        foreach ($data['data'] as $index => $item) {
            $hotList[] = array(
                'title' => $item['Title'],
                'url' => $item['Url'],
                'heat' => $item['HotValue'],
                'rank' => $index + 1
            );
        }

        // 返回数据
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'code' => 0,
                'msg' => 'success',
                'data' => array(
                    'list' => $hotList
                )
            )));
    }

    public function tencent_hot_ajax() {
        // 调用腾讯新闻API
        $ch = curl_init();
        $url = 'https://i.news.qq.com/gw/event/pc_hot_ranking_list?ids_hash=&offset=0&page_size=50&appver=15.5_qqnews_7.1.60&rank_id=hot';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Origin: https://news.qq.com',
            'Referer: https://news.qq.com/'
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // 记录请求信息
        error_log('腾讯新闻API请求URL: ' . $url);
        error_log('HTTP状态码: ' . $httpCode);
        if ($error) {
            error_log('CURL错误: ' . $error);
        }
        error_log('响应内容长度: ' . strlen($response));

        if ($httpCode !== 200) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '获取腾讯新闻数据失败，HTTP状态码：' . $httpCode,
                    'error' => $error
                )));
            return;
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['idlist'][0]['newslist'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '解析腾讯新闻数据失败',
                    'debug_info' => array(
                        'response_length' => strlen($response),
                        'first_100_chars' => substr($response, 0, 100)
                    )
                )));
            return;
        }

        // 处理数据
        $newslist = $data['idlist'][0]['newslist'];
        $hotList = array();
        
        // 收集所有有效的热度值用于排序
        $validItems = array();
        foreach ($newslist as $index => $item) {
            if ($index === 0) continue; // 跳过第一条数据
            
            $readCount = isset($item['readCount']) ? $item['readCount'] : '0';
            // 尝试转换热度值为数字
            $heat = intval(preg_replace('/[^0-9]/', '', $readCount));
            
            $validItems[] = array(
                'heat' => $heat,
                'data' => array(
                    'title' => $item['title'],
                    'url' => $item['surl'],
                    'author' => isset($item['chlname']) ? $item['chlname'] : '',
                    'heat' => $readCount,
                    'cover' => isset($item['miniProShareImage']) ? $item['miniProShareImage'] : ''
                )
            );
        }

        // 按热度排序
        usort($validItems, function($a, $b) {
            return $b['heat'] - $a['heat'];
        });

        // 生成最终列表
        foreach ($validItems as $index => $item) {
            $hotList[] = array_merge($item['data'], array('rank' => $index + 1));
        }

        // 返回数据
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'code' => 0,
                'msg' => 'success',
                'data' => array(
                    'list' => $hotList
                )
            )));
    }

    public function sougou_hot_ajax() {
        // 调用搜狗热榜API
        $ch = curl_init();
        $url = 'https://go.ie.sogou.com/hot_ranks';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Origin: https://www.sogou.com',
            'Referer: https://www.sogou.com/'
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // 记录请求信息
        error_log('搜狗热榜API请求URL: ' . $url);
        error_log('HTTP状态码: ' . $httpCode);
        if ($error) {
            error_log('CURL错误: ' . $error);
        }
        error_log('响应内容长度: ' . strlen($response));

        if ($httpCode !== 200) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '获取搜狗热榜数据失败，HTTP状态码：' . $httpCode,
                    'error' => $error
                )));
            return;
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['data'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '解析搜狗热榜数据失败',
                    'debug_info' => array(
                        'response_length' => strlen($response),
                        'first_100_chars' => substr($response, 0, 100)
                    )
                )));
            return;
        }

        // 处理数据
        $hotList = array();
        foreach ($data['data'] as $index => $item) {
            if (isset($item['attributes']) && isset($item['attributes']['title'])) {
                $title = $item['attributes']['title'];
                $hotList[] = array(
                    'title' => $title,
                    'url' => 'https://www.sogou.com/web?ie=utf8&query=' . urlencode($title),
                    'heat' => isset($item['attributes']['num']) ? $item['attributes']['num'] : '未知',
                    'rank' => $index + 1
                );
            }
        }

        if (empty($hotList)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '解析搜狗热榜数据失败，未找到有效数据',
                    'debug_info' => array(
                        'data_count' => count($data['data'])
                    )
                )));
            return;
        }

        // 返回数据
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'code' => 0,
                'msg' => 'success',
                'data' => array(
                    'list' => $hotList
                )
            )));
    }

    public function juejin_hot_ajax() {
        // 调用掘金API
        $ch = curl_init();
        $url = 'https://api.juejin.cn/content_api/v1/content/article_rank?category_id=1&type=hot&spider=0';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Origin: https://juejin.cn',
            'Referer: https://juejin.cn/'
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // 记录请求信息
        error_log('掘金API请求URL: ' . $url);
        error_log('HTTP状态码: ' . $httpCode);
        if ($error) {
            error_log('CURL错误: ' . $error);
        }
        error_log('响应内容长度: ' . strlen($response));

        if ($httpCode !== 200) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '获取掘金数据失败，HTTP状态码：' . $httpCode,
                    'error' => $error
                )));
            return;
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['data'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '解析掘金数据失败',
                    'debug_info' => array(
                        'response_length' => strlen($response),
                        'first_100_chars' => substr($response, 0, 100)
                    )
                )));
            return;
        }

        // 处理数据
        $hotList = array();
        foreach ($data['data'] as $index => $item) {
            $content = $item['content'];
            $counter = $item['content_counter'];
            $author = $item['author'];
            
            $hotList[] = array(
                'title' => $content['title'],
                'url' => 'https://juejin.cn/post/' . $content['content_id'],
                'author' => $author['author_name'],
                'author_avatar' => $author['avatar_large'],
                'heat' => $counter['view_count'],
                'rank' => $index + 1
            );
        }

        // 返回数据
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'code' => 0,
                'msg' => 'success',
                'data' => array(
                    'list' => $hotList
                )
            )));
    }

    public function hupu_hot_ajax() {
        // 调用虎扑步行街API
        $ch = curl_init();
        $url = 'https://bbs.hupu.com/all-gambia';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Referer: https://bbs.hupu.com/'
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // 记录请求信息
        error_log('虎扑步行街请求URL: ' . $url);
        error_log('HTTP状态码: ' . $httpCode);
        if ($error) {
            error_log('CURL错误: ' . $error);
        }
        error_log('响应内容长度: ' . strlen($response));

        if ($httpCode !== 200) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '获取虎扑步行街数据失败，HTTP状态码：' . $httpCode,
                    'error' => $error
                )));
            return;
        }

        // 添加meta标签确保UTF-8编码
        $response = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $response;

        // 使用DOMDocument解析HTML
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($response, LIBXML_NOWARNING | LIBXML_NOERROR);
        $xpath = new DOMXPath($dom);

        // 获取所有帖子信息
        $posts = array();
        $postNodes = $xpath->query("//div[contains(@class, 'text-list-model')]//div[contains(@class, 'list-item-wrap')]//div[contains(@class, 'list-item')]");
        
        foreach ($postNodes as $postNode) {
            // 获取标题和链接
            $titleNode = $xpath->query(".//a[contains(@class, 'false')]//span[contains(@class, 't-title')]", $postNode)->item(0);
            $linkNode = $xpath->query(".//a[contains(@class, 'false')]", $postNode)->item(0);
            
            // 获取亮数和回复数
            $lightsNode = $xpath->query(".//span[contains(@class, 't-lights')]", $postNode)->item(0);
            $repliesNode = $xpath->query(".//span[contains(@class, 't-replies')]", $postNode)->item(0);
            
            // 获取分区
            $labelNode = $xpath->query(".//div[contains(@class, 't-label')]//a", $postNode)->item(0);
            
            if ($titleNode && $linkNode) {
                $title = trim($titleNode->textContent);
                $url = 'https://bbs.hupu.com' . $linkNode->getAttribute('href');
                $lights = $lightsNode ? trim($lightsNode->textContent) : '0亮';
                $replies = $repliesNode ? trim($repliesNode->textContent) : '0回复';
                $label = $labelNode ? trim($labelNode->textContent) : '';
                
                // 提取数字
                $lights = intval(preg_replace('/[^0-9]/', '', $lights));
                $replies = intval(preg_replace('/[^0-9]/', '', $replies));
                
                $posts[] = array(
                    'title' => $title,
                    'url' => $url,
                    'heat' => $replies,
                    'likes' => $lights,
                    'label' => $label
                );
            }
        }

        if (empty($posts)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '解析虎扑步行街数据失败，未找到帖子列表',
                    'debug_info' => array(
                        'response_length' => strlen($response),
                        'first_100_chars' => substr($response, 0, 100)
                    )
                )));
            return;
        }

        // 按热度（回复数）排序
        usort($posts, function($a, $b) {
            return $b['heat'] - $a['heat'];
        });

        // 重新设置排名并格式化数据
        $hotList = array();
        foreach ($posts as $index => $post) {
            $hotList[] = array(
                'title' => $post['title'],
                'url' => $post['url'],
                'heat' => $post['heat'] . '回复',
                'likes' => $post['likes'] . '亮',
                'label' => $post['label'],
                'rank' => $index + 1
            );
        }

        // 返回数据
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'code' => 0,
                'msg' => 'success',
                'data' => array(
                    'list' => $hotList
                )
            )));
    }

    public function douyin_hot_ajax() {
        // 调用抖音热榜API
        $ch = curl_init();
        $url = 'https://www.iesdouyin.com/web/api/v2/hotsearch/billboard/word/';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Origin: https://www.douyin.com',
            'Referer: https://www.douyin.com/'
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // 记录请求信息
        error_log('抖音热榜API请求URL: ' . $url);
        error_log('HTTP状态码: ' . $httpCode);
        if ($error) {
            error_log('CURL错误: ' . $error);
        }
        error_log('响应内容长度: ' . strlen($response));

        if ($httpCode !== 200) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '获取抖音热榜数据失败，HTTP状态码：' . $httpCode,
                    'error' => $error
                )));
            return;
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['word_list'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '解析抖音热榜数据失败',
                    'debug_info' => array(
                        'response_length' => strlen($response),
                        'first_100_chars' => substr($response, 0, 100)
                    )
                )));
            return;
        }

        // 处理数据
        $hotList = array();
        foreach ($data['word_list'] as $index => $item) {
            $hotList[] = array(
                'title' => $item['word'],
                'url' => 'https://www.douyin.com/search/' . urlencode($item['word']) . '?type=general',
                'heat' => $item['hot_value'],
                'rank' => $index + 1
            );
        }

        // 返回数据
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'code' => 0,
                'msg' => 'success',
                'data' => array(
                    'list' => $hotList
                )
            )));
    }

    public function csdn_hot_ajax() {
        // 调用CSDN热榜API
        $ch = curl_init();
        $url = 'https://blog.csdn.net/phoenix/web/blog/hot-rank?page=0&pageSize=25';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Origin: https://blog.csdn.net',
            'Referer: https://blog.csdn.net/'
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // 记录请求信息
        error_log('CSDN热榜API请求URL: ' . $url);
        error_log('HTTP状态码: ' . $httpCode);
        if ($error) {
            error_log('CURL错误: ' . $error);
        }
        error_log('响应内容长度: ' . strlen($response));

        if ($httpCode !== 200) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '获取CSDN热榜数据失败，HTTP状态码：' . $httpCode,
                    'error' => $error
                )));
            return;
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['data'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '解析CSDN热榜数据失败',
                    'debug_info' => array(
                        'response_length' => strlen($response),
                        'first_100_chars' => substr($response, 0, 100)
                    )
                )));
            return;
        }

        // 处理数据
        $hotList = array();
        foreach ($data['data'] as $index => $item) {
            $hotList[] = array(
                'title' => $item['articleTitle'],
                'url' => $item['articleDetailUrl'],
                'heat' => $item['hotRankScore'],
                'author' => $item['nickName'],
                'author_avatar' => $item['avatarUrl'],
                'rank' => $index + 1
            );
        }

        // 返回数据
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'code' => 0,
                'msg' => 'success',
                'data' => array(
                    'list' => $hotList
                )
            )));
    }

    public function baidu_hot_ajax() {
        // 调用百度热搜API
        $ch = curl_init();
        $url = 'https://top.baidu.com/board?tab=realtime&sa=fyb_realtime_31065';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Referer: https://top.baidu.com/'
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // 记录请求信息
        error_log('百度热搜请求URL: ' . $url);
        error_log('HTTP状态码: ' . $httpCode);
        if ($error) {
            error_log('CURL错误: ' . $error);
        }
        error_log('响应内容长度: ' . strlen($response));

        if ($httpCode !== 200) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '获取百度热搜数据失败，HTTP状态码：' . $httpCode,
                    'error' => $error
                )));
            return;
        }

        // 添加meta标签确保UTF-8编码
        $response = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $response;

        // 使用DOMDocument解析HTML
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($response, LIBXML_NOWARNING | LIBXML_NOERROR);
        $xpath = new DOMXPath($dom);

        // 获取所有热搜信息
        $hotList = array();
        
        // 获取标题
        $titleNodes = $xpath->query("//div[contains(@class, 'c-single-text-ellipsis')]");
        // 获取图片
        $imgNodes = $xpath->query("//div[contains(@class, 'category-wrap_iQLoo')]//img");
        // 获取内容
        $contentNodes = $xpath->query("//div[contains(@class, 'hot-desc_1m_jR')]");
        // 获取链接
        $urlNodes = $xpath->query("//div[contains(@class, 'category-wrap_iQLoo')]//a[contains(@class, 'img-wrapper_29V76')]");
        // 获取热度
        $heatNodes = $xpath->query("//div[contains(@class, 'hot-index_1Bl1a')]");

        for ($i = 0; $i < $heatNodes->length; $i++) {
            $title = $titleNodes->item($i) ? trim($titleNodes->item($i)->textContent) : '';
            $img = $imgNodes->item($i) ? $imgNodes->item($i)->getAttribute('src') : '';
            $content = $contentNodes->item($i) ? str_replace('查看更多>', '', trim($contentNodes->item($i)->textContent)) : '';
            $url = $urlNodes->item($i) ? $urlNodes->item($i)->getAttribute('href') : '';
            $heat = $heatNodes->item($i) ? trim($heatNodes->item($i)->textContent) : '';

            $hotList[] = array(
                'title' => $title,
                'url' => $url,
                'img' => $img,
                'content' => $content,
                'heat' => $heat,
                'rank' => $i + 1
            );
        }

        if (empty($hotList)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'code' => 500,
                    'msg' => '解析百度热搜数据失败，未找到热搜列表',
                    'debug_info' => array(
                        'response_length' => strlen($response),
                        'first_100_chars' => substr($response, 0, 100)
                    )
                )));
            return;
        }

        // 返回数据
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'code' => 0,
                'msg' => 'success',
                'data' => array(
                    'list' => $hotList
                )
            )));
    }
} 