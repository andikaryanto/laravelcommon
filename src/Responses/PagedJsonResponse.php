<?php

namespace LaravelCommon\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LaravelCommon\App\Queries\Query;
use LaravelCommon\Responses\CollectionResponse;
use LaravelCommon\ViewModels\PaggedCollection;

class PagedJsonResponse extends CollectionResponse
{
    protected PaggedCollection $collection;
    protected ?Query $query = null;
    protected ?Request $request = null;

    public function __construct(string $message, array $responseCode = [], ?PaggedCollection $collection = null)
    {

        $this->collection = $collection;

        parent::__construct($message, 200, $responseCode);
    }

    /**
     *
     * @return Query|null
     */
    public function getQuery(): ?Query
    {
        return $this->collection->getQuery();
    }

    /**
     * getPagedCollection
     *
     * @return PagedCollection
     */
    public function buildData()
    {
        // DB::enableQueryLog();
        $this->getQuery()->setDoCountTotal(true);
        $this->collection->filterAndSortFromRequest();

        $data = $this->collection->finalArray();
        $this->setData($data);
        if (!is_null($data)) {
            $json = [
                '_paging' => [
                    'page' =>  $this->collection->getPage(),
                    'limit' => $this->collection->getSize(),
                    // TODO: in the future, we might not need this, it's collected using COUNT(id) of table
                    // which make slow return from database when data grows
                    // what will be affected is pagination in the FE component, because it is used there
                    'total_data' => $this->collection->getTotalRecord(),
                    // END TODO
                    'is_last_page' => count($data) < $this->collection->getSize(),
                ]
            ];

            $json['_links'] = [
                'next_page' => $this->collection->getNextUrl(),
                'prev_page' => $this->collection->getPreviousUrl(),
                'current_page' => $this->collection->getCurrentUrl()
            ];
            $this->setAdditional($json);

            if (count($data) == 0) {
                $this->setCode(204);
            }
        } else {
        }

        // $quer = DB::getQueryLog();
        // dd($quer);
    }
}
