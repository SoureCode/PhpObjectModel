<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\ValueObject;

use PhpParser\Node;

class AbstractNamespaceName extends AbstractNamespace
{
    public function getLongestCommonNamespace(AbstractNamespaceName $namespaceB): ?NamespaceName
    {
        $partsA = $this->parts;
        $partsB = $namespaceB->parts;

        $commonParts = [];

        while (count($partsA) > 0 && count($partsB) > 0 && $partsA[0] === $partsB[0]) {
            $commonParts[] = array_shift($partsA);
            array_shift($partsB);
        }

        if (0 === count($commonParts)) {
            return null;
        }

        return new NamespaceName(implode('\\', $commonParts));
    }

    public function getNamespaceRelativeTo(AbstractNamespaceName $namespace): RelativeNamespaceName
    {
        return new RelativeNamespaceName($this, $namespace);
    }

    public function toNode(): Node\Name
    {
        return new Node\Name($this->getShortName(), [
            'resolvedName' => new Node\Name\FullyQualified($this->getName()),
        ]);
    }

    public function toFqcnNode(): Node\Name
    {
        return new Node\Name($this->getName());
    }
}
