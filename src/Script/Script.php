<?php

declare(strict_types=1);

namespace AndKom\Bitcoin\Blockchain\Script;

use AndKom\BCDataStream\Reader;
use AndKom\Bitcoin\Blockchain\Exceptions\ScriptException;

/**
 * Class Script
 * @package AndKom\Bitcoin\Blockchain\Script
 */
class Script
{
    /**
     * @var string
     */
    public $data;

    /**
     * @var
     */
    public $size;

    /**
     * @var array
     */
    protected $operations;

    /**
     * Script constructor.
     * @param string $data
     */
    public function __construct(string $data)
    {
        $this->setData($data);
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return Script
     */
    public function setData(string $data): self
    {
        $this->data = $data;
        $this->size = strlen($data);
        $this->operations = null;
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getHumanReadable(): string
    {
        return implode(' ', $this->parse());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->getHumanReadable();
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * @return Operation[]
     * @throws ScriptException
     */
    public function parse(): array
    {
        if (!is_null($this->operations)) {
            return $this->operations;
        }

        if (!$this->data) {
            throw new ScriptException('Empty script.');
        }

        $this->operations = [];
        $stream = new Reader($this->data);

        try {
            while ($stream->getPosition() < $stream->getSize()) {
                $code = ord($stream->read(1));
                $data = null;
                $size = 0;

                if ($code == Opcodes::OP_0) {
                    $data = '';
                } elseif ($code > Opcodes::OP_0 && $code < Opcodes::OP_PUSHDATA1) {
                    $data = $stream->read($code);
                    $size = $code;
                } elseif ($code >= Opcodes::OP_PUSHDATA1 && $code <= Opcodes::OP_PUSHDATA4) {
                    $size = $stream->readCompactSize();
                    $data = $stream->read($size);
                } elseif ($code == Opcodes::OP_1NEGATE) {
                    $data = chr(-1);
                    $size = 1;
                } elseif ($code >= Opcodes::OP_1 && $code <= Opcodes::OP_16) {
                    $data = chr($code - Opcodes::OP_1 + 1);
                    $size = 1;
                }

                $this->operations[] = new Operation($code, $data, $size);
            }
        } catch (\Exception $exception) {
            throw new ScriptException('Script parse error.', 0, $exception);
        }

        return $this->operations;
    }
}