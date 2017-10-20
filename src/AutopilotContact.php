<?php

namespace Autopilot;

use JsonSerializable;

class AutopilotContact implements JsonSerializable
{
    /**
     * All fields
     *
     * @var array
     */
    protected $fields;

    /**
     * List of ids that the user is a part of
     *
     * @var array
     */
    protected $lists;

    public function __construct(array $options = [])
    {
        $this->fields = [];

        $this->lists = [];

        $this->fill($options);
    }

    /**
     * Getter for contact properties
     *
     * @param $name
     *
     * @return string|null
     */
    public function __get($name)
    {
        return $this->getFieldValue($name);
    }

    /**
     * Setter for contact properties
     *
     * @param $name
     * @param $value
     *
     * @return string|null
     */
    public function __set($name, $value)
    {
        return $this->setFieldValue($name, $value);
    }

    /**
     * Check if contact property is set
     *
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->issetFieldValue($name);
    }

    /**
     * Unsetter for contact properties
     *
     * @param $name
     */
    public function __unset($name)
    {
        $this->unsetFieldValue($name);
    }

    /**
     * @param $name
     *
     * @return string|null
     */
    public function getFieldValue($name)
    {
        $name = AutopilotField::getFieldName($name);

        // no validation on "getValue()" required since set internally
        return isset($this->fields[$name]) ? $this->fields[$name]->getValue() : null;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return string|null
     * @throws AutopilotException
     */
    public function setFieldValue($name, $value)
    {
        $name = AutopilotField::getFieldName($name);

        /** @var AutopilotField $field */
        if (! isset($this->fields[$name])) {
            $field = new AutopilotField($name, $value);
            $this->fields[$name] = $field;
        } else {
            $this->fields[$name]->setValue($value);
        }

        return $this->fields[$name]->getValue();
    }

    /**
     * Remove field
     *
     * @param $name
     */
    public function unsetFieldValue($name)
    {
        $name = AutopilotField::getFieldName($name);

        unset($this->fields[$name]);
    }

    /**
     * Check if contact object contains field
     *
     * @param $name
     *
     * @return bool
     */
    public function issetFieldValue($name)
    {
        $name = AutopilotField::getFieldName($name);

        return isset($this->fields[$name]);
    }

    /**
     * Get all lists (cache, not an API call)
     *
     * @return array
     */
    public function getAllContactLists()
    {
        return $this->lists;
    }

    /**
     * Check if is member of list (cache, not API call)
     *
     * @param $list
     *
     * @return bool
     */
    public function hasList($list)
    {
        return in_array($list, $this->lists);
    }

    /**
     * For each item, add appropriate field with value
     *
     * @param array $options
     *
     * @return $this
     */
    public function fill(array $options = [])
    {
        foreach($options as $key => $value) {
            if ($key === 'custom_fields') {
                foreach($value as $custom) {
                    $field = new AutopilotField($custom['kind'], $custom['value'], $custom['fieldType']);
                    $this->fields[$field->getName()] = $field;
                }
            } elseif ($key === 'lists') {
                $this->lists = $value;
            } elseif (!is_array($value)) {
                $field = new AutopilotField($key, $value);
                $this->fields[$field->getName()] = $field;
            }
        }

        return $this;
    }

    /**
     * Prepare an array for the API call
     *
     * @param bool $prependKey
     *
     * @return array
     */
    public function toRequest($prependKey = true)
    {
        $result = [
            'custom' => []
        ];

        /** @var AutopilotField $field */
        foreach($this->fields as $field) {
            if (! $field->isReserved()) {
                $result['custom'][$field->formatName()] = $field->getValue();
            } else {
                $result[$field->formatName()] = $field->getValue();
            }
        }

        // if not custom values, remove unnecessary key
        if (sizeof($result['custom']) === 0) {
            unset($result['custom']);
        }

        return $prependKey ? ['contact' => $result] : $result;
    }

    /**
     * Return all fields and their values
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];

        /** @var AutopilotField $field */
        foreach($this->fields as $field) {
            $result[$field->getName()] = $field->getValue();
        }

        return $result;
    }

    /**
     * Return json of all fields and their values
     *
     * @return array
     */
    function jsonSerialize()
    {
        return $this->toArray();
    }
}
