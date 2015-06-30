<?php
namespace Tiga\Framework;

/**
 * Validator, based on GUMP - A fast, extensible PHP input validation class
 *
 * @author      Sean Nieuwoudt (http://twitter.com/SeanNieuwoudt)
 * @copyright   Copyright (c) 2014 Wixelhq.com
 * @link        http://github.com/Wixel/GUMP
 * @version     1.0
 */

class Validator
{
	// Validation rules for execution
	protected $validationRules = array();

	// Filter rules for execution
	protected $filterRules = array();

	// Instance attribute containing errors from last run
	protected $errors = array();

	// Error Message by Key
	protected $errorMessages = array(); 

	// All Error Message
	protected $allErrorMessages = array(); 

	// Custom validation methods
	protected  $validation_methods = array();

	// Customer filter methods
	protected  $filter_methods = array();

	// ** ------------------------- Validation Data ------------------------------- ** //

	public  $basic_tags     = "<br><p><a><strong><b><i><em><img><blockquote><code><dd><dl><hr><h1><h2><h3><h4><h5><h6><label><ul><li><span><sub><sup>";

	
	/**
	 * Magic method to generate the validation error messages
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->errors(true);
	}

	/**
	 * Perform XSS clean to prevent cross site scripting
	 *
	 * @access public
	 * @param  array $data
	 * @return array
	 */
	public function xssClean(array $data)
	{
		foreach($data as $k => $v)
		{
			$data[$k] = filter_var($v, FILTER_SANITIZE_STRING);
		}

		return $data;
	}

	/**
	 * Adds a custom validation rule using a callback function
	 *
	 * @access public
	 * @param string $rule
	 * @param callable $callback
	 * @return bool
	 */
	public function addValidator($rule, $callback)
	{
		$method = 'validate_'.$rule;
		
		if(method_exists(__CLASS__, $method) || isset(self::$validation_methods[$rule])) {
			throw new Exception("Validator rule '$rule' already exists.");
		}

		self::$validation_methods[$rule] = $callback;

		return $this;
	}

	/**
	 * Adds a custom filter using a callback function
	 *
	 * @access public
	 * @param string $rule
	 * @param callable $callback
	 * @return bool
	 */
	public function addFilter($rule, $callback)
	{
		$method = 'filter_'.$rule;
		
		if(method_exists(__CLASS__, $method) || isset(self::$filter_methods[$rule])) {
			throw new Exception("Filter rule '$rule' already exists.");
		}

		self::$filter_methods[$rule] = $callback;

		return $this;
	}

	/**
	 * Getter/Setter for the validation rules
	 *
	 * @param array $rules
	 * @return array
	 */
	public function validationRules(array $rules = array())
	{
		if(empty($rules)) {
			return $this->validationRules;
		}

		$this->validationRules = $rules;

		return $this;
	}

	/**
	 * Getter/Setter for the filter rules
	 *
	 * @param array $rules
	 * @return array
	 */
	public function filterRules(array $rules = array())
	{
		if(empty($rules)) {
			return $this->filterRules;
		}

		$this->filterRules = $rules;

		return $this;
	}

	/**
	 * Run the filtering and validation after each other
	 * @param array $data
	 * @return array
	 * @return boolean
	 */
	public function run(array $data, $checkFields = false)
	{
		$data = $this->filter($data, $this->filterRules());

		$this->validate(
			$data, $this->validationRules()
		);

		if($checkFields === true) {
			$this->checkFields($data);
		}

		return $data;
	}

	/**
	 * Ensure that the field counts match the validation rule counts
	 *
	 * @param array $data
	 */
	private function checkFields(array $data)
	{
		$ruleset  = $this->validationRules();
		$mismatch = array_diff_key($data, $ruleset);
		$fields   = array_keys($mismatch);

		foreach ($fields as $field) {
			$this->errors[] = array(
				'field' => $field,
				'value' => $data[$field],
				'rule'  => 'mismatch',
				'param' => NULL
			);
		}

		return $this;
	}

