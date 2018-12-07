<?php

namespace Zumba\PHPUnit\Extensions\ElasticSearch\Client;

use Zumba\PHPUnit\Extensions\ElasticSearch\Base\Connector as BaseConnector;

class Connector implements BaseConnector {

	/**
	 * Holds the Elastic Search client connection.
	 *
	 * @var \Elasticsearch\Client
	 */
	protected $connection;


	/**
	 * Constructor
	 */
	public function __construct(\Elasticsearch\Client $connection) {
		if (!$connection instanceof \Elasticsearch\Client) {
			throw new \Exception("Client not instantiated correctly. Must be instance of Elasticsearch\Client");
		}
		$this->connection = $connection;
	}

	/**
	 * Retrieves a connection.
	 *
	 * @return \Elasticsearch\Client
	 */
	public function getConnection() {
		return $this->connection;
	}

}
