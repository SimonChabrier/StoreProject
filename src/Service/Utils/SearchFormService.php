<?php

namespace App\Service\Utils;

use App\Form\SearchType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class SearchFormService
{
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function createForm(): FormInterface
    {
        return $this->formFactory->createBuilder(SearchType::class)->getForm();
    }
}