	/**
	 * Sanitize the input data
	 *
	 * @access public
	 * @param  array $data
	 * @return array
	 */
	public function sanitize(array $input, $fields = NULL, $utf8_encode = true)
	{
		$magic_quotes = (bool)get_magic_quotes_gpc();

		if(is_null($fields))
		{
			$fields = array_keys($input);
		}

		foreach($fields as $field)
		{
			if(!isset($input[$field]))
			{
				continue;
			}
			else
			{
				$value = $input[$field];

				if(is_string($value))
				{
					if($magic_quotes === TRUE)
					{
						$value = stripslashes($value);
					}

					if(strpos($value, "\r") !== FALSE)
					{
						$value = trim($value);
					}

					if(function_exists('iconv') && function_exists('mb_detect_encoding') && $utf8_encode)
					{
						$current_encoding = mb_detect_encoding($value);

						if($current_encoding != 'UTF-8' && $current_encoding != 'UTF-16') {
							$value = iconv($current_encoding, 'UTF-8', $value);
						}
					}

					$value = filter_var($value, FILTER_SANITIZE_STRING);
				}

				$input[$field] = $value;
			}
		}

		return $input;
	}

	/**
	 * Return the error array from the last validation run
	 *
	 * @return array
	 */
	public function getError($key=false)
	{
		if(!$key)
			return $this->errorMessages;

		if($this->hasError($key))
			return $this->errorMessages[$key];

		return false;
	}

	public function getAllError()
	{
		return $this->allErrorMessages;
	}

	/**
	 * Perform data validation against the provided ruleset
	 *
	 * @access public
	 * @param  mixed $input
	 * @param  array $ruleset
	 * @return mixed
	 */
	public function validate(array $input, array $ruleset)
	{
		$this->errors = array();

		foreach($ruleset as $field => $rules)
		{
			#if(!array_key_exists($field, $input))
			#{
			#   continue;
			#}

			$rules = explode('|', $rules);
			
	        if(in_array("required", $rules) || (isset($input[$field]) && trim($input[$field]) != ''))
	        {			
				foreach($rules as $rule)
				{
					$method = NULL;
					$param  = NULL;

					if(strstr($rule, ',') !== FALSE) // has params
					{
						$rule   = explode(',', $rule);
						$method = 'validate_'.$rule[0];
						$param  = $rule[1];
						$rule   = $rule[0];
					}
					else
					{
						$method = 'validate_'.$rule;
					}

					if(is_callable(array($this, $method)))
					{
						$result = $this->$method($field, $input, $param);

						if(is_array($result)) // Validation Failed
						{
							$this->errors[] = $result;
						}
					}
					else if (isset(self::$validation_methods[$rule]))
					{
						if (isset($input[$field])) {
							$result = call_user_func(self::$validation_methods[$rule], $field, $input, $param);

							if (!$result) // Validation Failed
							{
								$this->errors[] = array(
									'field' => $field,
									'value' => $input[$field],
									'rule'  => $method,
									'param' => $param
								);
							}
						}
					}
					else
					{
						throw new Exception("Validator method '$method' does not exist.");
					}
				}
			}
		}

		$this->compileMessage();

		return $this;
	}

	public function hasError($field=false)
	{

		if(!$field)
			return (count($this->errors) > 0)? TRUE : FALSE;

		return isset($this->errorMessages[$field]);
	}

