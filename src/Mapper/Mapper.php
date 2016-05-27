<?php
namespace ScriptFUSION\Mapper;

use ScriptFUSION\Mapper\Strategy\Strategy;

/**
 * Maps records according to strategies, mappings and mapping fragments.
 */
class Mapper
{
    /**
     * @param \Iterator $records
     * @param Mapping|null $mapping
     * @param mixed $context
     *
     * @return \Generator
     *
     * @throws \Exception
     */
    public function mapRecords(\Iterator $records, Mapping $mapping = null, $context = null)
    {
        foreach ($records as $record) {
            if (!is_array($record)) {
                throw new \Exception('Record must be an array.'); // TODO: Specific exception type.
            }

            yield $mapping
                ? $this->mapMapping($record, $mapping, $context)
                : $record;
        }
    }

    /**
     * @param array $record
     * @param Strategy|Mapping|array|mixed $strategyOrMapping
     * @param mixed $context
     *
     * @return mixed
     * @throws \Exception
     */
    public function map(array $record, $strategyOrMapping, $context = null)
    {
        /* Strategy. */
        if ($strategyOrMapping instanceof Strategy) {
            return $this->mapStrategy($record, $strategyOrMapping, $context);
        } /* Mapping. */
        elseif ($strategyOrMapping instanceof Mapping) {
            return $this->mapMapping($record, $strategyOrMapping, $context);
        } /* Mapping fragment. */
        elseif (is_array($strategyOrMapping)) {
            return $this->mapFragment($record, $strategyOrMapping, $context);
        }

        // Pass unidentified object through.
        return $strategyOrMapping;
    }

    /**
     * @param array $record Record.
     * @param Mapping $mapping Mapping.
     * @param mixed $context Contextual data.
     *
     * @return array Mapped record.
     * 
     * @throws \Exception
     */
    private function mapMapping(array $record, Mapping $mapping, $context = null)
    {
        return $this->mapFragment($record, $mapping->getArrayCopy(), $context);
    }

    private function mapFragment(array $record, array $fragment, $context = null)
    {
        if (array_walk(
            $fragment,
            function (&$strategy, $key, array $record) use ($context) {
                $strategy = $this->map($record, $strategy, $context);
            },
            $record
        )) {
            return $fragment;
        }

        throw new \Exception; // TODO: Proper exception.
    }

    /**
     * @param array $record
     * @param Strategy $strategy
     * @param mixed $context
     *
     * @return mixed
     */
    private function mapStrategy(array $record, Strategy $strategy, $context = null)
    {
        $this->injectDependencies($strategy);

        return $strategy($record, $context);
    }

    /**
     * TODO: Move to dedicated class.
     *
     * @param object $object
     */
    protected function injectDependencies($object)
    {
        if ($object instanceof MapperAware) {
            $object->setMapper($this);
        }
    }
}
