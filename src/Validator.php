<?php

namespace BlackBear\Validation;

class Validator {

	protected $data = array();
	protected $rules;

	public function __construct(array $data, $rules)
	{
		$this->data = $data;
		$this->rules = $rules;
	}

	public function run()
	{
		# code...
	}
}