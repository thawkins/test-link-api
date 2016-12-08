<?php
namespace Skalda\TestLinkAPI\Entities;

class TestSuite extends BaseEntity
{
	protected $fullPath;

	public $name;
	public $id;
	public $parent_id;


	public function getFullPath()
	{
		if($this->fullPath === null) {
			$this->fullPath = $this->client->getFullPath($this);
		}

		return $this->fullPath;
	}

	public function setFullPath(array $path)
	{
		$this->fullPath = $path;
	}

}