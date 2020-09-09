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
use App\Models\UserModel;
use Illuminate\Support\Arr;

class GameRepository extends BaseRepository {
    protected $userModel = null;
    protected $teamModel = null;
    protected $scheduleModel = null;
    protected $contestModel = null;

    public function __construct(UserModel $userModel, TeamModel $teamModel, ScheduleModel $scheduleModel, ContestModel $contestModel) {
        $this->userModel = $userModel;
        $this->teamModel = $teamModel;
        $this->scheduleModel = $scheduleModel;
        $this->contestModel = $contestModel;
    }

    public function getScheduleList($date) {
        $where = [];
        if(!empty($date)) {
            $where['game_time >='] = $date . ' 00:00:00';
            $where['game_time <='] = $date . ' 23:59:59';
        }
        return $this->scheduleModel->getRecList([DB_SELECT_ALL], $where);
    }

    public function getGameList($nickname, $date) {
        $user = $this->userModel->getUserByNickname($nickname);
        if(empty($user)) {
            return [];
        }
        $schedules = $this->getScheduleList($date);
        $schedule_ids = array_column($schedules, 'id');
        $contest_list = $this->contestModel->getRecList(['schedule_id', 'bet', 'success', 'lucky'], ['in' => ['schedule_id' => $schedule_ids], 'user_id' => $user]);
        $contest_info = array_column($contest_list, null, 'schedule_id');
        foreach($schedules as &$schedule) {
            $schedule['bet'] = 0;
            $schedule['success'] = false;
            $schedule['lucky'] = false;
            if(isset($contest_info[$schedule['id']])) {
                $contest = $contest_info[$schedule['id']];
                $schedule['bet'] = $contest['bet'];
                $schedule['success'] = $contest['success'];
                $schedule['lucky'] = $contest['lucky'];
            }
        }
        return $schedules;
    }

    public function betGames($nickname, $games) {
        $user = $this->userModel->getUserByNickname($nickname);
        if(empty($user)) {
            return 0;
        }
        $contest = [];
        foreach($games as $schedule_id => $bet) {
            $contest[] = [
                'schedule_id'   => $schedule_id,
                'user_id'       => $user['id'],
                'bet'           => $bet
            ];
        }
        return $this->contestModel->addRec($contest);
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