<?php

namespace EloquentSearch;

trait SortOrderTrait
{
    public $sortable = [];

    public $sortOrderAsc = 'ascend';

    public $sortOrderDesc = 'descend';

    /**
     * 排序
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $sort
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOrder($query, $sort)
    {
        $sort = array_wrap($sort);

        $sortable = $this->getSortable();
        if (!empty($sort)) {
            $column = array_get($sort, 'column');
            if (array_get($sort, 'order')) {
                $order = $this->formatSort(array_get($sort, 'order'));
            } else {
                $order = $this->formatSort(array_get($sortable, $column));
            }

            if ($column != '' && $order != '') {
                $query->orderBy($column,  $order);
            }
        } else {
            $column = head(array_keys($sortable));
            $order = $this->formatSort(array_get($sortable, $column, $this->sortOrderAsc));

            $query->orderBy($column,  $order);
        }

        return $query;
    }

    public function getSortable()
    {
        if (empty($this->sortable)) {
            return [$this->getKeyName() => $this->sortOrderDesc];
        }

        $sortable = [];
        foreach ($this->sortable as $key => $value) {
            if (is_int($key)) {
                $sortable[$value] = $this->sortOrderDesc;
            } else {
                $sortable[$key] = ($value == $this->sortOrderAsc ? $this->sortOrderAsc : $this->sortOrderDesc);
            }
        }

        return $sortable;
    }

    public function setSortable($column, $order)
    {
        $sortable = [];
        if (is_string($column)) {
            $sortable[$column] = ($order == $this->sortOrderAsc ? $this->sortOrderAsc : $this->sortOrderDesc);
        } else if(is_array($column)) {
            foreach ($column as $key => $value) {
                if (is_int($key)) {
                    $sortable[$value] = $this->sortOrderDesc;
                } else {
                    $sortable[$key] = ($value == $this->sortOrderAsc ? $this->sortOrderAsc : $this->sortOrderDesc);
                }
            }
        }
        $this->sortable = $sortable;

        return $this;
    }

    public function addSortable($column, $order)
    {
        $sortable = [];
        if (is_string($column)) {
            $sortable[$column] = ($order == $this->sortOrderAsc ? $this->sortOrderAsc : $this->sortOrderDesc);
        } else if(is_array($column)) {
            foreach ($column as $key => $value) {
                if (is_int($key)) {
                    $sortable[$value] = $this->sortOrderDesc;
                } else {
                    $sortable[$key] = ($value == $this->sortOrderAsc ? $this->sortOrderAsc : $this->sortOrderDesc);
                }
            }
        }

        $this->sortable = array_merge($this->sortable, $sortable);

        return $this;
    }

    private function formatSort($sort)
    {
        if ($sort == $this->sortOrderAsc) {
            return 'asc';
        }

        return 'desc';
    }
}
