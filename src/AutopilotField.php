<?php

namespace Autopilot;

use Exception;
use Autopilot\Exceptions\AutopilotException;

class AutopilotField
{
    /**
     * Field name
     *
     * @var string
     */
    protected $name;

    /**
     * Field type
     *
     * @var null
     */
    protected $type;

    /**
     * Field value
     *
     * @var null
     */
    protected $value;

    /**
     * Autopilot read-only field
     *
     * @var bool
     */
    protected $isReadOnly = false;

    /**
     * Autopilot defined field name
     *
     * @var bool
     */
    protected $isReserved = false;

    /**
     * Allowed field types
     *
     * @var array
     */
    protected static $allowedTypes = [
        'boolean',
        'date',
        'float',
        'integer',
        'NULL',
        'string',
    ];

    /**
     * Fields that require specific type
     *
     * @var array
     */
    protected $casts = [
        'contact_id'            => 'readonly',
        'custom'                => 'readonly',
        'owner_name'            => 'readonly',
        'unsubscribed'          => 'boolean',
        '_autopilot_session_id' => 'readonly',
        '_autopilot_list'       => 'readonly',
    ];

    protected static $reservedFields = [
        'contact_id',
        'created_at',
        'updated_at',
        'Email',
        'Twitter',
        'FirstName',
        'LastName',
        'Salutation',
        'Company',
        'NumberOfEmployees',
        'Title',
        'Industry',
        'Phone',
        'MobilePhone',
        'Fax',
        'Website',
        'MailingStreet',
        'MailingCity',
        'MailingState',
        'MailingPostalCode',
        'MailingCountry',
        'owner_name',
        'LeadSource',
        'Status',
        'LinkedIn',
        'unsubscribed',
        'custom',
        '_autopilot_session_id',
        '_autopilot_list',
        '_NewEmail',
    ];

    /**
     * AutopilotField constructor.
     *
     * Check name against Autopilot defined fields, and set value type
     *
     * @param      $name
     * @param      $value
     * @param null $type
     */
    public function __construct($name, $value, $type = null)
    {
        // if parsing an "autopilot" name ($type--$name)
        $parts = explode('--', $name);
        if (sizeof($parts) > 1) {
            $this->checkType($parts[0]);
            $this->type = $parts[0];
            array_shift($parts);
            // save raw name in order to not lose reference to it
            $this->name = implode(' ', $parts);
        } else {
            $this->name = self::getFieldName($name);
        }

        $cast = isset($this->casts[$this->name]) ? $this->casts[$this->name] : null;

        // since name cannot be changed, determine "readonly" and "reserved" in constructor
        $this->isReadOnly = $cast === 'readonly';
        $this->isReserved = in_array($this->name, self::$reservedFields);

        if (is_null($this->type)) {
            $this->setTypeByValue($type, $value, $cast);
        }

        $this->value = $this->setValue($value);
    }

    /**
     * Use type, value, and cast to auto-assign field type
     *
     * @param      $type
     * @param      $value
     * @param null $cast
     *
     * @return null|string
     */
    protected function setTypeByValue($type, $value, $cast = null)
    {
        // determine type before setting initial value
        if (! is_null($cast) && $cast !== 'readonly') {
            $this->type = $cast;
        } elseif(is_null($type)){
            $this->type = $this->getTypeByValue($value);
        } else {
            $this->type = $type;
        }

        return $this->type;
    }

    /**
     * Check if autopilot defined or custom field
     *
     * @return bool
     */
    public function isReserved()
    {
        return $this->isReserved;
    }

    /**
     * Check if autopilot read-only field
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->isReadOnly;
    }

    /**
     * Return name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get field type
     *
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get field value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get type of value, check against current type and set value if matches
     *
     * @param $value
     *
     * @return null
     * @throws AutopilotException
     */
    public function setValue($value)
    {
        if ($this->isReadOnly() && ! is_null($this->value)) {
            return null;
        }

        $type = $this->getTypeByValue($value);
        if (! in_array($type, self::$allowedTypes)) {
            throw AutopilotException::invalidAutopilotType($type);
        }

        // type of field is set in the constructor
        if (($this->getType() !== $type) && !is_null($value)) {
            throw AutopilotException::typeMismatch($this->getType(), $type);
        }

        return $this->value = $value;
    }

    /**
     * @param $name
     *
     * @return string
     */
    public static function getFieldName($name)
    {
        // check raw value against reserved
        if (in_array($name, self::$reservedFields)) {
            return $name;
        }

        // autopilot custom field (ex: integer--Age)
        $delimited = explode('--', $name);
        if (sizeof($delimited) > 1) {
            if (in_array($delimited[0], self::$allowedTypes)) {
                // remove "type" from array
                array_shift($delimited);

                return implode(' ', $delimited);
            } else {
                return implode(' ', $delimited);
            }
        }

        // autopilot naming convention (prevent studly from stripping spaces)
        $field = self::toStudlyCase($name);
        if (in_array($field, self::$reservedFields)) {
            return $field;
        }

        // try to force-match some fields (saves on "custom" fields)

        if ($field === 'Zip') {
            return 'MailingPostalCode';
        }

        if ($field === 'Mobile') {
            return 'MobilePhone';
        }

        if ($field === 'Site' || $field === 'Webpage' || $field === 'WebPage') {
            return 'Website';
        }

        // mailing info
        if (in_array('Mailing' . $field, self::$reservedFields)) {
            return 'Mailing' . $field;
        }

        return implode(' ', array_map(function($value) {
            return self::toStudlyCase($value);
        }, explode(' ', $name)));
    }

    /**
     * Extract type from value
     *
     * @param $value
     *
     * @return string
     * @throws Exception
     */
    protected function getTypeByValue($value)
    {
        $type = gettype($value);
        if ($type === 'double') {
            return 'float';
        }

        // regex below throws up when value is an object or array
        if ($type === 'object' || $type === 'array') {
            throw new Exception('type "' . $type . '" is not a valid autopilot data type');
        }

        // datetime string
        $matches = [];
        $pattern = '/(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})(\s|\+\d{4}|\+\d{2}:\d{2}|Z)?/';
        preg_match($pattern, $value, $matches);
        if (sizeof($matches) > 0) {
            return 'date';
        }

        $this->checkType($type);

        return $type;
    }

    protected function checkType($type)
    {
        if (! in_array($type, self::$allowedTypes)) {
            throw new Exception('type "' . $type . '" is not a valid autopilot data type');
        }

        return true;
    }

    /**
     * Return formatted field (match autopilot defined or custom field names)
     *
     * @returns string
     */
    public function formatName()
    {
        return $this->isReserved() ? $this->name : $this->type . '--' . str_replace(' ', '--', $this->name);
    }

    /**
     * Convert string to StudlyCase
     *
     * Taken from: illuminate string helper
     *
     * @param $value
     *
     * @return mixed
     */
    protected static function toStudlyCase($value)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

}



