<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Local\Status;

use EMS\ClientHelperBundle\Helper\Builder\BuilderDocumentInterface;

final class Status
{
    private string $name;
    private Items $items;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->items = new Items([]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function itemsAdded(): Items
    {
        return $this->items->filter(fn (Item $item): bool => $item->hasId());
    }

    public function itemsUpdated(): Items
    {
        return $this->items->filter(fn (Item $item): bool => !$item->dataIsEqual() && $item->hasAllData());
    }

    public function itemsDeleted(): Items
    {
        return $this->items->filter(fn (Item $item): bool => $item->hasDataLocal());
    }

    /**
     * @param BuilderDocumentInterface[] $documents
     */
    public function addBuilderDocuments(iterable $documents): void
    {
        foreach ($documents as $document) {
            $this->addItemOrigin(
                $document->getName(),
                $document->getContentType(),
                $document->getId(),
                $document->getDataSource(),
            );
        }
    }

    /**
     * @param array<mixed> $dataLocal
     */
    public function addItemLocal(string $key, string $contentType, array $dataLocal): void
    {
        if ($this->items->hasItem($key)) {
            $this->items->getItem($key)->setDataLocal($dataLocal);
        } else {
            $this->items->add(Item::fromLocal($key, $contentType, $dataLocal));
        }
    }

    /**
     * @param array<mixed> $dataOrigin
     */
    private function addItemOrigin(string $key, string $contentType, string $id, array $dataOrigin): void
    {
        if ($this->items->hasItem($key)) {
            $item = $this->items->getItem($key);
            $item->setDataOrigin($dataOrigin);
            $item->setId($id);
        } else {
            $this->items->add(Item::fromOrigin($key, $contentType, $id, $dataOrigin));
        }
    }
}
