<?php

namespace App\Http\Controllers;

use App\Models\TeamModel;

class TeamController extends Controller
{
    protected $teamModel = null;

    public function __construct(TeamModel $teamModel) {
        parent::__construct();
        $this->teamModel = $teamModel;
    }

    public function list() {
        return $this->succReturn($this->teamModel->getRecList());
    }

    public function add() {
        $add = $this->teamModel->addTeam($this->input['name'], $this->input['en_name']);
        return !empty($add) ? $this->succReturn() : $this->failReturn();
    }

    public function update() {
        $upd = $this->teamModel->updateRec($this->input);
        return !empty($upd) ? $this->succReturn() : $this->failReturn();
    }

    public function delete() {
        $upd = $this->teamModel->setRecEnabled($this->input['id'], false);
        return !empty($upd) ? $this->succReturn() : $this->failReturn();
    }
}
