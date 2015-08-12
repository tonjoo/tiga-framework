<?php

/*
 * Code are taken from illuminate/html & AdamWathan/form;
 */

namespace Tiga\Framework\Html;

use Tiga\Framework\Contract\OldInputInterface;
use Tiga\Framework\Session\Session;
use Tiga\Framework\Model;

/**
 * Form builder.
 */
class FormBuilder
{
    /**
     * The types of inputs to not fill values on by default.
     *
     * @var array
     */
    protected $skipValueTypes = array('file', 'password', 'checkbox', 'radio','submit');

    /**
     * The name of inputs to not fill values on by default.
     *
     * @var array
     */
    protected $skipValueNames = array('_tiga_token');

    /**
     * The form methods that should be spoofed, in uppercase.
     *
     * @var array
     */
    protected $spoofedMethods = array('DELETE', 'PATCH', 'PUT');

    /**
     * An array of label names we've created.
     *
     * @var array
     */
    protected $labels = array();

    /**
     * Old Input.
     *
     * @var OldInputInterface
     */
    private $oldInput;

    /**
     * Reserved method.
     *
     * @var array
     */
    protected $reserved = array('method', 'files');

    /**
     * CSRF Token.
     *
     * @var string
     */
    protected $csrfToken = '';

    /**
     * Constructor.
     *
     * @param HtmlBuilder       $htmlBuilder
     * @param OldInputInterface $oldInputProvider
     * @param Session           $session
     *
     * @return FormBuilder
     */
    public function __construct(HtmlBuilder $htmlBuilder, OldInputInterface $oldInputProvider, Session $session)
    {
        $this->oldInput = $oldInputProvider;
        $this->html = $htmlBuilder;
        $this->session = $session;

        return $this;
    }

    /**
     * Get CSRF token.
     *
     * @return string
     */
    public function getToken()
    {
        if ($this->csrfToken != '') {
            return $this->csrfToken;
        }

        $this->csrfToken = wp_generate_password(64, false);

        $this->session->set('tiga_csrf_token', $this->csrfToken);

        return $this->csrfToken;
    }

    /**
     * Set input provider.
     *
     * @param OldInputInterface $oldInputProvider
     */
    public function setOldInputProvider(OldInputInterface $oldInputProvider)
    {
        $this->oldInput = $oldInputProvider;
    }

    /**
     * Format the label value.
     *
     * @param string      $name
     * @param string|null $value
     *
     * @return string
     */
    protected function formatLabel($name, $value)
    {
        return $value ?: ucwords(str_replace('_', ' ', $name));
    }

    /**
     * Get the ID attribute for a field name.
     *
     * @param string $name
     * @param array  $attributes
     *
     * @return string
     */
    public function getIdAttribute($name, $attributes)
    {
        if (array_key_exists('id', $attributes)) {
            return $attributes['id'];
        }

        if (in_array($name, $this->labels)) {
            return $name;
        }
    }

    /**
     * Get value from an attribute.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function getValueAttribute($name, $value = null)
    {
        if (strpos($name, '[]') !== false) {
            $name = str_replace('[]', '', $name);
        }

        if ($this->hasOldInput()) {
            return $this->getOldInput($name);
        }

        if ($this->hasModelValue($name)) {
            return $this->getModelValue($name);
        }

        return $value;
    }

    /**
     * Check if old input is present in flash.
     *
     * @return bool
     */
    protected function hasOldInput()
    {
        if (!isset($this->oldInput)) {
            return false;
        }

        return $this->oldInput->hasOldInput();
    }

    /**
     * Get old input by $name.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getOldInput($name)
    {
        return $this->html->entities($this->oldInput->getOldInput($name));
    }

    /**
     * Check if a model has a attribute with key $name.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function hasModelValue($name)
    {
        if (!isset($this->model)) {
            return false;
        }

        return isset($this->model->{$name});
    }

    /**
     * Get model value by $name key.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getModelValue($name)
    {
        return $this->html->entities($this->model->{$name});
    }

    /**
     * Unbind model from form.
     */
    protected function unbindModel()
    {
        $this->model = null;
    }

