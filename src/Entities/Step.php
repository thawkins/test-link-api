<?php
namespace thawkins\TestLinkAPI\Entities;

class Step extends BaseEntity
{
	public $step_number;
	public $actions;
	public $expected_results;
	public $execution_type;
}