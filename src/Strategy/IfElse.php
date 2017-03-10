<?php
namespace ScriptFUSION\Mapper\Strategy;

use ScriptFUSION\Mapper\Mapping;

/**
 * Delegates to one expression or another depending on whether the specified condition strictly evaluates to true.
 */
class IfElse extends Delegate
{
    /** @var callable */
    private $condition;

    /** @var Strategy|Mapping|array|mixed */
    private $else;

    /**
     * Initializes this instance with the specified condition, the specified
     * expression to be resolved when condition is true and, optionally, the
     * specified expression to be resolved when condition is false.
     *
     * @param callable $condition Condition.
     * @param Strategy|Mapping|array|mixed $if Primary expression.
     * @param Strategy|Mapping|array|mixed|null $else Optional. Fallback expression.
     */
    public function __construct(callable $condition, $if, $else = null)
    {
        $this->condition = $condition;
        parent::__construct($if);
        $this->else = $else;
    }

    /**
     * Resolves the stored expression when the stored condition strictly
     * evaluates to true, otherwise resolve the stored fallback expression.
     *
     * @param mixed $data
     * @param mixed $context
     *
     * @return mixed
     */
    public function __invoke($data, $context = null)
    {
        if (call_user_func($this->condition, $data, $context) === true) {
            return parent::__invoke($data, $context);
        }

        return $this->delegate($this->else, $data, $context);
    }
}
