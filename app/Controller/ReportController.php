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

use App\Request\SaveReportItemRequest;
use App\Service\ReportService;
use Hyperf\Di\Annotation\Inject;

class ReportController extends Controller
{
    #[Inject]
    protected ReportService $service;

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
}
