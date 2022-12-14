<?php

declare(strict_types=1);

use Robo\Collection\CollectionBuilder;
use Robo\Symfony\ConsoleIO;
use Robo\Task\Base\Exec;
use Robo\Task\Composer\Install;
use Robo\Task\Testing\PHPUnit;
use Robo\Tasks;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see https://robo.li/
 */
class RoboFile extends Tasks
{
    public function cs(): Exec|CollectionBuilder
    {
        return $this->taskPhp()
            ->env('PHP_CS_FIXER_IGNORE_ENV', '1')
            ->args([
                'vendor/bin/php-cs-fixer',
                'fix',
                '--dry-run',
                '--diff',
            ]
            );
    }

    public function psalm(): Exec|CollectionBuilder
    {
        if (isset($_SERVER['TERMINAL_EMULATOR'])) {
            return $this->taskPhp()
                ->args([
                    'vendor/bin/psalm',
                    '--show-info=true',
                    '--no-cache',
                    '--output-format=phpstorm',
                ]
                );
        }

        return $this->taskPhp()
            ->args([
                'vendor/bin/psalm',
                '--show-info=true',
                '--no-cache',
            ]
            );
    }

    public function baseline(): Exec|CollectionBuilder
    {
        return $this->taskPhp()
            ->args([
                'vendor/bin/psalm',
                '--set-baseline=psalm-baseline.xml',
            ]);
    }

    public function test(): PHPUnit|CollectionBuilder
    {
        return $this->taskPhpUnit();
    }

    public function ci(ConsoleIO $io): CollectionBuilder
    {
        return $this->collectionBuilder($io)
            ->addTask($this->cs())
            ->addTask($this->psalm())
            ->addTask($this->sniff())
            ->addTask($this->test());
    }

    public function sniff(): Exec|CollectionBuilder
    {
        return $this->taskPhp()
            ->args([
                'vendor/bin/phpcs',
                '--standard=PSR12',
                'src',
            ]);
    }

    public function csFix(): Exec|CollectionBuilder
    {
        return $this->taskPhp()
            ->env('PHP_CS_FIXER_IGNORE_ENV', '1')
            ->args(
                [
                    'vendor/bin/php-cs-fixer',
                    'fix',
                ]
            );
    }

    public function vendor(): Install|CollectionBuilder
    {
        return $this->taskComposerInstall()
            ->optimizeAutoloader();
    }

    private function taskPhp(string $php = 'php'): Exec|CollectionBuilder
    {
        return $this->taskExec($php);
    }
}
