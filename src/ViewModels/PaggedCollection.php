<?php

namespace LaravelCommon\ViewModels;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use LaravelCommon\App\Queries\Query;

abstract class PaggedCollection extends AbstractCollection
{
    protected ?int $page = null;
    protected ?int $size = null;
    protected ?int $totalRecord = null;
    protected bool $disableKeywordSearch;
    protected bool $disableDefaultOrder;

    public function __construct(Query $query, ?Request $request = null, bool $disableKeywordSearch = false, bool $disableDefaultOrder = false)
    {
        parent::__construct($query, $request);
        $this->disableKeywordSearch = $disableKeywordSearch;
        $this->disableDefaultOrder = $disableDefaultOrder;
    }


    /**
     * Undocumented function
     *
     * @return LengthAwarePaginator
     */
    public function filterAndSortFromRequest()
    {
        $request = request();
        $table = $this->query->getTable();
        $sortDirection = 'ASC';
        $sortColumn = null;
        $size = config("common-config")['collection_paging']['size'];
        $page = 1;

        if (!$this->disableDefaultOrder) {
            if (isset($request->order_direction)) {
                $sortDirection = strtolower($request->order_direction);
            }

            if (isset($request->order_by)) {
                $sortColumn = $request->order_by;
            }

            if (!is_null($sortColumn)) {
                if (str_contains($sortColumn, '.')) {
                    $this->query->orderByAndAddSelect($sortColumn, $sortDirection);
                } else {
                    $this->query->orderBy($table . '.' . $sortColumn, $sortDirection);
                }
            }
        }

        if (isset($request->size)) {
            $size = $request->size;
        }

        if (isset($request->page)) {
            $page = $request->page;
        }

        if (isset($request->keyword) && !$this->disableKeywordSearch) {
            $keyword = $request->keyword;
            $searchColumns = Schema::getColumnListing($this->query->getTable());
            $this->query->where(function ($query) use ($searchColumns, $keyword, $table) {
                foreach ($searchColumns as $column) {
                    $query->orWhere($table . '.' . $column, 'like', '%' . $keyword . '%');
                }
            });
        } else {
            if (isset($request->search_by) && isset($request->search_value)) {
                $searchBy = $request->search_by;
                $searchValue = $request->search_value;
                $this->query->where($searchBy, 'like', '%' . $searchValue . '%');
            }

            if (isset($request->search_value)) {
            }
        }

        return $this->query->setPaging($page, $size);
    }

    /**
     * Get the value of page
     * @return ?int
     */
    public function getPage(): ?int
    {
        return $this->query->getPage();
    }

    /**
     * Get the value of size
     *
     * @return ?int
     */
    public function getSize(): ?int
    {
        return $this->query->getPerPage();
    }

    /**
     * Get the value of totalRecord
     *
     * @return ?int
     */
    public function getTotalRecord(): ?int
    {
        return $this->query->getTotal();
    }

    /**
     *
     *
     * @return int
     */
    public function getTotalPage(): int
    {
        if ($this->getTotalRecord() > $this->getSize()) {
            return ceil($this->getTotalRecord() / $this->getSize());
        } else {
            return 1;
        }
    }

    /**
     * Get next page
     *
     * @return ?int
     */
    public function getNextPage(): ?int
    {
        if ($this->getTotalPage() > 1) {
            if ($this->getPage() < $this->getTotalPage()) {
                return $this->getPage() + 1;
            }
        }
        return null;
    }

    /**
     * Get Prev page
     *
     * @return ?int
     */
    public function getPreviousPage(): ?int
    {
        if ($this->getTotalPage() > 1) {
            if ($this->getPage() <= $this->getTotalPage()) {
                return $this->getPage() > 1 ? $this->getPage() - 1 : null;
            }
        }
        return null;
    }

    public function getAwarePaginator()
    {
        return $this->query->getAwarePaginator();
    }

    public function getNextUrl()
    {
        return $this->getAwarePaginator()->url($this->getNextPage());
    }

    public function getPreviousUrl()
    {
        return $this->getAwarePaginator()->url($this->getPreviousPage());
    }

    public function getCurrentUrl()
    {
        return $this->getAwarePaginator()->url($this->getPage());
    }
}
