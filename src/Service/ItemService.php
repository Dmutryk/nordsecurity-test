<?php

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class ItemService
{
    private $entityManager;
    /** @var ItemRepository $itemRepository */
    private $itemRepository;

    public function __construct(EntityManagerInterface $entityManager, ItemRepository $itemRepository)
    {
        $this->entityManager = $entityManager;
        $this->itemRepository = $itemRepository;
    }

    public function create(User $user, string $data): void
    {
        $item = new Item();
        $item->setUser($user);
        $item->setData($data);

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    public function update(Item $item, string $data): void
    {
        $item->setData($data);

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }
    
    public function getById(int $id): ?Item
    {
        return $this->itemRepository->findOneBy(['id' => $id]);
    }
} 