    /**
     * Bind model to form.
     *
     * @param object|\Tiga\Framework\Model $model
     */
    public function bind($model)
    {
        $this->model = $model;
    }

    /**
     * Bind model to form and open form tag.
     *
     * @param Model $model
     * @param array $options
     *
     * @return string
     */
    public function model($model, $options = array())
    {
        $this->model = $model;

        return $this->open($options);
    }

    /**
     * Parse the form action method. 
     * Always return GET or POST. If PUT,DELETE,PATCH is used, it will be appended in `_method`.
     *
     * @param string $method
     *
     * @return string
     */
    protected function getMethod($method)
    {
        $method = strtoupper($method);

        return $method != 'GET' ? 'POST' : $method;
    }

    /**
     * Open up a new HTML form.
     *
     * @param array $options. $options['action'] must be absolute path
     *
     * @return string
     */
    public function open(array $options = array())
    {
        $method = isset($options['method']) ? $options['method'] : 'post';

        // We need to extract the proper method from the attributes. If the method is
        // something other than GET or POST we'll use POST since we will spoof the
        // actual method since forms don't support the reserved methods in HTML.
        $attributes['method'] = $this->getMethod($method);

        $attributes['accept-charset'] = 'UTF-8';

        // If the method is PUT, PATCH or DELETE we will need to add a spoofer hidden
        // field that will instruct the Symfony request to pretend the method is a
        // different method than it actually is, for convenience from the forms.
        $append = $this->getAppendage($method);

        if (isset($options['files']) && $options['files']) {
            $options['enctype'] = 'multipart/form-data';
        }

        // Finally we're ready to create the final form HTML field. We will attribute
        // format the array of attributes. We will also add on the appendage which
        // is used to spoof requests for this PUT, PATCH, etc. methods on forms.

        foreach ($this->reserved as $reserved) {
            if (array_key_exists($reserved, $options)) {
                unset($options[$reserved]);
            }
        }

        $attributes = array_merge($attributes, $options);

        // Finally, we will concatenate all of the attributes into a single string so
        // we can build out the final form open statement. We'll also append on an
        // extra value for the hidden _method field if it's needed for the form.
        $attributes = $this->html->attributes($attributes);

        return '<form'.$attributes.'>'.$append;
    }

    /**
     * Close the current form.
     *
     * @return string
     */
    public function close()
    {
        $this->labels = array();

        $this->model = null;

        return '</form>';
    }

    /**
     * Create a form label element.
     *
     * @param string $name
     * @param string $value
     * @param array  $options
     *
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
     * @param string $type
     * @param string $name
     * @param string $value
     * @param array  $options
     *
     * @return string
     */
    public function input($type, $name, $value = null, $options = array())
    {
        if (!isset($options['name'])) {
            $options['name'] = $name;
        }

        // We will get the appropriate value for the given field. We will look for the
        // value in the session for the value in the old input data then we'll look
        // in the model instance if one is set. Otherwise we will just use empty.
        $id = $this->getIdAttribute($name, $options);

        if (!in_array($type, $this->skipValueTypes) && !in_array($name, $this->skipValueNames)) {
            $value = $this->getValueAttribute($name, $value);
        }

        // Once we have the type, value, and ID we can merge them into the rest of the
        // attributes array so we can convert them into their HTML attribute format
        // when creating the HTML element. Then, we will return the entire input.
        $merge = compact('type', 'value', 'id');

        $options = array_merge($options, $merge);

        return '<input'.$this->html->attributes($options).'>';
    }

    /**
     * Create a text input field.
     *
     * @param string $name
     * @param string $value
     * @param array  $options
     *
     * @return string
     */
    public function text($name, $value = null, $options = array())
    {
        return $this->input('text', $name, $value, $options);
    }

