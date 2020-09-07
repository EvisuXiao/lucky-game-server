<?php
/**
 * Created by PhpStorm.
 * User: evisu
 * Date: 2018/7/3
 * Time: 下午1:58
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * 插入数据, 支持批量
     * 单条返回生成id, 批量返回成功条数
     * @param $data
     * @return int
     */
    public function addRec($data) {
        if(empty($data)) {
            return 0;
        }
        if(isset($data[0])) {
            return $this->insert($data) ? count($data) : 0;
        }
        return $this->insertGetId($data);
    }

    /**
     * 通用获取单条记录方法
     * @param array   $fields
     * @param array   $where
     * @param array   $order
     * @param array   $option
     * @return Model|array
     */
    public function getOne($fields = [DB_SELECT_ALL], $where = [], $order = [], $option = []) {
        $option['limit'] = 1;
        $list = $this->getAll($fields, $where, $order, $option);
        return !empty($list) ? $list[0] : [];
    }

    /**
     * 通用查询列表方法,查询全部
     * @param array $fields 查询字段
     * @param array $where  查询条件
     * @param array $order  排序
     * @param array $option 额外选项, 如group, page, limit, master
     * @return Model|array
     */
    public function getAll($fields = [DB_SELECT_ALL], $where = [], $order = [], $option = []) {
        $list = $this->createQuery($this, $fields, $where, $order, $option)->get();
        return !empty($option['object']) ? $list : $list->toArray();
    }

    /**
     * 查询字段分组数量
     * @param string $field
     * @param array  $where
     * @param string $sort
     * @return array
     */
    public function getGroupNum($field, $where = [], $sort = DB_SORT_ASC) {
        $num_as = 'num';
        $fields['raw'] = sprintf('%s, COUNT(%s) AS %s', $field, $field, $num_as);
        $list = $this->getAll($fields, $where, [$field => $sort], [$field]);
        return !empty($list) ? array_column($list, $num_as, $field) : [];
    }

    /**
     * 查询列表(可分页)
     * @param array $fields
     * @param array $where
     * @param array $order
     * @param int   $page_size
     * @param array $option
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginate($fields = [], $where = [], $order = [], $page_size = 15, $option = []) {
        return $this->createQuery($this, $fields, $where, $order, $option)->paginate($page_size);
    }

    /**
     * 根据条件统计总数
     * @param array $where
     * @return int
     */
    public function countBy($where = []) {
        return $this->createWhere($this, $where)->count();
    }

    /**
     * 根据条件更新数据
     * @param array $data
     * @param array $where
     * @param int   $limit
     * @param array $order
     * @return int
     */
    public function updateBy($data, $where, $limit = 0, $order = []) {
        !empty($limit) && empty($order) && $order = [$this->primaryKey => DB_SORT_DESC];
        return $this->createWhere($this, $where, $order, ['limit' => $limit])->update($data);
    }

    /**
     * 根据条件删除数据
     * @param array $where
     * @param int   $limit
     * @param array $order
     * @return int
     */
    public function deleteBy($where, $limit = 0, $order = []) {
        !empty($limit) && empty($order) && $order = [$this->primaryKey => DB_SORT_DESC];
        return $this->createWhere($this, $where, $order, ['limit' => $limit])->delete();
    }

    /**
     * @param Model $query
     * @param array $fields
     * @param array $where
     * @param array $order
     * @param array $option
     * @return Model
     */
    public function createQuery($query = null, $fields = [DB_SELECT_ALL], $where = [], $order = [], $option = []) {
        is_null($query) && $query = $this;
        if(isset($fields['raw'])) {
            $query = $this->selectRaw($fields['raw']);
            $fields = $fields['fields'] ?? [];
        }
        !empty($fields) && $query = $this->select($fields);
        // 是否读主库
        !empty($option['master']) && $query->useWritePdo();
        return $this->createWhere($query, $where, $order, $option);
    }

    /**
     * 格式化where条件
     * @param Model $query
     * @param array $where
     * @param array $order
     * @param array $option
     * @return Model
     */
    public function createWhere($query = null, $where = [], $order = [], $option = []) {
        is_null($query) && $query = $this;
        if(isset($where['in'])) {
            foreach($where['in'] as $k => $v) {
                $query = $query->whereIn($k, $v);
            }
            unset($where['in']);
        }
        if(isset($where['not_in'])) {
            foreach($where['not_in'] as $k => $v) {
                $query = $query->whereNotIn($k, $v);
            }
            unset($where['not_in']);
        }
        if(isset($where['raw'])) {
            foreach($where['raw'] as $k => $v) {
                $query = $query->whereRaw($v);
            }
            unset($where['raw']);
        }
        if(!empty($where)) {
            foreach($where as $key => $value) {
                $arg_op = explode(' ', $key);
                $arg = $arg_op[0];
                $op = $arg_op[1] ?? '=';
                if($op === 'like') {
                    $value = sprintf('%%%s%%', $value);
                }
                if($op === '%') {
                    $query = $query->whereRaw(sprintf('%s %% ? = ?', $arg), $value);
                } else {
                    $query = $query->where($arg, $op, $value);
                }
            }
        }
        if(!empty($order)) {
            foreach($order as $k => $v) {
                $query = $query->orderBy($k, $v);
            }
        }
        if(!empty($option['group'])) {
            foreach($option['group'] as $v) {
                $query = $query->groupBy($v);
            }
        }
        if(!empty($option['limit'])) {
            $query = $query->limit($option['limit']);
            !empty($option['page']) && $query = $query->skip(($option['page'] - 1) * $option['limit']);
        }
        return $query;
    }
}