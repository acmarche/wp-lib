<?php

namespace AcMarche\Bottin\Repository;

use AcMarche\Bottin\Bottin;
use AcMarche\Common\Env;
use AcMarche\Common\Mailer;
use Exception;
use PDO;

class BottinRepository
{
    /**
     * @var PDO
     */
    private $dbh;

    public function __construct()
    {
        Env::loadEnv();
        $dsn      = 'mysql:host=localhost;dbname=bottin';
        $username = $_ENV['DB_BOTTIN_USER'];
        $password = $_ENV['DB_BOTTIN_PASS'];
        $options  = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        $this->dbh = new PDO($dsn, $username, $password, $options);
    }

    public function getClassementsFiche(int $ficheId): array
    {
        $sql   = 'SELECT * FROM classements WHERE `fiche_id` = '.$ficheId.' ORDER BY `principal` DESC ';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    public function getCategoriesOfFiche(int $ficheId): array
    {
        $categories  = [];
        $classements = $this->getClassementsFiche($ficheId);
        foreach ($classements as $classement) {
            $category = $this->getCategory($classement['category_id']);
            if ($category) {
                $categories[] = $category;
            }
        }

        return $categories;
    }

    /**
     * @param int $id
     *
     * @return object|bool
     * @throws Exception
     */
    public function getFicheById(int $id): object
    {
        $sql   = 'SELECT * FROM fiche WHERE `id` = '.$id;
        $query = $this->execQuery($sql);

        return $query->fetchObject();
    }

    /**
     * @param int $id
     *
     * @return object|bool
     * @throws Exception
     */
    public function getFicheBySlug(string $slug): object
    {
        $sql = 'SELECT * FROM fiche WHERE `slug` = :slug ';
        $sth = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute(array(':slug' => $slug));

        return $sth->fetchObject();
    }

    /**
     * @return object[]
     * @throws Exception
     */
    public function getFiches(): array
    {
        $sql   = 'SELECT * FROM fiche';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws Exception
     */
    public function getImagesFiche(int $id): array
    {
        $sql   = 'SELECT * FROM fiche_images WHERE `fiche_id` = '.$id.' ORDER BY `principale` DESC';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws Exception
     */
    public function getDocuments(int $id): array
    {
        $sql   = 'SELECT * FROM document WHERE `fiche_id` = '.$id.' ORDER BY `name` DESC';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws Exception
     */
    public function getSituations(int $id): array
    {
        $sql   = 'SELECT * FROM `fiche_situation` LEFT JOIN situation ON situation.id = fiche_situation.situation_id WHERE `fiche_id` = '.$id.' ORDER BY `name` DESC';
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
        $logo   = null;

        if (count($images) > 0) {
            $logo = Bottin::getUrlBottin().$id.DIRECTORY_SEPARATOR.$images[0]['image_name'];
        }

        return $logo;
    }

    /**
     * @param int $id
     *
     * @return object|null
     * @throws Exception
     */
    public function getCategory(int $id): ?object
    {
        $sql   = 'SELECT * FROM category WHERE `id` = '.$id;
        $query = $this->execQuery($sql);

        return $query->fetchObject();
    }

    /**
     * @param string $slug
     *
     * @return object|bool
     * @throws Exception
     */
    public function getCategoryBySlug(string $slug): object
    {
        $sql = 'SELECT * FROM category WHERE `slug` = :slug ';
        $sth = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute(array(':slug' => $slug));

        return $sth->fetch(PDO::FETCH_OBJ);
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws Exception
     */
    public function getCategories(int $parentId): array
    {
        $sql   = 'SELECT * FROM category WHERE `parent_id` = '.$parentId;
        $query = $this->execQuery($sql);

        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    public function getFichesByCategories(array $ids): array
    {
        $fiches = [[]];
        foreach ($ids as $id) {
            $fiches[] = $this->getFichesByCategory($id);
        }

        return array_merge(...$fiches);
    }

    public function getFichesByCategory(int $id): array
    {
        $category = $this->getCategory($id);
        if ( ! $category) {
            Mailer::sendError('fiche non trouvée', 'categorie id: '.$id);
        }

        $sql         = 'SELECT * FROM classements WHERE `category_id` = '.$id;
        $query       = $this->execQuery($sql);
        $classements = $query->fetchAll();

        $fiches = array_map(
            function ($classement) {
                return $this->getFicheById($classement['fiche_id']);
            },
            $classements
        );

        $fiches = array_unique($fiches, SORT_REGULAR);

        return $fiches;
    }

    /**
     * @param $sql
     *
     * @return false|\PDOStatement
     * @throws Exception
     */
    public function execQuery($sql)
    {
        $query = $this->dbh->query($sql);
        $error = $this->dbh->errorInfo();
        if ($error[0] != '0000') {
            Mailer::sendError("wp error sql", $sql.' '.$error[2]);
            throw new Exception($error[2]);
        };

        return $query;
    }
}
