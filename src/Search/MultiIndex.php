<?php
namespace App\Search;

use App\Search\QueryConditions\QueryConditionInterface;
use App\Search\FilterConditions\FilterConditionInterface;
use App\Search\FilterConditions\RoomFilterCondition;
use Elastica\Exception\InvalidException;
use Elastica\Index;
use Elastica\ResultSet\BuilderInterface;
use Elastica\Search;
use FOS\ElasticaBundle\Finder\TransformedFinder;

use App\Utils\UserService;
use App\Utils\ItemService;

use Elastica\Query as Queries;
use Elastica\Aggregation as Aggregations;

class MultiIndex extends Index
{

    /**
     * Array of indices.
     *
     * @var array
     */
    protected $_indices = [];
    /**
     * @var array
     */
    protected $_types = [];

    /**
     * Adds a index to the list.
     *
     * @param Index|string $index Index object or string
     *
     * @throws InvalidException
     *
     * @return $this
     */
    public function addIndex($index)
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        }

        if (!is_scalar($index)) {
            throw new InvalidException('Invalid param type');
        }

        $this->_indices[] = (string) $index;

        return $this;
    }

    /**
     * Add array of indices at once.
     *
     * @param array $indices
     *
     * @return $this
     */
    public function addIndices(array $indices = [])
    {
        foreach ($indices as $index) {
            $this->addIndex($index);
        }

        return $this;
    }

    /**
     * Return array of indices.
     *
     * @return array List of index names
     */
    public function getIndices()
    {
        return $this->_indices;
    }

    /**
     * @param string|array|\Elastica\Query $query
     * @param int|array                    $options
     * @param BuilderInterface             $builder
     *
     * @return Search
     */
    public function createSearch($query = '', $options = null, BuilderInterface $builder = null)
    {

        $search = new Search($this->getClient(), $builder);
        $search->addIndices($this->getIndices());
        $search->addTypes($this->getTypes());
        $search->setOptionsAndQuery($options, $query);

        return $search;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->_types;
    }

    /**
     * @param array $types
     */
    public function addTypes(array $types): void
    {
        $this->_types = $types;
    }
}
