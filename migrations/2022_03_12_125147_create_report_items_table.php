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
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateReportItemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->default(0)->index('INDEX_USER_ID')->comment('用户ID');
            $table->unsignedBigInteger('report_id')->default(0)->index('INDEX_REPORT_ID')->comment('报告ID');
            $table->string('project', 32)->default('')->comment('项目');
            $table->string('module', 32)->default('')->comment('模块');
            $table->string('summary', 1024)->default('')->comment('工作详情');
            $table->string('begin_time', 5)->comment('工作开始时间');
            $table->string('end_time', 5)->nullable()->comment('工作结束时间');
            $table->unsignedInteger('used_time')->default(0)->comment('耗时');
            $table->json('extra')->nullable()->comment('额外信息');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_items');
    }
}
