<?php declare(strict_types=1);
namespace App\Application\Command;

use Ramsey\Uuid\UuidInterface;

final class CreateProduct
{
    /** @var UuidInterface */
    private $id;
    /** @var string */
    private $sku;
    /** @var string */
    private $type;

    public function __construct(UuidInterface $id, string $sku, string $type)
    {
        $this->id = $id;
        $this->sku = $sku;
        $this->type = $type;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
