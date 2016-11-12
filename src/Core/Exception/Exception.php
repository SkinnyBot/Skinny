<?php
namespace Skinny\Core\Exception;

class Exception extends \RuntimeException
{
    /**
     * Array of attributes that are passed in from the constructor, and
     * made available in the view when a development error is displayed.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Template string that has attributes sprintf()'ed into it.
     *
     * @var string
     */
    protected $messageTemplate = '';

    /**
     * Array of headers to be passed to \Skinny\Network\Response::header()
     *
     * @var array
     */
    protected $responseHeaders = null;

    /**
     * Constructor.
     *
     * Allows you to create exceptions that are treated as framework errors and disabled
     * when debug = 0.
     *
     * @param string|array $message Either the string of the error message, or an array of attributes
     *   that are made available in the view, and sprintf()'d into Exception::$_messageTemplate
     * @param int $code The code of the error, is also the HTTP status code for the error.
     *
     * @param \Exception $previous the previous exception.
     */
    public function __construct($message, $code = 500, $previous = null)
    {
        if (is_array($message)) {
            $this->attributes = $message;
            $message = vsprintf($this->messageTemplate, $message);
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the passed in attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
