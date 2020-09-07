<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;

class SettingController extends Controller
{
    public function show() {
        return $this->succReturn(config('game'));
    }

    public function update() {
        foreach($this->input as $key => $value) {
            Config::write("game.{$key}", $value);
        }
        return $this->succReturn();
    }
}
