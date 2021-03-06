<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Server Error")
     */
    public const SERVER_ERROR = 500;

    /**
     * @Message("Token 已失效")
     */
    public const TOKEN_INVALID = 700;

    /**
     * @Message("越权操作")
     */
    public const PERMISSION_INVALID = 701;

    /**
     * @Message("参数非法")
     */
    public const PARAMS_INVALID = 1000;

    /**
     * @Message("用户不存在")
     */
    public const USER_NOT_EXIST = 1100;

    /**
     * @Message("当前日报条目不存在")
     */
    public const REPORT_ITEM_NOT_EXIST = 1200;

    /**
     * @Message("只能修改当日日报条目")
     */
    public const REPORT_ITEM_CANNOT_UPDATE = 1201;

    /**
     * @Message("日报临时票据已失效")
     */
    public const REPORT_TOKEN_EXPIRED = 1202;

    /**
     * @Message("当前日报不存在")
     */
    public const REPORT_NOT_EXIST = 1203;
}