	protected function compileMessage()
	{

		foreach($this->errors as $e) {

			$message = '';

			$field = ucwords(str_replace(array('_','-'), chr(32), $e['field']));
			$param = $e['param'];

			switch($e['rule']) {
				case 'mismatch' :
					$message = "There is no validation rule for $field";
					break;
				case 'validate_required':
					$message = "The $field field is required";
					break;
				case 'validate_valid_email':
					$message = "The $field field is required to be a valid email address";
					break;
				case 'validate_max_len':
					if($param == 1) {
						$message = "The $field field needs to be shorter than $param character";
					} else {
						$message = "The $field field needs to be shorter than $param characters";
					}
					break;
				case 'validate_min_len':
					if($param == 1) {
						$message = "The $field field needs to be longer than $param character";
					} else {
						$message = "The $field field needs to be longer than $param characters";
					}
					break;
				case 'validate_exact_len':
					if($param == 1) {
						$message = "The $field field needs to be exactly $param character in length";
					} else {
						$message = "The $field field needs to be exactly $param characters in length";
					}
					break;
				case 'validate_alpha':
					$message = "The $field field may only contain alpha characters(a-z)";
					break;
				case 'validate_alpha_numeric':
					$message = "The $field field may only contain alpha-numeric characters";
					break;
				case 'validate_alpha_dash':
					$message = "The $field field may only contain alpha characters &amp; dashes";
					break;
				case 'validate_numeric':
					$message = "The $field field may only contain numeric characters";
					break;
				case 'validate_integer':
					$message = "The $field field may only contain a numeric value";
					break;
				case 'validate_boolean':
					$message = "The $field field may only contain a true or false value";
					break;
				case 'validate_float':
					$message = "The $field field may only contain a float value";
					break;
				case 'validate_valid_url':
					$message = "The $field field is required to be a valid URL";
					break;
				case 'validate_url_exists':
					$message = "The $field URL does not exist";
					break;
				case 'validate_valid_ip':
					$message = "The $field field needs to contain a valid IP address";
					break;
				case 'validate_valid_cc':
					$message = "The $field field needs to contain a valid credit card number";
					break;
				case 'validate_valid_name':
					$message = "The $field field needs to contain a valid human name";
					break;
				case 'validate_contains':
					$message = "The $field field needs to contain one of these values: ".implode(', ', $param);
					break;
				case 'validate_street_address':
					$message = "The $field field needs to be a valid street address";
					break;
				case 'validate_date':
					$message = "The $field field needs to be a valid date";
					break;
				case 'validate_min_numeric':
					$message = "The $field field needs to be a numeric value, equal to, or higher than $param";
					break;
				case 'validate_max_numeric':
					$message = "The $field field needs to be a numeric value, equal to, or lower than $param";
					break;
				default:
					$message = "The $field field is invalid";				
			}	

			array_push($this->allErrorMessages,$message);

			if(!isset($this->errorMessages[$e['field']]))
				$this->errorMessages[$e['field']] = array($message);
			else
				array_push($this->errorMessages[$e['field']],$message);
		}
	}

	/**
	 * Filter the input data according to the specified filter set
	 *
	 * @access public
	 * @param  mixed $input
	 * @param  array $filterset
	 * @return mixed
	 */
	public function filter(array $input, array $filterset)
	{
		foreach($filterset as $field => $filters)
		{
			if(!array_key_exists($field, $input))
			{
				continue;
			}

			$filters = explode('|', $filters);

			foreach($filters as $filter)
			{
				$params = NULL;

				if(strstr($filter, ',') !== FALSE)
				{
					$filter = explode(',', $filter);

					$params = array_slice($filter, 1, count($filter) - 1);

					$filter = $filter[0];
				}

				if(is_callable(array($this, 'filter_'.$filter)))
				{
					$method = 'filter_'.$filter;
					$input[$field] = $this->$method($input[$field], $params);
				}
				else if(function_exists($filter))
				{
					$input[$field] = $filter($input[$field]);
				}
				else if (isset(self::$filter_methods[$filter]))
				{
					$input[$field] = call_user_func(self::$filter_methods[$filter], $input[$field], $params);
				}
				else
				{
					throw new Exception("Filter method '$filter' does not exist.");
				}
			}
		}

		return $input;
	}

	// ** ------------------------- Filters --------------------------------------- ** //



