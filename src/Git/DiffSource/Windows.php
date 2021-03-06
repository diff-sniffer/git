<?php

declare(strict_types=1);

namespace DiffSniffer\Git\DiffSource;

use DiffSniffer\Cli;
use DiffSniffer\DiffSource;

use function array_merge;

/**
 * Windows-specific implementation of the diff source
 */
class Windows implements DiffSource
{
    /**
     * CLI utilities
     *
     * @var Cli
     */
    private $cli;

    /** @var array<int,string> */
    private $args;

    /**
     * Git working directory
     *
     * @var string
     */
    private $dir;

    /**
     * Constructor
     *
     * @param Cli               $cli  CLI utilities
     * @param array<int,string> $args
     */
    public function __construct(Cli $cli, array $args, string $dir)
    {
        $this->cli  = $cli;
        $this->args = $args;
        $this->dir  = $dir;
    }

    /**
     * {@inheritDoc}
     *
     * @see https://bugs.php.net/bug.php?id=49446
     */
    public function getDiff(): string
    {
        return $this->cli->execPiped([
            $this->cli->cmd('git', 'diff', ...array_merge($this->args, [
                '--numstat',
                '--',
            ])),
            'for /f "tokens=1,3" %i in (\'findstr "^"\') do @if not "%i" == "0" '
            . $this->cli->cmd('git', 'diff', ...array_merge($this->args, ['--'])) . ' %j',
        ], $this->dir);
    }
}
