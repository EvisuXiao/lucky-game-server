<?php
/**
 * Created by PhpStorm.
 * User: evisu
 * Date: 2018/7/3
 * Time: 下午6:45
 */

/**
 * GET请求方法名
 */
!defined('HTTP_METHOD_GET') && define('HTTP_METHOD_GET', 'GET');
/**
 * POST请求方法名
 */
!defined('HTTP_METHOD_POST') && define('HTTP_METHOD_POST', 'POST');
/**
 * PUT请求方法名
 */
!defined('HTTP_METHOD_PUT') && define('HTTP_METHOD_PUT', 'PUT');
/**
 * DELETE请求方法名
 */
!defined('HTTP_METHOD_DELETE') && define('HTTP_METHOD_DELETE', 'DELETE');
/**
 * 验证基本规则
 */
!defined('VALIDATE_RULE_BASE') && define('VALIDATE_RULE_BASE', 'BASE');
/*
 * http接口响应成功
 */
!defined('HTTP_RESPONSE_SUCCESS_CODE') && define('HTTP_RESPONSE_SUCCESS_CODE', 1);
/*
 * http接口(用于MQ)响应成功
 */
!defined('HTTP_RESPONSE_MQ_SUCCESS_CODE') && define('HTTP_RESPONSE_MQ_SUCCESS_CODE', 0);

/*
 * http接口响应失败
 */
!defined('HTTP_RESPONSE_FAIL_CODE') && define('HTTP_RESPONSE_FAIL_CODE', -1);

/*
 * http接口响应成功返回信息
 */
!defined('HTTP_RESPONSE_SUCCESS_MSG') && define('HTTP_RESPONSE_SUCCESS_MSG', '操作成功');

/*
 * http接口响应失败返回信息
 */
!defined('HTTP_RESPONSE_FAIL_MSG') && define('HTTP_RESPONSE_FAIL_MSG', '操作失败');

/*
 * http接口缺少参数返回信息
 */
!defined('HTTP_RESPONSE_PARAM_MSG') && define('HTTP_RESPONSE_PARAM_MSG', '参数传递错误');