    /**
     * Create a number input field.
     *
     * @param string $name
     * @param string $value
     * @param array  $options
     *
     * @return string
     */
    public function number($name, $value = null, $options = array())
    {
        return $this->input('number', $name, $value, $options);
    }

    /**
     * Create a password input field.
     *
     * @param string $name
     * @param array  $options
     *
     * @return string
     */
    public function password($name, $options = array())
    {
        return $this->input('password', $name, '', $options);
    }

    /**
     * Create a hidden input field.
     *
     * @param string $name
     * @param string $value
     * @param array  $options
     *
     * @return string
     */
    public function hidden($name, $value = null, $options = array())
    {
        return $this->input('hidden', $name, $value, $options);
    }

    /**
     * Create an e-mail input field.
     *
     * @param string $name
     * @param string $value
     * @param array  $options
     *
     * @return string
     */
    public function email($name, $value = null, $options = array())
    {
        return $this->input('email', $name, $value, $options);
    }

    /**
     * Create a url input field.
     *
     * @param string $name
     * @param string $value
     * @param array  $options
     *
     * @return string
     */
    public function url($name, $value = null, $options = array())
    {
        return $this->input('url', $name, $value, $options);
    }

    /**
     * Create a file input field.
     *
     * @param string $name
     * @param array  $options
     *
     * @return string
     */
    public function file($name, $options = array())
    {
        return $this->input('file', $name, null, $options);
    }

    /**
     * Create a textarea input field.
     *
     * @param string $name
     * @param string $value
     * @param array  $options
     *
     * @return string
     */
    public function textarea($name, $value = null, $options = array())
    {
        if (!isset($options['name'])) {
            $options['name'] = $name;
        }

        // Next we will look for the rows and cols attributes, as each of these are put
        // on the textarea element definition. If they are not present, we will just
        // assume some sane default values for these attributes for the developer.
        $options = $this->setTextAreaSize($options);

        $options['id'] = $this->getIdAttribute($name, $options);

        $value = (string) $this->getValueAttribute($name, $value);

        unset($options['size']);

        // Next we will convert the attributes into a string form. Also we have removed
        // the size attribute, as it was merely a short-cut for the rows and cols on
        // the element. Then we'll create the final textarea elements HTML for us.
        $options = $this->html->attributes($options);

        return '<textarea'.$options.'>'.$this->html->entities($value).'</textarea>';
    }

    /**
     * Create a WordPress Editor.
     *
     * @param string $name
     * @param string $value
     * @param array  $options
     *
     * @return string
     */
    public function wpEditor($name, $value = null, $options = array())
    {
        ob_start();

        $value = $this->getValueAttribute($name, $value);

        $value = $this->html->decode($value);

        wp_editor($value, $name, $options = array());

        $wpEditor = ob_get_contents();

        ob_end_clean();

        return $wpEditor;
    }

    /**
     * Set the text area size on the attributes.
     *
     * @param array $options
     *
     * @return array
     */
    protected function setTextAreaSize($options)
    {
        if (isset($options['size'])) {
            return $this->setQuickTextAreaSize($options);
        }

        // If the "size" attribute was not specified, we will just look for the regular
        // columns and rows attributes, using sane defaults if these do not exist on
        // the attributes array. We'll then return this entire options array back.
        $cols = array_get($options, 'cols', 50);

        $rows = array_get($options, 'rows', 10);

        return array_merge($options, compact('cols', 'rows'));
    }

    /**
     * Set the text area size using the quick "size" attribute.
     *
     * @param array $options
     *
     * @return array
     */
    protected function setQuickTextAreaSize($options)
    {
        $segments = explode('x', $options['size']);

        return array_merge($options, array('cols' => $segments[0], 'rows' => $segments[1]));
    }

