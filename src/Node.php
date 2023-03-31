<?php

namespace Stone\Maze;

class Node {

    const UP = 'U';
    const DOWN = 'D';
    const RIGHT = 'R';
    const LEFT = 'L';

    public int $row;
    public int $col;

    public string $dir;

    public int $f;
    public int $g;
    public int $h;

    public int $mapIndex;

    public ?Node $parent;

    /**
     * @param int $r
     * @param int $c
     */
    public function __construct(int $r, int $c, string $dir = '')
    {
        $this->row = $r;
        $this->col = $c;
        $this->dir = $dir;
        $this->f = 0;
        $this->g = 0;
        $this->h = 0;
    }

    /**
     * @return int
     */
    public function getRow(): int
    {
        return $this->row;
    }

    /**
     * @param int $r
     */
    public function setRow(int $r): void
    {
        $this->row = $r;
    }

    /**
     * @return int
     */
    public function getCol(): int
    {
        return $this->col;
    }

    /**
     * @param int $c
     */
    public function setCol(int $c): void
    {
        $this->col = $c;
    }
}