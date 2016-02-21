<?php

/**
 * @group 5.4
 */
class PizzaTraitTest extends \PHPUnit_Framework_TestCase {
	use \Zumba\PHPUnit\Extensions\ElasticSearch\TestTrait;

	const PIZZA_TRAIT_INDEX = 'store';

	protected $connection;

	protected $dataSet;

	protected $fixture = [
		self::PIZZA_TRAIT_INDEX => [
			'items' => [
				['size' => 'large', 'toppings' => ['cheese', 'ham']],
				['size' => 'medium', 'toppings' => ['cheese']]
			],
			'toppings' => [
				[
					'name' => 'bacon',
					'cost' => '0.75',
					'categories' => [
						['name' => 'meat', 'display_order' => 3],
					]
				],
				[
					'name' => 'pepperoni',
					'cost' => '0.75',
					'categories' => [
						['name' => 'meat', 'display_order' => 1],
					]
				],
				[
					'name' => 'mushrooms',
					'cost' => '0.50',
					'categories' => [
						['name' => 'vegetarian', 'display_order' => 1],
					]
				],
				[
					'name' => 'sausage',
					'cost' => '1.00',
					'categories' => [
						['name' => 'meat', 'display_order' => 2],
						['name' => 'homemade', 'display_order' => 4],
					]
				]
			]
		]
	];

	protected $mappings = [
		self::PIZZA_TRAIT_INDEX => [
			'items' => [],
			'toppings' => [
				'properties' => [
					'cost' => ['type' => 'float'],
					'categories' => [
						'type' => 'nested',
						'include_in_parent' => true,
						'properties' => [
							'display_order' => ['type' => 'integer'],
						]
					]
				]
			]
		]
	];

	protected $settings = [
		self::PIZZA_TRAIT_INDEX => [
			'analysis' => [
				'analyzer' => [
					'AutoCompleteAnalyzer' => [
						'type' => 'custom',
						'tokenizer' => 'AutoCompleteTokenizer',
						'filter' => [
							'lowercase',
							'asciifolding',
						]
					],
				],
				'tokenizer' => [
					'AutoCompleteTokenizer' => [
						'type' => 'edgeNGram',
						'min_gram' => 2,
						'max_gram' => 20,
						'token_chars' => ['letter', 'digit', 'punctuation', 'symbol', 'whitespace']
					]
				]
			]
		]
	];

	public function getElasticSearchConnector() {
		if (empty($this->connection)) {
			$clientBuilder = \Elasticsearch\ClientBuilder::create();
			if (getenv('ES_TEST_HOST')) {
				$clientBuilder->setHosts([getenv('ES_TEST_HOST')]);
			}
			$this->connection = new \Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector($clientBuilder->build());
		}
		return $this->connection;
	}

	public function getElasticSearchDataSet() {
		if (empty($this->dataSet)) {
			$this->dataSet = new \Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet($this->getElasticSearchConnector());
			$this->dataSet->setFixture($this->fixture);
			$this->dataSet->setMappings($this->mappings);
			$this->dataSet->setSettings($this->settings);
		}
		return $this->dataSet;
	}

	public function testSizesFromFixture() {
		$params = ['index' => static::PIZZA_TRAIT_INDEX, 'type' => 'items'];
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

	public function testNestedSearchSort() {
		$params = [
			'index' => static::PIZZA_TRAIT_INDEX,
			'type' => 'toppings',
		];
		$params['body']['query']['bool']['must'][]['term']['categories.name'] = 'meat';
		$params['body']['sort']['categories.display_order'] = [
			'order' => 'asc',
			'nested_filter' => [['term' => ['categories.name' => 'meat']]]
		];
		$results = $this->getElasticSearchConnector()->getConnection()->search($params);
		$this->assertEquals(3, $results['hits']['total']);
		$this->assertEquals(
			[
				$this->fixture['store']['toppings'][1],
				$this->fixture['store']['toppings'][3],
				$this->fixture['store']['toppings'][0],
			],
			array_column($results['hits']['hits'], '_source')
		);
	}

	public function testSettings() {
		$settings = $this->getElasticSearchConnector()->getConnection()->indices()->getSettings(array('index' => static::PIZZA_TRAIT_INDEX))['store']['settings']['index'];
		$this->assertEquals(
			$this->settings[static::PIZZA_TRAIT_INDEX]['analysis'],
			$settings['analysis']
		);
	}
}
