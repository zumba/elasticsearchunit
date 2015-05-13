<?php

namespace Zumba\PHPUnit\Extensions\ElasticSearch\Base;

interface Connector {

	/**
	 * Get the connection.
	 *
	 * @return \Elasticsearch\Client
	 */
	public function getConnection();

}