	/**
	 * Remove all known punctuation from a string
	 *
	 * Usage: '<index>' => 'rmpunctuataion'
	 *
	 * @access protected
	 * @param  string $value
	 * @param  array $params
	 * @return string
	 */
	protected function filter_rmpunctuation($value, $params = NULL)
	{
		return preg_replace("/(?![.=$'€%-])\p{P}/u", '', $value);
	}

	/**
	 * Translate an input string to a desired language [DEPRECIATED]
	 *
	 * Any ISO 639-1 2 character language code may be used
	 *
	 * See: http://www.science.co.il/language/Codes.asp?s=code2
	 *
	 * @access protected
	 * @param  string $value
	 * @param  array $params
	 * @return string
	 */
	/*
	protected function filter_translate($value, $params = NULL)
	{
		$input_lang  = 'en';
		$output_lang = 'en';

		if(is_null($params))
		{
			return $value;
		}

		switch(count($params))
		{
			case 1:
				$input_lang  = $params[0];
				break;
			case 2:
				$input_lang  = $params[0];
				$output_lang = $params[1];
				break;
		}

		$text = urlencode($value);

		$translation = file_get_contents(
			"http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q={$text}&langpair={$input_lang}|{$output_lang}"
		);

		$json = json_decode($translation, true);

		if($json['responseStatus'] != 200)
		{
			return $value;
		}
		else
		{
			return $json['responseData']['translatedText'];
		}
	}
	*/

	/**
	 * Sanitize the string by removing any script tags
	 *
	 * Usage: '<index>' => 'sanitize_string'
	 *
	 * @access protected
	 * @param  string $value
	 * @param  array $params
	 * @return string
	 */
	protected function filter_sanitize_string($value, $params = NULL)
	{
		return filter_var($value, FILTER_SANITIZE_STRING);
	}

	/**
	 * Sanitize the string by urlencoding characters
	 *
	 * Usage: '<index>' => 'urlencode'
	 *
	 * @access protected
	 * @param  string $value
	 * @param  array $params
	 * @return string
	 */
	protected function filter_urlencode($value, $params = NULL)
	{
		return filter_var($value, FILTER_SANITIZE_ENCODED);
	}

	/**
	 * Sanitize the string by converting HTML characters to their HTML entities
	 *
	 * Usage: '<index>' => 'htmlencode'
	 *
	 * @access protected
	 * @param  string $value
	 * @param  array $params
	 * @return string
	 */
	protected function filter_htmlencode($value, $params = NULL)
	{
		return filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
	}

	/**
	 * Sanitize the string by removing illegal characters from emails
	 *
	 * Usage: '<index>' => 'sanitize_email'
	 *
	 * @access protected
	 * @param  string $value
	 * @param  array $params
	 * @return string
	 */
	protected function filter_sanitize_email($value, $params = NULL)
	{
		return filter_var($value, FILTER_SANITIZE_EMAIL);
	}

	/**
	 * Sanitize the string by removing illegal characters from numbers
	 *
	 * @access protected
	 * @param  string $value
	 * @param  array $params
	 * @return string
	 */
	protected function filter_sanitize_numbers($value, $params = NULL)
	{
		return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	}

	/**
	 * Filter out all HTML tags except the defined basic tags
	 *
	 * @access protected
	 * @param  string $value
	 * @param  array $params
	 * @return string
	 */
	protected function filter_basic_tags($value, $params = NULL)
	{
		return strip_tags($value, self::$basic_tags);
	}
	
	/**
	 * Convert the provided numeric value to a whole number
	 *
	 * @access protected
	 * @param  string $value
	 * @param  array $params
	 * @return string
	 */
	protected function filter_whole_number($value, $params = NULL)
	{
		return intval($value);
	}	

	// ** ------------------------- Validators ------------------------------------ ** //