    /**
     * Create a select box field.
     *
     * @param string $name
     * @param array  $list
     * @param string $selected
     * @param array  $options
     *
     * @return string
     */
    public function select($name, $list = array(), $selected = null, $options = array())
    {
        // When building a select box the "value" attribute is really the selected one
        // so we will use that when checking the model or session for a value which
        // should provide a convenient method of re-populating the forms on post.
        $selected = $this->getValueAttribute($name, $selected);

        $options['id'] = $this->getIdAttribute($name, $options);

        if (!isset($options['name'])) {
            $options['name'] = $name;
        }

        // We will simply loop through the options and build an HTML value for each of
        // them until we have an array of HTML declarations. Then we will join them
        // all together into one single HTML element that can be put on the form.
        $html = array();

        foreach ($list as $value => $display) {
            $html[] = $this->getSelectOption($display, $value, $selected);
        }

        // Once we have all of this HTML, we can join this into a single element after
        // formatting the attributes into an HTML "attributes" string, then we will
        // build out a final select statement, which will contain all the values.
        $options = $this->html->attributes($options);

        $list = implode('', $html);

        return "<select{$options}>{$list}</select>";
    }

    /**
     * Create a select range field.
     *
     * @param string $name
     * @param string $begin
     * @param string $end
     * @param string $selected
     * @param array  $options
     *
     * @return string
     */
    public function selectRange($name, $begin, $end, $selected = null, $options = array())
    {
        $range = array_combine($range = range($begin, $end), $range);

        return $this->select($name, $range, $selected, $options);
    }

    /**
     * Create a select year field.
     *
     * @param string $name
     * @param string $begin
     * @param string $end
     * @param string $selected
     * @param array  $options
     *
     * @return string
     */
    public function selectYear()
    {
        return call_user_func_array(array($this, 'selectRange'), func_get_args());
    }

    /**
     * Create a select month field.
     *
     * @param string $name
     * @param string $selected
     * @param array  $options
     * @param string $format
     *
     * @return string
     */
    public function selectMonth($name, $selected = null, $options = array(), $format = '%B')
    {
        $months = array();

        foreach (range(1, 12) as $month) {
            $months[$month] = strftime($format, mktime(0, 0, 0, $month, 1));
        }

        return $this->select($name, $months, $selected, $options);
    }

    /**
     * Get the select option for the given value.
     *
     * @param string $display
     * @param string $value
     * @param string $selected
     *
     * @return string
     */
    public function getSelectOption($display, $value, $selected)
    {
        if (is_array($display)) {
            return $this->optionGroup($display, $value, $selected);
        }

        return $this->option($display, $value, $selected);
    }

    /**
     * Create an option group form element.
     *
     * @param array  $list
     * @param string $label
     * @param string $selected
     *
     * @return string
     */
    protected function optionGroup($list, $label, $selected)
    {
        $html = array();

        foreach ($list as $value => $display) {
            $html[] = $this->option($display, $value, $selected);
        }

        return '<optgroup label="'.$this->html->entities($label).'">'.implode('', $html).'</optgroup>';
    }

    /**
     * Create a select element option.
     *
     * @param string $display
     * @param string $value
     * @param string $selected
     *
     * @return string
     */
    protected function option($display, $value, $selected)
    {
        $selected = $this->getSelectedValue($value, $selected);

        $options = array('value' => $this->html->entities($value), 'selected' => $selected);

        return '<option'.$this->html->attributes($options).'>'.$this->html->entities($display).'</option>';
    }

    /**
     * Determine if the value is selected.
     *
     * @param string $value
     * @param string $selected
     *
     * @return string
     */
    protected function getSelectedValue($value, $selected)
    {
        if (is_array($selected)) {
            return in_array($value, $selected) ? 'selected' : null;
        }

        return ((string) $value == (string) $selected) ? 'selected' : null;
    }

    /**
     * Create a checkbox input field.
     *
     * @param string $name
     * @param mixed  $value
     * @param bool   $checked
     * @param array  $options
     *
     * @return string
     */
    public function checkbox($name, $value = 1, $checked = null, $options = array())
    {
        return $this->checkable('checkbox', $name, $value, $checked, $options);
    }

