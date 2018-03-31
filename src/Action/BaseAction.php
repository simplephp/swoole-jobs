<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) kcloze <pei.greet@qq.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kcloze\Jobs\Action;

use Kcloze\Jobs\JobObject;

abstract class BaseAction
{
    public function init()
    {
    }

    public function start(JobObject $JobObject)
    {
    }
}
