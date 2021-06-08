<?php

namespace AcMarche\Concours;

use PDO;

require_once(__DIR__.'/../../wp-load.php');

class MarcheDb
{
    private $bdd;

    function __construct()
    {
        $dsn      = 'mysql:host=localhost;dbname=marche_wp';
        $username = DB_USER;
        $password = DB_PASSWORD;
        $options  = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        $this->bdd = new PDO($dsn, $username, $password, $options);
    }

    function getInscrits(string $table = 'inscrits')
    {
        $reponse = $this->bdd->query("SELECT * FROM `$table`");

        return $reponse->fetchAll();
    }

    function insert(
        string $table,
        string $nom,
        string $prenom,
        string $email,
        string $telephone,
        string $accord,
        string $reglement,
        string $codepostal,
        string $localite
    ): array {
        $date  = new \DateTime();
        $today = $date->format('Y-m-d H:i:s');

        $sth = $this->bdd->prepare(
            'INSERT INTO '.$table.' (nom, prenom, email, telephone, inscrit_le, accord, reglement, codepostal, localite) VALUES 
(:nom, :prenom, :email, :telephone, :today, :accord, :reglement, :codepostal, :localite )'
        );
        $sth->bindParam(':nom', $nom, \PDO::PARAM_STR);
        $sth->bindParam(':prenom', $prenom, \PDO::PARAM_STR);
        $sth->bindParam(':email', $email, \PDO::PARAM_STR);
        $sth->bindParam(':telephone', $telephone, \PDO::PARAM_STR);
        $sth->bindParam(':today', $today, \PDO::PARAM_STR);
        $sth->bindParam(':accord', $accord, \PDO::PARAM_STR);
        $sth->bindParam(':reglement', $reglement, \PDO::PARAM_STR);
        $sth->bindParam(':codepostal', $codepostal, \PDO::PARAM_STR);
        $sth->bindParam(':localite', $localite, \PDO::PARAM_STR);

        if ( ! $sth->execute()) {
            $error = $sth->errorInfo();
          //    $sth->debugDumpParams();
            $result = ['danger', $error];
        } else {
            $message = $this->bdd->lastInsertId();
            $result  = ['success', $message];
        }

        return $result;
    }

    function insertGagant(array $gagnant, string $table = 'gagnants')
    {
        $date   = new \DateTime();
        $today  = $date->format('Y-m-d H:i:s');
        $nom    = $gagnant['nom'];
        $prenom = $gagnant['prenom'];
        $email  = $gagnant['email'];

        $sth = $this->bdd->prepare(
            'INSERT INTO '.$table.' (nom, prenom, email, tire_le) VALUES (:nom, :prenom, :email, :today )'
        );
        $sth->bindParam(':nom', $nom, \PDO::PARAM_STR);
        $sth->bindParam(':prenom', $prenom, \PDO::PARAM_STR);
        $sth->bindParam(':email', $email, \PDO::PARAM_STR);
        $sth->bindParam(':today', $today, \PDO::PARAM_STR);
        if ( ! $sth->execute()) {
            $error = $sth->errorInfo();
            mail("jf@marche.be", "error gagnat", join(",", $error));
        }
    }

    function redirectEuro()
    {
        header('Location: /concours-euro/validation.php');
        exit();
    }

    function redirectSante()
    {
        header('Location: /concours-sante/validation.php');
        exit();
    }

    function checkemail($email, string $table = 'inscrits')
    {
        $reponse = $this->bdd->query("SELECT * FROM `$table` WHERE `email` = '$email' ");
        $result  = $reponse->fetch();

        if ($result) {
            return false;
        }

        return true;
    }

}
