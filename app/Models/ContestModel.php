<?php
/**
 * Created by PhpStorm.
 * User: evisu
 * Date: 2018/7/3
 * Time: 下午1:58
 */

namespace App\Models;

class ContestModel extends DaoModel
{
    protected $table = 'contest';

    public function addContest($schedule_id, $user_id, $bet) {
        return $this->addRec(compact('schedule_id', 'user_id', 'bet'));
    }
}