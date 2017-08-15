<?php
return array(
	//'配置项'=>'配置值'

    /* 模板相关配置 */
    'TMPL_PARSE_STRING'     => [
        '__STATIC__'        => __ROOT__ . '/Public/static',
        '__STATIC_IMG__'    => __ROOT__ . '/Public/static/img',
        '__STATIC_CSS__'    => __ROOT__ . '/Public/static/css',
        '__STATIC_JS__'     => __ROOT__ . '/Public/static/js',
        '__STATIC_PLUGIN__' => __ROOT__ . '/Public/static/plugin',
        '__IMG__'           => __ROOT__ . '/Public/' . MODULE_NAME . '/img',
        '__CSS__'           => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
        '__JS__'            => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
        '__PLUGIN__'        => __ROOT__ . '/Public/' . MODULE_NAME . '/plugin',
    ],
);