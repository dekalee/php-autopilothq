<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends \Codeception\Module
{

    /**
     * Assert instanceof against an object
     *
     * @param        $class
     * @param        $object
     * @param string $description
     */
    public function assertInstanceOf($class, $object, $description = '')
    {
        if (! is_object($object)) {
            throw new \PHPUnit_Framework_ExpectationFailedException($object . ' is a ' . gettype($object) . ' not an instance of ' . $class);
        }
        if (! $object instanceof $class) {
            throw new \PHPUnit_Framework_ExpectationFailedException(get_class($object) . ' is not an instance of ' . $class);
        }
    }

    /**
     * Assert exception class, message, and code
     *
     * @param $exception
     * @param $function
     */
    public function seeException($exception, $function)
    {
        try {
            $function();
            $this->assertTrue(false);
        } catch (\Exception $e) {
            // get exceptions name
            $thrownException = get_class($e);

            // PHPUnit_Framework exception is thrown from try "assertTrue"
            if ($thrownException == 'PHPUnit_Framework_ExpectationFailedException') {
                $this->assertTrue(false, 'Exception not found "' . $exception . '"');
            } else {
                $this->assertEquals(get_class($exception), $thrownException);
                $this->assertEquals($exception->getMessage(), $e->getMessage());
                $this->assertEquals($exception->getCode(), $e->getCode());

            }
        }
    }
}
