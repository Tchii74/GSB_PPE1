<?php
/**
 * Vue Liste des visiteurs et des mois
 *
 * PHP Version 8
 *
 * @category  PPE
 * @package   GSB
 * @author    Réseau CERTA <contact@reseaucerta.org>
 * @author    Audrey Laval
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 */
?>
<div class="row">
    <div class ="span3">
        <div class="col-md-4">
            <div class = "control-group">
                <form action="index.php?uc=validerFrais&action=voirDetailFrais" 
                    method="post" role="form">
                    <h2 id="couleurOrange">Valider les fiches de frais</h2>
                    <?php
                    $lesMoisParVisiteur=array();
              
                    foreach ($lesMois as $unMois)
                    {
                        $lesMoisParVisiteur[$unMois['idvisiteur']][$unMois['mois']] = $unMois['mois'];
                    }?>    
                    </select>
                        <div class="form-group">
                        <label for="Visiteur">Choisir le visiteur : </label>
                            <select id = "lstVisiteur" name="lstVisiteur" class="form-control" onload = "myfunction()" onchange="myfunction()">
                                <?php
                                foreach ($lesVisiteurs as $unVisiteur) {
                                $id = $unVisiteur['id'];
                                $nom = $unVisiteur['nom'];
                                $prenom = $unVisiteur['prenom'];
                                    if ($unVisiteur['id'] == $visiteurASelectionner) {?>
                                        <option selected value="<?php echo $id?>">
                                            <?php echo $nom . ' ' . $prenom ?> </option>
                                            <?php
                                        } else {
                                            ?>
                                            <option value="<?php echo $id ?>">
                                            <?php echo $nom . ' ' . $prenom ?> </option>
                                            <?php
                                        }
                                    }?>    
                            </select>
                        </div>
                    </select>
                    <div class ="span3">
                        <div class = "control-group">
                        <label for="lstMois">Choisir le mois : </label>
                            <?php
                            $tousLesId =array();
                            
                            foreach ($visiteurToutValider as $unIdVisiteur):
                            ?>
                            <p id = "<?= $unIdVisiteur; ?>" name = "<?= $unIdVisiteur; ?>" class =" mois" style = "<?php if ($unIdVisiteur == $visiteurASelectionner)
                                                                 {
                                                                    echo "visibility:visible; display:block";                                                                  
                                                                }
                                                                else{
                                                                    echo "visibility:hidden; display:none" ;

                                                                } ?>"
                                                                >Pas de fiche de frais à valider pour ce visiteur</p>


                            <?php endforeach;
                            

                            foreach ($lesMoisParVisiteur as $key =>$moisduVisiteur): 
                            $tousLesId[]= "$key";
                            $id = $key;
                             ?>                                
                            <select id = "<?= $id; ?>" name = "<?= $id; ?>" class ="form-control mois" style=  "<?php if ($id == $visiteurASelectionner)
                                                                 {
                                                                    echo "visibility:visible; display:block";
                                                                 }
                                                                else{
                                                                    echo "visibility:hidden; display:none" ;
                                                                    } ?>">
                                <?php foreach ($moisduVisiteur as $moisparVisiteur):
                                    $numAnnee = substr($moisparVisiteur, 0, 4);
                                    $numMois = substr($moisparVisiteur, 4, 2);
                                    ?>
                                <option  value="<?=$moisparVisiteur;?>"><?= $numMois . '-' . $numAnnee ?></option>
                                <?php endforeach?>
                            </select>
                            <?php endforeach?>
                        </div>                        
                    </div>
                  
                    <input id="ok" type="submit" value="Valider" class="btn btn-success" 
                    role="button">
                </form>
            </div>
        </div>
    </div>
</div>
