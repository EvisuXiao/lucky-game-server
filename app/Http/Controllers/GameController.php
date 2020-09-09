<?php

namespace App\Http\Controllers;

use App\Models\ScheduleModel;
use App\Repositories\GameRepository;

class GameController extends Controller
{
    protected $gameRepository = null;
    protected $scheduleModel = null;

    public function __construct(GameRepository $gameRepository, ScheduleModel $scheduleModel) {
        parent::__construct();
        $this->gameRepository = $gameRepository;
        $this->scheduleModel = $scheduleModel;
    }

    public function list() {
        return $this->succReturn($this->gameRepository->getGameList($this->input['nickname'], $this->input['date']));
    }

    public function bet() {
        $succ = $this->gameRepository->betGames($this->input['nickname'], $this->input['games']);
        return !empty($succ) ? $this->succReturn() : $this->failReturn();
    }

    public function record() {
        return $this->succReturn($this->gameRepository->getBetRecordByUser($this->input['nickname']));
    }

    public function lucky() {
        try {
            $this->gameRepository->chooseLuckyUser($this->input['schedule_id']);
        } catch(\Exception $e) {
            return $this->failReturn($e->getMessage());
        }
        return $this->succReturn();
    }
}
