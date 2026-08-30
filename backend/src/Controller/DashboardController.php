<?php

namespace App\Controller;
use App\Enum\TicketStatus;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(TicketRepository $ticketRepository, UserRepository $userRepository): Response
    {
       $ticketsOuverts = $ticketRepository->countByStatus(TicketStatus::NEW)
            + $ticketRepository->countByStatus(TicketStatus::ASSIGNED)
            + $ticketRepository->countByStatus(TicketStatus::IN_PROGRESS)
            + $ticketRepository->countByStatus(TicketStatus::WAITING);

        $ticketsResolus = $ticketRepository->countByStatus(TicketStatus::RESOLVED)
            + $ticketRepository->countByStatus(TicketStatus::CLOSED);

        $tempsMoyen = $ticketRepository->getAverageResolutionTimeInHours();

        $techniciensActifs = $userRepository->countActiveTechnicians();

        return $this->render('dashboard/index.html.twig', [
            'ticketsOuverts' => $ticketsOuverts,
            'ticketsResolus' => $ticketsResolus,
            'tempsMoyen' => $tempsMoyen,
            'techniciensActifs' => $techniciensActifs,
        ]); 
    }
}
