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
        $searchable = $this->getSearchable();

        $new_params = [];

        foreach ($params as $key => $_param) {
            $param = [];
            if (is_array($_param)) {
                $param['column'] = array_get($_param, 'column', $key);
                $param['operator'] = array_get($_param, 'operator', '=');
                $param['value'] = array_get($_param, 'value', null);

            } else {
                if ($_param == null) {
                    continue;
                }
                $param['column'] = $key;
                $param['operator'] = '=';
                $param['value'] = $_param;
            }

            if ($param['value'] == null) {
                continue;
            }

            $new_params[$param['column']] = $param;
        }

        $params = [];
        if ($searchable) {
            foreach ($searchable as $search) {
                if (isset($new_params[$search])) {
                    $params[] = $new_params[$search];
                }
            }
        } else {
            $params = $new_params;
            unset($new_params);
        }

        if (is_array($params)) {
            foreach ($params as $key => $param) {

                switch (strtolower($param['operator'])) {
                    case 'between':
                        $query->whereBetween($param['column'], $param['value']);
                        break;
                    case 'not_between':
                        $query->whereNotBetween($param['column'], $param['value']);
                        break;
                    case 'is_null':
                        $query->whereNull($param['column']);
                        break;
                    case 'is_not_null':
                        $query->whereNotNull($param['column']);
                        break;
                    case 'like_all':
                        $query->whereLike($param['column'], $param['value']);
                        break;
                    case 'ilike':
                        $query->whereBeginsWith($param['column'], $param['value']);
                        break;
                    case 'rlike':
                        $query->whereEndsWith($param['column'], $param['value']);
                        break;
                    case 'in':
                        $query->whereIn($param['column'], $param['value']);
                        break;
                    default:
                        $query->where($param['column'], $param['operator'], $param['value']);
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
            if ($param['value'] != '') {
                return false;
            }
        }

        return true;
    }

    public function getSearchable()
    {
        return [];
    }
}
