<?php

namespace Zumba\PHPUnit\Extensions\ElasticSearch\DataSet;
use \Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector;

class DataSet {

	/**
	 * Max retries / waiting time for indexing
	 */
	const MAX_RETRY = 30;

	/**
	 * Fixture data.
	 *
	 * [index name] => [type name] => [][data]
	 *
	 * @var array
	 */
	protected $fixture = array();

	/**
	 * Mappings
	 *
	 * [index_name] => [type name] => [mappings]
	 *
	 * @var array
	 */
	protected $mappings = array();

	/**
	 * Settings
	 *
	 * [index_name] => [settings]
	 *
	 * @var array
	 */
	public $settings = array();

	/**
	 * Connection object.
	 *
	 * @var Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector
	 */
	protected $connection;

	/**
	 * Constructor.
	 *
	 * @param Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector
	 */
	public function __construct(Connector $connection) {
		$this->connection = $connection;
	}

	/**
	 * Sets up the fixture data.
	 *
	 * see $this->fixture
	 *
	 * @param array $data
	 * @return Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet
	 */
	public function setFixture(array $data) {
		$this->fixture = $data;
		return $this;
	}

	/**
	 * Sets up the fixture mappings
	 *
	 * @param array $mappings
	 * @return Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet
	 */
	public function setMappings(array $mappings) {
		$this->mappings = $mappings;
		return $this;
	}

	/**
	 * Sets up the fixture settings
	 *
	 * @param array $settings
	 * @return Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet
	 */
	public function setSettings(array $settings) {
		$this->settings = $settings;
		return $this;
	}

	/**
	 * Delete all indices specified in the fixture keys.
	 *
	 * @return Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet
	 */
	public function deleteIndices() {
		foreach (array_keys($this->fixture) as $index) {
			if ($this->connection->getConnection()->indices()->exists(compact('index'))) {
				$this->connection->getConnection()->indices()->delete(compact('index'));
			}
		}
		return $this;
	}

	/**
	 * Creates all types with data from the fixture.
	 *
	 * @return Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet
	 */
	public function buildIndices() {
		$verify = [];
		foreach ($this->fixture as $index => $types) {

			if (!$this->connection->getConnection()->indices()->exists(compact('index'))) {
				$this->connection->getConnection()->indices()->create(compact('index'));
			}
			if (!empty($this->settings[$index])) {
				$this->defineSettings($index);
			}
			if (!empty($this->mappings[$index])) {
				$this->defineMappings($index);
			}

			$documents[$index] = $this->getDocumentCount($index);
			foreach ($types as $type => $data) {
				if (empty($data)) {
					continue;
				}
				$params = [
					'index' => $index,
					'type' => $type,
					'body' => []
				];
				foreach ($data as $key => $entry) {
					$params['body'][] = [
						'index' => [
							'_id' => (!empty($entry['id'])) ? $entry['id'] : $key
						]
					];
					$params['body'][] = $entry;
				}
				$response = $this->connection->getConnection()->bulk($params);
				if (!empty($response['items'])) {
					if (!isset($verify[$index])) {
						$verify[$index] = 0;
					}
					$verify[$index] += count($response['items']);
				}
			}
		}
		//ensure that data has been indexed before you can use it
		if (!empty($verify)) {
			foreach ($verify as $index => $count) {
				if (empty($count)) {
					continue;
				}
				$retries = 1;
				do {
					if ($retries == static::MAX_RETRY) {
						throw new \RuntimeException("Indexing time out for Elastic Search Fixture");
					}
					$response = $this->connection->getConnection()->indices()->stats(compact('index'));
					$retries++;
					usleep(100000);
				} while ($response['indices'][$index]['total']['docs']['count'] != $documents[$index]);
			}
		}

		return $this;
	}

	/**
	 * Add settings to the index
	 *
	 * @param string $index
	 * @return void
	 */
	public function defineSettings($index) {
		if (empty($this->settings[$index])) {
			return;
		}
		$params = ['index' => $index];

		$this->connection->getConnection()->cluster()->health($params + ['wait_for_status' => 'yellow']);
		$this->connection->getConnection()->indices()->close($params);
		$this->connection->getConnection()->indices()->putSettings($params + ['body' => ['settings' => $this->settings[$index]]]);
		$this->connection->getConnection()->indices()->open($params);
	}

	/**
	 * Add mappings to the index
	 *
	 * @param string $index
	 * @return void
	 */
	protected function defineMappings($index) {
		foreach ($this->mappings[$index] as $type => $mappings) {
			$params = [
				'index' => $index,
				'type' => $type
			];

			if ($this->connection->getConnection()->indices()->existsType($params)) {
				$this->connection->getConnection()->indices()->deleteMapping($params);
			}
			if (empty($mappings)) {
				continue;
			}
			$params['body'][$type] = (array)$mappings;
			$this->connection->getConnection()->indices()->putMapping($params);
		}
	}

	/**
	 * Get the document count for an index
	 *
	 * @param string $index
	 * @return integer
	 */
	protected function getDocumentCount($index) {
		if (empty($this->fixture[$index])) {
			return 0;
		}
		$documents = 0;
		foreach ($this->fixture[$index] as $type => $records) {
			$documents += count($records);
			if (empty($this->mappings[$index][$type])) {
				continue;
			}
			foreach ($records as $record) {
				$documents += $this->getDocumentNestedCount($this->mappings[$index][$type], $record);
			}
		}
		return $documents;
	}

	/**
	 * Get the document count for an index
	 *
	 * @param array $mappings
	 * @param array $records
	 * @return integer
	 */
	protected function getDocumentNestedCount(array $mappings, array $records) {
		if (empty($records)) {
			return 0;
		}
		$documents = 0;
		foreach ($mappings['properties'] as $key => $properties) {
			if (empty($properties['type']) || $properties['type'] !== 'nested') {
				continue;
			}
			$documents += count($records[$key]);
			if (!empty($properties['properties'])) {
				foreach ($records[$key] as $record) {
					if (!is_array($record)) {
						continue;
					}
					$documents += $this->getDocumentNestedCount($properties, $record);
				}
			}
		}
		return $documents;
	}
}
