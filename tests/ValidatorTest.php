<?php

use BlackBear\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase {

	public function test_instance()
	{
		$data = [
			'email' => 'cong.itsoft@gmail.com'
		];
		$rules = [
			'age' => 'required',
			'email' => 'email|required'
		];
		$validator = new Validator($data, $rules);
		$this->assertInstanceOf('BlackBear\Validation\Validator', $validator);
	}

	public function test_fails()
	{
		$data = [
			'age' => 20,
			'email' => ''
		];
		$rules = [
			'age' => 'required',
			'email' => 'email|required'
		];
		$messages = [
			'age.required' => 'Please fill age',
			'email.email' => 'Please fill email',
			"email.required" => "Please abc"
		];

		$validator = new Validator($data, $rules, $messages);
		$this->assertTrue($validator->fails());
	}

	public function test_passes()
	{
		$data = [
			'age' => 20,
			'email' => 'cong.itsoft@gmail.com'
		];
		$rules = [
			'age' => 'required',
			'email' => 'email|required'
		];
		$messages = [
			'age.required' => 'Please fill age',
			'email.email' => 'Please fill email'
		];

		$validator = new Validator($data, $rules, $messages);
		$this->assertTrue($validator->passes());
	}


	public function test_extend()
	{
		$data = [
			'age' => 20,
			'email' => 'cong.itsoft@gmail.com'
		];
		$rules = [
			'age' => 'required|bigger:18|add_rule_test|camCase',
			'email' => 'email|required'
		];
		$messages = [
			'age.required' => 'Please fill age',
			'email.email' => 'Please fill email'
		];

		$validator = new Validator();
		$validator->addExtension('bigger', function($attribue, $value) {
			return $value > $attribue[0];
		});
		$validator->addExtension('add_rule_test', function ($attribute, $value) {
			return true;
		});
		$validator->addExtension('camCase', function ($attribute, $value) {
			return true;
		});
		$validator->setData($data)
				  ->setRules($rules)
		          ->setMessages($messages);

		$this->assertTrue($validator->passes());
	}

	public function test_email()
	{
		$data = [
			'email' => 'cong.itsoft@gmail.com'
		];

		$rules = [
			'email' => 'email'
		];

		$validator = new Validator($data, $rules);
		$this->assertTrue($validator->passes());
	}

	public function test_between()
	{
		$data = [
			'age' => 20
		];
		$rules = [
			'age' => 'between:10,22'
		];

		$validator = new Validator($data, $rules);
		$this->assertTrue($validator->passes());
	}

	public function test_nullable()
	{
		$data = [
			'age' => null
		];
		$rules = [
			'age' => 'nullable'
		];

		$validator = new Validator($data, $rules);
		$this->assertTrue($validator->passes());
	}


	public function test_min() {
		$data = [
			'age' => 20
		];
		$rules = [
			'age' => 'min:18'
		];

		$validator = new Validator($data, $rules);
		$this->assertTrue($validator->passes());
	}

	public function test_max() {
		$data = [
			'age' => 20
		];
		$rules = [
			'age' => 'max:22'
		];

		$validator = new Validator($data, $rules);
		$this->assertTrue($validator->passes());

		//
		$data = [
			'age' => 20
		];
		$rules = [
			'age' => 'max:18'
		];

		$validator = new Validator($data, $rules);
		$this->assertFalse($validator->passes());
	}

	public function test_in_array() {
		$data = [
			'age' => 20
		];
		$rules = [
			'age' => 'in_array:10,20,22,200'
		];

		$validator = new Validator($data, $rules);
		$this->assertTrue($validator->passes());

		//
		$data = [
			'age' => 20
		];
		$rules = [
			'age' => 'in_array:10,200,22,200'
		];

		$validator = new Validator($data, $rules);
		$this->assertFalse($validator->passes());
	}

	public function test_regex()
	{
		$data = [
			'age' => '121212'
		];
		$rules = [
			'age' => 'regexp:/^([\d]+)$/'
		];

		$validator = new Validator($data, $rules);
		$this->assertTrue($validator->passes());
	}

	public function test_ip()
	{
		$data = [
			'age' => '192.159.2.2',
			'ip' => '1.2.2.2',
			'name' => "123"
		];
		$rules = [
			'age' => 'ip',
			'name' => 'required'
		];

		$validator = new Validator($data, $rules);
		$this->assertTrue($validator->passes());
		$this->assertEmpty($validator->getErrors());
	}


	public function test_equals()
	{
		$data = [
			'age' => 20
		];
		$rules = [
			'age' => 'equals:20'
		];

		$validator = new Validator($data, $rules);
		$this->assertTrue($validator->passes());
	}


	public function test_same()
	{
		$data = [
			'age' => 20,
			'age_re' => 20
		];

		$rules = [
			'age' => 'required',
			'age_re' => 'required|same:age'
		];

		$validator = new Validator($data, $rules);
		$this->assertTrue($validator->passes());
	}


	public function test_exception()
	{
		$data = [
			'age' => '192.159.2.2'
		];
		$rules = [
			'age' => 'ipv6'
		];

		$validator = new Validator($data, $rules);

		try {
			$actual = $validator->passes();
		} catch(\BlackBear\Validation\Exceptions\RuleNotFoundException $error) {
			$this->assertInstanceOf('\BlackBear\Validation\Exceptions\RuleNotFoundException', $error);
		}
	}
}