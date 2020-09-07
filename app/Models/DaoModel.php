<?php
/**
 * Created by PhpStorm.
 * User: evisu
 * Date: 2018/7/3
 * Time: 下午2:07
 */

namespace App\Models;


class DaoModel extends BaseModel
{
    public $timestamps = false;
    /**
     * 默认排序
     * @var array
     */
    protected $order = [];
    /**
     * 有效性字段
     * @var string
     */
    protected $enabled_key = 'enabled';

    /**
     * 根据id获取数据
     * @param int|array    $id      id, 支持批量
     * @param array|string $fields  查询字段
     * @param bool         $enabled 是否仅查询有效的
     * @param array        $option  额外选项, 如group, page, limit, master
     * @return array|mixed          如果$fields是string类型, 则结果褪去索引, 例: [['name' => 'aaa'], ['name' => 'bbb']] -> ['aaa',
     *                              'bbb']
     */
    public function getRecInfoById($id, $fields = [DB_SELECT_ALL], $enabled = true, $option = []) {
        if(empty($id)) {
            return $this->getRecList($fields, [], $enabled, [], $option);
        }
        if(!is_array($id)) {
            return $this->getRecInfo($fields, [$this->primaryKey => $id], $enabled, [], $option);
        }
        return $this->getRecList($fields, ['in' => [$this->primaryKey => $id]], $enabled, [], $option);
    }

    /**
     * 根据条件获取列表数据
     * @param array|string $fields  查询字段
     * @param array        $where   查询条件
     * @param bool         $enabled 是否仅查询有效的
     * @param array        $order   排序规则
     * @param array        $option  额外选项, 如group, page, limit, master
     * @return array                如果$fields是string类型, 则结果褪去索引, 例: [['name' => 'aaa'], ['name' => 'bbb']] -> ['aaa',
     *                              'bbb']
     */
    public function getRecList($fields = [DB_SELECT_ALL], $where = [], $enabled = true, $order = [], $option = []) {
        $enabled && $this->hasCast($this->enabled_key) && $where[$this->enabled_key] = $enabled;
        // 默认排序
        empty($order) && ($order = $this->order ?: [$this->primaryKey => DB_SORT_ASC]);
        // 是否是单一字段
        $only_field = is_string($fields) && $fields !== DB_SELECT_ALL && !str_contains($fields, ',');
        $list = $this->getAll($fields, $where, $order, $option);
        if(empty($list)) {
            return [];
        }
        return $only_field ? array_unique(array_column($list, $fields)) : $list;
    }

    public function getRecPageList($fields = [DB_SELECT_ALL], $where = [], $enabled = true, $page = 1, $page_size = DB_PAGE_SIZE, $order = [], $option = []) {
        $page > 0 || $page = 1;
        $page_size > 0 || $page_size = DB_PAGE_SIZE;
        $option['page'] = $page;
        $option['limit'] = $page_size;
        $count = $this->countBy($where);
        $list = $this->getRecList($fields, $where, $enabled, $order, $option);
        return compact('count', 'list');
    }

    /**
     * 根据条件获取单条数据
     * @param array|string $fields 查询字段
     * @param array        $where  查询条件
     * @param bool         $enabled 是否仅查询有效的
     * @param array        $order  排序规则
     * @param array        $option 额外选项, 如group, page, limit, master
     * @return array|mixed         如果$fields是string类型, 则结果直接为数值
     */
    public function getRecInfo($fields = [DB_SELECT_ALL], $where = [], $enabled = true, $order = [], $option = []) {
        $list = $this->getRecList($fields, $where, $enabled, $order, $option);
        return !empty($list) ? $list[0] : [];
    }

    /**
     * 设置该记录为有/无效
     * @param $id
     * @param $enabled
     * @return int
     */
    protected function setRecEnabled($id, $enabled) {
        if(!$this->hasCast($this->enabled_key)) {
            return 0;
        }
        return $this->updateRecById($id, [$this->enabled_key => $enabled]);
    }

    /**
     * 根据条件修改数据
     * 如果$data中存在id, 则将id加入到查询规则
     * 先根据条件查询id, 再根据id修改
     * @param array $data  修改数据
     * @param array $where 修改条件
     * @return int         修改条数
     */
    public function updateRec($data, $where = []) {
        if(isset($data[$this->primaryKey])) {
            $where[$this->primaryKey] = $data[$this->primaryKey];
            unset($data[$this->primaryKey]);
        }
        $id = $this->getRecList($this->primaryKey, $where, false);
        if(empty($id)) {
            return 0;
        }
        return $this->updateRecById($id, $data);
    }

    /**
     * 根据id修改数据
     * @param int|array $id   id, 支持批量
     * @param array     $data 修改数据
     * @return int            修改条数
     */
    public function updateRecById($id, $data) {
        if(empty($id)) {
            return 0;
        }
        if(isset($data[$this->primaryKey])) {
            unset($data[$this->primaryKey]);
        }
        if(is_array($id)) {
            return $this->updateBy($data, ['in' => [$this->primaryKey => $id]]);
        }
        return $this->updateBy($data, [$this->primaryKey => $id]);
    }

    /**
     * 根据条件删除数据
     * 先根据条件查询id, 再根据id删除
     * @param array $where 删除条件
     * @param int $limit   删除条数
     * @return int         删除条数
     */
    public function deleteRec($where, $limit = 200) {
        $id = $this->getRecList($this->primaryKey, $where, false);
        if(empty($id)) {
            return 0;
        }
        return $this->deleteRecById($id, $limit);
    }

    /**
     * 根据id删除数据
     * @param int|array $id id, 支持批量
     * @param int $limit    删除条数
     * @return int          删除条数
     */
    public function deleteRecById($id, $limit = 200) {
        if(empty($id)) {
            return 0;
        }
        if(is_array($id)) {
            return $this->deleteBy(['in' => [$this->primaryKey => $id]], $limit);
        }
        return $this->deleteBy([$this->primaryKey => $id]);
    }

    /**
     * 根据索引字段值获取索引字段与信息字段映射
     * @param array        $value      索引字段值(查询用)
     * @param array|string $info_field 信息字段
     * @param string       $index      索引字段, 默认为id
     * @param bool         $enabled    是否仅查询有效的
     * @return array                   如果$info_field为string, 结果为[index => value], 如果未array, 结果为[index => [info1 => value1, info2 => value2]]
     */
    public function getInfoWithIndex($value = [], $info_field = [], $index = '', $enabled = true) {
        empty($index) && $index = $this->primaryKey;
        $field = [DB_SELECT_ALL];
        if(!empty($info_field)) {
            if(is_array($info_field)) {
                if(!in_array(DB_SELECT_ALL, $info_field)) {
                    $field = $info_field;
                    array_add_item($field, $index);
                }
            } else {
                $field = [$index, $info_field];
            }
        }
        $list = $this->getRecList($field, !empty($value) ? ['in' => [$index => $value]] : [], $enabled);
        return !empty($list) ? array_column($list, is_array($info_field) ? null : $info_field, $index) : [];
    }
}