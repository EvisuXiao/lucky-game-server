<?php
/**
 * Created by PhpStorm.
 * User: evisu
 * Date: 2020/9/5
 * Time: 3:19 AM
 */

namespace App\Repositories;

use App\Models\ContestModel;
use App\Models\ScheduleModel;
use App\Models\TeamModel;
use Illuminate\Support\Arr;

class GameRepository extends BaseRepository {
    protected $teamModel = null;
    protected $scheduleModel = null;
    protected $contestModel = null;

    public function __construct(TeamModel $teamModel, ScheduleModel $scheduleModel, ContestModel $contestModel) {
        $this->teamModel = $teamModel;
        $this->scheduleModel = $scheduleModel;
        $this->contestModel = $contestModel;
    }

    public function randomSchedule($date) {
        $moments = [
            ['10:00', 3],
            ['15:00', 3],
            ['20:00', 3]
        ];
        $team_ids = $this->teamModel->getRecList('id');
        $flag = false;
        foreach($moments as $moment) {
            if($flag) {
                break;
            }
            for($i = 0; $i < $moment[1]; $i++) {
                if(count($team_ids) < 2) {
                    $flag = true;
                    break;
                }
                $home_team_idx = array_rand($team_ids);
                $home_team_id = Arr::pull($team_ids, $home_team_idx);
                $away_team_idx = array_rand($team_ids);
                $away_team_id = Arr::pull($team_ids, $away_team_idx);
                $this->scheduleModel->addSchedule($home_team_id, $away_team_id, "{$date} {$moment[0]}");
            }
        }
    }
}