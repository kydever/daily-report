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
namespace App\Service;

use App\Model\User;
use Carbon\Carbon;
use Hyperf\Redis\Redis;
use function di;
use function Han\Utils\date_load;

class WorkToday
{
    public const NOT_START = 0;

    public const WAIT_TO_INPUT_PROJECT = 1;

    public const WAIT_TO_INPUT_MODULE = 2;

    public const WAIT_TO_INPUT_SUMMARY = 3;

    protected string $project = '';

    protected string $module = '';

    protected int $beginTime = 0;

    public function __construct(protected int $userId, protected string $openId, protected int $step = 0)
    {
    }

    public static function load(User $user): static
    {
        $key = 'step:' . date('Y-m-d') . ':' . $user->id;
        $data = di()->get(Redis::class)->get($key);
        if (empty($data)) {
            $self = new self($user->id, $user->open_id);
            di()->get(Redis::class)->set($key, serialize($self), 86400);
        } else {
            $self = unserialize($data);
        }

        return $self;
    }

    public function begin(): static
    {
        $this->step = self::WAIT_TO_INPUT_PROJECT;
        return $this;
    }

    public function quit(): static
    {
        $this->step = self::NOT_START;
        return $this;
    }

    public function save(): static
    {
        $key = 'step:' . date('Y-m-d') . ':' . $this->userId;

        di()->get(Redis::class)->set($key, serialize($this), 86400);

        return $this;
    }

    public function next(): static
    {
        $this->step = match ($this->step) {
            self::NOT_START => self::WAIT_TO_INPUT_PROJECT,
            self::WAIT_TO_INPUT_PROJECT => self::WAIT_TO_INPUT_MODULE,
            self::WAIT_TO_INPUT_MODULE, self::WAIT_TO_INPUT_SUMMARY => self::WAIT_TO_INPUT_SUMMARY,
            default => self::NOT_START,
        };

        return $this;
    }

    public function isStart(): bool
    {
        return $this->step > 0;
    }

    public function handle(string $content): static
    {
        switch ($this->step) {
            case self::WAIT_TO_INPUT_PROJECT:
                $this->project = $content;
                $this->beginTime = time();
                break;
            case self::WAIT_TO_INPUT_MODULE:
                $this->module = $content;
                break;
            case self::WAIT_TO_INPUT_SUMMARY:
                $now = Carbon::now();
                $beginTime = date_load($this->beginTime);

                di()->get(ReportService::class)->addItem(
                    0,
                    $this->userId,
                    $this->project,
                    $this->module,
                    $content,
                    $beginTime,
                    $now
                );

                $this->beginTime = $now->getTimestamp();
                break;
        }

        return $this;
    }

    public function afterHandle(): void
    {
        di()->get(WeChatService::class)->sendText($this->openId, match ($this->step) {
            self::NOT_START => '已退出快捷日报模式',
            self::WAIT_TO_INPUT_PROJECT => '请输入当前项目',
            self::WAIT_TO_INPUT_MODULE => '请输入当前模块',
            self::WAIT_TO_INPUT_SUMMARY => '请输入完成的任务',
        });
    }
}
