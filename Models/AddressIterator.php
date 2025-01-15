<?php
class AddressIterator implements \Iterator 
{
    private $addresses = [];
    private $position = 0;

    public function __construct($addresses) {
        $this->addresses = $addresses;
        $this->position = 0;
    }

    public function current() :mixed
    {
        return $this->addresses[$this->position];
    }

    public function key() :int{
        return $this->position;
    }

    public function next():void
    {
        ++$this->position;
    }

    public function rewind():void {
        $this->position = 0;
    }

    public function valid():bool
    {
        return isset($this->addresses[$this->position]);
    }
}