	/**
	 * Verify that a value is contained within the pre-defined value set
	 *
	 * Usage: '<index>' => 'contains,value value value'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_contains($field, $input, $param = NULL)
	{
		if(!isset($input[$field]))
		{
			return;
		}

		$param = trim(strtolower($param));

		$value = trim(strtolower($input[$field]));

		if (preg_match_all('#\'(.+?)\'#', $param, $matches, PREG_PATTERN_ORDER)) {
			$param = $matches[1];
		} else  {
			$param = explode(chr(32), $param);
		}

		if(in_array($value, $param)) { // valid, return nothing
			return;
		}

		return array(
			'field' => $field,
			'value' => $value,
			'rule'  => __FUNCTION__,
			'param' => $param
		);
	}

	/**
	 * Check if the specified key is present and not empty
	 *
	 * Usage: '<index>' => 'required'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_required($field, $input, $param = NULL)
	{
    if(isset($input[$field]) && ($input[$field] === false || $input[$field] === 0 || $input[$field] === 0.0 || $input[$field] === "0" || !empty($input[$field])))
		{
			return;
		}

		return array(
		'field' => $field,
		'value' => NULL,
		'rule'  => __FUNCTION__,
		'param' => $param
		);
	}

	/**
	 * Determine if the provided email is valid
	 *
	 * Usage: '<index>' => 'valid_email'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_valid_email($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if(!filter_var($input[$field], FILTER_VALIDATE_EMAIL))
		{
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided value length is less or equal to a specific value
	 *
	 * Usage: '<index>' => 'max_len,240'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_max_len($field, $input, $param = NULL)
	{
		if(!isset($input[$field]))
		{
			return;
		}

		if(function_exists('mb_strlen'))
		{
			if(mb_strlen($input[$field]) <= (int)$param)
			{
				return;
			}
		}
		else
		{
			if(strlen($input[$field]) <= (int)$param)
			{
				return;
			}
		}

		return array(
			'field' => $field,
			'value' => $input[$field],
			'rule'  => __FUNCTION__,
			'param' => $param
		);
	}

	/**
	 * Determine if the provided value length is more or equal to a specific value
	 *
	 * Usage: '<index>' => 'min_len,4'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_min_len($field, $input, $param = NULL)
	{
		if(!isset($input[$field]))
		{
			return;
		}

		if(function_exists('mb_strlen'))
		{
			if(mb_strlen($input[$field]) >= (int)$param)
			{
				return;
			}
		}
		else
		{
			if(strlen($input[$field]) >= (int)$param)
			{
				return;
			}
		}

		return array(
			'field' => $field,
			'value' => $input[$field],
			'rule'  => __FUNCTION__,
			'param' => $param
		);
	}

	/**
	 * Determine if the provided value length matches a specific value
	 *
	 * Usage: '<index>' => 'exact_len,5'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_exact_len($field, $input, $param = NULL)
	{
		if(!isset($input[$field]))
		{
			return;
		}

		if(function_exists('mb_strlen'))
		{
			if(mb_strlen($input[$field]) == (int)$param)
			{
				return;
			}
		}
		else
		{
			if(strlen($input[$field]) == (int)$param)
			{
				return;
			}
		}

		return array(
			'field' => $field,
			'value' => $input[$field],
			'rule'  => __FUNCTION__,
			'param' => $param
		);
	}

	/**
	 * Determine if the provided value contains only alpha characters
	 *
	 * Usage: '<index>' => 'alpha'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_alpha($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if(!preg_match("/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ])+$/i", $input[$field]) !== FALSE)
		{
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided value contains only alpha-numeric characters
	 *
	 * Usage: '<index>' => 'alpha_numeric'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_alpha_numeric($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if(!preg_match("/^([a-z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ])+$/i", $input[$field]) !== FALSE)
		{
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided value contains only alpha characters with dashed and underscores
	 *
	 * Usage: '<index>' => 'alpha_dash'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_alpha_dash($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if(!preg_match("/^([a-z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ_-])+$/i", $input[$field]) !== FALSE)
		{
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided value is a valid number or numeric string
	 *
	 * Usage: '<index>' => 'numeric'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_numeric($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if(!is_numeric($input[$field]))
		{
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided value is a valid integer
	 *
	 * Usage: '<index>' => 'integer'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_integer($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if(!filter_var($input[$field], FILTER_VALIDATE_INT))
		{
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided value is a PHP accepted boolean
	 *
	 * Usage: '<index>' => 'boolean'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_boolean($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		$bool = filter_var($input[$field], FILTER_VALIDATE_BOOLEAN);

		if(!is_bool($bool))
		{
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided value is a valid float
	 *
	 * Usage: '<index>' => 'float'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_float($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if(!filter_var($input[$field], FILTER_VALIDATE_FLOAT))
		{
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided value is a valid URL
	 *
	 * Usage: '<index>' => 'valid_url'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_valid_url($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if(!filter_var($input[$field], FILTER_VALIDATE_URL))
		{
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if a URL exists & is accessible
	 *
	 * Usage: '<index>' => 'url_exists'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_url_exists($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}
		
		$url = parse_url(strtolower($input[$field]));
		
		if(isset($url['host'])) {
			$url = $url['host'];
		}

		if(function_exists('checkdnsrr'))
		{
			if(checkdnsrr($url) === false)
			{
				return array(
					'field' => $field,
					'value' => $input[$field],
					'rule'  => __FUNCTION__,
					'param' => $param
				);
			}
		}
		else
		{
			if(gethostbyname($url) == $url)
			{
				return array(
					'field' => $field,
					'value' => $input[$field],
					'rule'  => __FUNCTION__,
					'param' => $param
				);
			}
		}
	}

	/**
	 * Determine if the provided value is a valid IP address
	 *
	 * Usage: '<index>' => 'valid_ip'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_valid_ip($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if(!filter_var($input[$field], FILTER_VALIDATE_IP) !== FALSE)
		{
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided value is a valid IPv4 address
	 *
	 * Usage: '<index>' => 'valid_ipv4'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 * @see http://pastebin.com/UvUPPYK0
	 */

