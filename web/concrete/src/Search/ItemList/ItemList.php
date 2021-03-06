<?php
namespace Concrete\Core\Search\ItemList;

use Concrete\Core\Search\StickyRequest;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;

abstract class ItemList
{
    protected $sortColumnParameter = 'ccm_order_by';
    protected $sortDirectionParameter = 'ccm_order_by_direction';
    protected $paginationPageParameter = 'ccm_paging_p';
    protected $sortBy;
    protected $sortByDirection;
    protected $autoSortColumns = array();
    protected $itemsPerPage = -1; // determined by the pagination object.
    protected $debug = false;

    abstract protected function executeSortBy($field, $direction = 'asc');
    abstract public function executeGetResults();
    abstract public function getResult($mixed);
    abstract public function debugStart();
    abstract public function debugStop();

    /**
     * @return \Concrete\Core\Search\Pagination\Pagination
     */
    abstract protected function createPaginationObject();

    public function debug()
    {
        $this->debug = true;
    }

    public function isDebugged()
    {
        return $this->debug;
    }

    public function sortBy($field, $direction = 'asc')
    {
        $this->sortBy = $field;
        $this->sortByDirection = $direction;
        $this->executeSortBy($field, $direction);
    }

    /** Returns a full array of results. */
    public function getResults()
    {
        $results = array();

        $this->debugStart();

        $executeResults = $this->executeGetResults();

        $this->debugStop();

        foreach($executeResults as $result) {
            $r = $this->getResult($result);
            if ($r != null) {
                $results[] = $r;
            }
        }

        return $results;
    }

    public function getActiveSortColumn()
    {
        return $this->sortBy;
    }

    public function isActiveSortColumn($field)
    {
        return $this->sortBy == $field;
    }    /** @var \Concrete\Core\Search\Pagination\Pagination  */
    protected $pagination;


    public function getActiveSortDirection()
    {
        return $this->sortByDirection;
    }

    public function getQuerySortColumnParameter()
    {
        return $this->sortColumnParameter;
    }

    public function getQueryPaginationPageParameter()
    {
        return $this->paginationPageParameter;
    }

    public function getQuerySortDirectionParameter()
    {
        return $this->sortDirectionParameter;
    }

    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * Returns the total results in this item list.
     * @return int
     */
    abstract public function getTotalResults();

    /**
     * @return \Concrete\Core\Search\Pagination\Pagination|\Concrete\Core\Search\Pagination\PermissionablePagination
     */
    public function getPagination()
    {
        $pagination = $this->createPaginationObject();
        if ($this->itemsPerPage > -1) {
            $pagination->setMaxPerPage($this->itemsPerPage);
        }
        $query = \Request::getInstance()->query;
        if ($query->has($this->getQueryPaginationPageParameter())) {
            $page = intval($query->get($this->getQueryPaginationPageParameter()));
            try {
                $pagination->setCurrentPage($page);
            } catch (OutOfRangeCurrentPageException $e) {
                $pagination->setCurrentPage(1);
            }
        }
        return $pagination;
    }

    /**
     *
     * @param StickyRequest $request
     */
    public function setupAutomaticSorting(StickyRequest $request = null)
    {
        if ($request) {
            $data = $request->getSearchRequest();
        } else {
            $data = \Request::getInstance()->query->all();
        }
        $direction = 'asc';
        if (isset($data[$this->getQuerySortDirectionParameter()])) {
            $direction = $data[$this->getQuerySortDirectionParameter()];
        }
        if (isset($data[$this->getQuerySortColumnParameter()])) {
            $value = $data[$this->getQuerySortColumnParameter()];
            if (in_array($value, $this->autoSortColumns)) {
                $this->sortBy($value, $direction);
            }
        }
    }

    /**
     * @deprecated
     */
    public function get()
    {
        return $this->getResults();
    }
}
