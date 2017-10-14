<?php

namespace BlackBear\Validation;

use BlackBear\Validation\Exceptions\RuleNotFoundException;
use Closure;

class Validator {

	protected $data = array();

	protected $initialRules = array();
	protected $rules = [];

	protected $attributes = [];

	protected $initialMessages = [];
	protected $messages = [];

	protected $errors = [];
	protected $extensions = [];

	public function __construct(array $data, array $rules, array $messages = array())
	{
		$this->data = $data;
		$this->initialRules = $rules;
		$this->initialMessages = $messages;
		$this->setRules($rules);
		$this->setMessages($messages);
	}

	public function passes()
	{
		return $this->validate($this->data, $this->rules);
	}

	protected function validate(array $data, array $rules)
	{
		foreach($data as $key => $value) {
			$this->applyRule($key, $value);
		}

		return empty($this->errors);
	}

	protected function setRules(array $rules)
	{
		foreach($rules as $key => $value) {
			$rulesArray = explode('|', $value);

			foreach($rulesArray as $rule) {
				if(strpos($rule, ':')) {
					$ruleArray = explode(':', $rule);
					list($ruleName, $ruleParams) = $ruleArray;
					$this->rules[$key][] = $ruleName;
					$this->attributes[$key][$ruleName] = explode(',', $ruleParams);
				} else {
					$this->rules[$key][] = $rule;
					$this->attributes[$key][$rule] = [];
				}
			}
		}
	}

	protected function setMessages(array $messages) {

		foreach($messages as $key => $value) {
			$keyArray = explode('.', $key);
			list($field, $rule) = $keyArray;
			$this->messages[$field][$rule] = $value;
		}

	}

	protected function applyRule($key, $value) {
		$rules = $this->rules[$key];
		$attributes = $this->attributes[$key];
		foreach($rules as $rule) {
			$callMethod = "validate".$this->snakeToCamelCase($rule);

			$attribute = $this->getAttribute($key, $rule);

			if(! $this->$callMethod($attribute, $value)) {
				$this->addError($key, $rule);
			}
		}
	}

	protected function addError($key, $rule) {
		if(isset($this->messages[$key])) {
			if( array_key_exists($rule, $this->messages[$key]) ) {
				$this->errors[$key][] = $this->messages[$key][$rule];
			} else {
				$this->errors[$key][] = "{$key} is not {$rule}";
			}
		} else {
			$this->errors[$key][] = "{$key} is not {$rule}";
		}
	}

	protected function getAttribute($key, $rule)
	{
		return array_key_exists($rule, $this->attributes[$key]) ? $this->attributes[$key][$rule] : array();
	}


	public function addExtension($key, Closure $callback)
	{
		$this->extensions[$key] = $callback;
	}

	protected function getExtension($key)
	{
		return $this->hasExtension($key) ? $this->extensions[$key] : null;
	}

	protected function hasExtension($key)
	{
		return array_key_exists($key, $this->extensions);
	}

	protected function callExtension($key, $parameters)
	{
		if($this->hasExtension($key)) {
			$method = $this->extensions[$key];
			$parameters[] = $this;
			return call_user_func_array($method, $parameters);
		}

		return false;
	}

	protected function validateEmail($attributes, $value)
	{
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	protected function validateUrl($attributes, $value)
	{
		return filter_var($value, FILTER_VALIDATE_URL);
	}

	protected function validateRequired($attributes, $value)
	{
		if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif (is_array($value) && count($value) < 1) {
            return false;
        }

        return true;
	}

	protected function validateIp($attributes, $value)
	{
		return filter_var($value, FILTER_VALIDATE_IP);
	}

	protected function validateInt($attributes, $value)
	{
		return filter_var($value, FILTER_VALIDATE_INT);
	}

	protected function validateFloat($attributes, $value)
	{
		return filter_var($value, FILTER_VALIDATE_FLOAT);
	}

	protected function validateDouble($attributes, $value)
	{
		return is_double($value);
	}

	protected function validateBoolean($attributes, $value)
	{
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	protected function validateBetween($attributes, $value)
	{
		if($value >= $attributes[0] && $value <= $attributes[1]) {
			return true;
		}

		return false;
	}

	protected function validateNullable($attributes, $value)
	{
		return is_null($value);
	}

	protected function validateMin($attributes, $value)
	{
		if(is_array($value)) {
			return count($value) >= $attributes[0];
		} else if(is_string($value)) {
			return mb_strlen($value) >= $attributes[0];
		} else {
			return $value >= $attributes[0];
		}
	}

	protected function validateMax($attributes, $value)
	{
		if(is_array($value)) {
			return count($value) <= $attributes[0];
		} else if(is_string($value)) {
			return mb_strlen($value) <= $attributes[0];
		} else {
			return $value <= $attributes[0];
		}
	}


	protected function validateInArray($attributes, $value)
	{
		return in_array($value, $attributes);
	}


	protected function validateNotInArray($attributes, $value)
	{
		return !$this->validateInArray($attributes, $value);
	}

	protected function validateRegexp($attributes, $value)
	{
		return filter_var($value, FILTER_VALIDATE_REGEXP, array(
			'options' => array(
				'regexp' => $attributes[0]
			)
		));
	}

	protected function snakeToCamelCase($string)
	{
		$stringArray = explode('_', $string);
		foreach($stringArray as $key => $value) {
			$stringArray[$key] = ucfirst($value);
		}

		return implode('', $stringArray);
	}


	public function __call($method, $parameters)
	{
		$rule = strtolower(substr($method, 8));

		if(isset($this->extensions[$rule])) {
			return $this->callExtension($rule, $parameters);
		}

		throw new RuleNotFoundException("Method [$method] does not exist.");
	}
}