	/*
	 * What about private networks? http://en.wikipedia.org/wiki/Private_network
	 * What about loop-back address? 127.0.0.1
	 */
	protected function validate_valid_ipv4($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if(!filter_var($input[$field], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) // removed !== FALSE
		{ // it passes
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided value is a valid IPv6 address
	 *
	 * Usage: '<index>' => 'valid_ipv6'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_valid_ipv6($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if(!filter_var($input[$field], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
		{
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the input is a valid credit card number
	 *
	 * See: http://stackoverflow.com/questions/174730/what-is-the-best-way-to-validate-a-credit-card-in-php
	 * Usage: '<index>' => 'valid_cc'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_valid_cc($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		$number = preg_replace('/\D/', '', $input[$field]);

		if(function_exists('mb_strlen'))
		{
			$number_length = mb_strlen($number);
		}
		else
		{
			$number_length = strlen($number);
		}

		$parity = $number_length % 2;

		$total = 0;

		for($i = 0; $i < $number_length; $i++)
		{
			$digit = $number[$i];

			if ($i % 2 == $parity)
			{
				$digit *= 2;

				if ($digit > 9)
				{
					$digit -= 9;
				}
			}

			$total += $digit;
		}

		if($total % 10 == 0)
		{
			return; // Valid
		}

		return array(
			'field' => $field,
			'value' => $input[$field],
			'rule'  => __FUNCTION__,
			'param' => $param
		);
	}

	/**
	 * Determine if the input is a valid human name [Credits to http://github.com/ben-s]
	 *
	 * See: https://github.com/Wixel/Validator/issues/5
	 * Usage: '<index>' => 'valid_name'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_valid_name($field, $input, $param = NULL)
	{
		if(!isset($input[$field])|| empty($input[$field]))
		{
			return;
		}

		if(!preg_match("/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïñðòóôõöùúûüýÿ '-])+$/i", $input[$field]) !== FALSE)
		{
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided input is likely to be a street address using weak detection
	 *
	 * Usage: '<index>' => 'street_address'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_street_address($field, $input, $param = NULL)
	{
		if(!isset($input[$field])|| empty($input[$field]))
		{
			return;
		}

		// Theory: 1 number, 1 or more spaces, 1 or more words
		$hasLetter = preg_match('/[a-zA-Z]/', $input[$field]);
		$hasDigit  = preg_match('/\d/'      , $input[$field]);
		$hasSpace  = preg_match('/\s/'      , $input[$field]);

		$passes = $hasLetter && $hasDigit && $hasSpace;

		if(!$passes) {
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided value is a valid IBAN
	 *
	 * Usage: '<index>' => 'iban'
	 *
	 * @access protected
	 * @param  string $field
	 * @param  array $input
	 * @return mixed
	 */
	protected function validate_iban($field, $input, $param = NULL)
	{
		if(!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		$character = array (
			'A' => 10, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15, 'G' => 16,
			'H' => 17, 'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21, 'M' => 22,
			'N' => 23, 'O' => 24, 'P' => 25, 'Q' => 26, 'R' => 27, 'S' => 28,
			'T' => 29, 'U' => 30, 'V' => 31, 'W' => 32, 'X' => 33, 'Y' => 34,
			'Z' => 35,
		);

		if (!preg_match("/\A[A-Z]{2}\d{2} ?[A-Z\d]{4}( ?\d{4}){1,} ?\d{1,4}\z/", $input[$field])) {
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}

		$iban = str_replace(' ', '', $input[$field]);
		$iban = substr($iban, 4) . substr($iban, 0, 4);
		$iban = strtr($iban, $character);

		if(bcmod($iban, 97) != 1){
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided input is a valid date (ISO 8601)
	 *
	 * Usage: '<index>' => 'date'
	 *
	 * @access protected
	 * @param string $field
	 * @param string $input date ('Y-m-d') or datetime ('Y-m-d H:i:s')
	 * @param null   $param
	 *
	 * @return mixed
	 */
	protected function validate_date($field, $input, $param = null)
	{
		if (!isset($input[$field]) || empty($input[$field])) {
			return;
		}

		$cdate1 = date('Y-m-d', strtotime($input[$field]));
		$cdate2 = date('Y-m-d H:i:s', strtotime($input[$field]));


		if ($cdate1 != $input[$field] && $cdate2 != $input[$field]) {
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule'  => __FUNCTION__,
				'param' => $param
			);
		}
	}

	/**
	 * Determine if the provided numeric value is lower or equal to a specific value
	 *
	 * Usage: '<index>' => 'max_numeric,50'
	 *
	 * @access protected
	 * @param string $field
	 * @param array  $input
	 * @param null   $param
	 *
	 * @return mixed
	 */
	protected function validate_max_numeric($field, $input, $param = null)
	{
		if (!isset($input[$field]) || empty($input[$field])) {
			return;
		}

		if (is_numeric($input[$field]) && is_numeric($param) && ($input[$field] <= $param)) {
			return;
		}

		return array(
			'field' => $field,
			'value' => $input[$field],
			'rule'  => __FUNCTION__,
			'param' => $param
		);
	}

	/**
	 * Determine if the provided numeric value is higher or equal to a specific value
	 *
	 * Usage: '<index>' => 'min_numeric,1'
	 *
	 * @access protected
	 * @param string $field
	 * @param array  $input
	 * @param null   $param
	 *
	 * @return mixed
	 */
	protected function validate_min_numeric($field, $input, $param = null)
	{
		if (!isset($input[$field]) || empty($input[$field])) {
			return;
		}

		if (is_numeric($input[$field]) && is_numeric($param) && ($input[$field] >= $param)) {
			return;
		}

		return array(
			'field' => $field,
			'value' => $input[$field],
			'rule'  => __FUNCTION__,
			'param' => $param
		);
	}

	/**
	 * Trims whitespace only when the value is a scalar
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	private function trimScalar($value)
	{
		if (is_scalar($value)) {
			$value = trim($value);
		}

		return $value;
	}

} // EOC
