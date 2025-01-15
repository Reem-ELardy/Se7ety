<?php

class ObserversIterator implements \Iterator
{
    private array $observers;
    private int $position = 0;
    
    public function __construct(array $observers)
    {
        $this->observers = $observers;
        $this->position = 0;
    }
    
    // Return the current element
    public function current(): mixed
    {
        return $this->observers[$this->position];
    }

    // Return the current index
    public function key(): int
    {
        return $this->position;
    }

    // Move forward to the next element
    public function next(): void
    {
        $this->position++;
    }

    // Rewind to the first element
    public function rewind(): void
    {
        $this->position = 0;
    }

    // Check if current position is valid
    public function valid(): bool
    {
        return isset($this->observers[$this->position]);
    }
}
