<?php

/**
 * 公共函数
 */


/**
 * 获取和设置全局变量 支持批量定义
 * @param string|array $name 全局变量
 * @param mixed $value 值
 * @param mixed $default 默认值
 * @return mixed
 */
function V($name=null, $value=null, $default=null) {
    static $_global_custom_variable = array();
    // 无参数时获取所有
    if (empty($name)) {
        return $_global_custom_variable;
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            if (is_null($value)){
                return isset($_global_custom_variable[$name]) ? $_global_custom_variable[$name] : $default;
            }
            $_global_custom_variable[$name] = $value;
            return null;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        if (is_null($value)){
            return isset($_global_custom_variable[$name[0]][$name[1]]) ? $_global_custom_variable[$name[0]][$name[1]] : $default;
        }
        $_global_custom_variable[$name[0]][$name[1]] = $value;
        return null;
    }
    // 批量设置
    if (is_array($name)){
        $_global_custom_variable = array_merge($_global_custom_variable, $name);
        return null;
    }
    return null; // 避免非法参数
}
