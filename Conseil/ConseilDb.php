<?php

namespace AcMarche\Conseil;

use AcMarche\Common\Env;
use PDO;

class ConseilDb
{
    /**
     * @var \PDO
     */
    private $dbh;

    public function __construct()
    {
        Env::loadEnv();
        $dsn      = 'mysql:host=localhost;dbname=conseil';
        $username = $_ENV['DB_CONSEIL_USER'];
        $password = $_ENV['DB_CONSEIL_PASS'];
        $options  = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        $this->dbh = new PDO($dsn, $username, $password, $options);
    }

    public function getAllOrdre()
    {
        $today = new \DateTime();
        $sql   = 'SELECT * FROM ordre_jour WHERE `date_fin_diffusion` >= '.$today->format(
                'Y-m-d'
            ).' ORDER BY `date_ordre` DESC ';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    public function getPvByYear(int $year)
    {
        $sql   = "SELECT * FROM pv WHERE `date_pv` LIKE '$year-%' ORDER BY `date_pv` DESC ";
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    public function getAllPvs():array
    {
        $pvs = [];
        foreach (range(2013, 2025) as $year) {
            $pvs[$year] = $this->getPvByYear($year);
        }

        return $pvs;
    }

    /**
     * @param string $nom
     * @param string $dateOrdre
     * @param string $dateFin
     * @param string $fileName
     *
     * @return bool|string
     * @throws \Exception
     */
    public function insertOrdre(string $nom, string $dateOrdre, string $dateFin, string $fileName)
    {
        $today = new \DateTime();
        $today = $today->format('Y-m-d');
        $req   = "INSERT INTO `ordre_jour` (`nom`,`date_ordre`,`date_fin_diffusion`,`file_name`,`createdAt`,`updatedAt`) 
VALUES (?,?,?,?,?,?)";

        $stmt = $this->dbh->prepare($req);
        $stmt->bindParam(1, $nom);
        $stmt->bindParam(2, $dateOrdre);
        $stmt->bindParam(3, $dateFin);
        $stmt->bindParam(4, $fileName);
        $stmt->bindParam(5, $today);
        $stmt->bindParam(6, $today);

        try {
            return $stmt->execute();
        } catch (\PDOException $exception) {
            return $exception->getMessage();
        }

    }

    public function insertPv(string $nom, string $datePv, string $fileName)
    {
        $today = new \DateTime();
        $today = $today->format('Y-m-d');
        $req   = "INSERT INTO `pv` (`nom`,`date_pv`,`file_name`,`createdAt`,`updatedAt`) 
VALUES (?,?,?,?,?)";

        $stmt = $this->dbh->prepare($req);
        $stmt->bindParam(1, $nom);
        $stmt->bindParam(2, $datePv);
        $stmt->bindParam(3, $fileName);
        $stmt->bindParam(4, $today);
        $stmt->bindParam(5, $today);

        try {
            return $stmt->execute();
        } catch (\PDOException $exception) {
            return $exception->getMessage();
        }

    }

    public function deleteOrdre(string $dateOrdre, string $fileName)
    {
        $req = 'DELETE FROM ordre_jour WHERE date_ordre = ? AND file_name = ?';

        $stmt = $this->dbh->prepare($req);
        $stmt->bindParam(1, $dateOrdre);
        $stmt->bindParam(2, $fileName);

        try {
            return $stmt->execute();
        } catch (\PDOException $exception) {
            return $exception->getMessage();
        }
    }

    public function deletePv(string $datePv, string $fileName)
    {
        $req = 'DELETE FROM pv WHERE date_pv = ? AND file_name = ?';

        $stmt = $this->dbh->prepare($req);
        $stmt->bindParam(1, $datePv);
        $stmt->bindParam(2, $fileName);

        try {
            return $stmt->execute();
        } catch (\PDOException $exception) {
            return $exception->getMessage();
        }
    }

    public function execQuery($sql)
    {
        // var_dump($sql);
        $query = $this->dbh->query($sql);
        $error = $this->dbh->errorInfo();
        if ($error[0] != '0000') {
            //    var_dump($error[2]);
            // mail('jf@marche.be', 'duobac error sql', $error[2]);

            throw new \Exception($error[2]);
        };

        return $query;
    }

}

?>
