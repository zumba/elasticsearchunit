<?php

namespace Zumba\PHPUnit\Extensions\ElasticSearch\DataSet;
use \Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector;

class DataSet {

	/**
	 * Fixture data.
	 *
	 * [index name] => [type name] => [][data]
	 *
	 * @var array
	 */
	protected $fixture = array();

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
				do {
					$response = $this->connection->getConnection()->indices()->status(compact('index'));
					if (!isset($response['indices'][$index]['docs']['num_docs'])) {
						break;
					}
					usleep(1000);
				} while ($response['indices'][$index]['docs']['num_docs'] != $count);
			}
		}

		return $this;
	}

}