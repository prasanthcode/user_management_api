<?php 
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class SendTestEmailCommand extends Command
{
    protected static $defaultName = 'app:send-test-email';
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Sends a test email.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $email = (new Email())
            ->from('pintuprasanth9@gmail.com')
            ->to('gavvalaprasanth84@gmail.com')
            ->subject('Test Email')
            ->text('This is a test email.');

        $this->mailer->send($email);
        $output->writeln('Test email sent.');

        return Command::SUCCESS;
    }
}
