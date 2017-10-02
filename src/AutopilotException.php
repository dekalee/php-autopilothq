<?php

namespace Autopilot;

use Exception;

class AutopilotException extends Exception
{
    /**
     * GET, POST, DELETE
     *
     * @var string
     */
    protected $action;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var string
     */
    protected $response;

    /**
     * client vs server
     *
     * @var string
     */
    protected $type;

    /**
     * Constructor
     *
     * @param null $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, (int)$code, $previous);

        $this->parseResponseMessage($message);
    }

    /**
     * Update message
     *
     * NOTE: not sure if this is a good idea though
     *
     * @param $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get HTTP action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get reason (if exists)
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Update reason
     *
     * @param $reason
     *
     * @return mixed
     */
    public function setReason($reason)
    {
        return $this->reason = $reason;
    }

    /**
     * Get resource
     *
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get response from Autopilot
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get error type (server vs client)
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Extract and parse the JSON message in the Autopilot error response message
     *
     * @param $message
     */
    protected function parseJsonError($message)
    {
        $matches = [];
        preg_match('/{"(\s|\w|\n|:|\.|\,|")+"}/', $message, $matches);
        if (sizeof($matches) > 0) {
            $errors = json_decode($matches[0]);
            $this->message = $errors->message;
            if (isset($errors->error)) {
                $this->reason = $errors->error;
            } elseif (isset($errors->code)) {
                $this->reason = $errors->code;
            }
        } else {
            // if empty json, set message field to empty as well
            $this->message = '';
        }
    }

    /**
     * Extract API information from Autopilot error response message
     *
     * @param $message
     */
    protected function parseResponseMessage($message)
    {
        $path = explode('`', $message);
        if (sizeof($path) === 5) {
            if (strpos($path[0], 'Client') !== false) {
                $this->type = 'CLIENT';
            } elseif (strpos($path[0], 'Server') !== false) {
                $this->type = 'SERVER';
            }
            $resource = explode(' ', $path[1]);
            $this->action = $resource[0];
            $this->resource = $resource[1];
            $this->response = $path[3];

            $this->parseJsonError($path[4]);
        }
    }

    /**
     * Import exception into Autopilot Exception
     *
     * @param Exception $e
     *
     * @return static
     */
    public static function fromExisting(Exception $e)
    {
        return new static($e->getMessage(), $e->getCode(), $e->getPrevious());
    }

    /**
     * Could not find Autopilot API key in config
     *
     * @return static
     */
    public static function missingApiKey()
    {
        return new static('api key for autopilot is not defined');
    }

    /**
     * During a save, the new contact id doesn't match original contact id
     *
     * @param null $original
     * @param null $new
     *
     * @return static
     */
    public static function cannotOverwriteContactId($original = null, $new = null)
    {
        return new static('saved contact id is different from current contact id');
    }

    /**
     * During bulk uploads, the maximum number of contact uploads was reached
     *
     * @return static
     */
    public static function exceededContactsUploadLimit()
    {
        return new static('maximum contact upload is 100');
    }

    /**
     * An AutopilotContact object was expected. Something else was given
     *
     * @return static
     */
    public static function invalidContactType()
    {
        return new static('contacts must be of type "AutopilotContact"');
    }

    /**
     * Something failed during a bulk upload operation (message should have more details, but not required)
     *
     * @param null $message
     *
     * @return static
     */
    public static function contactsBulkSaveFailed($message = null)
    {
        return new static('contacts bulk upload failed' . (is_null($message) ? '' : ': ' . $message));
    }

    /**
     * Value type is not allowed by Autopilot
     *
     * @param null $type
     *
     * @return static
     */
    public static function invalidAutopilotType($type = null)
    {
        return new static((is_null($type) ? 'Invalid data type.' : '"' . $type . '" is not a valid Autopilot data type'));
    }

    /**
     * Value type does not match expected type
     *
     * @param      $expected
     * @param null $type
     *
     * @return static
     */
    public static function typeMismatch($expected, $type = null)
    {
        return new static('Type value mismatch! Expected: ' . $expected . (is_null($type) ? '' : ', got: '. $type));
    }

}
