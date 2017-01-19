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

***TODO***

---

## <a name=install>Installation</a> ##

***TODO***

---

## <a name=usage>Usage</a> ##

***TODO***

---

## <a name=contributing>Contributing</a> ##

***TODO***

---

## <a name=license>License</a> ##

[MIT License](LICENSE)
