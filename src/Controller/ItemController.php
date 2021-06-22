<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Repository\ItemRepository;
use App\Service\ItemService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends AbstractController
{
    /**
     * @Route("/item", name="item_list", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function list(ItemRepository $itemRepository): JsonResponse
    {
        $items = $itemRepository->findBy(['user' => $this->getUser()]);

        $allItems = [];
        foreach ($items as $item) {
            $allItems[] = [
                'id' => $item->getId(),
                'data' => $item->getData(),
                'created_at' => $item->getCreatedAt(),
                'updated_at' => $item->getUpdatedAt(),
            ];
        }

        return $this->json($allItems);
    }

    /**
     * @Route("/item", name="item_create", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, ItemService $itemService)
    {
        $data = $request->get('data');

        if (empty($data)) {
            return $this->json(['error' => 'No data parameter']);
        }

        $itemService->create($this->getUser(), $data);

        return $this->json(['success' => true], Response::HTTP_OK);
    }

    /**
     * @Route("/item", name="item_update", methods={"PUT"})
     * @IsGranted("ROLE_USER")
     */
    public function update(Request $request, ItemService $itemService, ItemRepository $itemRepository)
    {
        $requestData = json_decode($request->getContent());
        if (is_null($requestData->id) OR is_null($requestData->data)) {
            return $this->json(['error' => 'You must provide item id and new data.'], Response::HTTP_BAD_REQUEST);
        }
        $itemId = $requestData->id;
        $data = $requestData->data;
        
        $item = $itemService->getById($itemId);

        if (is_null($item)) {
            return $this->json(
                ['error' => 'Item with id: ' . $itemId . ' doesn`t exist.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        
        if($item->getUser()->getId() !== $this->getUser()->getId() ) {
            return $this->json(['error' => 'You can not update other`s items.']);
        }

        $itemService->update($item, $data);

        return $this->json(['success' => true],Response::HTTP_OK);
    }

    /**
     * @Route("/item/{id}", name="items_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(Request $request, int $id)
    {
        if (empty($id)) {
            return $this->json(['error' => 'No data parameter'], Response::HTTP_BAD_REQUEST);
        }

        $item = $this->getDoctrine()->getRepository(Item::class)->find($id);

        if ($item === null) {
            return $this->json(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($item);
        $manager->flush();

        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}
