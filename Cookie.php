<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Http;

use DateTimeImmutable;

/**
 * Cookie 
 */
class Cookie 
{      
    /**
     * Delete cookie 
     * 
     * @param string $name
     * @param Psr\Http\Message\ResponseInterface|null $response
     * @param string $domain
     * @return Psr\Http\Message\ResponseInterface|boolean
    */
    public static function delete($name, $response = null, $domain = '')
    {        
        $cookie = \urlencode($name) . '=' . \urlencode('false') . '; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0; path=/; secure; httponly';
        if (\is_object($response) == true) {
            return $response->withAddedHeader('Set-Cookie', $cookie);          
        } 
        
        return \setcookie($name,'',\time() - 100,'/',$domain);        
    }

    /**
     * Add cookie 
     * 
     * @param string $name
     * @param string $value
     * @param Psr\Http\Message\ResponseInterface|null $response
     * @param integer $expire Minutes
     * @param string $domain
     * @param string|null $sameSite
     * @return Psr\Http\Message\ResponseInterface|boolean
     */
    public static function add($name, $value, $response = null, $expire = 360, $domain = '', $sameSite = null)
    {             
        $expires = \time() + ($expire * 60);     

        if (\is_object($response) == true) {
            $cookie = \urlencode($name) . '=' . \urlencode($value) . '; ' . 
                Self::getExpireParam($expire) . 
                '; ' . Self::getAgeParam($expire) . 
                '; path=/; secure; httponly';
            if (empty($sameSite) == false) {
                $cookie .= '; samesite=' . $sameSite;
            }                    
            $response = $response->withAddedHeader('Set-Cookie',$cookie);
            
            return $response;
        } 
    
        return setcookie($name,$value,$expires,'/',$domain);        
    }

    /**
     * Get cookie
     * 
     * @param string $name
     * @param Psr\Http\Message\ServerRequestInterface|null $request
     * @param mixed $default
     * @return mixed
     */
    public static function get($name, $request = null, $default = null)
    {
        $cookies = \is_object($request) ? $request->getCookieParams() : $_COOKIE;

        return $cookies[$name] ?? $default;
    }

    /**
     * Get max age cookie param
     *
     * @param integer $minutes
     * @return string
     */
    protected static function getAgeParam($minutes)
    {
        return 'Max-Age=' . ($minutes * 60);
    }

    /**
     * Get expires cookie param
     *
     * @param integer $minutes
     * @return void
     */
    protected static function getExpireParam($minutes)
    {
        $expire = new DateTimeImmutable('now + ' . $minutes . 'minutes');

        return 'expires=' . $expire->format(\DateTime::COOKIE);
    }
}
