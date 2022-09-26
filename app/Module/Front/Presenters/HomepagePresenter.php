<?php

declare(strict_types=1);

namespace App\Module\Front\Presenters;

use App\Model\ProductFacade;
use Nette;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{    
    private ProductFacade $productFacade;

    public function __construct(ProductFacade $productFacade) {
        $this->productFacade = $productFacade;
    }

	public function renderDefault(): void
    {
        $this->template->products = $this->productFacade->getAll();
    }
}
