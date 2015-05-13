<?php

class PizzaTest extends \Zumba\PHPUnit\Extensions\ElasticSearch\TestCase {

	protected $connector;

	protected $dataset;

	public function setUp() {
		$this->fixture = array(
			'store' => array(
				'items' => array(
					array('id' => 1, 'size' => 'large', 'toppings' => array('cheese', 'ham')),
					array('id' => 2, 'size' => 'medium', 'toppings' => array('cheese'))
				)
			)
		);
		parent::setUp();
	}

	public function getConnector() {
		if (empty($this->connector)) {
			$this->connector = new \Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector(new \Elasticsearch\Client());
		}
		return $this->connector;
	}

	public function getDataSet() {
		if (empty($this->dataSet)) {
			$this->dataSet = new \Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet($this->getConnector());
			$this->dataSet->setFixture($this->fixture);
		}
		return $this->dataSet;
	}

	public function testSizesFromFixture() {
		$params = ['index' => 'store'];
		$this->assertEquals(2, $this->getConnector()->getConnection()->search($params)['hits']['total']);
		$params['body'] = [
			'query' => [
				'match' => [
					'size' => 'medium'
				]
			]
		];
		$this->assertEquals(1, $this->getConnector()->getConnection()->search($params)['hits']['total']);
	}

}