<?php

namespace AcMarche\Bottin\Repository;

use AcMarche\Bottin\Bottin;
use AcMarche\Common\Env;

class BottinRepository
{
    /**
     * @var \PDO
     */
    private $dbh;

    public function __construct()
    {
        Env::loadEnv();
        $dsn = 'mysql:host=localhost;dbname=bottin';
        $username = $_ENV['DB_BOTTIN_USER'];
        $password = $_ENV['DB_BOTTIN_PASS'];
        $options = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        $this->dbh = new \PDO($dsn, $username, $password, $options);
    }

    public function getClassementsFiche(int $ficheId): array
    {
        $sql = 'SELECT * FROM classements WHERE `fiche_id` = '.$ficheId.' ORDER BY `principal` DESC ';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    /**
     * @param int $id
     * @return \stdClass|bool
     * @throws \Exception
     */
    public function getFiche(int $id)
    {
        $sql = 'SELECT * FROM fiche WHERE `id` = '.$id;
        $query = $this->execQuery($sql);

        return $query->fetchObject();
    }

    /**
     * @return \stdClass[]
     * @throws \Exception
     */
    public function getFiches(): array
    {
        $sql = 'SELECT * FROM fiche';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    /**
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getImagesFiche(int $id)
    {
        $sql = 'SELECT * FROM fiche_images WHERE `fiche_id` = '.$id.' ORDER BY `principale` DESC';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    /**
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getDocuments(int $id)
    {
        $sql = 'SELECT * FROM document WHERE `fiche_id` = '.$id.' ORDER BY `name` DESC';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    /**
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getSituations(int $id)
    {
        $sql = 'SELECT * FROM `fiche_situation` LEFT JOIN situation ON situation.id = fiche_situation.situation_id WHERE `fiche_id` = '.$id.' ORDER BY `name` DESC';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    public function isCentreVille(int $id): bool
    {
        $situations = $this->getSituations($id);
        foreach ($situations as $situation) {
            if (in_array('Centre ville', $situation)) {
                return true;
            }
        }

        return false;
    }

    public function getLogo(int $id): ?string
    {
        $images = $this->getImagesFiche($id);
        $logo = null;

        if (count($images) > 0) {
            $logo = Bottin::getUrlBottin().$id.DIRECTORY_SEPARATOR.$images[0]['image_name'];
        }

        return $logo;
    }

    /**
     * @param int $id
     * @return \stdClass|null
     * @throws \Exception
     */
    public function getCategory(int $id): ?\stdClass
    {
        $sql = 'SELECT * FROM category WHERE `id` = '.$id;
        $query = $this->execQuery($sql);

        return $query->fetchObject();
    }

    /**
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getCategories(int $parentId)
    {
        $sql = 'SELECT * FROM category WHERE `parent_id` = '.$parentId;
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    public function getFichesByCategory($id)
    {
        $category = $this->getCategory($id);
        if (!$category) {
            //send email error
        }

        $sql = 'SELECT * FROM classements WHERE `category_id` = '.$id;
        $query = $this->execQuery($sql);
        $classements = $query->fetchAll();

        $fiches = array_map(
            function ($classement) {
                return $this->getFiche($classement['fiche_id']);
            },
            $classements
        );

        return array_unique($fiches, SORT_REGULAR);
    }

    public function execQuery($sql)
    {
        $query = $this->dbh->query($sql);
        $error = $this->dbh->errorInfo();
        if ($error[0] != '0000') {
            throw new \Exception($error[2]);
        };

        return $query;
    }


}
