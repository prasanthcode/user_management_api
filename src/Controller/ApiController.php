<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use App\Message\EmailNotification;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Psr\Log\LoggerInterface;

class ApiController extends AbstractController
{
    #[Route('/api/upload', name: 'api_upload', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $em, MessageBusInterface $bus): Response
    {
        $file = $request->files->get('file');
        if (!$file || $file->getClientOriginalExtension() !== 'csv') {
            return $this->json(['error' => 'Invalid file'], Response::HTTP_BAD_REQUEST);
        }

        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        foreach ($records as $record) {
            $user = new User();
            $user->setName($record['name']);
            $user->setEmail($record['email']);
            $user->setUsername($record['username']);
            $user->setAddress($record['address']);
            $user->setRole($record['role']);
            $em->persist($user);

            $bus->dispatch(new EmailNotification($record['email']));
        }
        $em->flush();

        return $this->json(['status' => 'success']);
    }

    #[Route('/api/users', name: 'api_users', methods: ['GET'])]
    public function getUsers(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'address' => $user->getAddress(),
                'role' => $user->getRole(),
            ];
        }

        return $this->json($data);
    }
    #[Route('/api/backup', name: 'api_backup', methods: ['GET'])]
    public function backupDatabase(): Response
    {
        $backupDir = 'backup';
        $backupFile = $backupDir . '/backup.sql';

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $process = new Process([
            'mysqldump',
            '--user=root'.$_ENV["DB_USERNAME"],
            '--password='.$_ENV["DB_PASSWORD"],
            '--host='.$_ENV["DB_HOST"],
            $_ENV['DB_NAME']
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            return $this->json(['error' => 'Backup failed: ' . $process->getErrorOutput()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        file_put_contents($backupFile, $process->getOutput());

        return $this->json(['status' => 'backup created']);
    }
    #[Route('/api/restore', name: 'api_restore', methods: ['POST'])]
    public function restoreDatabase(Request $request, LoggerInterface $logger): Response
    {
        $databaseName = $_ENV['DB_NAME'];
        $filePath = 'backup/backup.sql';
        $user = $_ENV['DB_USERNAME'];
        $password = $_ENV["DB_PASSWORD"];
        $host = $_ENV["DB_HOST"];


        $restoreCommand = sprintf(
            'mysql --user=%s --password=%s --host=%s %s < %s',
            $user,
            $password,
            $host,
            $databaseName,
            $filePath
        );

        $restoreProcess = Process::fromShellCommandline($restoreCommand);
        try {
            $restoreProcess->mustRun();
        } catch (ProcessFailedException $exception) {
            $logger->error('Restore failed: ' . $exception->getMessage());
            return $this->json(['error' => 'Restore failed: ' . $exception->getMessage()]);
        }

        return $this->json(['status' => 'database restored']);
    }
}
