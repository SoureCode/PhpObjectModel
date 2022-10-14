<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\ValueObject;

class RelativeNamespaceName extends AbstractNamespace
{
    private AbstractNamespaceName $from;

    private AbstractNamespaceName $to;

    public function __construct(AbstractNamespaceName $from, AbstractNamespaceName $to)
    {
        $this->from = $from;
        $this->to = $to;

        parent::__construct($this->calculateRelativeNamespace());
    }

    private function calculateRelativeNamespace(): string
    {
        $fromItems = $this->from->parts;
        $toItems = $this->to->parts;

        while (count($fromItems) > 0 && count($toItems) > 0 && $fromItems[0] === $toItems[0]) {
            array_shift($fromItems);
            array_shift($toItems);
        }

        return implode('\\', $toItems);
    }

    public function getFrom(): AbstractNamespaceName
    {
        return $this->from;
    }

    public function getTo(): AbstractNamespaceName
    {
        return $this->to;
    }
}
