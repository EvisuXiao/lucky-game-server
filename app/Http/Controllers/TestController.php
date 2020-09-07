<?php

namespace App\Http\Controllers;

use App\Models\TeamModel;
use App\Models\UserModel;

class TestController extends Controller
{
    protected $userModel = null;
    protected $teamModel = null;

    public function __construct(UserModel $userModel, TeamModel $teamModel) {
        parent::__construct();
        $this->userModel = $userModel;
        $this->teamModel = $teamModel;
    }

    public function init() {
        for($i = 1; $i <= 10; $i++) {
            $this->userModel->addUser('用户' . $i, '13800138000', '');
        }
        echo '添加用户成功' . PHP_EOL;
        $teams = [
            ['亚特兰大老鹰队', 'Atlanta Hawks'],
            ['波士顿凯尔特人队', 'Boston Celtics'],
            ['芝加哥公牛队', 'Chicago Bulls'],
            ['达拉斯独行侠队', 'Dallas Mavericks'],
            ['丹佛掘金队', 'Denver Nuggets'],
            ['金州勇士队', 'Golden State Warriors'],
            ['夏洛特黄蜂队', 'Charlotte Hornets'],
            ['布鲁克林篮网队', 'Brooklyn Nets'],
            ['克利夫兰骑士队', 'Cleveland Cavaliers'],
            ['休斯敦火箭队', 'Houston Rockets'],
            ['明尼苏达森林狼队', 'Minnesota Timberwolves'],
            ['洛杉矶快船队', 'Los Angeles Clippers'],
            ['迈阿密热火队', 'Miami Heat'],
            ['纽约尼克斯队', 'New York Knicks'],
            ['底特律活塞队', 'Detroit Pistons'],
            ['孟菲斯灰熊队', 'Memphis Grizzlies'],
            ['俄克拉荷马城雷霆队', 'Oklahoma City Thunder'],
            ['洛杉矶湖人队', 'Los Angeles Lakers'],
            ['奥兰多魔术队', 'Orlando Magic'],
            ['费城76人队', 'Philadelphia 76ers'],
            ['印第安纳步行者队', 'Indiana Pacers'],
            ['新奥尔良鹈鹕队', 'New Orleans Pelicans'],
            ['波特兰开拓者队', 'Portland Trail Blazers'],
            ['菲尼克斯太阳队', 'Phoenix Suns'],
            ['华盛顿奇才队', 'Washington Wizards'],
            ['多伦多猛龙队', 'Toronto Raptors'],
            ['密尔沃基雄鹿队', 'Milwaukee Bucks'],
            ['圣安东尼奥马刺队', 'San Antonio Spurs'],
            ['犹他爵士队', 'Utah Jazz'],
            ['萨克拉门托国王队', 'Sacramento Kings']
        ];
        foreach($teams as $team) {
            $this->teamModel->addTeam($team[0], $team[1]);
        }
        echo '添加球队成功' . PHP_EOL;
    }
}