    /**
     * Create a radio button input field.
     *
     * @param string $name
     * @param mixed  $value
     * @param bool   $checked
     * @param array  $options
     *
     * @return string
     */
    public function radio($name, $value = null, $checked = null, $options = array())
    {
        if (is_null($value)) {
            $value = $name;
        }

        return $this->checkable('radio', $name, $value, $checked, $options);
    }

    /**
     * Create a checkable input field.
     *
     * @param string $type
     * @param string $name
     * @param mixed  $value
     * @param bool   $checked
     * @param array  $options
     *
     * @return string
     */
    protected function checkable($type, $name, $value, $checked, $options)
    {
        $checked = $this->getCheckedState($type, $name, $value, $checked);

        if ($checked) {
            $options['checked'] = 'checked';
        }

        return $this->input($type, $name, $value, $options);
    }

    /**
     * Get the check state for a checkable input.
     *
     * @param string $type
     * @param string $name
     * @param mixed  $value
     * @param bool   $checked
     *
     * @return bool
     */
    protected function getCheckedState($type, $name, $value, $checked)
    {
        switch ($type) {
            case 'checkbox':
                return $this->getCheckboxCheckedState($name, $value, $checked);

            case 'radio':
                return $this->getRadioCheckedState($name, $value, $checked);

            default:
                return $this->getValueAttribute($name) == $value;
        }
    }

    /**
     * Get the check state for a checkbox input.
     *
     * @param string $name
     * @param mixed  $value
     * @param bool   $checked
     *
     * @return bool
     */
    protected function getCheckboxCheckedState($name, $value, $checked)
    {
        $posted = $this->getValueAttribute($name, $checked);

        return is_array($posted) ? in_array($value, $posted) : (bool) $posted;
    }

    /**
     * Get the check state for a radio input.
     *
     * @param string $name
     * @param mixed  $value
     * @param bool   $checked
     *
     * @return bool
     */
    protected function getRadioCheckedState($name, $value, $checked)
    {
        return $this->getValueAttribute($name, $checked) == $value;
    }

    /**
     * Create a HTML reset input element.
     *
     * @param string $value
     * @param array  $attributes
     *
     * @return string
     */
    public function reset($value, $attributes = array())
    {
        return $this->input('reset', null, $value, $attributes);
    }

    /**
     * Create a HTML image input element.
     *
     * @param string $url
     * @param string $name
     * @param array  $attributes
     *
     * @return string
     */
    public function image($url, $name = null, $attributes = array())
    {
        $attributes['src'] = $this->url->asset($url);

        return $this->input('image', $name, null, $attributes);
    }

    /**
     * Create a submit button element.
     *
     * @param string $value
     * @param array  $options
     *
     * @return string
     */
    public function submit($value = null, $options = array())
    {
        return $this->input('submit', null, $value, $options);
    }

    /**
     * Create a button element.
     *
     * @param string $value
     * @param array  $options
     *
     * @return string
     */
    public function button($value = null, $options = array())
    {
        if (!array_key_exists('type', $options)) {
            $options['type'] = 'button';
        }

        return '<button'.$this->html->attributes($options).'>'.$value.'</button>';
    }

    /**
     * Get the form appendage for the given method.
     *
     * @param string $method
     *
     * @return string
     */
    protected function getAppendage($method)
    {
        list($method, $appendage) = array(strtoupper($method), '');

        // If the HTTP method is in this list of spoofed methods, we will attach the
        // method spoofer hidden input to the form. This allows us to use regular
        // form to initiate PUT and DELETE requests in addition to the typical.
        if (in_array($method, $this->spoofedMethods)) {
            $appendage .= $this->hidden('_method', $method);
        }

        // If the method is something other than GET we will go ahead and attach the
        // CSRF token to the form, as this can't hurt and is convenient to simply
        // always have available on every form the developers creates for them.
        if ($method != 'GET') {
            $appendage .= $this->token();
        }

        return $appendage;
    }

    /**
     * Generate a hidden field with the current CSRF token.
     *
     * @return string
     */
    public function token()
    {
        return $this->hidden('_tiga_token', $this->getToken());
    }
}
