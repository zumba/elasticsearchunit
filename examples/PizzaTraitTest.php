<?php

/**
 * @group 5.4
 */
class PizzaTraitTest extends \PHPUnit_Framework_TestCase {
	use \Zumba\PHPUnit\Extensions\ElasticSearch\TestTrait;

	protected $connection;

	protected $dataset;

	protected $fixture = [
		'store' => [
			'items' => [
				['size' => 'large', 'toppings' => ['cheese', 'ham']],
				['size' => 'medium', 'toppings' => ['cheese']]
			]
		]
	];

	public function getElasticSearchConnector() {
		if (empty($this->connection)) {
			$this->connection = new \Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector(new \Elasticsearch\Client());
		}
		return $this->connection;
	}

	public function getElasticSearchDataSet() {
		if (empty($this->dataSet)) {
			$this->dataSet = new \Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet($this->getElasticSearchConnector());
			$this->dataSet->setFixture($this->fixture);
		}
		return $this->dataSet;
	}

	public function testSizesFromFixture() {
		$params = ['index' => 'store'];
		$this->assertEquals(2, $this->getElasticSearchConnector()->getConnection()->search($params)['hits']['total']);
		$params['body'] = [
			'query' => [
				'match' => [
					'size' => 'medium'
				]
			]
		];
		$this->assertEquals(1, $this->getElasticSearchConnector()->getConnection()->search($params)['hits']['total']);
	}
}
