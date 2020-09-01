<?php
namespace thawkins\TestLinkAPI\Entities;

class TestCase extends BaseEntity
{
	protected $fullPath = null;
	protected $keywords = null;

	public $updater_login;
	public $author_login;
	public $name;
	public $node_order;
	public $testsuite_id;
	public $testcase_id;
	public $id;
	public $tc_external_id;
	public $version;
	public $layout;
	public $status;
	public $summary;
	public $preconditions;
	public $importance;
	public $author_id;
	public $creation_ts;
	public $updater_id;
	public $modification_ts;
	public $active;
	public $is_open;
	public $execution_type;
	public $estimated_exec_duration;
	public $author_first_name;
	public $author_last_name;
	public $updater_first_name;
	public $updater_last_name;
	public $steps = [];
	public $full_tc_external_id;


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

	public function getKeywords()
	{
		if($this->keywords === null) {
			$this->keywords = $this->client->getKeywordsByTestCase($this);
		}

		return $this->keywords;
	}

}