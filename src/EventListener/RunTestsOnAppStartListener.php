<?php

// src/EventListener/RunTestsOnAppStartListener.php
namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class RunTestsOnAppStartListener
{
    private FilesystemAdapter $cache;
    private KernelInterface $kernel;
    private LoggerInterface $logger;

    public function __construct(KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->cache = new FilesystemAdapter();
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    public function onKernelRequest(RequestEvent $event): void
    {   

        $cacheItem = $this->cache->getItem('tests_already_run');
        $testsAlreadyRun = $cacheItem->get() ?? false;

        if ($testsAlreadyRun) {
            $this->logger->debug("Tests already run, skipping...");
            return;
        }

        // Mark tests as run
        $cacheItem->set(true);
        $cacheItem->expiresAfter(1800); // Set expiration time in seconds (30 minutes)
        $this->cache->save($cacheItem);
        
        $this->logger->debug("Running tests...");
    
        // Run PHPUnit tests using Symfony Process
        $projectDir = $this->kernel->getProjectDir();

        $process = Process::fromShellCommandline('php vendor/bin/phpunit --configuration phpunit.xml.dist --testdox', $projectDir);
        $process->setTimeout(120);
        $process->setWorkingDirectory($projectDir);
        $process->setEnv(['PHPUNIT_OPTIONS' => '--no-coverage']);
        

    
        try {
            $process->run();
        
            // Log both standard output and error output
            $output = $process->getOutput();
            $errorOutput = $process->getErrorOutput();
        
            if ($process->isSuccessful()) {
                $this->logger->info('✅✅ All tests passed successfully!✅✅');
                $this->logger->info('Test Output: ' . $output);
            } else {
                $this->logger->error('❌ Tests completed with failures:');
                $this->logger->error('Test Output: ' . $output);
                $this->logger->error('Error Output: ' . $errorOutput);
            }
        } catch (ProcessFailedException $exception) {
            $this->logger->error('❌ PHPUnit process failed to run:');
            $this->logger->error($exception->getMessage());
            $this->logger->error('Test Output: ' . $process->getOutput());
            $this->logger->error('Error Output: ' . $process->getErrorOutput());
        }
    }
}
