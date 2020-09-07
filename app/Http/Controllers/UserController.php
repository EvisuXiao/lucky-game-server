<?php

namespace App\Http\Controllers;

use App\Models\UserModel;

class UserController extends Controller
{
    protected $userModel = null;

    public function __construct(UserModel $userModel) {
        parent::__construct();
        $this->userModel = $userModel;
    }

    public function list() {
        return $this->succReturn($this->userModel->getRecList());
    }

    public function add() {
        $add = $this->userModel->addUser($this->input['nickname'], $this->input['phone'], $this->input['wechat_id'], $this->input['location']);
        return !empty($add) ? $this->succReturn() : $this->failReturn();
    }

    public function update() {
        $upd = $this->userModel->updateRec($this->input);
        return !empty($upd) ? $this->succReturn() : $this->failReturn();
    }

    public function delete() {
        $upd = $this->userModel->setRecEnabled($this->input['id'], false);
        return !empty($upd) ? $this->succReturn() : $this->failReturn();
    }
}
