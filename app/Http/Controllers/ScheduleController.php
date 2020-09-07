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
        return $this->succReturn($this->scheduleModel->getRecList());
    }

    public function add() {
        $add = $this->scheduleModel->addSchedule($this->input['home_team_id'], $this->input['away_team_id'], $this->input['game_time']);
        return !empty($add) ? $this->succReturn() : $this->failReturn();
    }

    public function random() {
        $this->gameRepository->randomSchedule($this->input['date']);
        return $this->succReturn();
    }
}
