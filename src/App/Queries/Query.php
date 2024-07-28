<?php

namespace LaravelCommon\App\Queries;

use Exception;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
    protected ?LengthAwarePaginator $lengthAwarePaginator = null;

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

    protected ?int $page = null;
    protected ?int $size = null;

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

    protected function getSelectColumns()
    {
        $columns = Schema::getColumnListing($this->model->getTable());
        $columnsWithAlias = [];
        foreach ($columns as $column) {
            $columnsWithAlias[] = $this->table . '.' . $column; // . ' as ' .  $this->table . '_' . $column;
        }

        return $columnsWithAlias;
    }

    /**
     * Undocumented function
     *
     * @param array $columns
     * @return Collection
     */
    public function getIterator($columns = ['*'])
    {
        $this->onModelContext();
        $models = null;
        if (!is_null($this->lengthAwarePaginator)) {
            $models = $this->lengthAwarePaginator->items();
        } else {
            $models = $this->get($columns)->all();
        }

        $identityClass = get_class($this->model);
        $collection = $identityClass::hydrate($models);

        return $collection;
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
        if (!empty($this->joins)) {
            $newBuilder = new static($this->connection, $this->grammar, $this->getProcessor());
            $this->limit = null;
            $this->offset = null;
            $ids = $this->distinct()->pluck($this->table . '.' . $this->model->getKeyName());

            // $newBuilder->joins = $this->joins;
            $newBuilder->fromSelect()
                ->distinct()
                ->whereIdIn($ids->toArray());
            if (!empty($this->page) && !empty($this->size)) {
                $newBuilder->setPage($this->page)
                    ->setSize($this->size)
                    ->paging($newBuilder->getSelectColumns());
            }

            $newBuilder->orders = $this->orders;

            $this->lengthAwarePaginator = $newBuilder->lengthAwarePaginator;
        } else {
            if (!empty($this->page) && !empty($this->size)) {
                $this->paging($this->getSelectColumns());
            }
        }

        return $this;
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
        array $columns = ['*'],
        string $pageName = 'page'
    ): Query {
        $this->lengthAwarePaginator = $this->paginate($this->size, $columns, $pageName, $this->page);
        return $this;
    }

    /**
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
        return $this->lengthAwarePaginator?->currentPage();
    }

    /** Get total data
     *
     * @return int|null
     */
    public function getTotal(): ?int
    {
        return $this->lengthAwarePaginator?->total();
    }

    /** Get total data
     *
     * @return int|null
     */
    public function getPerPage(): ?int
    {
        return $this->lengthAwarePaginator?->perPage();
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
