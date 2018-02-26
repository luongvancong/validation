<?php

namespace BlackBear\Validation;

use BlackBear\Validation\Exceptions\RuleNotFoundException;
use Closure;

class Validator {

	/**
	 * Data need to validate
	 * @var array
	 */
	protected $data = array();

	/**
	 * Initial rules
	 * @var array
	 */
	protected $initialRules = array();

	/**
	 * Convert rules
	 * @var array
	 */
	protected $rules = [];

	/**
	 * Attributes
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Initial messages
	 * @var array
	 */
	protected $initialMessages = [];

	/**
	 * Convert messages
	 * @var array
	 */
	protected $messages = [];

	/**
	 * Errors
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Custom validator
	 * @var array
	 */
	protected $extensions = [];

	public function __construct(array $data = array(), array $rules = array(), array $messages = array())
	{
		$this->data = $data;
		$this->setRules($rules);
		$this->setMessages($messages);
	}


	/**
	 * Set data for validate
	 * @param array $data
	 * @return \BlackBear\Validation\Validator
	 */
	public function setData(array $data)
	{
		$this->data = $data;
		return $this;
	}


	/**
	 * Get data
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}


	/**
	 * Check validate passes
	 * @return boolean
	 */
	public function passes()
	{
		return $this->validate();
	}


	/**
	 * Check validate fails
	 * @return boolean
	 */
	public function fails()
	{
		return !$this->passes();
	}

	/**
	 * Get errors
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}


	/**
	 * Validate
	 * @param  array  $data
	 * @param  array  $rules
	 * @return bool
	 */
	protected function validate()
	{
		$data = $this->getData();

		foreach($data as $key => $value) {
			$this->applyRule($key, $value);
		}

		return empty($this->errors);
	}

	/**
	 * Process rules and attributes
	 * @param array $rules
	 * @return \BlackBear\Validation\Validator
	 */
	public function setRules(array $rules)
	{
		$this->initialRules = $rules;
		foreach($rules as $key => $value) {
			$rulesArray = explode('|', $value);

			foreach($rulesArray as $rule) {
				if(false !== strpos($rule, ':')) {
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

		return $this;
	}

	/**
	 * Set messages
	 * @param array $messages
	 * @return \BlackBear\Validation\Validator
	 */
	public function setMessages(array $messages) {
		$this->initialMessages = $messages;
		foreach($messages as $key => $value) {
			$keyArray = explode('.', $key);
			list($field, $rule) = $keyArray;
			$this->messages[$field][$rule] = $value;
		}

		return $this;
	}

	/**
	 * Apply rule for one data with key and value
	 * @param  mixed $key
	 * @param  mixed $value
	 * @return void
	 */
	protected function applyRule($key, $value) {
		if(isset($this->rules[$key])) {
			$rules = $this->rules[$key];
			$attributes = $this->attributes[$key];
			foreach($rules as $rule) {
				$callMethod = "validate".$this->snakeToCamelCase($rule);

				$attribute = $this->getAttribute($key, $rule);

				// If rule is required, we run validate required rule
				// else if isset value, we run validate with other rule
				if($rule === 'required') {
					if(! $this->$callMethod($attribute, $value)) {
						$this->addError($key, $rule);
					}
				} else {
					if($value) {
						if(! $this->$callMethod($attribute, $value)) {
							$this->addError($key, $rule);
						}
					}
				}
			}
		}
	}

	/**
	 * Add error message
	 * @param mixed $key
	 * @param mixed $rule
	 */
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

	/**
	 * Get an attribute
	 * @param  mixed $key
	 * @param  mixed $rule
	 * @return array
	 */
	protected function getAttribute($key, $rule)
	{
		return array_key_exists($rule, $this->attributes[$key]) ? $this->attributes[$key][$rule] : array();
	}


	/**
	 * Add a custom validation
	 * @param string  $key
	 * @param Closure $callback
	 */
	public function addExtension($key, Closure $callback)
	{
		$key = $this->snakeToCamelCase($key);
		$this->extensions[$key] = $callback;
	}

	/**
	 * Get a custom validation handle by key
	 * @param  string $key
	 * @return Closure|null
	 */
	protected function getExtension($key)
	{
		return $this->hasExtension($key) ? $this->extensions[$key] : null;
	}

	/**
	 * Check custom validation exist by key
	 * @param  string  $key
	 * @return boolean
	 */
	protected function hasExtension($key)
	{
		return array_key_exists($key, $this->extensions);
	}

	/**
	 * Call a custom validation
	 * @param  string $key
	 * @param  array $parameters
	 * @return mixed
	 */
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

	protected function validateEquals($attributes, $value)
	{
		if(is_array($value)) {
			return count($value) == $attributes[0];
		} else if(is_string($value)) {
			return $value === strval($attributes[0]);
		} else if(is_integer($value)) {
			return $value === intval($attributes[0]);
		}
	}


	protected function validateSame($attributes, $value) {
		if($value == $this->data[$attributes[0]]) {
			return true;
		}

		return false;
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
		$rule = substr($method, 8);

		if(isset($this->extensions[$rule])) {
			return $this->callExtension($rule, $parameters);
		}

		throw new RuleNotFoundException("Method [$method] does not exist.");
	}
}