# Hookable #

[![Build Status](https://travis-ci.org/jrbarnard/hookable.svg?branch=master)](https://travis-ci.org/jrbarnard/hookable)
[![StyleCI](https://styleci.io/repos/79446774/shield?branch=master)](https://styleci.io/repos/79446774)

Hookable is a trait that can be used by any PHP class, it's purpose is to provide a simple API for creating hookable
'points' within your methods and allowing you to 'hook' into those points when using the class externally or from an 
extending child.

The purpose for this is to be able to quickly add event like functionality during code execution with a very
flexible API.

E.g:
```php
namespace App;

use JRBarnard\Hookable\Hookable;

class ToBeHooked
{
    use Hookable;

    public function methodToBeHooked()
    {
        $data = ['item', 'another_item'];

        $someOtherVariable = 'Hello';

        // We run a hook by key and pass through some data
        $this->runHook('hook_key', $data, $someOtherVariable);
        
        // Because we pass by reference, this variable may have been changed
        return $someOtherVariable;
    }
}

$toBeHooked = new ToBeHooked();

// Before we have registered the hook
$return = $toBeHooked->methodToBeHooked();
// $return === 'Hello';

// Registering a hook on the key is as simple as specifying the key and the relevant callback.
$toBeHooked->registerHook('hook_key', function($data, &$someOtherVariable){
    if ($someOtherVariable === 'Hello') {
        $someOtherVariable = 'world';        
    }
});

// After registering the hook
$return = $toBeHooked->methodToBeHooked();
// $return === 'world';
```

## Contents ##

1. [Requirements](#requirements)
2. [Installation](#install)
3. [Usage](#usage)
4. [Contributing](#contributing)
5. [License](#license)

## <a name=requirements>Requirements</a> ##

Hookables minimum PHP requirement is 5.6, this is because of it's use of 
[variadic functions](http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list).
Beyond that, there shouldn't be any other requirements, if you find any, please log as a ticket / do a pull request.

Currently HHVM / Hack is not supported.

## <a name=install>Installation</a> ##

Composer is the recommended installation method:
```
composer require jrbarnard/hookable
```

However you can also download this repo, unzip it and include it in your project.


## <a name=usage>Usage</a> ##

### Registering and running hooks ###

One of the main use cases for this Trait is when you have a very generic abstract / parent class that is extended a lot, and
sometimes you want to override / run actions based on the generic methods, without overriding the whole method.

An example of this is below:

```php
use JRBarnard\Hookable\Hookable;

abstract class SomeClass
{
    use Hookable;

    public function store(array $data) 
    {
        $model = new Model();
        $model->fill($data);
        $result = $model->save($result);
        
        $this->runHook('after_store', $data, $model);
        
        return $result;
    }
}
```

```php
class ChildClass extends SomeClass
{
    public function __construct()
    {
        $this->registerHook('after_store', function(array $data, Model $model){
            if ($model->isSpecial()) {
                // Do some sort of specific action for this child.
                // Perhaps send an email, log something etc.
            }
        });
        
        // Same hook, will still run, but this will run after, unless we specify priorities.
        $this->registerHook('after_store', function(array $data, Model $model){
            if ($model->isSpecial()) {
                // Do some sort of other action for this child.
            }
        });
    }
}
```

You can also register hooks externally to the object:
```php
class SomeClass
{
    use Hookable;
}

$someObject = new SomeClass();
$someObject->registerHook('some_hook', function(){ // Do something })

// You can also run hooks externally too (useful for testing, not sure if useful elsewhere)
$someObject->runHook('some_hook');
```

You don't need to pass a closure directly, you can also reference a specific function or class method using array syntax:
```php
// Standalone function
function someFunction(){
    // Do something
}

// Within a hookable classes constructor
$this->registerHook('hook_key', ['someFunction']);

// Class method
class SomeClass {
    use Hookable;
    
    public function someMethod(){
        // Do something
    }
}
$someObject = new SomeClass();

// Within a hookable classes constructor
$this->registerHook('hook_key', ['someMethod', $someObject]);
```

All parameters passed when running a hook are passed by reference by default, this allows us to (if we want to alter
passed parameters that aren't objects):
```php
class SomeClass {
    use Hookable;
    
    public function someMethod($array) {
        $this->runHook('hook_key', $array);
        
        return $array;
    }
}
$someObject = new SomeClass();

// Calling someMethod now will return the standard array
$array = $someObject->someMethod(['test']);
// $array will equal ['test']

$someObject->registerHook('hook_key', function(&$array){
    $array = [];
});

// Calling someMethod now will return the altered array as we have a hook
$array = $someObject->someMethod(['test']);
// $array will equal [];
```

You can use any number of parameters you want with runHook and they will be unpacked and available as parameters on the
callbacks you specify with registerHook.

### Priorities ###

Hookable also has priorities built in, when you register a hook you can pass a third parameter after the callback which
will be the priority of the callback, this defines where it's added in the calling order, the higher the number the later
in the calling order it's run:
```php
class SomeClass {
    use Hookable;
}
$someObject = new SomeClass();

$someObject->registerHook('test_priority', function(){ echo 'this will appear last'; }, 99);
$someObject->registerHook('test_priority', function(){ echo 'this will appear first'; }, 1);
$someObject->registerHook('test_priority', function(){ echo 'this will appear in the middle'; }, 50);

// We would expect the output:
// this will appear first
// this will appear in the middle
// this will appear last
```

If you set two hooks with the same key and the same priority, it will use when they were registered as the order, it 
won't override the already set one.

### Checking and removing hooks ###

You can check to see if a hook exists by calling hasHook on the relevant hookable object:
```php
class SomeClass {
    use Hookable;
}
$someObject = new SomeClass();

// Will be false
$someObject->hasHook('test_has_hook');

$someObject->registerHook('test_has_hook', function(){ // Do something });
// Will be true
$someObject->hasHook('test_has_hook');
```

If you want to remove hooks for an object you can do so with the clearHooks method:
```php
class SomeClass {
    use Hookable;
}
$someObject = new SomeClass();
$someObject->registerHook('hook_one', function(){ // Do something });
$someObject->registerHook('hook_two', function(){ // Do something });

// This will remove all hooks registered to the name hook_one
$someObject->clearHooks('hook_one');

// This will remove all hooks from the object, under all names
$someObject->clearHooks();
```

## <a name=contributing>Contributing</a> ##

[Please look at the contributing file](CONTRIBUTING.md)


## <a name=license>License</a> ##

[MIT License](LICENSE)
