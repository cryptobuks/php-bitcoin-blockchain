<?php

declare(strict_types=1);

namespace AndKom\Bitcoin\Blockchain;

/**
 * Class ScriptOperation
 * @package AndKom\Bitcoin\Blockchain
 */
class Operation
{
    /**
     * @var int
     */
    public $code;

    /**
     * @var mixed|null
     */
    public $data;

    /**
     * @var int|null
     */
    public $size;

    /**
     * ScriptOperation constructor.
     * @param int $code
     * @param mixed|null $data
     * @param int $size
     */
    public function __construct(int $code, $data = null, int $size = 0)
    {
        $this->code = $code;
        $this->data = $data;
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        try {
            return $this->getHumanReadable();
        } catch (\Exception $exception) {
            return 'Script parse error.';
        }
    }

    /**
     * @return bool
     */
    public function isPush(): bool
    {
        return $this->code >= 0x00 && $this->code <= 0x60;
    }

    /**
     * @return string
     */
    public function getHumanReadable(): string
    {
        if ($this->code >= 0x01 && $this->code <= 0x4e) {
            return sprintf('PUSHDATA(%d)[%s]', $this->size, bin2hex($this->data));
        }

        if (isset(Opcodes::$names[$this->code])) {
            return str_replace('OP_', '', Opcodes::$names[$this->code]);
        }

        return 'UNKNOWN(' . $this->code . ')';
    }
}