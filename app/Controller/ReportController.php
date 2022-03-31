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
namespace App\Controller;

use App\Request\PaginationRequest;
use App\Request\SaveReportItemRequest;
use App\Service\ReportService;
use Hyperf\Di\Annotation\Inject;

class ReportController extends Controller
{
    #[Inject]
    protected ReportService $service;

    public function index(PaginationRequest $request)
    {
        $userId = get_user_id();

        $list = $this->service->find($userId, $request->offset(), $request->limit());

        return $this->response->success($list);
    }

    public function addItem(SaveReportItemRequest $request)
    {
        $id = (int) $request->input('id');
        $project = (string) $request->input('project');
        $module = (string) $request->input('module');
        $summary = (string) $request->input('summary');
        $beginTime = (string) $request->input('begin_time');
        $endTime = (string) $request->input('end_time');
        $userId = get_user_id();

        $result = $this->service->addItem($id, $userId, $project, $module, $summary, $beginTime, $endTime);

        return $this->response->success($result);
    }

    public function deleteItem(int $id)
    {
        $userId = get_user_id();

        $result = $this->service->deleteItem($id, $userId);

        return $this->response->success($result);
    }

    public function items(string $token)
    {
        // TODO: $reportId = redis->get($token);
        $reportId = 1;
        $result = $this->service->items($reportId);

        return $this->response->success($result);
    }
}
