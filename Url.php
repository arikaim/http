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

/**
 * Url helper
 */
class Url
{   
    const BASE_URL         = DOMAIN . BASE_PATH;  
    const VIEW_URL         = APP_URL . '/view';
    const EXTENSIONS_URL   = APP_URL . '/extensions';
    const LIBRARY_URL      = Self::VIEW_URL . '/library';
    const TEMPLATES_URL    = Self::VIEW_URL . '/templates';
    const COMPONENTS_URL   = Self::VIEW_URL . '/components';
  
    /**
     * Return url link with current language code
     *
     * @param string $path
     * @param boolean $relative
     * @param string|null $language
     * @param string|null $defaultLanguage
     * @return string
     */
    public static function getUrl($path = '', $relative = false, $language = null, $defaultLanguage = null)
    {       
        $defaultLanguage = $defaultLanguage ?? $language;
        $path = (\substr($path,0,1) == '/') ? \substr($path,1) : $path;      
        $url = ($relative == false) ? Url::BASE_URL : BASE_PATH;        
        $url = ($url == '/') ? $url : $url . '/';   
        $url .= $path;       
        if (empty($language) == true) {
            return $url;
        }
        
        return ($defaultLanguage != $language) ? Self::getLanguagePath($url,$language) : $url;
    }

    /**
     * Get language path
     *
     * @param string $path
     * @param string $language
     * @return string
     */
    public static function getLanguagePath($path, $language)
    {   
        return (\substr($path,-1) == '/') ? $path . $language . '/' : $path . '/' . $language . '/';
    }

    /**
     * Retrun true if url is relative
     *
     * @param string $url
     * @return boolean
     */
    public static function isRelative($url)
    {
        return (\strpos($url,DOMAIN) === false);
    }

    /**
     * Get url query param value
     *
     * @param string $url
     * @param string $paramName
     * @return string|null
     */
    public static function getUrlParam($url, $paramName)
    {
        $parts = \parse_url($url);
        \parse_str($parts['query'],$query);

        return $query[$paramName] ?? null;
    }

    /**
     * Return true if url is remote server
     *
     * @param string $url
     * @return boolean
     */
    public static function isRemote($url)
    {
        if (Self::isValid($url) == false) {
            return false;
        }
        $info = \parse_url($url);

        return ($info['hostname'] != DOMAIN);
    }

    /**
     * Init domain and base path constants
     *
     * @param string $domain
     * @param string $basePath
     * @return void
     */
    public static function init($domain,$basePath)
    {
        if (\defined('DOMAIN') == false) {
            \define('DOMAIN',$domain);
        }

        if (\defined('BASE_PATH') == false) {
            \define('BASE_PATH',$basePath);
        }
    }

    /**
     * Set app url
     *
     * @param string $path
     * @return void
     */
    public static function setAppUrl($path) 
    {
        if (\defined('APP_URL') == false) {
            \define('APP_URL',Self::BASE_URL . $path);
        }       
    }

    /**
     * Get theme file url
     *
     * @param string $template
     * @param string $theme
     * @param string $themeFile
     * @return string
     */
    public static function getThemeFileUrl($template, $theme, $themeFile)
    {
        return (empty($themeFile) == true) ? null : Self::getTemplateThemeUrl($template,$theme) . $themeFile;       
    }

    /**
     * Get template theme url
     *
     * @param string $template
     * @param string $theme
     * @return string
     */
    public static function getTemplateThemeUrl($template, $theme)
    {
        return Self::getTemplateThemesUrl($template) . '/' . $theme . '/';
    }

    /**
     * Get template url
     *
     * @param string $template
     * @param string $path
     * @return string
     */
    public static function getTemplateUrl($template, $path = '') 
    {       
        return Self::TEMPLATES_URL . '/' . $template . $path;       
    }

    /**
     * Get template themes url
     *
     * @param string $template
     * @return string
     */
    public static function getTemplateThemesUrl($template)
    {
        return Self::getTemplateUrl($template) . '/themes';
    }
    
    /**
     * Get UI library theme url
     *
     * @param string $library
     * @param string $theme
     * @return string
     */
    public static function getLibraryThemeUrl($library, $theme)
    {
        return Self::getLibraryUrl($library) . '/themes/' . $theme . '/';
    }

    /**
     * Get UI library theme file url
     *
     * @param string $library
     * @param string $file
     * @param string $theme
     * @return string
     */
    public static function getLibraryThemeFileUrl($library, $file, $theme)
    {
        return Self::getLibraryThemeUrl($library,$theme) . $file;
    }

    /**
     * Get UI library url
     *
     * @param string $library
     * @return string
     */
    public static function getLibraryUrl($library)
    {
        return Self::LIBRARY_URL . '/' . $library;
    }

    /**
     * Get UI library file url
     *
     * @param string $library
     * @param string|null $fileName
     * @param array|null $params
     * @return string
     */
    public static function getLibraryFileUrl($library, $fileName = '', $params = null)
    {
        $paramsText = (empty($params) == false) ? '?' . \http_build_query($params) : '';

        return Self::getLibraryUrl($library) . '/' . $fileName . $paramsText;
    }

    /**
     * Get extension view url
     *
     * @param string $extension
     * @param string $path
     * @return string
     */
    public static function getExtensionViewUrl($extension, $path = '')
    {
        return Self::EXTENSIONS_URL . '/' . $extension . '/view' . $path;
    }

    /**
     * Return true if url is valid
     *
     * @param string $url
     * @return boolean
     */
    public static function isValid($url)
    {
        return (\filter_var($url,FILTER_VALIDATE_URL) == true);
    }
}
