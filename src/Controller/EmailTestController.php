<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
class EmailTestController extends AbstractController
{
    #[Route('/test-email', name: 'test_email')]
    public function sendTestEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('bahamilouah@gmail.com')
            ->to('mati33aklak@gmail.com') // Replace with the recipient's email
            ->subject('Test Email')
            ->text('This is a test email sent from Symfony using Gmail SMTP.');

        $mailer->send($email);

        return new Response('Test email ssaent!');
    }
}
