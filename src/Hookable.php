<?php
namespace JRBarnard\Hookable;

/**
 * Trait Hookable
 * A simple trait that allows any class to register hooks (events) and run them
 * @package JRBarnard\Hookable
 */
trait Hookable
{
    /**
     * Where we store the closures / method references for the hooks
     * Like:
     * [
     *      'hook_name' => [
     *          99 => [ // priority
     *              callback, // multiple stored callbacks
     *              callback
     *          ],
     *      ]
     * ]
     * @var array
     */
    protected $hooks = [];

    /**
     * Call to register a hook
     * @param $hookName
     * @param callable $hookCallBack
     * @param int $priority
     */
    public function registerHook($hookName, Callable $hookCallBack, $priority = 0)
    {
        $this->addHook($hookName, $hookCallBack, $priority);
    }

    /**
     * Internal method to actually do the adding
     * @param $hookName
     * @param $hookCallBack
     * @param int $priority
     * @return $this
     */
    protected function addHook($hookName, $hookCallBack, $priority = 0)
    {
        $hookName = (array) $hookName;
        foreach ($hookName as $hookNameSingle) {
            $this->hooks[$hookNameSingle][(int) $priority][] = $hookCallBack;
            ksort($this->hooks[$hookNameSingle], SORT_NUMERIC);
        }

        return $this;
    }

    /**
     * Check whether a specific hook exists under the specified name / key
     * @param $hookName
     * @return bool
     */
    public function hasHook($hookName)
    {
        // We have something under the hook name
        if (!array_key_exists($hookName, $this->hooks)){
            return false;
        }

        // Under the hook name is an array
        if (!is_array($hooks = $this->hooks[$hookName])) {
            return false;
        }

        // The first value within the hook name array is also an array
        $firstHookArray = array_values($hooks)[0];
        if (!is_array($firstHookArray)) {
            return false;
        }

        // An item exists at offset 0 and is a valid callable callback
        if (!isset($firstHookArray[0]) || !is_callable($firstHookArray[0])) {
            return false;
        }

        return true;
    }

    /**
     * Get hooks for a specific hook name / key
     * @param $hookName
     * @return mixed
     */
    public function getHooks($hookName)
    {
        return $this->hooks[$hookName];
    }

    /**
     * Run hooks for a specific hook name, passing in hook arguments (any number of)
     * NB: All arguments are passed by reference so we can chose to alter the arguments within our callback,
     * hooks technically can return, but this should'nt be expected / relied on.
     * @param $hookName
     * @param array ...$hookArgs
     * @return mixed
     */
    public function runHook($hookName, &...$hookArgs)
    {
        if (!$this->hasHook($hookName)) {
            return false;
        }

        $hooks = $this->getHooks($hookName);

        // Loop over the priorities, then the hooks within.
        $result = null;
        foreach($hooks as $priority => $hook) {
            foreach($hook as $individualCallable) {
                $result = call_user_func_array($individualCallable, $hookArgs);
            }
        }

        return $result;
    }

    /**
     * Clear hooks, you can clear all hooks, or you can clear a specific hook by name
     * @param string|null $hookName
     * @return $this
     */
    public function clearHooks($hookName = null)
    {
        if (is_string($hookName) && array_key_exists($hookName, $this->hooks)) {
            unset($this->hooks[$hookName]);
            return $this;
        }

        $this->hooks = [];
        return $this;
    }
}