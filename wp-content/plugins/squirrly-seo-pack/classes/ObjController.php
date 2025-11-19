<?php
defined('ABSPATH') || die('Cheatin\' uh?');

/**
 * The class creates object for plugin classes
 */
class SQP_Classes_ObjController
{

    /**
     * 
     *
     * @var array of instances 
     */
    public static $instances;

    /**
     * @param string $className
     * @param array $args
     * @return mixed|bool
     */
    public static function getClass($className, $args = array())
    {

        if ($class = self::getClassPath($className)) {
            if (!isset(self::$instances[$className])) {
                /* check if class is already defined */
                if (!class_exists($className)) {
                    try {
                        self::includeClass($class['dir'], $class['name']);

                        //check if the current class is abstract
                        $check = new ReflectionClass($className);
                        $abstract = $check->isAbstract();
                        if (!$abstract) {
                            self::$instances[$className] = new $className();
                            if (!empty($args)) {
                                call_user_func_array(array(self::$instances[$className], '__construct'), $args);
                            }
                            return self::$instances[$className];
                        } else {
                            self::$instances[$className] = true;
                        }
                    } catch (Exception $e) {
                    }
                }
            } else
                return self::$instances[$className];
        }
        return false;
    }

    /**
     * Get a new instance of the class
     *
     * @param string $className
     * @param array $args
     * @return bool|$className
     */
    public static function getNewClass($className, $args = array())
    {
       if(isset(self::$instances[$className])){
	       self::$instances[$className] = null;
       }

		return self::getClass($className, $args);
    }

    /**
     * @param string $classDir
     * @param string $className
     * @throws Exception
     */
    private static function includeClass($classDir, $className)
    {
        $file = $classDir . $className . '.php';
        try {
            if (file_exists($file)) {
                include_once $file;
            }
        } catch (Exception $e) {
            throw new Exception('Controller Error: ' . $e->getMessage());
        }
    }

    /**
     * @param string $className
     * @param array $args
     * @return stdClass
     */
    public static function getDomain($className, $args = array())
    {
        try {
            if ($class = self::getClassPath($className)) {
                self::includeClass($class['dir'], $class['name']);
                return new $className($args);
            }
        } catch (Exception $e) {

        }

        return new stdClass();
    }


    /**
     * Check if the class is correctly set
     *
     * @param string $className
     * @return boolean
     */
    private static function checkClassPath($className)
    {
        $path = preg_split('/[_]+/', $className);
        if (is_array($path) && count($path) > 1) {
            if (in_array(_SQP_NAMESPACE_, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the path of the class and name of the class
     *
     * @param string $className
     * @return array | boolean
     * array(
     * dir - absolute path of the class
     * name - the name of the file
     * )
     */
    public static function getClassPath($className)
    {
        $dir = '';

        if (self::checkClassPath($className)) {
            $path = preg_split('/[_]+/', $className);
            for ($i = 1; $i < sizeof($path) - 1; $i++)
                $dir .= strtolower($path[$i]) . '/';

            $class = array('dir' => _SQP_ROOT_DIR_ . $dir,
                'name' => $path[sizeof($path) - 1]);

            if (file_exists($class['dir'] . $class['name'] . '.php')) {
                return $class;
            }
        }
        return false;
    }

}
