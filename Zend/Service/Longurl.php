<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Longurl
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Search.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Http_Client
 */
require_once 'Zend/Rest/Client.php';

/**
 * @see Zend_Json
 */
require_once 'Zend/Json.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Longurl
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Service_Longurl extends Zend_Rest_Client
{
    /**
     * Response Type
     * @var String
     */
    protected $_responseType = 'xml';

    /**
     * Response Format Types
     * @var array
     */
    protected $_responseTypes = array(
        'json',
    	'php',
        'xml'
    );

    /**
     * Uri Component
     *
     * @var Zend_Uri_Http
     */
    protected $_uri;

    /**
     * Constructor
     *
     * @param string $responseType
     * @internal param string $returnType
     * @return Zend_Service_Longurl
     */
    public function __construct($responseType = 'xml')
    {
        $this->setResponseType($responseType);
        $this->setUri("http://api.longurl.org");
    }

    /**
     * set responseType
     *
     * @param string $responseType
     * @throws Zend_Service_Longurl_Exception
     * @return Zend_Service_Longurl
     */
    public function setResponseType($responseType = 'xml')
    {
        if(!in_array($responseType, $this->_responseTypes, TRUE)) {
            require_once 'Zend/Service/Longurl/Exception.php';
            throw new Zend_Service_Longurl_Exception('Invalid Response Type');
        }
        $this->_responseType = $responseType;
        return $this;
    }

    /**
     * Retrieve responseType
     *
     * @return string
     */
    public function getResponseType()
    {
        return $this->_responseType;
    }

    /**
     * Get the available services
     *
     * @param array $params
     * @throws Zend_Service_Longurl_Exception
     * @return mixed
     */
    public function services(array $params = array())
    {
    	$_query = array('format' => $this->_responseType);
    	$_responseType = $this->_responseType;

    	foreach($params as $key=>$param) {
            switch($key) {
                case 'format':
         		    if(!in_array($param, $this->_responseTypes, TRUE)) {
            			require_once 'Zend/Service/Longurl/Exception.php';
            			throw new Zend_Service_Longurl_Exception('Invalid Response Type');
        			}
        			$_responseType = $param;
            	case 'callback':
            		$_query[$key] = $param;
            	break;
            }
    	}
        $response = $this->restGet('/v2/services', $_query);

    	switch ($_responseType)
    	{
    		case 'json':
    			// check if we get back jsonp
    			if (isset($_query['callback']))
    			{
	    			return $response->getBody();
    			}
    			else
    			{    			
	        		return Zend_Json::decode($response->getBody());
    			}
	        	break;
    		case 'php':
    		case 'xml':
    			return $response->getBody();
    			break;
    	}
    }

    /**
     * Expand an url
     *
     * @param array $params
     * @throws Zend_Service_Longurl_Exception
     * @return mixed
     */
    public function expandUrl(array $params = array())
    {
    	$_query = array('format' => $this->_responseType);
    	$_responseType = $this->_responseType;

        foreach($params as $key=>$param) {
            switch($key) {
                case 'all-redirects':
                case 'content-type':
                case 'response-code':
                case 'title':
                case 'rel-canonical':
                case 'meta-keywords':
                case 'meta-description':
                	if (is_bool($param))
                	{
                		// 0 is default, so we don't need to set the parameter in that case
                		if ($param)
                		{
                			$_query[$key] = 1;
                		}
                	}
                	else 
                	{
						require_once 'Zend/Service/Longurl/Exception.php';
            			throw new Zend_Service_Longurl_Exception('Invalid Parameter setting');                		
                	}
                	break;
                case 'format':
         		    if(!in_array($param, $this->_responseTypes, TRUE)) {
            			require_once 'Zend/Service/Longurl/Exception.php';
            			throw new Zend_Service_Longurl_Exception('Invalid Response Type');
        			}
        			$_responseType = $param;
                case 'url':
        		case 'callback':
                		$_query[$key] = $param;
                	break;
            }
        }
    	$response = $this->restGet('/v2/expand', $_query);

    	switch ($_responseType)
    	{
    		case 'json':
    			// check if we get back jsonp
    			if (isset($_query['callback']))
    			{
	    			return $response->getBody();
    			}
    			else
    			{
	    			return Zend_Json::decode($response->getBody());
    			}
    			break;
	    	case 'php':
    		case 'xml':
    			return $response->getBody();
    			break;
    	}
    }
}
