<?php

namespace LaravelCommon\ViewModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use LaravelCommon\App\Queries\Query;

abstract class AbstractCollection
{
    protected Collection $collection;
    protected Query $query;
    protected ?Request $request;
    protected array $element = [];

    public function __construct(Query $query, ?Request $request = null)
    {
        $this->query = $query;
        $this->request = $request;
    }

    /**
     * Eloquent to View Model
     */
    abstract public function shape(Model $model);

    /**
     * proceed shaping to view model
     */
    public function proceed()
    {
        $this->collection = $this->query->getIterator();
        foreach ($this->collection as $item) {
            $this->shape($item);
        }
        return $this;
    }

    public function finalProcceed()
    {
        return $this->proceed()->getElements();
    }

    /**
     * Add item element
     *
     */
    public function addItem(AbstractViewModel $viewModel): void
    {
        $items = $viewModel->finalArray();
        $this->element[] = $items;
    }

    /**
     * Get elemet
     */
    public function getElements(): array
    {
        return $this->element;
    }

    /**
     * Get count of element
     *
     * @return int
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * Get Entity List
     *
     * @return EntityList
     */
    public function getEntityList()
    {
        return $this->collection;
    }
}
