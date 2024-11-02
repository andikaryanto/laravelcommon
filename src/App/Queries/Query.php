<?php

namespace LaravelCommon\App\Queries;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Query extends Builder
{
    protected $model;
    protected string $table;
    protected bool $asRaw = false;
    protected array $addedSelect = [];
    protected ?LengthAwarePaginator $lengthAwarePaginator = null;
    protected ?int $page = null;
    protected ?int $size = null;
    protected ?int $total = 0;
    protected bool $isHaveCount = false;


    // NOTE: we used to do have issue on grammar on laravel 9.xx
    // when use DB::connection()->query()->getGrammar() the grammar is always incorrect while querying the database
    // this is constructor that fix the issue on laravel 9.xx
    // public function __construct(
    //     Model $model,
    //     ConnectionInterface $connection,
    //     Grammar $grammar = null,
    //     Processor $processor = null
    // ) {
    //     $grammar = $connection->query()->getGrammar();
    //     parent::__construct($connection, $grammar, $processor);

    //     $this->model = $model;
    //     $this->table = $model->getTable();
    //     $this->fromSelect();
    // }

    /**
     * Create a new query builder instance.
     *
     * @return void
     */
    public function __construct(
        ConnectionInterface $connection = null,
        Grammar $grammar = null,
        Processor $processor = null
    ) {
        $connection = DB::connection();
        $grammar = $connection->query()->getGrammar();
        parent::__construct($connection, $grammar);

        $identity = $this->identityClass();
        $this->model = new $identity();
        $this->table = $this->model->getTable();
        $this->fromSelect();
    }

    public function setIsHaveCount(bool $isHaveCount)
    {
        $this->isHaveCount = $isHaveCount;
        return $this;
    }

    protected function getSelectColumns()
    {
        $columns = Schema::getColumnListing($this->model->getTable());
        $columnsWithAlias = [];
        foreach ($columns as $column) {
            $columnsWithAlias[] = $this->table . '.' . $column; // . ' as ' .  $this->table . '_' . $column;
        }

        return $columnsWithAlias;
    }

    public function setAsRaw(bool $asRaw)
    {
        $this->asRaw = $asRaw;
        return $this;
    }

    public function addSelect($column)
    {
        $this->addedSelect[] = $column;
        return parent::addSelect($column);
    }

    /**
     * Order by column and add select with alias
     *
     * @param string $column column to order
     * @param string $direction direction of order (ASC|DESC)
     * @return $this
     */
    public function orderByAndAddSelect($column, $direction = 'ASC')
    {
        $tableColum = explode('.', $column);
        $orderTable = $tableColum[0];
        if ($orderTable != $this->table) {
            $this->addSelect($column . ' as ' . implode('_', $tableColum));
        }
        $this->orderBy($column, $direction);
    }

    /**
     * Undocumented function
     *
     * @param array $columns
     * @return Collection
     */
    public function getIterator($columns = ['*'])
    {
        $context = $this->onModelContext();
        $models = null;
        $lengthAwarePaginator = $context->lengthAwarePaginator;
        if (!is_null($lengthAwarePaginator)) {
            $models = $lengthAwarePaginator->items();
        } else {
            $models = $context->get($columns)->all();
        }

        $identityClass = get_class($this->model);
        $collection = $identityClass::hydrate($models);
        return $collection;
    }

    public function getLazyIterator($columns = ['*'])
    {
        $context = $this->onModelContext();
        $models = null;
        $lengthAwarePaginator = $context->lengthAwarePaginator;
        if (!is_null($lengthAwarePaginator)) {
            $models = $lengthAwarePaginator->items();
        } else {
            $models = $context->get($columns)->all();
        }

        $identityClass = get_class($this->model);
        foreach($models as $model) {
            yield $identityClass::hydrate([$model])->first();
        }
    }

