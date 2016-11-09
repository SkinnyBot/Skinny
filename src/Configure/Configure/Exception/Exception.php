<?php
namespace Bot\Configure\Configure\Exception;

class Exception extends \RuntimeException
{
    /**
     * Array of attributes that are passed in from the constructor, and
     * made available in the view when a development error is displayed.
     *
     * @var array
     */
    protected $_attributes = [];

    /**
     * Template string that has attributes sprintf()'ed into it.
     *
     * @var string
     */
    protected $_messageTemplate = '';

    /**
     * Array of headers to be passed to \Bot\Network\Response::header()
     *
     * @var array
     */
    protected $_responseHeaders = null;

    /**
     * Constructor.
     *
     * Allows you to create exceptions that are treated as framework errors and disabled
     * when debug = 0.
     *
     * @param string|array $message Either the string of the error message, or an array of attributes
     *   that are made available in the view, and sprintf()'d into Exception::$_messageTemplate
     * @param int $code The code of the error, is also the HTTP status code for the error.
     * @param \Exception $previous the previous exception.
     */
    public function __construct($message, $code = 500, $previous = null)
    {
        if (is_array($message)) {
            $this->_attributes = $message;
            $message = vsprintf($this->_messageTemplate, $message);
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
        return $this->_attributes;
    }

    /**
     * Get/set the response header to be used
     *
     * @param string|array|null $header An array of header strings or a single header string
     *  - an associative array of "header name" => "header value"
     *  - an array of string headers is also accepted
     * @param string $value The header value.
     *
     * @return array
     */
    public function responseHeader($header = null, $value = null)
    {
        if ($header === null) {
            return $this->_responseHeaders;
        }
        if (is_array($header)) {
            return $this->_responseHeaders = $header;
        }
        $this->_responseHeaders = [$header => $value];
    }
}
