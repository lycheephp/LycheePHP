<?php
/**
 * 数据库运算符
 * @author Samding
 */
namespace Lychee\DB;

class Operator
{

    /**
     * 大于
     * @var string
     */
    const QUERY_GT = '$>';

    /**
     * 大于等于
     * @var string
     */
    const QUERY_GTE = '$>=';

    /**
     * 小于
     * @var string
     */
    const QUERY_LT = '$<';

    /**
     * 小于等于
     * @var string
     */
    const QUERY_LTE = '$<=';

    /**
     * IN
     * @var string
     */
    const QUERY_IN = '$IN';

    /**
     * NOT IN操作
     * @var string
     */
    const QUERY_NOT_IN = '$NOT IN';

    /**
     * 等于
     * @var string
     */
    const QUERY_EQUAL = '$=';

    /**
     * 搜索
     * @var string
     */
    const QUERY_LIKE = '$like';

    /**
     * 不等于
     * @var string
     */
    const QUERY_NE = '$!=';

    /**
     * AND
     * @var string
     */
    const QUERY_AND = '$AND';

    /**
     * OR
     * @var string
     */
    const QUERY_OR = '$OR';

    /**
     * betwwen
     * @var string
     */
    const QUERY_BETWEEN = '$BETWEEN';

    /**
     * 升序排序
     * @var string
     */
    const SORT_ASC = '$ASC';

    /**
     * 降序排序
     * @var string
     */
    const SORT_DESC = '$DESC';

    /**
     * 内链接表
     * @var string
     */
    const JOIN_INNTER = '$INNER JOIN';

    /**
     * 外连接表
     * @var string
     */
    const JOIN_OUTTER = '$OUTTER JOIN';

    /**
     * 左连接表
     * @var string
     */
    const JOIN_LEFT = '$LEFT JOIN';

    /**
     * 右连接表
     * @var string
     */
    const JOIN_RIGHT = '$RIGHT JOIN';
}