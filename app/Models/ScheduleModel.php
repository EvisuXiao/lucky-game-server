<?php
/**
 * Created by PhpStorm.
 * User: evisu
 * Date: 2018/7/3
 * Time: 下午1:58
 */

namespace App\Models;

class ScheduleModel extends DaoModel
{
    protected $table = 'schedule';

    public function addSchedule($home_team_id, $away_team_id, $game_time) {
        return $this->addRec(compact('home_team_id', 'away_team_id', 'game_time'));
    }
}