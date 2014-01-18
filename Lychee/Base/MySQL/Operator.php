<?php
/**
 * Copyright 2013 koboshi(Samding)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Lychee\Base\MySQL;

/**
 * Mysql query operator
 * @author Samding
 * @package Lychee\Base\MySQL
 */
class Operator
{

    /**
     * greater than
     * @var string
     */
    const QUERY_GT = '$>';

    /**
     * greater than equal
     * @var string
     */
    const QUERY_GTE = '$>=';

    /**
     * less than
     * @var string
     */
    const QUERY_LT = '$<';

    /**
     * less than equal
     * @var string
     */
    const QUERY_LTE = '$<=';

    /**
     * IN
     * @var string
     */
    const QUERY_IN = '$IN';

    /**
     * NOT IN
     * @var string
     */
    const QUERY_NOT_IN = '$NOT IN';

    /**
     * equal
     * @var string
     */
    const QUERY_EQUAL = '$=';

    /**
     * like
     * @var string
     */
    const QUERY_LIKE = '$like';

    /**
     * not equal
     * @var string
     */
    const QUERY_NE = '$!=';

    /**
     * and
     * @var string
     */
    const QUERY_AND = '$AND';

    /**
     * or
     * @var string
     */
    const QUERY_OR = '$OR';

    /**
     * between
     * @var string
     */
    const QUERY_BETWEEN = '$BETWEEN';

    /**
     * ascending
     * @var string
     */
    const SORT_ASC = '$ASC';

    /**
     * descending
     * @var string
     */
    const SORT_DESC = '$DESC';

    /**
     * inner join
     * @var string
     */
    const JOIN_INNTER = '$INNER JOIN';

    /**
     * outter join
     * @var string
     */
    const JOIN_OUTTER = '$OUTTER JOIN';

    /**
     * left join
     * @var string
     */
    const JOIN_LEFT = '$LEFT JOIN';

    /**
     * right join
     * @var string
     */
    const JOIN_RIGHT = '$RIGHT JOIN';
}