<?php

namespace LaravelCommon\App\Queries;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelCommon\System\Support\DB as CommonDB;

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
    protected static array $selectColumnsCache = [];


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
        $connection = CommonDB::connection();

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

    public function getSelectColumns()
    {
        $connectionName = $this->connection->getName();
        $databaseName = $this->connection->getDatabaseName() ?? '';
        $cacheKey = $connectionName . ':' . $databaseName . ':' . $this->table;

        if (isset(static::$selectColumnsCache[$cacheKey])) {
            return static::$selectColumnsCache[$cacheKey];
        }

        $columns = $this->connection->getSchemaBuilder()->getColumnListing($this->model->getTable());
        $columnsWithAlias = [];
        foreach ($columns as $column) {
            $columnsWithAlias[] = $this->table . '.' . $column; // . ' as ' .  $this->table . '_' . $column;
        }

        if (empty($columnsWithAlias)) {
            $columnsWithAlias[] = $this->table . '.*';
        }

        static::$selectColumnsCache[$cacheKey] = $columnsWithAlias;
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
                $distinctIdsQuery = $this->buildDistinctIdsQuery($tableAndId, false);

                $this->total = CommonDB::query()
                    ->fromSub($distinctIdsQuery, 'distinct_ids')
                    ->count();

                $lastSizedIds = [];
                if (!empty($this->page) && !empty($this->size)) {
                    $distinctIdsQuery->take($this->size)
                        ->offset(($this->page - 1) * $this->size);
                    $lastSizedIds = $distinctIdsQuery->pluck($tableAndId)->toArray();
                } else {
                    $lastSizedIds = $distinctIdsQuery->pluck($tableAndId)->toArray();
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
                    $this->orderByIdsBySequence($newBuilder, $lastSizedIds, $tableAndId);
                }

                if (!empty($this->page) && !empty($this->size)) {
                    $items = count($lastSizedIds) > 0
                        ? $newBuilder->get($this->getSelectColumns())
                        : collect();
                    $newBuilder->lengthAwarePaginator = new LengthAwarePaginator(
                        $items,
                        $this->total,
                        $this->size,
                        1,
                        ['path' => request()->url(), 'query' => request()->query()]
                    );
                }

                $this->lengthAwarePaginator = $newBuilder->lengthAwarePaginator;
                return $newBuilder;
            } else {
                $newBuilder = new static($this->connection, $this->grammar, $this->getProcessor());

                $tableAndId = $this->table . '.' . $this->model->getKeyName();
                $distinctIdsQuery = $this->buildDistinctIdsQuery($tableAndId);

                if (!empty($this->page) && !empty($this->size)) {
                    $distinctIdsQuery->take($this->size)
                        ->offset(($this->page - 1) * $this->size);
                }
                $lastSizedIds = $distinctIdsQuery->pluck($tableAndId)->toArray();

                $newBuilder->fromSelect()
                    ->distinct()
                    ->whereIdIn($lastSizedIds);

                if ($this->orders && count($lastSizedIds) > 0) {
                    $this->orderByIdsBySequence($newBuilder, $lastSizedIds, $tableAndId);
                }

                if (!empty($this->page) && !empty($this->size)) {
                    $items = count($lastSizedIds) > 0
                        ? $newBuilder->get($this->getSelectColumns())
                        : collect();
                    $newBuilder->lengthAwarePaginator = new LengthAwarePaginator(
                        $items,
                        count($lastSizedIds),
                        $this->size,
                        1,
                        ['path' => request()->url(), 'query' => request()->query()]
                    );
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

    protected function buildDistinctIdsQuery(string $qualifiedIdColumn, bool $keepOrders = true): Builder
    {
        $idsQuery = $this->connection->query()->from($this->from);
        $idsQuery->joins = $this->joins;
        $idsQuery->wheres = $this->wheres;
        $idsQuery->groups = $this->groups;
        $idsQuery->havings = $this->havings;
        $idsQuery->unions = $this->unions;
        $idsQuery->unionLimit = $this->unionLimit;
        $idsQuery->unionOffset = $this->unionOffset;
        $idsQuery->unionOrders = $this->unionOrders;
        $idsQuery->distinct()->select($qualifiedIdColumn);

        if ($keepOrders) {
            $idsQuery->orders = $this->orders;
        }

        $idsQuery->bindings['join'] = $this->bindings['join'] ?? [];
        $idsQuery->bindings['where'] = $this->bindings['where'] ?? [];
        $idsQuery->bindings['groupBy'] = $this->bindings['groupBy'] ?? [];
        $idsQuery->bindings['having'] = $this->bindings['having'] ?? [];
        $idsQuery->bindings['union'] = $this->bindings['union'] ?? [];
        $idsQuery->bindings['unionOrder'] = $this->bindings['unionOrder'] ?? [];

        if ($keepOrders) {
            $idsQuery->bindings['order'] = $this->bindings['order'] ?? [];
        }

        return $idsQuery;
    }

    protected function orderByIdsBySequence(Builder $builder, array $ids, string $qualifiedIdColumn): void
    {
        if (empty($ids)) {
            return;
        }

        $driver = $this->connection->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $builder->orderByRaw("FIELD($qualifiedIdColumn, $placeholders)", $ids);
            return;
        }

        $cases = [];
        $bindings = [];
        foreach (array_values($ids) as $index => $id) {
            $cases[] = 'WHEN ? THEN ?';
            $bindings[] = $id;
            $bindings[] = $index;
        }

        $builder->orderByRaw('CASE ' . $qualifiedIdColumn . ' ' . implode(' ', $cases) . ' END', $bindings);
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
