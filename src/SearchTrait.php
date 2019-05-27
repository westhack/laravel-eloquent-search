<?php

namespace EloquentSearch;

trait SearchTrait
{
    /**
     * WHERE $column LIKE %$value% query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $column
     * @param $value
     * @param string                                $boolean
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereLike($query, $column, $value, $boolean = 'and')
    {
        return $query->where($column, 'LIKE', "%$value%", $boolean);
    }

    /**
     * WHERE $column LIKE $value% query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $column
     * @param $value
     * @param string                                $boolean
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereBeginsWith($query, $column, $value, $boolean = 'and')
    {
        return $query->where($column, 'LIKE', "$value%", $boolean);
    }

    /**
     * WHERE $column LIKE %$value query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $column
     * @param $value
     * @param string                                $boolean
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereEndsWith($query, $column, $value, $boolean = 'and')
    {
        return $query->where($column, 'LIKE', "%$value", $boolean);
    }

    /**
     * @param array                                 $params
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $params)
    {
        if (is_array($params)) {
            foreach ($params as $key => $_param) {
                $param = [];
                if (is_array($_param)) {
                    $param['column']   = array_get($_param, 'column', $key);
                    $param['operator'] = array_get($_param, 'operator', '=');
                    $param['values']   = array_get($_param, 'values', null);

                } else {
                    if ($_param == null) {
                        continue;
                    }
                    $param['column']   = $key;
                    $param['operator'] = '=';
                    $param['values']   = $_param;
                }

                if ($param['values'] == null) {
                    continue;
                }

                switch (strtolower($param['operator'])) {
                    case 'between':
                        $query->whereBetween($param['column'], $param['values']);
                        break;
                    case 'not between':
                        $query->whereNotBetween($param['column'], $param['values']);
                        break;
                    case 'is null':
                        $query->whereNull($param['column']);
                        break;
                    case 'is not null':
                        $query->whereNotNull($param['column']);
                        break;
                    case 'like all':
                        $query->whereLike($param['column'], $param['values']);
                        break;
                    case 'begin with':
                        $query->whereBeginsWith($param['column'], $param['values']);
                        break;
                    case 'end with':
                        $query->whereEndsWith($param['column'], $param['values']);
                        break;
                    case 'in':
                        $query->whereIn($param['column'], $param['values']);
                        break;
                    default:
                        $query->where($param['column'], $param['operator'], $param['values']);
                }
            }
        }

        return $query;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $groups
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGroupSearch($query, array $groups)
    {
        $t = class_basename($this);

        if (is_array($groups)) {
            $new_groups = [];
            foreach ($groups as $key => $group) {
                $arr                 = explode('.', $key);
                $new_groups[$arr[0]] = $group;
            }

            unset($groups);

            foreach ($new_groups as $key => $params) {
                if ($key == $this->getTable() || strtolower($key) == strtolower($t)) {
                    $query->search($params);
                } else {
                    if (!$this->checkValueIsNull($params)) {
                        $query->whereHas($key, function ($query) use ($params) {
                            $query->search($params);
                        });
                    }
                }
            }
        }

        return $query;
    }

    /**
     * @param array $params
     * @return bool
     */
    private function checkValueIsNull(array $params)
    {
        foreach ($params as $key => $param) {
            if ($param['values'] != '') {
                return false;
            }
        }

        return true;
    }
}
