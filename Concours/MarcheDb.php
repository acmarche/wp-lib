<?php

namespace AcMarche\Concours;

use PDO;

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

    function insertSante($nom, $prenom, $email, $telephone, $accord, $codepostal, $localite)
    {
        $date  = new \DateTime();
        $today = $date->format('Y-m-d h:i:s');

        $sth = $this->bdd->prepare(
            'INSERT INTO inscrits_sante (nom, prenom, email, telephone, inscrit_le, accord, codepostal, localite) VALUES 
(:nom, :prenom, :email, :telephone, :today, :accord, :codepostal, :localite )'
        );
        $sth->bindParam(':nom', $nom, \PDO::PARAM_STR);
        $sth->bindParam(':prenom', $prenom, \PDO::PARAM_STR);
        $sth->bindParam(':email', $email, \PDO::PARAM_STR);
        $sth->bindParam(':telephone', $telephone, \PDO::PARAM_STR);
        $sth->bindParam(':today', $today, \PDO::PARAM_STR);
        $sth->bindParam(':accord', $accord, \PDO::PARAM_STR);
        $sth->bindParam(':codepostal', $codepostal, \PDO::PARAM_STR);
        $sth->bindParam(':localite', $localite, \PDO::PARAM_STR);
        if ( ! $sth->execute()) {
            $error  = $sth->errorInfo();
            $result = ['danger', $error];
        } else {
            $message = $this->bdd->lastInsertId();
            $result  = ['success', $message];
        }

        return $result;
    }

    function insertRoman($nom, $prenom, $email, $accord, $vote, $comite)
    {
        $date  = new \DateTime();
        $today = $date->format('Y-m-d h:i:s');

        $sth = $this->bdd->prepare(
            'INSERT INTO inscrits_roman (nom, prenom, email, inscrit_le, accord, vote, comite) VALUES 
(:nom, :prenom, :email, :today, :accord, :vote, :comite )'
        );
        $sth->bindParam(':nom', $nom, \PDO::PARAM_STR);
        $sth->bindParam(':prenom', $prenom, \PDO::PARAM_STR);
        $sth->bindParam(':email', $email, \PDO::PARAM_STR);
        $sth->bindParam(':today', $today, \PDO::PARAM_STR);
        $sth->bindParam(':accord', $accord, \PDO::PARAM_STR);
        $sth->bindParam(':vote', $vote, \PDO::PARAM_STR);
        $sth->bindParam(':comite', $comite, \PDO::PARAM_STR);
        if ( ! $sth->execute()) {
            $error  = $sth->errorInfo();
            $result = ['danger', $error];
        } else {
            $message = $this->bdd->lastInsertId();
            $result  = ['success', $message];
        }

        return $result;
    }

    function insert($nom, $prenom, $email, $telephone, $accord, $table = 'inscrits')
    {
        $date  = new \DateTime();
        $today = $date->format('Y-m-d h:i:s');

        $sth = $this->bdd->prepare(
            'INSERT INTO '.$table.' (nom, prenom, email, telephone, inscrit_le, accord) VALUES (:nom, :prenom, :email, :telephone, :today, :accord )'
        );
        $sth->bindParam(':nom', $nom, \PDO::PARAM_STR);
        $sth->bindParam(':prenom', $prenom, \PDO::PARAM_STR);
        $sth->bindParam(':email', $email, \PDO::PARAM_STR);
        $sth->bindParam(':telephone', $telephone, \PDO::PARAM_STR);
        $sth->bindParam(':today', $today, \PDO::PARAM_STR);
        $sth->bindParam(':accord', $accord, \PDO::PARAM_STR);
        if ( ! $sth->execute()) {
            $error  = $sth->errorInfo();
            $result = ['danger', $error];
        } else {
            $message = $this->bdd->lastInsertId();
            $result  = ['success', $message];
        }

        return $result;
    }

    function insertGagant($gagnant, string $table = 'gagnants')
    {
        $date   = new \DateTime();
        $today  = $date->format('Y-m-d h:i:s');
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

    function redirect()
    {
        header('Location: /marche/validation.php');
        exit();
    }

    function redirectEquitable()
    {
        header('Location: /equitable/validation.php');
        exit();
    }

    function redirectSante()
    {
        header('Location: /sante-concours/validation.php');
        exit();
    }

    function redirectRoman()
    {
        header('Location: /roman/validation.php');
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
