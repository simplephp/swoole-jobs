<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) kcloze <pei.greet@qq.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kcloze\Jobs\Queue;

use Kcloze\Jobs\JobObject;
use Kcloze\Jobs\Logs;

interface TopicQueueInterface
{
    public static function getConnection(array $config, Logs $logger);

    /**
     * @return array a array of topics
     */
    public function getTopics();

    /**
     * @param array $topics
     */
    public function setTopics(array $topics);

    /**
     * 推送队列，返回jobid字符串.
     *
     * @param [type]    $topic
     * @param JobObject $job
     *
     * @return string
     */
    public function push($topic, JobObject $job);

    /**
     * 从队列拿消息.
     *
     * @param [type] $topic
     *
     * @return array
     */
    public function pop($topic);

    /**
     * @param $topic
     *
     * @return int
     */
    public function len($topic);

    public function close();

    public function isConnected();
}
