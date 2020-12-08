<?php


namespace AcMarche\Bottin\Category;

use AcMarche\Bottin\Repository\WpBottinRepository;
use AcMarche\Bottin\Repository\BottinRepository;
use AcMarche\Common\MarcheConst;

class CategorySynchronizer
{
    /**
     * @var int
     */
    private $categoryId;
    /**
     * @var WpBottinRepository
     */
    private $wpRepository;
    /**
     * @var BottinRepository
     */
    private $bottinRepository;
    /**
     * @var CategoryCreator
     */
    private $categoryCreator;

    public function __construct(int $categoryId)
    {
        $this->categoryId = $categoryId;
        $this->bottinRepository = new BottinRepository();
        $this->wpRepository = new WpBottinRepository();
        $this->categoryCreator = new CategoryCreator();
    }

    public function synchronize()
    {
        $category = $this->bottinRepository->getCategory($this->categoryId);
        foreach (MarcheConst::SITES as $site) {
            switch_to_blog($site);
            $this->execute($category);
        }
    }

    private function execute(\stdClass $category)
    {
        foreach ($this->wpRepository->getCategoriesWp() as $categoryWp) {
            if ($this->categoryId == $categoryWp->bottinId) {
                $result = $this->categoryCreator->updateCategory($categoryWp, $category);
            }
        }
    }

}
