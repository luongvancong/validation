# Class Validation for php

# Validator

  Validate input value

# Installation

    composer require blackbear/validation

# Example

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

    if ($validator->passes()) {
      echo 'Validate successful';
    } else {
      echo 'Validate fails'
    }

# Get error messages

    $errors = $validator->getErrors();

# Add custom rule

		$validator->addExtension('bigger', function($attribue, $value) {
			return $value > $attribue[0];
		});

    $data = [
			'age' => 20,
			'email' => 'cong.itsoft@gmail.com'
		];
		$rules = [
			'age' => 'required|bigger:18',
			'email' => 'email|required'
		];
		$messages = [
			'age.required' => 'Please fill age',
			'email.email' => 'Please fill email'
		];

    $validator = new Validator();
    $validator->setData($data)
				    ->setRules($rules)
		        ->setMessages($messages);

    if ($validator->passes()) {
      echo 'Validate successful';
    } else {
      echo 'Validate fails'
    }

# Public method

`setData`

`setRules`

`setMessages`

`passes`

`fails`

# Default rules

`required`

`email`

`exception`

`ip`

`min`: min:20

`max`: max:20

`in_array`: in_array:1,2,3

`not_in_array`: not_in_array:1,2,3

`between`: between:10,100

`regex`: regexp:/^([\d]+)$/

`url`

`int`

`float`

`double`

`boolean`

`nullable`

`equals`: equals:8


**Unit test [tests folder]**(https://github.com/luongvancong/validation/blob/master/tests/ValidatorTest.php)**