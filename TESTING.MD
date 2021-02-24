# Testing PLATINE FRAMEWORK

**PLATINE FRAMEWORK** uses [PHPUnit](http://phpunit.de/) for it's Unit Testing needs. It must be installed for the tests to run.

**NOTE: No code will be accepted without tests written.**

## Running Tests

Running the unit tests is as simple as navigating to the root folder and running in command line the following:

    $ vendor/bin/phpunit
    
    or
    
    $ composer test

That's it! You can also tell it specific groups (which we will get into in minute) to run. For example to run only some group of tests:

    $ vendor/bin/phpunit --group=core
    $ vendor/bin/phpunit --group=common
    $ vendor/bin/phpunit --group=core,database

## Writing Tests

### Where do they go?

All tests are to go in the **tests/Platine** folders.

### File/Class/Method Naming

The Test class names should be in the form of **RealClassNameTest**. 
The Test method names should be in the form of **testMethodNameWithHumanReadableDescription**.

### Test Grouping

All tests inside the **core** folder must be in the **core** group.  A classes test's should also be grouped together under the name of the class.

Here is an example of a core class test with proper DocBlocks:

    /**
     * Form class tests
     *
     * @group core
     * @group form
     */
    class FormTest extends PlatineTestCase {

    	/**
    	 * Tests Form::open()
    	 *
    	 */
    	public function testOpen() {
    		// Test code here
    	}
    }


### What class do I extend?

All tests should extend the **Platine\Test\PlatineTestCase** (**tests/PlatineTestCase.php**) class.

