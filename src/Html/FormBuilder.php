<?php
/*
 * Some of the codes are taken from illuminate/html & AdamWathan/form;
 */
namespace Tiga\Framework\Html;

class FormBuilder {

	/**
	 * The types of inputs to not fill values on by default.
	 *
	 * @var array
	 */
	protected $skipValueTypes = array('file', 'password', 'checkbox', 'radio');

	private $oldInput;

    public function __construct(OldInputInterface $oldInputProvider)
    {
        $this->oldInput = $oldInputProvider;
    }

 	public function setOldInputProvider(OldInputInterface $oldInputProvider)
    {
        $this->oldInput = $oldInputProvider;
    }
	/**
	 * Format the label value.
	 *
	 * @param  string  $name
	 * @param  string|null  $value
	 * @return string
	 */
	protected function formatLabel($name, $value)
	{
		return $value ?: ucwords(str_replace('_', ' ', $name));
	}

	/**
	 * Create a form label element.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $options
	 * @return string
	 */
	public function label($name, $value = null, $options = array())
	{
		$this->labels[] = $name;

		$options = $this->html->attributes($options);

		$value = $this->html->entities($this->formatLabel($name, $value));

		return '<label for="'.$name.'"'.$options.'>'.$value.'</label>';
	}


	/**
	 * Create a form input field.
	 *
	 * @param  string  $type
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $options
	 * @return string
	 */
	public function input($type, $name, $value = null, $options = array())
	{
		if ( ! isset($options['name'])) $options['name'] = $name;

		// We will get the appropriate value for the given field. We will look for the
		// value in the session for the value in the old input data then we'll look
		// in the model instance if one is set. Otherwise we will just use empty.
		$id = $this->getIdAttribute($name, $options);

		if ( ! in_array($type, $this->skipValueTypes))
		{
			$value = $this->getValueFor($name, $value);
		}

		// Once we have the type, value, and ID we can merge them into the rest of the
		// attributes array so we can convert them into their HTML attribute format
		// when creating the HTML element. Then, we will return the entire input.
		$merge = compact('type', 'value', 'id');

		$options = array_merge($options, $merge);

		return '<input'.$this->html->attributes($options).'>';
	}

	/**
	 * Get the ID attribute for a field name.
	 *
	 * @param  string  $name
	 * @param  array   $attributes
	 * @return string
	 */
	public function getIdAttribute($name, $attributes)
	{
		if (array_key_exists('id', $attributes))
		{
			return $attributes['id'];
		}

		if (in_array($name, $this->labels))
		{
			return $name;
		}
	}

    public function getValueFor($name,$value=null)
    {
        if ($this->hasOldInput()) {
            return $this->getOldInput($name);
        }

        if ($this->hasModelValue($name)) {
            return $this->getModelValue($name);
        }

        return $value;
    }

    protected function hasOldInput()
    {
        if (! isset($this->oldInput)) {
            return false;
        }

        return $this->oldInput->hasOldInput();
    }

    protected function getOldInput($name)
    {
        return $this->html->entities($this->oldInput->getOldInput($name));
    }

    protected function hasModelValue($name)
    {
        if (! isset($this->model)) {
            return false;
        }
        return isset($this->model->{$name}) || method_exists($this->model, '__get');
    }

    protected function getModelValue($name)
    {
        return $this->html->entities($this->model->{$name});
    }

    protected function unbindModel()
    {
        $this->model = null;
    }

    public function bind($model)
    {
        $this->model = is_array($model) ? (object) $model : $model;
    }

    /**
     * Form Component
     */
}