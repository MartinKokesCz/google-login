<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

final class ProductFacade {

    private Nette\Database\Explorer $database;

    public function __construct(Nette\Database\Explorer $database)
	{
		$this->database = $database;
	}

    public function getAll(): array
    {
        return $this->database->table('products')->fetchAll();
    }
}