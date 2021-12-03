<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Http;

use Psr\Http\Message\ResponseInterface;

use Arikaim\Core\Utils\Utils;
use Closure;

/**
 * Api Respnse support JSON format only.
*/
class ApiResponse 
{
    /**
     * response result
     *
     * @var array
     */
    protected $result;

    /**
     * Errors list
     *
     * @var array
     */
    protected $errors; 

    /**
     * pretty format json 
     *
     * @var bool
     */
    protected $prettyFormat;

    /**
     * Raw json response
     *
     * @var boolean
     */
    protected $raw;

    /**
     * Request response object
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Constructor
     *
     * @param ResponseInterface|null $response
     */
    public function __construct(?ResponseInterface $response = null) 
    {                    
        $this->errors = [];
        $this->result = [
            'result' => null,
            'status' => 'ok',
            'code'   => 200,
            'errors' => $this->errors
        ];
        $this->prettyFormat = false; 
        $this->raw = false; 
        $this->setClientResponse($response);
    }

    /**
     * Set json pretty format to true
     *
     * @return ApiResponse
     */
    public function useJsonPrettyformat()
    {
        $this->prettyFormat = true;

        return $this;
    }

    /**
     * Add errors
     *
     * @param array $errors
     * @return void
     */
    public function addErrors(array $errors): void
    {      
        $this->errors = \array_merge($this->errors,$errors);       
    }

    /**
     * Set errors 
     *
     * @param array $errors
     * @return void
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * Clear all errors.
     *
     * @return void
    */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Set error message
     *
     * @param string $errorMessage
     * @param boolean $condition
     * @return void
     */
    public function setError(string $errorMessage, bool $condition = true): void 
    {
        if ($condition !== false) {
            \array_push($this->errors,$errorMessage);  
        }               
    }

    /**
     * Set error message
     *
     * @param string $errorMessage
     * @param boolean $condition
     * @return ApiResponse
     */
    public function withError(string $errorMessage, bool $condition = true) 
    {
        $this->setError($errorMessage,$condition);

        return $this;
    }

    /**
     * Set response result
     *
     * @param mixed $data
     * @return ApiResponse
     */
    public function setResult($data) 
    {
        $this->result['result'] = $data;   

        return $this;
    }

    /**
     * Get result
     *
     * @return mixed
     */
    public function getResult() 
    {
        return $this->result['result'] ?? null;
    }

    /**
     * Set response 
     *
     * @param boolean $condition
     * @param array|string|Closure $data
     * @param string|Closure $error
     * @return mixed
     */
    public function setResponse(bool $condition, $data, $error)
    {
        if ($condition !== false) {
            if (\is_callable($data) == true) {
                return $data();
            } 
            if (\is_array($data) == true) {
                return $this->setResult($data);
            }
            if (\is_string($data) == true) {
                return $this->message($data);
            }
        } else {
            return (\is_callable($error) == true) ? $error() : $this->setError($error);          
        }
    }

    /**
     * Set result message
     *
     * @param string $message
     * @return ApiResponse
     */
    public function message(string $message)
    {
        return $this->field('message',$message);       
    }

    /**
     * Set field to result array 
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setResultField(string $name, $value): void
    {      
        $this->result['result'][$name] = $value;
    }

    /**
     * Set result field 
     *
     * @param string $name
     * @param mixed $value
     * @return ApiResponse
     */
    public function field(string $name, $value)
    {
        $this->setResultField($name,$value);

        return $this;
    }

    /**
     * Get field
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getField(string $name, $default = null)
    {
        return $this->result['result'][$name] ?? $default;
    }

    /**
     * Return errors count
     *
     * @return int
     */
    public function getErrorCount(): int
    {
        return \count($this->errors);
    }

    /**
     * Return true if response have error
     *
     * @return boolean
     */
    public function hasError(): bool 
    {    
        return (count($this->errors) > 0);         
    }

    /**
     * Return request response
     *     
     * @param boolean $raw
     *  
     * @return ResponseInterface
     */
    public function getResponse(bool $raw = false) 
    {           
        $this->raw = $raw;
        $json = $this->getResponseJson();
        $this->response->getBody()->write($json);

        return $this->response->withStatus($this->result['code'])->withHeader('Content-Type','application/json');           
    }

    /**
     * Return json 
     *
     * @return string
     */
    public function getResponseJson(): string
    {
        $this->result = \array_merge($this->result,[
            'errors'         => $this->errors,
            'execution_time' => (\microtime(true) - (\defined('APP_START_TIME') ? APP_START_TIME : $GLOBALS['APP_START_TIME'] ?? 0)),
            'status'         => ($this->hasError() == true) ? 'error' : 'ok',
            'code'           => ($this->hasError() == true) ? 400 : 200           
        ]);
        $result = ($this->raw == true) ? $this->result['result'] : $this->result;
    
        return ($this->prettyFormat == true) ? Utils::jsonEncode($result) : \json_encode($result,true);      
    }    

    /**
     * Set client response
     *
     * @param ResponseInterface|null $response
     * @return void
     */
    public function setClientResponse(?ResponseInterface $response = null): void
    {
        $this->response = $response;

        if (\is_object($response) == true) {          
            $json = $this->response->getBody()->getContents();
            $data = \json_decode($json,true);
            $this->result['code'] = $response->getStatusCode(); // 200 - ok
            $this->errors = $data['errors'] ?? [];         
            $this->result['result'] = $data['result'] ?? [];               
        }
    }
}
