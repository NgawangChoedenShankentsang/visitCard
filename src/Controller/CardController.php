<?php

namespace App\Controller;

use App\Entity\Card;
use App\Form\CardTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Endroid\QrCode\Builder\Builder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CardRepository;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\Font\NotoSans;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/card')]
class CardController extends AbstractController
{
    #[Route('/new', name: 'card_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $card = new Card();
        $form = $this->createForm(CardTypeForm::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($card);
            $em->flush();

            $this->addFlash('success', 'Visitenkarte wurde erstellt.');

            return $this->redirectToRoute('app_card_show', ['id' => $card->getId()]);
        }

        return $this->render('card/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/card/{id}', name: 'app_card_show')]
    public function show(Card $card, UrlGeneratorInterface $urlGenerator): Response
    {
        $url = $urlGenerator->generate('app_card_show', ['id' => $card->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $qrCode = new QrCode($url);
        $writer = new PngWriter();

        $qrResult = $writer->write($qrCode);

        $qrImageBase64 = base64_encode($qrResult->getString());

        return $this->render('card/show.html.twig', [
            'card' => $card,
            'qrCode' => $qrImageBase64,
        ]);
    }


    #[Route('/card', name: 'card_index')]
    public function index(CardRepository $cardRepo): Response
    {
        $cards = $cardRepo->findAll();

        return $this->render('card/all.html.twig', [
            'cards' => $cards,
        ]);
    }
}
