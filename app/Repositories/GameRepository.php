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

    public function saveScore($id, $home_team_score, $away_team_score) {
        $game_result = $home_team_score > $away_team_score ? 1 : ($home_team_score < $away_team_score ? 3 : 2);
        $contests = $this->contestModel->getRecList(['id', 'bet', 'lucky'], ['schedule_id' => $id]);
        $success_ids = [];
        $failure_ids = [];
        foreach($contests as $contest) {
            if($contest['lucky']) {
                throw new \Exception('开奖后不可再修改比分');
            }
            if($contest['bet'] == $game_result) {
                $success_ids[] = $contest['id'];
            } else {
                $failure_ids[] = $contest['id'];
            }
        }
        $this->scheduleModel->updateRecById($id, compact('home_team_score', 'away_team_score', 'game_result'));
        if(!empty($success_ids)) {
            $this->contestModel->updateRecById($success_ids, ['success' => true]);
        }
        if(!empty($failure_ids)) {
            $this->contestModel->updateRecById($failure_ids, ['success' => false]);
        }
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

    public function getBetRecordByUser($nickname) {
        $user = $this->userModel->getUserByNickname($nickname);
        if(empty($user)) {
            return [];
        }
        $contests = $this->contestModel->getRecList(['schedule_id', 'bet', 'success', 'lucky', 'created_at'], ['user_id' => $user['id']]);
        if(empty($contests)) {
            return [];
        }
        $schedule_ids = array_column($contests, 'schedule_id');
        $schedules = $this->scheduleModel->getRecInfoById($schedule_ids);
        $schedule_info = array_column($schedules, null, 'id');
        foreach($contests as &$contest) {
            $schedule = $schedule_info[$contest['schedule_id']] ?? [];
            $contest = array_merge($schedule, $contest);
        }
        return $contests;
    }

    public function getBetRecordBySchedule($schedule_id) {
        $contests = $this->contestModel->getRecList(['id', 'user_id', 'bet', 'success', 'lucky', 'created_at'], ['schedule_id' => $schedule_id]);
        if(empty($contests)) {
            return [];
        }
        $user_ids = array_column($contests, 'user_id');
        $users = $this->userModel->getRecInfoById($user_ids, ['id', 'nickname', 'phone', 'wechat_id']);
        $user_info = array_column($users, null, 'id');
        foreach($contests as &$contest) {
            $contest['user_nickname'] = '';
            $contest['user_phone'] = '';
            $contest['user_wechat_id'] = '';
            if(isset($user_info[$contest['user_id']])) {
                $user = $user_info[$contest['user_id']];
                $contest['user_nickname'] = $user['nickname'];
                $contest['user_phone'] = $user['phone'];
                $contest['user_wechat_id'] = $user['wechat_id'];
            }
        }
        return $contests;
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

    public function chooseLuckyUser($schedule_id) {
        $user_ids = $this->userModel->getRecList('id');
        $contests = $this->contestModel->getRecList(['id', 'lucky'], ['schedule_id' => $schedule_id, 'in' => ['user_id' => $user_ids], 'success' => true]);
        $lucky_ids = [];
        foreach($contests as $contest) {
            if($contest['lucky']) {
                throw new \Exception('本场已开过奖');
            }
            $lucky_ids[] = $contest['id'];
        }
        $success_num = config('game.success_num');
        if(empty($success_num)) {
            return;
        }
        if(count($lucky_ids) > $success_num) {
            $lucky_ids = Arr::random($lucky_ids, $success_num);
        }
        $this->contestModel->updateRecById($lucky_ids, ['lucky' => true]);
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