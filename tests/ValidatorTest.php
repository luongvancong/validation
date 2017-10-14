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
			'age.required' => 'Vui long nhap tuoi',
			'email.email' => 'Vui long nhap dung dinh dang email',
			"email.required" => "Vui long nhap email"
		];

		$validator = new Validator($data, $rules, $messages);
		$this->assertFalse($validator->passes());
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
			'age.required' => 'Vui long nhap tuoi',
			'email.email' => 'Vui long nhap dung dinh dang email'
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
			'age' => 'required|bigger:18',
			'email' => 'email|required'
		];
		$messages = [
			'age.required' => 'Vui long nhap tuoi',
			'email.email' => 'Vui long nhap dung dinh dang email'
		];

		$validator = new Validator($data, $rules, $messages);
		$validator->addExtension('bigger', function($attribue, $value) {
			return $value > $attribue[0];
		});

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
			'age' => '192.159.2.2'
		];
		$rules = [
			'age' => 'ip'
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