    public function joinWith($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                if ($join->table == $table) {
                    return $this;
                }
            }
        }

        return  $this->join($table, $first, $operator, $second, $type, $where);
    }

    public function fromSelect()
    {
        return $this->from($this->table, $this->table)->select($this->getSelectColumns());
    }

    public function onModelContext()
    {
        if (!empty($this->joins) && !$this->asRaw) {
            if ($this->isHaveCount) {
                $newBuilder = new static($this->connection, $this->grammar, $this->getProcessor());

                $tableAndId = $this->table . '.' . $this->model->getKeyName();
                $ids = $this->distinct()->pluck($tableAndId)->toArray();
                $this->total = count($ids);

                $lastSizedIds = $ids;
                if (!empty($this->page) && !empty($this->size) && count($ids) > 0) {
                    $lastSizedIds = array_slice($ids, $this->size * ($this->page - 1), $this->size);
                }

                $newBuilder->fromSelect();

                if ($this->groups) {
                    // only do join and addselect when have group
                    // so we are keeping the record clean
                    $newBuilder->addSelect($this->addedSelect);
                    $newBuilder->joins = $this->joins;
                    foreach ($this->wheres as $where) {
                        $newBuilder->wheres[] = $where;
                        $newBuilder->bindings['where'] = $this->bindings['where'];
                    }
                    $newBuilder->groups = $this->groups;
                }

                $newBuilder->whereIdIn($lastSizedIds);

                if ($this->orders && count($lastSizedIds) > 0) {
                    // using WHEN Statement to order the data to suppor sqlite
                    foreach ($lastSizedIds as $index => $id) {
                        $table = $this->getTable();
                        $orderByCases[] = "WHEN $table.id = $id THEN $index";
                    }

                    $orderByCaseSql = 'CASE ' . implode(' ', $orderByCases) . ' END';
                    $newBuilder->orderByRaw($orderByCaseSql);

                    // TODO: SQLITE did not support this, we might need consider other way
                    // $newBuilder->orderByRaw('FIELD(' . $tableAndId . ', ' . implode(',', $lastSizedIds) . ')');
                }

                if (!empty($this->page) && !empty($this->size)) {
                    $newBuilder->paging(1, $this->size, $this->getSelectColumns());
                }

                $this->lengthAwarePaginator = $newBuilder->lengthAwarePaginator;
                return $newBuilder;
            } else {
                $newBuilder = new static($this->connection, $this->grammar, $this->getProcessor());

                $tableAndId = $this->table . '.' . $this->model->getKeyName();
                $clonedDistinctQuery = clone $this;
                $clonedCountQuery = clone $this;

                $clonedDistinctQuery
                    ->select($tableAndId)
                    ->distinct();
                    
                if (!empty($this->page) && !empty($this->size)) {
                    $clonedDistinctQuery->take($this->size)
                    ->offset(($this->page - 1) * $this->size);
                }
                $lastSizedIds = $clonedDistinctQuery->pluck($tableAndId)->toArray();
                
                // TODO: in the future we might not need this, it gets the query prety slow if we dont fiilter by range date
                $clonedCountQuery->orders = [];
                $this->total = $clonedCountQuery
                    ->select(DB::Raw("COUNT(DISTINCT $tableAndId) as count"))
                    ->get()[0]->count;
                // END TODO
                    
                $newBuilder->fromSelect()
                    ->distinct()
                    ->whereIdIn($lastSizedIds);

                if ($this->orders && count($lastSizedIds) > 0) {
                    // using WHEN Statement to order the data to suppor sqlite
                    foreach ($lastSizedIds as $index => $id) {
                        $orderByCases[] = "WHEN id = $id THEN $index";
                    }

                    $orderByCaseSql = 'CASE ' . implode(' ', $orderByCases) . ' END';
                    $newBuilder->orderByRaw($orderByCaseSql);

                    // TODO: SQLITE did not support this, we might need consider other way
                    // $newBuilder->orderByRaw('FIELD(' . $tableAndId . ', ' . implode(',', $lastSizedIds) . ')');
                }

                if (!empty($this->page) && !empty($this->size)) {
                    $newBuilder->paging(1, $this->size, $this->getSelectColumns());
                }

                $this->lengthAwarePaginator = $newBuilder->lengthAwarePaginator;
                return $newBuilder;
            }
        } else {
            if (!$this->asRaw) {
                if (!empty($this->page) && !empty($this->size)) {
                    $this->paging($this->page, $this->size, $this->getSelectColumns());
                    $this->total = $this->lengthAwarePaginator->total();
                }
            } else {
                $this->total = $this->count();
                if (!empty($this->page) && !empty($this->size)) {
                    $offset = ($this->page - 1) * $this->size;

                    $this->offset($offset)
                        ->limit($this->size);

                    $dataQuery = $this->get();
                    $paginator = new LengthAwarePaginator(
                        $dataQuery,
                        $this->total,
                        $this->size,
                        $this->page,
                        ['path' => request()->url(), 'query' => request()->query()] // For proper pagination links
                    );

                    $this->lengthAwarePaginator = $paginator;
                }
            }
        }

        return $this;
    }

    public function groupByContextFields()
    {
        return $this->groupBy($this->getSelectColumns());
    }

    /**
     * Reset Query
     *
     * @return $this
     */
    public function reset()
    {
        return $this->newQuery();
    }

    public function setPage(int $page): Query
    {
        $this->page = $page;

        return $this;
    }

    public function setSize(int $size): Query
    {
        $this->size = $size;

        return $this;
    }

    public function setPaging(int $page, int $size): Query
    {
        $this->page = $page;
        $this->size = $size;

        return $this;
    }

    /**
     * paginate
     *
     * @param array $columns
     * @param string $pageName
     * @return Query
     */
    private function paging(
        int $page,
        int $size,
        array $columns = ['*'],
        string $pageName = 'page'
    ): Query {
        $this->lengthAwarePaginator = $this->paginate($size, $columns, $pageName, $page);
        return $this;
    }

    /**
     * Be Aware, this is for get url only,
     * the value of current page, next page, prev page might not be relevant
     *
     * when we are paginate the data
     * this awarepaginator will always contains 1 page only with the size of it paging size.
     *
     * @return LengthAwarePaginator|null
     */
    public function getAwarePaginator(): ?LengthAwarePaginator
    {
        return $this->lengthAwarePaginator;
    }

    /**
     * get table name
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->model->getTable();
    }

    /**
     * get current page
     *
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /** Get total data
     *
     * @return int|null
     */
    public function getTotal(): ?int
    {
        return $this->total;
    }

    /** Get total data
     *
     * @return int|null
     */
    public function getPerPage(): ?int
    {
        return $this->size;
    }

    /**
     *
     *
     * @return string
     */
    public function identityClass()
    {
        // return get_class($this->model);
    }

    /**
     *
     * @param array $ids
     * @return $this
     */
    public function whereIdIn(array $ids)
    {
        $this->whereIn($this->table . '.id', $ids);
        return $this;
    }

    public function noResult()
    {
        $this->whereRaw('1 = 0');
        return $this;
    }
}
