<?php
/**
 * 用户自定义全局方法
 * Created by PhpStorm.
 * User: evisu
 * Date: 2018/7/3
 * Time: 下午2:58
 */

if(!function_exists('siteEnv')) {
    /**
     * 判断当前环境是否为生产环境
     * @return bool
     */
    function siteEnv() {
        return env('APP_ENV', 'testing');
    }
}

if(!function_exists('isProdEnv')) {
    /**
     * 判断当前环境是否为生产环境
     * @return bool
     */
    function isProdEnv() {
        return siteEnv() === 'production';
    }
}

if(!function_exists('isTestingEnv')) {
    /**
     * 判断当前环境是否为测试环境
     * @return bool
     */
    function isTestingEnv() {
        return siteEnv() === 'testing';
    }
}

if(!function_exists('isLocalEnv')) {
    /**
     * 判断当前环境是否为开发环境
     * @return bool
     */
    function isLocalEnv() {
        return siteEnv() === 'local';
    }
}

if(!function_exists('isHttps')) {
    /**
     * 是否是https请求
     * @return bool
     */
    function isHttps() {
        return request()->isSecure();
    }
}

if(!function_exists('array_add_item')) {
    /**
     * 向数组中添加不存在的值
     * @param array $array
     * @param int|string $value
     */
    function array_add_item(&$array, $value) {
        in_array($value, $array) || $array[] = $value;
    }
}

if(!function_exists('array_contain')) {
    /**
     * 数组是否包含键
     * @param array        $array
     * @param array|string $keys
     * @return bool
     */
    function array_contain($array, $keys = []) {
        if(empty($keys)) {
            return false;
        }
        is_array($keys) || $keys = [$keys];
        $flag = true;
        foreach($keys as $key) {
            if(!isset($array[$key])) {
                $flag = false;
                break;
            }
        }
        return $flag;
    }
}

if(!function_exists('array_compare')) {
    /**
     * 比较两数组
     * @param array $before
     * @param array $after
     * @return array $result ['add' => $after比$before增加的元素, 'sub' => 减少的元素, 'assoc' => 交集, 'merge' => 并集]
     */
    function array_compare($before, $after) {
        $result = array_fill_keys(['add', 'sub', 'assoc', 'merge'], []);
        foreach($after as $value) {
            array_add_item($result['merge'], $value);
            array_add_item($result[in_array($value, $before) ? 'assoc' : 'add'], $value);
        }
        foreach($before as $value) {
            array_add_item($result['merge'], $value);
            !in_array($value, $after) && array_add_item($result['sub'], $value);
        }
        return $result;
    }
}

if(!function_exists('array_equal')) {
    /**
     * 比较两数组是否相同
     * @param array $before
     * @param array $after
     * @return bool
     */
    function array_equal($before, $after) {
        $compare = array_compare($before, $after);
        return empty($compare['add']) && empty($compare['sub']);
    }
}

if(!function_exists('msleep')) {
    /**
     * 毫秒记sleep
     * @param int $ms 毫秒数
     */
    function msleep($ms) {
        usleep($ms * 1000);
    }
}

if(!function_exists('millitime')) {
    /**
     * 获取当前毫秒数
     * @param float|null $ms
     * @return int
     */
    function millitime($ms = null) {
        return floor((is_null($ms) ? microtime(true) : $ms) * 1000);
    }
}

if(!function_exists('datetimeStr')) {
    /**
     * 获取格式化时间
     * 相当于将原生方法date()的$format增加默认值, 并交换参数位置
     * @param int|null $time
     * @param string   $format
     * @return string
     */
    function datetimeStr($time = null, $format = 'Y-m-d H:i:s') {
        is_null($time) && $time = time();
        return date($format, $time);
    }
}

if(!function_exists('spend')) {
    /**
     * 获取项目执行时间
     * 单位ms
     * @return int
     */
    function spend() {
        return intval(millitime() - millitime(LUMEN_START));
    }
}

if(!function_exists('apiTemplate')) {
    function apiTemplate($code = HTTP_RESPONSE_FAIL_CODE, $msg = HTTP_RESPONSE_FAIL_MSG, $data = []) {
        return [
            'code'    => $code,
            'message' => $msg,
            'data'    => $data,
            'cost'    => spend()
        ];
    }
}