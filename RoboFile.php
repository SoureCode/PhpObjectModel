<?php

declare(strict_types=1);

use Robo\Tasks;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see https://robo.li/
 */
class RoboFile extends Tasks
{
    public function install(): void
    {
        $this->vendor();
    }

    public function cs(): void
    {
        $this->php(
            [
                'vendor/bin/php-cs-fixer',
                'fix',
                '--dry-run',
                '--diff',
            ]
        );
    }

    public function psalm(): void
    {
        if (isset($_SERVER['TERMINAL_EMULATOR'])) {
            $this->php(
                [
                    'vendor/bin/psalm',
                    '--show-info=true',
                    '--no-cache',
                    '--output-format=phpstorm',
                ]
            );
        } else {
            $this->php(
                [
                    'vendor/bin/psalm',
                    '--show-info=true',
                    '--no-cache',
                ]
            );
        }
    }

    public function baseline(): void
    {
        $this->php(
            [
                'vendor/bin/psalm',
                '--set-baseline=psalm-baseline.xml',
            ]
        );
    }

    public function test(): void
    {
        $this->php(
            ['vendor/bin/phpunit']
        );
    }

    public function ci(): void
    {
        $this->cs();
        $this->psalm();
        $this->sniff();
        $this->test();
    }

    public function sniff(): void
    {
        $this->php([
            'vendor/bin/phpcs',
            '--standard=PSR12',
            'src',
        ]);
    }

    public function csFix(): void
    {
        $this->php(
            [
                'vendor/bin/php-cs-fixer',
                'fix',
            ]
        );
    }

    private function php(array $args): void
    {
        $this->taskExec('php')
            ->env('PHP_CS_FIXER_IGNORE_ENV', '1')
            ->args($args)
            ->run();
    }

    public function vendor(): void
    {
        $this->taskComposerInstall()
            ->optimizeAutoloader()
            ->run();
    }
}
