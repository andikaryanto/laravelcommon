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
        $this->getQuery(); 
        $this->collection->filterAndSortFromRequest();

        $data = $this->collection->finalArray();
        $this->setData($data);
        if (!is_null($data)) {
            $json = [
                '_paging' => [
                    'page' =>  $this->collection->getPage(),
                    'limit' => $this->collection->getSize(),
                    // To Support FE now that uses total_data, we do not count total data anymore, so it's 0
                    // so FE is not breaking any thing
                    'total_data' => 0,
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
