<?php
/**
 * Created by PhpStorm.
 * User: evisu
 * Date: 2018/7/3
 * Time: 下午2:59
 */

namespace App\Library;

use App\Library\MQ\Alarm;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class Utils
{
    /**
     * 通用请求接口
     * @param string $domain     域名
     * @param string $func       接口名
     * @param string $secret     密钥
     * @param array  $data       接口参数
     * @param string $method     请求方式
     * @param array  $secret_cfg 密钥配置
     * @param bool   $url_debug  接口地址调试
     * @param bool   $json       Content-Type为application/json
     * @param array  $options    其他选项
     * @return array $res  接口结果
     */
    public static function curlRequest($domain, $func, $secret = '', $data = [], $method = HTTP_METHOD_POST, $secret_cfg = [], $url_debug = false, $json = false, $options = []) {
        // 设置默认密钥配置
        $secret_default = [
            'enable' => false,
            'key'    => 'sn',
            'func'   => '_getSignWithDate',
        ];
        $secret_cfg = array_merge($secret_default, $secret_cfg);
        $client = app(Client::class);
        // 拼接请求地址
        $domain = rtrim($domain, '/');
        $func = !empty($func) ? '/' . ltrim($func, '/') : '';
        $url = $domain . $func;
        // 构建密钥
        if($secret_cfg['enable']) {
            $data[$secret_cfg['key']] = self::{$secret_cfg['func']}($data, $secret);
        }
        // url调试
        $get_url = !empty($data) ? ($url . '?' . http_build_query($data)) : $url;
        if($url_debug) {
            dd([
                'url'    => $url,
                'data'   => $data,
                'getUrl' => $get_url
            ]);
        }
        $method = strtoupper($method);
        // 默认超时5秒
        isset($options['connect_timeout']) || $options['connect_timeout'] = 5;
        try {
            if($method === HTTP_METHOD_POST) {
                $json ? ($options['json'] = $data) : ($options['form_params'] = $data);
                $res = $client->request($method, $url, $options);
            } else {
                $res = $client->request($method, $get_url, $options);
            }
            $res = json_decode($res->getBody()->getContents(), true);
        } catch(\Exception $e) {
            $res = [
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }
        return $res;
    }

    /**
     * 请求uri是否在列表中
     * 一般在middleware中使用
     * @param Request $request
     * @param array   $list
     * @return bool
     */
    public static function uriInList(Request $request, $list = []) {
        foreach($list as $uri) {
            if($uri !== '/') {
                $uri = trim($uri, '/');
            }
            if($request->fullUrlIs($uri) || $request->is($uri)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取本次请求的controller与action
     * @return array
     */
    public static function getControllerAndAction() {
        list($controller, $action) = explode('@', request()->route()->getActionName());
        $controller = strtolower($controller);
        $controller = subrstr_str($controller, '\\', 'controller');
        $controller = $controller !== false ? $controller : '';
        return [$controller, $action];
    }

    /**
     * 设置cookie
     * @param string $name   名称
     * @param string $value  值, 空则为清除cookie
     * @param int    $expire 过期时间
     * @param string $domain 作用域
     * @return bool
     */
    public static function setCookie($name, $value = '', $expire = 1800, $domain = '') {
        empty($value) && $expire = -$expire;
        return setcookie($name, $value, time() + $expire, '/', $domain);
    }

    /**
     * 格式化捕获的异常信息, 并发送邮件/微信
     * @param string       $title     标题
     * @param \Exception   $exception 异常
     * @param string       $tag       标签
     * @param array|string $receiver  收件人, 不推荐, 一般在调试或应急使用
     */
    public static function alarmWithException($title, $exception, $tag = '', $receiver = []) {
        $content = sprintf("错误码: %s\r\n文件名: %s\r\n行号: %d\r\n错误信息: %s",
            $exception->getCode(), $exception->getFile(), $exception->getLine(), self::simpleMsg($exception->getMessage()));
        self::sendAlarm($title, $content, $tag, $receiver);
    }

    /**
     * 发送邮件及微信
     * @param string       $title    标题
     * @param string       $content  内容
     * @param string       $tag      标签
     * @param array|string $receiver 收件人, 不推荐, 一般在调试或应急使用
     */
    public static function sendAlarm($title, $content, $tag = '', $receiver = []) {
        self::sendMail($title, $content, $tag, $receiver);
        self::sendWx($title, $content, $tag, $receiver);
    }

    /**
     * 发送邮件
     * @param string       $title    标题
     * @param string       $content  内容
     * @param string       $tag      标签
     * @param array|string $receiver 收件人, 不推荐, 一般在调试或应急使用
     */
    public static function sendMail($title, $content, $tag = '', $receiver = []) {
        self::alarmBefore($title, $content, $tag);
        try {
            Alarm::alarmMail($title, $content, $tag, $receiver);
        } catch(\Exception $e) {

        }
    }

    /**
     * 发送邮件
     * @param string       $title    标题
     * @param string       $content  内容
     * @param string       $tag      标签
     * @param array|string $receiver 收件人, 不推荐, 一般在调试或应急使用
     */
    public static function sendWx($title, $content, $tag = '', $receiver = []) {
        self::alarmBefore($title, $content, $tag);
        try {
            Alarm::alarmWx($title, $content, $tag, $receiver);
        } catch(\Exception $e) {

        }
    }

    /**
     * 邮件/微信发送准备
     * @param string $title
     * @param string $content
     * @param string $tag
     */
    protected static function alarmBefore(&$title, &$content, &$tag) {
        isProdEnv() || $title = sprintf('【测%s】%s', $_SERVER['HTTP_HOST'], $title);
        $content = substr($content, 0, config('mail.content_size'));
        if(empty($tag)) {
            $tag = isProdEnv() ? config('mail.receiver_prod') : (isTestingEnv() ? config('mail.receiver_test') : config('mail.receiver_dev'));
        }
    }

    /**
     * 将太长的信息简化
     * 首尾保留100字
     * @param string $msg
     * @return string
     */
    public static function simpleMsg($msg) {
        if(strlen($msg) > 1024 * 2) {
            return substr($msg, 0, 100) . ' ... ' . substr($msg, -100);
        }
        return $msg;
    }

    /**
     * 格式化时间间隔
     * 如40 seconds, 2.3 hours
     * @param int $period 时间间隔, 单位毫秒
     * @param int $scale  精度
     * @return string
     */
    public static function formatPeriod($period, $scale = 2) {
        $units = [
            'seconds' => 1000,
            'minutes' => 60,
            'hours'   => 60,
            'days'    => 24
        ];
        $unit = 'ms';
        foreach($units as $key => $value) {
            if($period >= $value) {
                $period = round($period / $value, $scale);
                $unit = $key;
            } else {
                break;
            }
        }
        return sprintf('%.' . $scale . 'f %s', $period, $unit);
    }

    private static function _getSignWithDate($data = [], $secret = '') {
        return self::createSn($data, $secret);
    }

    /**
     * 签名生成函数
     * @param array  $data     参数数组
     * @param string $secret   密钥
     * @param bool   $use_date 签名中是否使用日期
     * @return string  签名
     */
    public static function createSn($data, $secret = '', $use_date = false) {
        $ignores = ['sn', 'debug'];
        foreach($ignores as $ignore) {
            if(isset($data[$ignore])) {
                unset($data[$ignore]);
            }
        }
        ksort($data);
        $str = urldecode(http_build_query($data, '', '&', PHP_QUERY_RFC3986)) . $secret;
        $use_date && $str .= date('Y-m-d');
        return strtolower(md5($str));
    }
}