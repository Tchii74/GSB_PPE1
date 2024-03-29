<?php
/**
 * Vue Liste des frais hors forfait Comptable
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
<hr>
<div class="row">
    <div class="panel panel-info" id="panel-primary_perso">
        <div class="panel-heading" id ="panel-heading_perso">Descriptif des éléments hors forfait</div>
        <table class="table table-bordered table-responsive">
            <thead>
                <tr>
                    <th class="date" id="th_perso">Date</th>
                    <th class="libelle" id="th_perso">Libellé</th>  
                    <th class="montant" id="th_perso">Montant</th>  
                    <th class="action" id="th_perso">&nbsp;</th> 
                </tr>
            </thead>  
            <tbody>
              <?php
            foreach ($lesFraisHorsForfait as $unFraisHorsForfait) {
                ?>
                <div class="form-group">
                 <form method="post" 
              action="index.php?uc=validerFrais&action=corrigerFraisHorsForfait" 
              role="form">
              <?php
                $libelle = htmlspecialchars($unFraisHorsForfait['libelle']);
                $date = $unFraisHorsForfait['date'];
                $montant = $unFraisHorsForfait['montant'];
                $id = $unFraisHorsForfait['id']; ?> 
                <tr>
                    <td> <input type="text" name="dateFraisHorsForfait" value="<?php echo $date ?>"></td>
                    <td> <input type="text" name="libelleFraisHorsForfait" value="<?php echo $libelle ?>"></td>
                    <td><input type="text" name="montantFraisHorsForfait" value="<?php echo $montant ?>"></td>
                    <input type="hidden"
                           name="idFraisHorsForfait" 
                           id="idFraisHorsForfait"
                           value="<?php echo $id?>">

                    <td> <input id="ok" type="submit" value="Corriger" class="btn btn-success" 
                   role="button">
                   <input id="annuler" type="reset" value="Réinitialiser" class="btn btn-danger" 
                   role="button">
                   <input id="reporter" type="submit" name ="Reporter" value="Reporter" class="btn btn-success" 
                   role="button">
                   <input id="refuser" type="submit" name ="Refuser" value="Refuser" class="btn btn-danger" 
                   role="button"></td>
                </tr>
                <input type="hidden"
                 name="idVisiteurSelectionne" 
                 id="idVisiteurSelectionne"
                 value="<?php echo $idVisiteurSelectionne?>">
                 <input type="hidden"
                 name="leMoisSelectionne" 
                 id="leMoisSelectionne"
                 value="<?php echo $leMoisSelectionne?>">
                 </form>
                </div>
                <?php  
            }
            ?>
</div>
</div>
            </tbody>  
        </table>
    </div>
</div>
<div>
<div class="form-group">
                 <form method="post" 
              action="index.php?uc=validerFrais&action=corrigerNbJustificatifs" 
              role="form">
                <label for="nbJustificatifs">Nombre de justificatifs : </label>
                <input type="text" 
                id="nbJustificatifs" 
                name = "nbJustificatifs"
                size="1" 
                value="<?php echo $nbJustificatifs ?>">
                <input id="ok" type="submit" value="Valider" class="btn btn-success" 
                   role="button">
            <input id="annuler" type="reset" value="Réinitialiser" class="btn btn-danger" 
                   role="button">

                   <input type="hidden"
                 name="idVisiteurSelectionne" 
                 id="idVisiteurSelectionne"
                 value="<?php echo $idVisiteurSelectionne?>">
                 <input type="hidden"
                 name="leMoisSelectionne" 
                 id="leMoisSelectionne"
                 value="<?php echo $leMoisSelectionne?>">
                 </form>
</div>
</div>

   