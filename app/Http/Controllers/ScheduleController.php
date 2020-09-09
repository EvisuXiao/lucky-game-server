<?php

namespace App\Http\Controllers;

use App\Models\ScheduleModel;
use App\Repositories\GameRepository;

class ScheduleController extends Controller
{
    protected $gameRepository = null;
    protected $scheduleModel = null;

    public function __construct(GameRepository $gameRepository, ScheduleModel $scheduleModel) {
        parent::__construct();
        $this->gameRepository = $gameRepository;
        $this->scheduleModel = $scheduleModel;
    }

    public function list() {
        $where = [];
        if($this->input['date']) {
            $where['game_time >='] = $this->input['date'] . ' 00:00:00';
            $where['game_time <='] = $this->input['date'] . ' 23:59:59';
        }
        return $this->succReturn($this->scheduleModel->getRecList([DB_SELECT_ALL], $where));
    }

    public function add() {
        $add = $this->scheduleModel->addRec($this->input['schedules']);
        return !empty($add) ? $this->succReturn() : $this->failReturn();
    }

    public function update() {
        try {
            $this->gameRepository->saveScore($this->input['id'], $this->input['home_team_score'], $this->input['away_team_score']);
        } catch(\Exception $e) {
            return $this->failReturn($e->getMessage());
        }
        return $this->succReturn();
    }

    public function delete() {
        $upd = $this->scheduleModel->setRecEnabled($this->input['id'], false);
        return !empty($upd) ? $this->succReturn() : $this->failReturn();
    }

    public function random() {
        $this->gameRepository->randomSchedule($this->input['date']);
        return $this->succReturn();
    }

    public function record() {
        return $this->succReturn($this->gameRepository->getBetRecordBySchedule($this->input['schedule_id']));
    }
}
