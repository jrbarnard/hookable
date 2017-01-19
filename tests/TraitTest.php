<?php
use JRBarnard\Hookable\Hookable;

/**
 * Class HookableTraitTest
 */
class HookableTraitTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function can_register_same_callback_to_multiple_hook_names()
    {
        $testObject = new HookableTestClass();

        $runOne = false;
        $alteredNumber = 235629857;
        $testObject->registerHook([
            'testHookOne',
            'testHookTwo'
        ], function(&$number) use (&$runOne, $alteredNumber){
            $runOne = true;
            $number = $alteredNumber;
        });

        $originalNumberOne = 10;
        $originalNumberTwo = 12;
        $testObject->runHook('testHookOne', $originalNumberOne);
        $testObject->runHook('testHookTwo', $originalNumberTwo);

        $this->assertTrue($runOne, 'Hook one failed to run');
        $this->assertSame($alteredNumber, $originalNumberOne);
        $this->assertSame($alteredNumber, $originalNumberTwo);
    }

    /** @test */
    public function can_pass_array_object_class_method_as_callback_and_will_run()
    {
        $testObject = new HookableTestClass();

        $testObject->registerHook('testHook', [$testObject, 'testArraySyntaxClassMethodToRun']);

        $originalNumber = 10;
        $result = $testObject->runHook('testHook', $originalNumber);

        $this->assertNotEquals($originalNumber, $result);
        $this->assertSame($originalNumber + 10, $result);
    }

    /** @test */
    public function hookable_callback_can_be_function_name()
    {
        $testObject = new HookableTestClass();

        $testObject->registerHook('testHook', 'test_function_for_hookable');

        $originalNumber = 10;
        $result = $testObject->runHook('testHook', $originalNumber);

        $this->assertNotEquals($originalNumber, $result);
        $this->assertSame($originalNumber + 10, $result);
    }

    /** @test */
    public function all_hook_params_will_be_passed_by_reference()
    {
        $testObject = new HookableTestClass();

        $runOne = false;
        $alteredNumber = 50727502750;
        $testObject->registerHook('testHook', function(&$number) use (&$runOne, $alteredNumber){
            $runOne = true;
            $number = $alteredNumber;
        });

        $originalNumber = 10;
        $testObject->runHook('testHook', $originalNumber);

        $this->assertTrue($runOne, 'Hook one failed to run');
        $this->assertSame($alteredNumber, $originalNumber);
    }

    /** @test */
    public function registering_hooks_with_priorities_will_run_biggest_number_last()
    {
        $testObject = new HookableTestClass();

        $runOne = false;
        $testObject->registerHook('testHook', function(&$number) use (&$runOne){
            $runOne = true;
            return $number + 10;
        });

        $runTwo = false;
        $testObject->registerHook('testHook', function(&$number) use (&$runTwo){
            $runTwo = true;
            return $number + 100;
        }, 50);

        $runThree = false;
        $testObject->registerHook('testHook', function(&$number) use (&$runThree){
            $runThree = true;
            return $number + 1; // WHAT WE EXPECT TO SEE
        }, 99);

        $runFour = false;
        $testObject->registerHook('testHook', function(&$number) use (&$runFour){
            $runFour = true;
            return $number + 1000;
        }, 98);

        $runFive = false;
        $testObject->registerHook('testHook', function(&$number) use (&$runFive){
            $runFive = true;
            return $number + 9;
        });

        $runSix = false;
        $testObject->registerHook('testHook', function(&$number) use (&$runSix){
            $runSix = true;
            return $number + 19;
        }, 40);

        $variable = 15;

        $result = $testObject->runHook('testHook', $variable);

        $this->assertTrue($runOne, 'Hook one failed to run');
        $this->assertTrue($runTwo, 'Hook two failed to run');
        $this->assertTrue($runThree, 'Hook three failed to run');
        $this->assertTrue($runFour, 'Hook four failed to run');
        $this->assertTrue($runFive, 'Hook five failed to run');
        $this->assertTrue($runSix, 'Hook six failed to run');

        $this->assertSame(16, $result);
    }

    /** @test */
    public function hook_callback_can_accept_any_number_of_parameters()
    {
        $testObject = new HookableTestClass();
        $testObject->registerHook('testHook', function($one, $two, $three, $four){
            return $one + $two + $three + $four;
        });

        $variable1 = 1;
        $variable2 = 2;
        $variable3 = 3;
        $variable4 = 4;

        $sum = $testObject->runHook('testHook', $variable1,$variable2,$variable3,$variable4);

        $this->assertSame(10, $sum);

        $testObject->registerHook('testSelfHook', function($self){
            $self->test = 'hello';
        });

        $this->assertObjectNotHasAttribute('test', $testObject);

        $testObject->runHook('testSelfHook', $testObject);

        $this->assertObjectHasAttribute('test', $testObject);
        $this->assertSame('hello', $testObject->test);
    }

    /** @test */
    public function can_check_if_hook_exists()
    {
        $testObject = new HookableTestClass();

        $this->assertFalse($testObject->hasHook('testHook'));

        $testObject->registerHook('testHook', function(){
            return 'test';
        });

        $this->assertTrue($testObject->hasHook('testHook'));
    }

    /** @test */
    public function can_register_hook_within_extended_class()
    {
        $testObject = new HookableExtendedClass();
        $this->assertInstanceOf(HookableTestClass::class, $testObject);

        $result = $testObject->runningHookFromExtendedClassTestMethod();

        $this->assertSame('THIS WORKED', $result);
    }

    /** @test */
    public function can_register_a_hook_using_hook_register_method()
    {
        $testObject = new HookableTestClass();

        $testObject->registerHook('testHook', function(){
            return 'test';
        });
        $this->assertTrue($testObject->hasHook('testHook'));
    }

    /** @test */
    public function register_hook_returns_nothing()
    {
        $testObject = new HookableTestClass();

        $result = $testObject->registerHook('testHook', function(){
            return 'test';
        });

        $this->assertNull($result);
    }
}

/**
 * Class HookableTestClass
 */
class HookableTestClass
{
    use Hookable;

    /**
     * @param $variable
     * @return mixed
     */
    public function testArraySyntaxClassMethodToRun(&$variable)
    {
        return $variable + 10;
    }

    /**
     * @return mixed
     */
    public function runningHookFromExtendedClassTestMethod()
    {
        return $this->runHook('testing_hook_in_extends');
    }
}


/**
 * Class HookableExtendedClass
 */
class HookableExtendedClass extends HookableTestClass
{
    /**
     * HookableExtendedClass constructor.
     */
    public function __construct()
    {
        $this->registerHook('testing_hook_in_extends', function(){
            return 'THIS WORKED';
        });
    }
}


/**
 * For testing passing function name
 * @param $variable
 * @return mixed
 */
function test_function_for_hookable(&$variable)
{
    return $variable + 10;
}