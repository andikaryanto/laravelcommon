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
    protected static array $columnsCache = [];
    protected ?LengthAwarePaginator $lengthAwarePaginator = null;
    protected ?int $page = null;
    protected ?int $size = null;
    protected ?int $total = 0;
    protected bool $isHaveCount = false;
    protected bool $doCountTotal = false;
    protected static array $windowFunctionSupport = [];


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
        ?ConnectionInterface $connection = null,
        ?Grammar $grammar = null,
        ?Processor $processor = null
    ) {
        if (config('multitenancy.enabled', true) && app()->bound('currentTenant')) {
            $connection = DB::connection('tenant');
            $currentTenant = app('currentTenant');
            if (!is_null($currentTenant) && isset($currentTenant->database)) {
                $connection->setDatabaseName($currentTenant->database);
            }
        } else {
            $connection = DB::connection();
        }

        $grammar = $connection->query()->getGrammar();
        parent::__construct($connection, $grammar);

        $identity = $this->identityClass();
        $this->model = new $identity();
        $this->table = $this->model->getTable();
        $this->fromSelect();
    }

    public function setDoCountTotal(bool $doCountTotal): Query
    {
        $this->doCountTotal = $doCountTotal;
        return $this;
    }

    public function setIsHaveCount(bool $isHaveCount)
    {
        $this->isHaveCount = $isHaveCount;
        return $this;
    }

    protected function getSelectColumns()
    {
        $table = $this->model->getTable();
        if (!isset(self::$columnsCache[$table])) {
            self::$columnsCache[$table] = Schema::getColumnListing($table);
        }

        $columns = self::$columnsCache[$table];
        $columnsWithAlias = [];
        foreach ($columns as $column) {
            $columnsWithAlias[] = $this->table . '.' . $column; // . ' as ' .  $this->table . '_' . $column;
        }

        if (empty($columnsWithAlias)) {
            $columnsWithAlias[] = $this->table . '.*';
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
        foreach ($models as $model) {
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
                $orderSql = $this->compileOrderBySql();
                $countQuery = clone $this;
                $countQuery->orders = [];
                $this->total = (int) $countQuery
                    ->select(DB::raw("COUNT(DISTINCT $tableAndId) as count"))
                    ->value('count');

                $distinctIdQuery = clone $this;
                $distinctIdQuery
                    ->select($tableAndId)
                    ->distinct();
                if (!empty($this->page) && !empty($this->size)) {
                    $distinctIdQuery->take($this->size)
                        ->offset(($this->page - 1) * $this->size);
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

                if ($this->orders && $orderSql && $this->supportsWindowFunctions()) {
                    $rankedQuery = clone $this;
                    $rankedQuery->columns = null;
                    $rankedQuery->orders = null;
                    $rankedQuery->selectRaw("$tableAndId as __id, ROW_NUMBER() OVER (ORDER BY $orderSql) as __pos");

                    $orderedIds = $this->newQuery()->fromSub($rankedQuery, '__ranked')
                        ->selectRaw('__id, MIN(__pos) as __pos')
                        ->groupBy('__id')
                        ->orderBy('__pos');

                    if (!empty($this->page) && !empty($this->size)) {
                        $orderedIds->offset(($this->page - 1) * $this->size)
                            ->limit($this->size);
                    }

                    $newBuilder->joinSub($orderedIds, '__ordered', '__ordered.__id', '=', $tableAndId)
                        ->orderBy('__ordered.__pos');
                } elseif ($this->orders) {
                    $lastSizedIds = $distinctIdQuery->pluck($tableAndId)->toArray();
                    $newBuilder->whereIdIn($lastSizedIds);

                    if (!empty($lastSizedIds)) {
                        $this->orderByIds($newBuilder, $tableAndId, $lastSizedIds);
                    }
                } else {
                    $newBuilder->whereIn($tableAndId, $distinctIdQuery);
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
                $orderSql = $this->compileOrderBySql();

                $clonedDistinctQuery
                    ->select($tableAndId)
                    ->distinct();

                if (!empty($this->page) && !empty($this->size)) {
                    $clonedDistinctQuery->take($this->size)
                        ->offset(($this->page - 1) * $this->size);
                }
                $lastSizedIds = null;
                if ($this->orders && (!$orderSql || !$this->supportsWindowFunctions())) {
                    $lastSizedIds = $clonedDistinctQuery->pluck($tableAndId)->toArray();
                }

                if ($this->doCountTotal) {
                    // TODO: in the future we might not need this, it gets the query prety slow if we dont fiilter by range date
                    $clonedCountQuery->orders = [];
                    $this->total = (int) $clonedCountQuery
                        ->select(DB::Raw("COUNT(DISTINCT $tableAndId) as count"))
                        ->value('count');
                    // END TODO
                }

                $newBuilder->fromSelect()
                    ->distinct();
                if ($this->orders && $orderSql && $this->supportsWindowFunctions()) {
                    $rankedQuery = clone $this;
                    $rankedQuery->columns = null;
                    $rankedQuery->orders = null;
                    $rankedQuery->selectRaw("$tableAndId as __id, ROW_NUMBER() OVER (ORDER BY $orderSql) as __pos");

                    $orderedIds = $this->newQuery()->fromSub($rankedQuery, '__ranked')
                        ->selectRaw('__id, MIN(__pos) as __pos')
                        ->groupBy('__id')
                        ->orderBy('__pos');

                    if (!empty($this->page) && !empty($this->size)) {
                        $orderedIds->offset(($this->page - 1) * $this->size)
                            ->limit($this->size);
                    }

                    $newBuilder->joinSub($orderedIds, '__ordered', '__ordered.__id', '=', $tableAndId)
                        ->orderBy('__ordered.__pos');
                } elseif ($this->orders) {
                    $newBuilder->whereIdIn($lastSizedIds);

                    if (!empty($lastSizedIds)) {
                        $this->orderByIds($newBuilder, $tableAndId, $lastSizedIds);
                    }
                } else {
                    $newBuilder->whereIn($tableAndId, $clonedDistinctQuery);
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

    protected function supportsWindowFunctions(): bool
    {
        $driver = $this->connection->getDriverName();
        if (isset(self::$windowFunctionSupport[$driver])) {
            return self::$windowFunctionSupport[$driver];
        }

        if ($driver === 'sqlite') {
            self::$windowFunctionSupport[$driver] = false;
            return false;
        }

        $versionRow = $this->connection->selectOne('select version() as v');
        $version = is_object($versionRow) ? ($versionRow->v ?? '') : '';
        $isMaria = stripos($version, 'MariaDB') !== false;

        if (preg_match('/(\\d+)\\.(\\d+)\\.(\\d+)/', $version, $matches)) {
            $major = (int) $matches[1];
            $minor = (int) $matches[2];
            $patch = (int) $matches[3];

            if ($isMaria) {
                self::$windowFunctionSupport[$driver] = ($major > 10) || ($major === 10 && $minor >= 2);
                return self::$windowFunctionSupport[$driver];
            }

            self::$windowFunctionSupport[$driver] = ($major > 8) || ($major === 8 && $minor >= 0);
            return self::$windowFunctionSupport[$driver];
        }

        self::$windowFunctionSupport[$driver] = false;
        return false;
    }

    protected function compileOrderBySql(): ?string
    {
        if (empty($this->orders)) {
            return null;
        }

        $segments = [];
        foreach ($this->orders as $order) {
            if (($order['type'] ?? '') === 'Raw') {
                $segments[] = $order['sql'];
                continue;
            }

            if (($order['type'] ?? '') === 'Basic' && isset($order['column'])) {
                $direction = $order['direction'] ?? 'asc';
                $segments[] = $this->grammar->wrap($order['column']) . ' ' . $direction;
                continue;
            }

            return null;
        }

        return implode(', ', $segments);
    }

    protected function orderByIds(Builder $builder, string $tableAndId, array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $driver = $this->connection->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $builder->orderByRaw('FIELD(' . $tableAndId . ', ' . implode(',', $ids) . ')');
            return;
        }

        // using CASE WHEN for sqlite/others
        $orderByCases = [];
        foreach ($ids as $index => $id) {
            $orderByCases[] = "WHEN $tableAndId = $id THEN $index";
        }
        $orderByCaseSql = 'CASE ' . implode(' ', $orderByCases) . ' END';
        $builder->orderByRaw($orderByCaseSql);
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
