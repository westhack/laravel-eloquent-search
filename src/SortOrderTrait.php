<?php

namespace EloquentSearch;

trait SortOrderTrait
{
    public $sortable = [];

    public $sortOrderAsc = 'ascend';

    public $sortOrderDesc = 'descend';

    public function getSortOrderAsc() {
        return $this->sortOrderAsc;
    }

    public function getSortOrderDesc() {
        return $this->sortOrderDesc;
    }
    
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

        $sortable = $this->parseSortable();
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
            $order = $this->formatSort(array_get($sortable, $column, $this->getSortOrderAsc()));

            $query->orderBy($column,  $order);
        }

        return $query;
    }

    public function getSortable()
    {
        return [
        ];
    }

    public function parseSortable()
    {
        if (empty($this->getSortable())) {
            return [$this->getKeyName() => $this->getSortOrderDesc()];
        }

        $sortable = [];
        foreach ($this->getSortable() as $key => $value) {
            if (is_int($key)) {
                $sortable[$value] = $this->getSortOrderDesc();
            } else {
                $sortable[$key] = ($value == $this->getSortOrderAsc() ? $this->getSortOrderAsc() : $this->getSortOrderDesc());
            }
        }

        return $sortable;
    }

    public function setSortable($column, $order)
    {
        $sortable = [];
        if (is_string($column)) {
            $sortable[$column] = ($order == $this->getSortOrderAsc() ? $this->getSortOrderAsc() : $this->getSortOrderDesc());
        } else if(is_array($column)) {
            foreach ($column as $key => $value) {
                if (is_int($key)) {
                    $sortable[$value] = $this->getSortOrderDesc();
                } else {
                    $sortable[$key] = ($value == $this->getSortOrderAsc() ? $this->getSortOrderAsc() : $this->getSortOrderDesc());
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
            $sortable[$column] = ($order == $this->getSortOrderAsc() ? $this->getSortOrderAsc() : $this->getSortOrderDesc());
        } else if(is_array($column)) {
            foreach ($column as $key => $value) {
                if (is_int($key)) {
                    $sortable[$value] = $this->getSortOrderDesc();
                } else {
                    $sortable[$key] = ($value == $this->getSortOrderAsc() ? $this->getSortOrderAsc() : $this->getSortOrderDesc());
                }
            }
        }

        $this->sortable = array_merge($this->getSortable(), $sortable);

        return $this;
    }

    private function formatSort($sort)
    {
        if ($sort == $this->getSortOrderAsc()) {
            return 'asc';
        }

        return 'desc';
    }
}
