<?php
/**
 * Classe d'accès aux données.
 *
 * PHP Version 7
 *
 * @category  PPE
 * @package   GSB
 * @author    Cheri Bibi - Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL - CNED <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

/**
 * Classe d'accès aux données.
 *
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO
 * $monPdoGsb qui contiendra l'unique instance de la classe
 *
 * PHP Version 7
 *
 * @category  PPE
 * @package   GSB
 * @author    Cheri Bibi - Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   Release: 1.0
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

class PdoGsb
{
    private static $serveur = 'mysql:host=localhost';
    private static $bdd = 'dbname=gsb_frais';
    private static $user = 'userGsb';
    private static $mdp = 'secret';
    private static $monPdo;
    private static $monPdoGsb = null;

    /**
     * Constructeur privé, crée l'instance de PDO qui sera sollicitée
     * pour toutes les méthodes de la classe
     */
    private function __construct()
    {
        PdoGsb::$monPdo = new PDO(
            PdoGsb::$serveur . ';' . PdoGsb::$bdd,
            PdoGsb::$user,
            PdoGsb::$mdp
        );
        PdoGsb::$monPdo->query('SET CHARACTER SET utf8');
    }


    /**
     * Méthode destructeur appelée dès qu'il n'y a plus de référence sur un
     * objet donné, ou dans n'importe quel ordre pendant la séquence d'arrêt.
     */
    public function __destruct()
    {
        PdoGsb::$monPdo = null;
    }

    /**
     * Fonction statique qui crée l'unique instance de la classe
     * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
     *
     * @return l'unique objet de la classe PdoGsb
     */
    public static function getPdoGsb()
    {
        if (PdoGsb::$monPdoGsb == null) {
            PdoGsb::$monPdoGsb = new PdoGsb();
        }
        return PdoGsb::$monPdoGsb;
    }

    /**
     * Retourne les informations d'un visiteur
     *
     * @param String $login Login du visiteur
     * @param String $mdp   Mot de passe du visiteur
     *
     * @return l'id, le nom, le prénom et le type 
     * sous la forme d'un tableau associatif
     */
    public function getInfosVisiteur($login, $mdp)
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT visiteur.id AS id, visiteur.nom AS nom, '
            . 'visiteur.prenom AS prenom, visiteur.idTypeVisiteur AS type '
            . 'FROM visiteur '
            . 'WHERE visiteur.login = :unLogin AND visiteur.mdp = :unMdp'
        );
        $requetePrepare->bindParam(':unLogin', $login, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMdp', $mdp, PDO::PARAM_STR);
        $requetePrepare->execute();
        return $requetePrepare->fetch();
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais
     * hors forfait concernées par les deux arguments.
     * La boucle foreach ne peut être utilisée ici car on procède
     * à une modification de la structure itérée - transformation du champ date-
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return array $lesLignes tous les champs des lignes de frais hors forfait sous la forme
     * d'un tableau associatif
     */
    public function getLesFraisHorsForfait($idVisiteur, $mois)
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            "SELECT * FROM lignefraishorsforfait 
            WHERE lignefraishorsforfait.idvisiteur = :unIdVisiteur 
            AND lignefraishorsforfait.mois = :unMois
            AND lignefraishorsforfait.idetatligne != 'RE'"
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesLignes = $requetePrepare->fetchAll();
        for ($i = 0; $i < count($lesLignes); $i++) {
            $date = $lesLignes[$i]['date'];
            $lesLignes[$i]['date'] = dateAnglaisVersFrancais($date);
        }
        return $lesLignes;
    }

    /**
     * Retourne le nombre de justificatif d'un visiteur pour un mois donné
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return int le nombre entier de justificatifs
     */
    public function getNbjustificatifs($idVisiteur, $mois)
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT fichefrais.nbjustificatifs as nb FROM fichefrais '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        return $laLigne['nb'];
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais
     * au forfait concernées par les deux arguments
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return l'id, le libelle et la quantité sous la forme d'un tableau
     * associatif
     */
    public function getLesFraisForfait($idVisiteur, $mois)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            'SELECT fraisforfait.id as idfrais, '
            . 'fraisforfait.libelle as libelle, '
            . 'lignefraisforfait.quantite as quantite '
            . 'FROM lignefraisforfait '
            . 'INNER JOIN fraisforfait '
            . 'ON fraisforfait.id = lignefraisforfait.idfraisforfait '
            . 'WHERE lignefraisforfait.idvisiteur = :unIdVisiteur '
            . 'AND lignefraisforfait.mois = :unMois '
            . 'ORDER BY lignefraisforfait.idfraisforfait'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        return $requetePrepare->fetchAll();
    }

    /**
     * Retourne tous les id de la table FraisForfait
     *
     * @return un tableau associatif
     */
    public function getLesIdFrais()
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT fraisforfait.id as idfrais '
            . 'FROM fraisforfait ORDER BY fraisforfait.id'
        );
        $requetePrepare->execute();
        return $requetePrepare->fetchAll();
    }

    /**
     * Met à jour la table ligneFraisForfait
     * Met à jour la table ligneFraisForfait pour un visiteur et
     * un mois donné en enregistrant les nouveaux montants
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     * @param Array  $lesFrais   tableau associatif de clé idFrais et
     *                           de valeur la quantité pour ce frais
     *
     * @return null
     */
    public function majFraisForfait($idVisiteur, $mois, $lesFrais)
    {
        $lesCles = array_keys($lesFrais);
        foreach ($lesCles as $unIdFrais) {
            $qte = $lesFrais[$unIdFrais];
            $requetePrepare = PdoGSB::$monPdo->prepare(
                'UPDATE lignefraisforfait '
                . 'SET lignefraisforfait.quantite = :uneQte '
                . 'WHERE lignefraisforfait.idvisiteur = :unIdVisiteur '
                . 'AND lignefraisforfait.mois = :unMois '
                . 'AND lignefraisforfait.idfraisforfait = :idFrais'
            );
            $requetePrepare->bindParam(':uneQte', $qte, PDO::PARAM_INT);
            $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
            $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
            $requetePrepare->bindParam(':idFrais', $unIdFrais, PDO::PARAM_STR);
            $requetePrepare->execute();
        }
    }

    /**
     * Met à jour le nombre de justificatifs de la table ficheFrais
     * pour le mois et le visiteur concerné
     *
     * @param String  $idVisiteur      ID du visiteur
     * @param String  $mois            Mois sous la forme aaaamm
     * @param Integer $nbJustificatifs Nombre de justificatifs
     *
     * @return null
     */
    public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs)
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'UPDATE fichefrais '
            . 'SET nbjustificatifs = :unNbJustificatifs '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(
            ':unNbJustificatifs',
            $nbJustificatifs,
            PDO::PARAM_INT
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return vrai ou faux
     */
    public function estPremierFraisMois($idVisiteur, $mois)
    {
        $boolReturn = false;
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT fichefrais.mois FROM fichefrais '
            . 'WHERE fichefrais.mois = :unMois '
            . 'AND fichefrais.idvisiteur = :unIdVisiteur'
        );
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        if (!$requetePrepare->fetch()) {
            $boolReturn = true;
        }
        return $boolReturn;
    }

    /**
     * Retourne le dernier mois en cours d'un visiteur
     *
     * @param String $idVisiteur ID du visiteur
     *
     * @return string le mois sous la forme aaaamm
     */
    public function dernierMoisSaisi($idVisiteur)
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT MAX(mois) as dernierMois '
            . 'FROM fichefrais '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        $dernierMois = $laLigne['dernierMois'];
        return $dernierMois;
    }

    /**
     * Crée une nouvelle fiche de frais et les lignes de frais au forfait
     * pour un visiteur et un mois donnés
     *
     * Récupère le dernier mois en cours de traitement, met à 'CL' son champs
     * idEtat, crée une nouvelle fiche de frais avec un idEtat à 'CR' et crée
     * les lignes de frais forfait de quantités nulles
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return null
     */
    public function creeNouvellesLignesFrais($idVisiteur, $mois)
    {
        $dernierMois = $this->dernierMoisSaisi($idVisiteur);
        $laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur, $dernierMois);
        if ($laDerniereFiche['idEtat'] == 'CR') {
            $this->majEtatFicheFrais($idVisiteur, $dernierMois, 'CL');
        }
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'INSERT INTO fichefrais (idvisiteur,mois,nbjustificatifs,'
            . 'montantvalide,datemodif,idetat) '
            . "VALUES (:unIdVisiteur,:unMois,0,0,now(),'CR')"
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesIdFrais = $this->getLesIdFrais();
        foreach ($lesIdFrais as $unIdFrais) {
            $requetePrepare = PdoGsb::$monPdo->prepare(
                'INSERT INTO lignefraisforfait (idvisiteur,mois,'
                . 'idfraisforfait,quantite) '
                . 'VALUES(:unIdVisiteur, :unMois, :idFrais, 0)'
            );
            $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
            $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
            $requetePrepare->bindParam(
                ':idFrais',
                $unIdFrais['idfrais'],
                PDO::PARAM_STR
            );
            $requetePrepare->execute();
        }
    }

    /**
     * Crée un nouveau frais hors forfait pour un visiteur un mois donné
     * à partir des informations fournies en paramètre
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     * @param String $libelle    Libellé du frais
     * @param String $date       Date du frais au format français jj//mm/aaaa
     * @param Float  $montant    Montant du frais
     *
     * @return null
     */
    public function creeNouveauFraisHorsForfait(
        $idVisiteur,
        $mois,
        $libelle,
        $date,
        $montant)
    {
        $dateFr = dateFrancaisVersAnglais($date);
        $requetePrepare = PdoGSB::$monPdo->prepare(
            "INSERT INTO lignefraishorsforfait 
            VALUES (null, :unIdVisiteur,:unMois, :unLibelle, :uneDateFr,
            :unMontant, 'CR') "
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unLibelle', $libelle, PDO::PARAM_STR);
        $requetePrepare->bindParam(':uneDateFr', $dateFr, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMontant', $montant, PDO::PARAM_INT);
        $requetePrepare->execute();
    }

    /**
     * Supprime le frais hors forfait dont l'id est passé en argument
     *
     * @param String $idFrais ID du frais
     *
     * @return null
     */
    public function supprimerFraisHorsForfait($idFrais)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            'DELETE FROM lignefraishorsforfait '
            . 'WHERE lignefraishorsforfait.id = :unIdFrais'
        );
        $requetePrepare->bindParam(':unIdFrais', $idFrais, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Retourne les mois pour lesquel un visiteur a une fiche de frais
     *
     * @param String $idVisiteur ID du visiteur
     *
     * @return un tableau associatif de clé un mois -aaaamm- et de valeurs
     *         l'année et le mois correspondant
     */
    public function getLesMoisDisponibles($idVisiteur)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            'SELECT fichefrais.mois AS mois FROM fichefrais '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'ORDER BY fichefrais.mois desc'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesMois = array();
        while ($laLigne = $requetePrepare->fetch()) {
            $mois = $laLigne['mois'];
            $numAnnee = substr($mois, 0, 4);
            $numMois = substr($mois, 4, 2);
            $lesMois[] = array(
                'mois' => $mois,
                'numAnnee' => $numAnnee,
                'numMois' => $numMois
            );
        }
        return $lesMois;
    }

    /**
     * Retourne tous les mois pour lesquel il existe au moins une fiche de frais
     * 
     * @return un tableau associatif de clé un mois -aaaamm- et de valeurs
     *         l'année et le mois correspondant
     */
    public function getTousLesMois()
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT DISTINCT mois, idvisiteur '
            . 'FROM fichefrais ORDER BY mois DESC'
        );
        $requetePrepare->execute();
        $tousLesMois = array();
        while ($laLigne = $requetePrepare->fetch()) {
            $idVisiteur = $laLigne['idvisiteur'];
            $mois = $laLigne['mois'];
            $numAnnee = substr($mois, 0, 4);
            $numMois = substr($mois, 4, 2);
            $tousLesMois[] = array(
                'idvisiteur' =>$idVisiteur,
                'mois' => $mois,
                'numAnnee' => $numAnnee,
                'numMois' => $numMois
            );
        }
            return $tousLesMois;
    }


     /**
     * Retourne tous les mois pour lesquel il existe une fiche de frais cloturée (à valider)
     * 
     * @return un tableau associatif de clé un mois -aaaamm- et de valeurs
     *         l'année et le mois correspondant
     */
    public function getLesMoisFicheCloturee()
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            "SELECT DISTINCT mois, idvisiteur
            FROM fichefrais 
            WHERE fichefrais.idetat = 'CL'"
        );
        
        $requetePrepare->execute();
        $tousLesMois = array();
        while ($laLigne = $requetePrepare->fetch()) {
            $idVisiteur = $laLigne['idvisiteur'];
            $mois = $laLigne['mois'];
            
            $tousLesMois[] = array(
                'idvisiteur' =>$idVisiteur,
                'mois' => $mois,
                
            );
        }
            return $tousLesMois;
    }

    /**
     * Retourne les informations d'une fiche de frais d'un visiteur pour un
     * mois donné
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return un tableau avec des champs de jointure entre une fiche de frais
     *         et la ligne d'état
     */
    public function getLesInfosFicheFrais($idVisiteur, $mois)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            'SELECT fichefrais.idetat as idEtat, '
            . 'fichefrais.datemodif as dateModif,'
            . 'fichefrais.nbjustificatifs as nbJustificatifs, '
            . 'fichefrais.montantvalide as montantValide, '
            . 'etat.libelle as libEtat '
            . 'FROM fichefrais '
            . 'INNER JOIN etat ON fichefrais.idetat = etat.id '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        return $laLigne;
    }

    /**
     * Modifie l'état et la date de modification d'une fiche de frais.
     * Modifie le champ idEtat et met la date de modif à aujourd'hui.
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     * @param String $etat       Nouvel état de la fiche de frais
     *
     * @return null
     */
    public function majEtatFicheFrais($idVisiteur, $mois, $etat)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            'UPDATE ficheFrais '
            . 'SET idetat = :unEtat, datemodif = now() '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unEtat', $etat, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }
       
    /**
     * Retourne les visiteurs médicaux
     *
     *
     * @return un tableau associatif de clé un visiteur -id- et de valeurs
     *         le nom et le prénom correspondant
     */
    public function getLesVisiteurs()
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            'SELECT visiteur.id AS id, visiteur.nom AS nom,visiteur.prenom AS prenom
             FROM visiteur 
             where visiteur.idTypeVisiteur = "MED" 
             ORDER BY nom '
        );
        
        $requetePrepare->execute();       
        $lesVisiteurs = array();
        while ($laLigne = $requetePrepare->fetch()) {
            $id = $laLigne['id'];
            $nom = $laLigne['nom'];
            $prenom = $laLigne['prenom'];
            $lesVisiteurs[] = array(
                'id' => $id,
                'nom' => $nom,
                'prenom' => $prenom
            );
        }
        return $lesVisiteurs;
    }
    
     /**
     * Clot toutes les fiches de frais du mois passé en paramètre
     *
     * @param String $mois       Mois sous la forme aaaamm
     *
     */
    public function ClotToutesFichesFrais($moisPrecedent)
    {
       $requetePrepare = PdoGsb::$monPdo->prepare(
           'SELECT idvisiteur, mois, idetat
            FROM fichefrais 
            where fichefrais.mois = :moisPrecedent' 
            );
         $requetePrepare->bindParam(':moisPrecedent', $moisPrecedent, PDO::PARAM_STR);
         $requetePrepare->execute();
         $lesFichesDuMoisPrecedent = array();
         while ($laLigne = $requetePrepare->fetch()) {
                $idVisiteur = $laLigne['idvisiteur'];
                $mois = $laLigne['mois'];
                $idetat = $laLigne['idetat'];
                $lesFichesDuMoisPrecedent[] = array(
                    'idvisiteur'=> $idVisiteur,
                    'mois' => $mois,
                    'idetat'=> $idetat
                );
                if ($idetat == 'CR') {
                    $this->majEtatFicheFrais($idVisiteur, $moisPrecedent, 'CL');
                }
            }
    }

     /**
     * refus d'un frais hors forfait
     * Ajoute 'Refusé : ' devant le libellé dont l'id est passé en paramètre
     * passe l'état de la ligne en refusée : RF
     *
     * @param String $idLigne       numéro id du frais hors forfait
     *
     */
    public function refuseLigneFrais($idLigne)
    {
       $requetePrepare = PdoGsb::$monPdo->prepare(
        "UPDATE  lignefraishorsforfait
         SET libelle = CONCAT ('REFUSE : ', (select  lignefraishorsforfait.libelle 
                                            FROM  lignefraishorsforfait 
                                            WHERE  lignefraishorsforfait.id = :idLigne)),
             idetatligne = 'RF'
         where lignefraishorsforfait.id = :idLigne"
         );
       $requetePrepare->bindParam(':idLigne', $idLigne, PDO::PARAM_STR);
       $requetePrepare->execute();
    }

     /**
     * met à jour les frais hors forfait
     *
     * @param String $idFrais    numéro id du frais hors forfait
     * @param String $libelle    nouveau libellé du frais hors forfait
     * @param String $date       nouvelle date sous la forme aaaamm
     * @param String $montant    nouveau montant du frais hors forfait
     *
     */
    public function majFraisHorsForfait(
         $idFrais,
         $libelle,
         $date,
         $montant)
    {
             $dateFr = dateFrancaisVersAnglais($date);
             $requetePrepare = PdoGSB::$monPdo->prepare(
                 'UPDATE lignefraishorsforfait 
                 SET libelle = :unLibelle, lignefraishorsforfait.date = :uneDateFr, montant = :unMontant 
                 WHERE lignefraishorsforfait.id = :unIdFrais'
                 );
             $requetePrepare->bindParam(':unIdFrais', $idFrais, PDO::PARAM_STR);
             $requetePrepare->bindParam(':unLibelle', $libelle, PDO::PARAM_STR);
             $requetePrepare->bindParam(':uneDateFr', $dateFr, PDO::PARAM_STR);
             $requetePrepare->bindParam(':unMontant', $montant, PDO::PARAM_INT);
             $requetePrepare->execute();
    }

     /**
     * reporte un frais hors forfait (pour justificatif non reçu) au mois suivant
     * passe l'état de la ligne en reportée : RE
     *
     * @param String $idLigne       numéro id du frais hors forfait
     *
     */
    public function reporteLigneFrais($idLigne)
    {
        $requetePrepare = PdoGsb::$monPdo->prepare(
            "UPDATE  lignefraishorsforfait
             SET  idetatligne = 'RE'
             where lignefraishorsforfait.id = :idLigne"
             );
           $requetePrepare->bindParam(':idLigne', $idLigne, PDO::PARAM_STR);
           $requetePrepare->execute();
    }

    public function valideLigneFraisHorsForfait($idVisiteurSelectionne,$leMoisSelectionne)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            "UPDATE lignefraishorsforfait 
            SET lignefraishorsforfait.idetatligne = 'VA'
            WHERE lignefraishorsforfait.idvisiteur = :unIdVisiteur
            AND lignefraishorsforfait.mois = :unMois
            AND lignefraishorsforfait.idetatligne = 'CR'"
            );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteurSelectionne, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $leMoisSelectionne, PDO::PARAM_STR);
        $requetePrepare->execute();
    }


    public function gettotalFraisHorsForfait($idVisiteurSelectionne,$leMoisSelectionne)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
        "SELECT sum(montant) AS 'montantTotal'
         FROM  lignefraishorsforfait
         WHERE idvisiteur = :unIdVisiteur
         AND mois = :unMois
         AND idetatligne = 'VA'"
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteurSelectionne, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $leMoisSelectionne, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        return $laLigne['montantTotal'];

    }

    public function getTotalFraisForfait($idVisiteurSelectionne,$leMoisSelectionne)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            "SELECT  sum(lignefraisforfait.quantite* fraisforfait.montant) AS 'montantTotal' 
            FROM lignefraisforfait 
            inner join fraisforfait 
            on lignefraisforfait.idfraisforfait = fraisforfait.id 
            where idvisiteur = :unIdVisiteur 
            AND mois = :unMois"
        );

        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteurSelectionne, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $leMoisSelectionne, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        return $laLigne['montantTotal'];


    }


    public function majMontantFraisValide($idVisiteurSelectionne,$leMoisSelectionne)
    {
        $fraisForfait = $this->getTotalFraisForfait($idVisiteurSelectionne,$leMoisSelectionne);
        $fraisHorsForfait = $this->gettotalFraisHorsForfait($idVisiteurSelectionne,$leMoisSelectionne);
        $totalFrais = $fraisForfait + $fraisHorsForfait;

        $requetePrepare = PdoGSB::$monPdo->prepare(
            " UPDATE fichefrais 
            SET montantvalide = $totalFrais 
            where idvisiteur = :unIdVisiteur 
            AND mois = :unMois"
        );

        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteurSelectionne, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $leMoisSelectionne, PDO::PARAM_STR);
        $requetePrepare->execute();
        
    }

    public function ficheValideExiste ($idVisiteur, $mois)
    {
        $boolReturn = false;
        $requetePrepare = PdoGSB::$monPdo->prepare(
            "SELECT fichefrais.idvisiteur 
            FROM  fichefrais
            where idvisiteur = :unIdVisiteur 
            AND mois = :unMois
            AND (idetat = 'VA'
            OR idetat = 'MP'
            OR idetat = 'RB')"
        );
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
    $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
    $requetePrepare->execute();
    if ($requetePrepare->fetch()) {
        $boolReturn = true;
    }
    return $boolReturn;

    }
    
    public function getEtatFiche($idVisiteur, $mois)
    {
        $requetePrepare = PdoGSB::$monPdo->prepare(
            "SELECT fichefrais.idetat as idEtat
            FROM  fichefrais
            where idvisiteur = :unIdVisiteur
            AND mois = :unMois "

        );
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        
        $laLigne = $requetePrepare->fetch();
        return $laLigne['idEtat'];
    }
    


}