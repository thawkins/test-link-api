<?php

namespace Skalda\TestLinkAPI\Entities;

use Skalda\TestLinkAPI\Client;

abstract class BaseEntity implements IResponse
{
	protected $client;

	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * @param Client $client
	 * @param array $array
	 * @return $this
	 */
	static function createFromArray(Client $client, array $array)
	{
		$className = get_called_class();
		$object = new $className($client);
		foreach ($array as $key => $value)
		{
			$object->$key = $value;
		}

		return $object;
	}
}