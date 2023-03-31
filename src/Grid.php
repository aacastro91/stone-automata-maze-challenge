<?php

namespace Stone\Maze;

use Exception;

class Grid {

    /**
     * @var int[][]
     */
    public array $gridArray;

    public int $gridIndex;

    public int $totalEvolution = 0;

    public int $height;
    public int $width;

    protected Node $start; // start x

    protected Node $end;

    /**
     * @var Node[]
     */
    protected array $openList = [];

    /**
     * @var Node[]
     */
    protected array $closeList = [];

    /**
     * @throws Exception
     */
    public function __construct($path)
    {
        $this->loadGridFromFile($path);
        $this->gridIndex = 0;
    }

    public function setStartPoint(int $row, int $col): void
    {
        $this->start = new Node($row, $col);
        $this->start->mapIndex = 0;
        $this->start->parent = null;
    }

    public function setEndPoint(int $row, int $col): void
    {
        $this->end = new Node($row, $col);
    }

    /**
     * @throws Exception
     */
    public function loadGridFromFile($path): void
    {
        $fullPath = PATH_ROOT . "/$path";
        $input = fopen($fullPath, 'r');
        if ($input === false) {
            throw new Exception('File not found');
        }
        $rowIndex = 0;
        $grid = [];
        while (!feof($input)) {
            $line = explode(' ',fgets($input));
            $grid[] = array_map(function ($key, $item) use ($rowIndex) {
                $item = intval(trim($item));
                if ($item > 1) { //se for maior 1, retorna para zero
                    $item = 0;
                }
                return $item;
            } , array_keys($line), $line);
            $rowIndex++;
        }
        $this->height = count($grid);
        $this->width = count($grid[0]);
        fclose($input);
        $this->gridArray[] = $grid;
    }

    public function nextEvolution(): void
    {
        $lastIndex = count($this->gridArray) - 1;
        $newMap = [];
        for ($r = 0; $r < $this->height; $r++) {
            $newRow = [];
            for ($c = 0; $c < $this->width; $c++) {
                $value = $this->cellEvolution($r, $c, $lastIndex);
                if ($r === 0 && $c === 0) {
                    $value = 0;
                }
                if ($r === $this->height - 1 && $c === $this->width - 1) {
                    $value = 0;
                }
                $newRow[] = $value;
            }
            $newMap[] = $newRow;
        }
        $this->gridArray[] = $newMap;
        $this->gridIndex = count($this->gridArray) - 1;
        $this->totalEvolution++;
    }

    /**
     * @param int $row
     * @param int $col
     * @param int $lastIndex
     * @return int
     */
    private function cellEvolution(int $row, int $col, int $lastIndex): int
    {
        $count = 0;
        $self = $this->gridArray[$lastIndex][$row][$col];

        $rMax = $row - 1;
        if ($rMax < 0) {
            $rMax = 0;
        }

        $rMin = $row + 1;
        if ($rMin >= $this->height) {
            $rMin = $this->height - 1;
        }

        $cMax = $col - 1;
        if ($cMax < 0) {
            $cMax = 0;
        }

        $cMin = $col + 1;
        if ($cMin >= $this->width) {
            $cMin = $this->width - 1;
        }

        for ($r = $rMax; $r <= $rMin; $r++) {
            for ($c = $cMax; $c <= $cMin; $c++) {
                if ($r === $row && $c === $col) {
                    continue;
                }
                if ($this->gridArray[$lastIndex][$r][$c] === 1) {
                    $count++;
                }
            }
        }

        if ($self === 1) {
            return ($count > 3 && $count < 6) ? 1 : 0;
        } else {
            return ($count > 1 && $count < 5) ? 1 : 0;
        }
    }


    public function getPath(): array
    {
        $this->openList[] = $this->start;
        while (!empty($this->openList)) {
            $current = reset($this->openList);
            $currentIdxOnOpenList = key($this->openList);
            foreach($this->openList as $key => $value) {
                if ($value->f < $current->f) {
                    $current = $value;
                    $currentIdxOnOpenList = $key;
                } if ($value->mapIndex - 1 > $current->mapIndex) {
                    $current = $value;
                    $currentIdxOnOpenList = $key;
                }
            }
            $mapIndex = $current->mapIndex + 1;
            if ($mapIndex > $this->totalEvolution) {
                $this->nextEvolution();
            }
            unset($this->openList[$currentIdxOnOpenList]);
            $this->closeList[] = "$current->row|$current->col|$current->mapIndex";
            if ($current->row ===  $this->end->row && $current->col ===  $this->end->col) {
                $path = array();
                while ($current->parent !== null) {
                    $path[] = $current->dir;
                    $current = $current->parent;
                }
                return array_reverse($path);
            }
            $availableMoves = $this->availableMove( $current, $mapIndex );
            foreach ($availableMoves as $neighbor) {
                $neighbor->mapIndex = $mapIndex;
                if ($this->isOnCloseList($neighbor)) {
                    continue;
                }
                $g = $current->g + 10;
                $h = $this->distance($neighbor, $this->end);
                $f = $g + $h;

                if ($this->isOnOpenList($neighbor)) {
                    if ($f > $neighbor->f) {
                        continue;
                    }
                }
                $neighbor->g =$g;
                $neighbor->h =$h;
                $neighbor->f =$f;
                $neighbor->parent =$current;
                $this->openList[] = $neighbor;
            }
        }
        return [];
    }

    private function isOnOpenList(Node $node): bool
    {
        foreach ($this->openList as $n) {
            if ($node->row === $n->row && $node->col === $n->col && $node->mapIndex === $n->mapIndex) {
                return true;
            }
        }
        return false;
    }


    private function isOnCloseList(Node $node): bool
    {
        return in_array("$node->row|$node->col|$node->mapIndex", $this->closeList);
    }

    /**
     * @param Node $node
     * @param int $mapIndex
     * @return Node[]
     */
    public function availableMove( Node $node, int $mapIndex ): array
    {
        $row = $node->getRow();
        $col = $node->getCol();
        $newNodes = [];
        if ($row > 0 && !($this->gridArray[$mapIndex][$row - 1][$col] === 1)) {
            $newNodes[] = new Node($row - 1, $col, Node::UP);
        }
        if ($row < $this->height - 1 && !($this->gridArray[$mapIndex][$row + 1][$col] === 1)) {
            $newNodes[] = new Node($row + 1, $col, Node::DOWN);
        }
        if ($col > 0 && !($this->gridArray[$mapIndex][$row][$col - 1] === 1)) {
            $newNodes[] = new Node($row, $col - 1, Node::LEFT);
        }
        if ($col < $this->width - 1 && !($this->gridArray[$mapIndex][$row][$col + 1] === 1)) {
            $newNodes[] = new Node($row, $col + 1, Node::RIGHT);
        }
        return $newNodes;
    }

    /**
     * @param Node $from
     * @param Node $to
     * @return int
     */
    public function distance(Node $from, Node $to): int
    {
        return abs($from->getCol() - $to->getCol()) + abs($from->getRow() - $to->getCol());
    }

}