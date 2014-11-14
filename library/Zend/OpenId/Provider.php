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
 * @package    Zend_OpenId
 * @subpackage Zend_OpenId_Provider
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Provider.php 24593 2012-01-05 20:35:02Z matthew $
 */

/**
 * @see Zend_OpenId
 */
require_once "Zend/OpenId.php";

/**
 * @see Zend_OpenId_Extension
 */
require_once "Zend/OpenId/Extension.php";

/**
 * OpenID provider (server) implementation
 *
 * @category   Zend
 * @package    Zend_OpenId
 * @subpackage Zend_OpenId_Provider
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_OpenId_Provider
{

    /**
     * Reference to an implementation of storage object
     *
     * @var Zend_OpenId_Provider_Storage $_storage
     */
    private $_storage;

    /**
     * Reference to an implementation of user object
     *
     * @var Zend_OpenId_Provider_User $_user
     */
    private $_user;

    /**
     * Time to live of association session in secconds
     *
     * @var integer $_sessionTtl
     */
    private $_sessionTtl;

    /**
     * URL to peform interactive user login
     *
     * @var string $_loginUrl
     */
    private $_loginUrl;

    /**
     * URL to peform interactive validation of consumer by user
     *
     * @var string $_trustUrl
     */
    private $_trustUrl;

    /**
     * The OP Endpoint URL
     *
     * @var string $_opEndpoint
     */
    private $_opEndpoint;

    /**
     * Constructs a Zend_OpenId_Provider object with given parameters.
     *
     * @param string $loginUrl is an URL that provides login screen for
     *  end-user (by default it is the same URL with additional GET variable
     *  openid.action=login)
     * @param string $trustUrl is an URL that shows a question if end-user
     *  trust to given consumer (by default it is the same URL with additional
     *  GET variable openid.action=trust)
     * @param Zend_OpenId_Provider_User $user is an object for communication
     *  with User-Agent and store information about logged-in user (it is a
     *  Zend_OpenId_Provider_User_Session object by default)
     * @param Zend_OpenId_Provider_Storage $storage is 