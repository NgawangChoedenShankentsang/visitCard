<?php

namespace App\Controller;

use App\Entity\Card;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

#[Route('/api/cards')]
class CardApiController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(CardRepository $repo): JsonResponse
    {
        $cards = $repo->findAll();

        $data = array_map(fn(Card $card) => [
            'id' => $card->getId(),
            'vorname' => $card->getVorname(),
            'nachname' => $card->getNachname(),
            'email' => $card->getEmail(),
            'linkedinUrl' => $card->getLinkedinUrl(),
            'funktion' => method_exists($card, 'getFunktion') ? $card->getFunktion() : null,
        ], $cards);

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Card $card): JsonResponse
    {
        return $this->json([
            'id' => $card->getId(),
            'vorname' => $card->getVorname(),
            'nachname' => $card->getNachname(),
            'email' => $card->getEmail(),
            'linkedinUrl' => $card->getLinkedinUrl(),
            'funktion' => method_exists($card, 'getFunktion') ? $card->getFunktion() : null,
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $card = new Card();
        $card->setVorname($data['vorname'] ?? '');
        $card->setNachname($data['nachname'] ?? '');
        $card->setEmail($data['email'] ?? null);
        $card->setLinkedinUrl($data['linkedinUrl'] ?? null);
        if (method_exists($card, 'setFunktion')) {
            $card->setFunktion($data['funktion'] ?? null);
        }

        $em->persist($card);
        $em->flush();

        return $this->json(['id' => $card->getId()], Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, Card $card, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $card->setVorname($data['vorname'] ?? $card->getVorname());
        $card->setNachname($data['nachname'] ?? $card->getNachname());
        $card->setEmail($data['email'] ?? $card->getEmail());
        $card->setLinkedinUrl($data['linkedinUrl'] ?? $card->getLinkedinUrl());
        if (method_exists($card, 'setFunktion')) {
            $card->setFunktion($data['funktion'] ?? $card->getFunktion());
        }

        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Card $card, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($card);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
