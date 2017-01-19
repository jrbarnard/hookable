# Hookable #

Hookable is a trait that can be used by any PHP class, it's purpose it provide a simple API for creating hookable 'points' within your methods and allowing you to 'hook' into those points when using the class externally or from an extending child.

E.g:
```
namespace App;

use JRBarnard\Hookable\Hookable;

class ToBeHooked
{
    use Hookable;

    public function methodToBeHooked()
    {
        $data = ['item', 'another_item'];

        $someOtherVariable = 'Hello';

        $this->runHook('hook_key', $data, $someOtherVariable);
        
        return $someOtherVariable;
    }
}

$toBeHooked = new ToBeHooked();

$toBeHooked->registerHook('hook_key', function($data, &$someOtherVariable){
    if ($someOtherVariable === 'Hello') {
        $someOtherVariable = 'world';        
    }
});

$return = $toBeHooked->methodToBeHooked();
// $return === 'world';
```

---

## Contents ##

1. [Requirements](#requirements)
2. [Installation](#install)
3. [Usage](#usage)
4. [Contributing](#contributing)
5. [License](#license)

---

## <a name=requirements>Requirements</a> ##

Hookables minimum PHP requirement is 5.6, this is because of it's use of [variadic functions](http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list).
Beyond that, there shouldn't be any other requirements, if you find any, please log as a ticket / do a pull request.

---

## <a name=install>Installation</a> ##

Composer is the recommended installation method:
```
composer require jrbarnard/hookable
```

However you can also download this repo, unzip it and include it in your project.

---

## <a name=usage>Usage</a> ##

***TODO***

---

## <a name=contributing>Contributing</a> ##

[Please look at the contributing file](CONTRIBUTING.md)

---

## <a name=license>License</a> ##

[MIT License](LICENSE)
